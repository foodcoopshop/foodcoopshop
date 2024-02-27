<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Services\PdfWriter\MyMemberCardPdfWriterService;
use App\Services\PdfWriter\MemberCardsPdfWriterService;
use App\Services\PdfWriter\TermsOfUsePdfWriterService;
use App\Mailer\AppMailer;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Utility\Hash;
use Admin\Traits\UploadTrait;
use App\Controller\Traits\RenewAuthSessionTrait;
use App\Model\Table\OrderDetailsTable;
use App\Model\Table\PaymentsTable;
use Cake\View\JsonView;
use App\Services\SanitizeService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class CustomersController extends AdminAppController
{

    protected OrderDetailsTable $OrderDetail;
    protected PaymentsTable $Payment;

    use UploadTrait;
    use RenewAuthSessionTrait;
    
    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    public function ajaxGetCreditBalance($customerId)
    {
        $this->request = $this->request->withParam('_ext', 'json');
        $customersTable = $this->getTableLocator()->get('Customers');
        $creditBalance = $customersTable->getCreditBalance($customerId);

        $this->set([
            'status' => 1,
            'creditBalance' => '<span class="'.($creditBalance < 0 ? 'negative' : '').'">' . Configure::read('app.numberHelper')->formatAsCurrency($creditBalance) . '</span>',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'creditBalance']);
    }

    public function ajaxGetCustomersForDropdown($includeManufacturers, $includeOfflineCustomers = true)
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $includeManufacturers = (bool) $includeManufacturers;
        $includeOfflineCustomers = (bool) $includeOfflineCustomers;

        $conditions = [];
        if ($this->identity->isCustomer()) {
            $conditions = ['Customers.id_customer' => $this->identity->getId()];
        }

        if ($this->identity->isSuperadmin()) {
            $includeOfflineCustomers = true;
        }

        $customerTable = $this->getTableLocator()->get('Customers');
        $customers = $customerTable->getForDropdown($includeManufacturers, $includeOfflineCustomers, $conditions);
        $customersForDropdown = [];
        foreach ($customers as $key => $ps) {
            $customersForDropdown[] = '<optgroup label="' . $key . '">';
            foreach ($ps as $pId => $p) {
                $customersForDropdown[] = '<option value="' . $pId . '">' . $p . '</option>';
            }
            $customersForDropdown[] = '</optgroup>';
        }

        $emptyElement = ['<option value="">' . __d('admin', 'all_members') . '</option>'];
        $customersForDropdown = array_merge($emptyElement, $customersForDropdown);

        $this->set([
            'status' => 1,
            'dropdownData' => join('', $customersForDropdown),
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'dropdownData']);
    }

    public function generateMyMemberCard()
    {
        $customerId = $this->identity->getId();
        $pdfWriter = new MyMemberCardPdfWriterService();
        $customers = $pdfWriter->getMemberCardCustomerData($customerId);
        $pdfWriter->setFilename(__d('admin', 'Member_card') . ' ' . $customers->toArray()[0]->name.'.pdf');
        $pdfWriter->setData([
            'customers' => $customers,
        ]);
        die($pdfWriter->writeInline());
    }

    public function generateMemberCards()
    {
        $customerIds = h($this->getRequest()->getQuery('customerIds'));
        $customerIds = explode(',', $customerIds);
        $pdfWriter = new MemberCardsPdfWriterService();
        $pdfWriter->setFilename(__d('admin', 'Members') . ' ' . Configure::read('appDb.FCS_APP_NAME').'.pdf');
        $pdfWriter->setData([
            'customers' => $pdfWriter->getMemberCardCustomerData($customerIds),
        ]);
        die($pdfWriter->writeInline());
    }

    public function ajaxEditGroup()
    {
        $customerId = (int) $this->getRequest()->getData('customerId');
        $groupId = (int) $this->getRequest()->getData('groupId');

        $this->request = $this->request->withParam('_ext', 'json');

        if (! in_array($groupId, array_keys(Configure::read('app.htmlHelper')->getAuthDependentGroups($this->identity->getGroupId())))) {
            $message = 'user group not allowed: ' . $groupId;
            $this->log($message);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $this->Customer = $this->getTableLocator()->get('Customers');
        $oldCustomer = $this->Customer->find('all', conditions: [
            'Customers.id_customer' => $customerId
        ])->first();

        // eg. member is not allowed to change groupId of admin, not even to set a groupid he would be allowed to (member)
        if ($this->identity->getGroupId() < $oldCustomer->id_default_group) {
            $message = 'logged user has lower groupId than the user he wants to edit: customerId: ' . $oldCustomer->id_customer . ', groupId: ' . $oldCustomer->id_default_group;
            $this->log($message);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $this->Customer->save(
            $this->Customer->patchEntity(
                $oldCustomer,
                [
                    'id_default_group' => $groupId
                ]
                )
            );

        $messageString = __d('admin', 'The_group_of_the_member_{0}_was_changed_to_{1}.', [
            '<b>' . $oldCustomer->name . '</b>',
            '<b>' . Configure::read('app.htmlHelper')->getGroupName($groupId) . '</b>'
        ]);
        $this->Flash->success($messageString);
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('customer_group_changed', $this->identity->getId(), $customerId, 'customers', $messageString);

        $this->set([
            'status' => 1,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status']);
    }

    public function changePassword()
    {
        $this->set('title_for_layout', __d('admin', 'Change_password'));

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', conditions: [
            'Customers.id_customer' => $this->identity->getId()
        ])->first();

        if (empty($this->getRequest()->getData())) {
            $this->set('customer', $customer);
            return;
        }

        $customer = $this->Customer->patchEntity(
            $customer,
            $this->getRequest()->getData(),
            [
                'validate' => 'changePassword'
            ]
        );

        if ($customer->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('customer', $customer);
        } else {
            $ph = new DefaultPasswordHasher();
            $this->Customer->save(
                $this->Customer->patchEntity(
                    $customer,
                    [
                        'passwd' => $ph->hash($this->getRequest()->getData('Customers.passwd_1'))
                    ]
                    )
                );

            if ($this->identity->isManufacturer()) {
                $message = __d('admin', 'The_manufacturer_{0}_has_changed_his_password.', ['<b>' . $this->identity->getManufacturerName() . '</b>']);
                $actionLogType = 'manufacturer_password_changed';
                $actionLogId = $this->identity->getManufacturerId();
                $actionLogModel = 'manufacturers';
            } else {
                $message = __d('admin', '{0}_has_changed_the_password.', ['<b>' . $this->identity->name . '</b>']);
                $actionLogType = 'customer_password_changed';
                $actionLogId = $this->identity->getId();
                $actionLogModel = 'customers';
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave($actionLogType, $this->identity->getId(), $actionLogId, $actionLogModel, $message);
            $this->Flash->success(__d('admin', 'Your_new_password_has_been_saved_successfully.'));
            $this->redirect($this->referer());
        }

        $this->set('customer', $customer);
    }


    public function delete(int $customerId)
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $isOwnProfile = $this->identity->getId() == $customerId;

        if (!$this->identity->isSuperadmin()) {
            throw new ForbiddenException('deleting user ' . $customerId . 'denied');
        }

        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->Payment = $this->getTableLocator()->get('Payments');

        try {

            $customer = $this->Customer->find('all',
            conditions: [
                'Customers.id_customer' => $customerId
            ],
            contain: [
                'Manufacturers',
                'ActiveOrderDetails'
            ])->first();

            if (empty($customer)) {
                throw new RecordNotFoundException('customer ' . $customerId . ' not found');
            }

            $errors = [];

            if (Configure::read('app.applyOrdersNotYetBilledCheckOnDeletingCustomers')) {
                $openOrderDetails = count($customer->active_order_details);
                if ($openOrderDetails > 0) {
                    $errors[] = __d('admin', 'Amount_of_orders_where_the_invoice_has_not_been_sent_yet_to_the_manufacturer:'). ' '. $openOrderDetails . '.';
                }
            }

            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $creditBalance = $this->Customer->getCreditBalance($customerId);
                if ($creditBalance != 0) {
                    $errors[] = __d('admin', 'The_credit_is') . ' ' . Configure::read('app.numberHelper')->formatAsCurrency($creditBalance) . '. ' . __d('admin', 'It_needs_to_be_zero.');
                }
            }

            if (Configure::read('app.applyPaymentsOkCheckOnDeletingCustomers')) {
                $notApprovedPaymentsCount = $this->Payment->find('all', conditions: [
                    'id_customer' => $customerId,
                    'approval < ' => APP_ON,
                    'status' => APP_ON,
                    'type' => 'product',
                    'DATE_FORMAT(date_add, \'%Y\') >= DATE_FORMAT(NOW(), \'%Y\') - 2' // check only last full 2 years (eg. payment of 02.02.2018 is checked on 12.11.2020)
                ])->count();
                if ($notApprovedPaymentsCount > 0) {
                    $errors[] = __d('admin', 'Amount_of_not_approved_payments_within_the_last_2_years:'). ' '. $notApprovedPaymentsCount . '.';
                }
            }

            if (!empty($customer->manufacturers)) {
                $manufacturerNames = [];
                foreach($customer->manufacturers as $manufacturer) {
                    $manufacturerNames[] = $manufacturer->name;
                }
                $errors[] = __d('admin', 'The_member_is_still_associated_to_the_following_manufacturers:') . ' ' . join(', ', $manufacturerNames);
            }

            if (!empty($errors)) {
                $errorString = '<ul><li>' . join('</li><li>', $errors) . '</li></ul>';
                throw new \Exception($errorString);
            }
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $this->Customer->deleteAll(['id_customer' => $customerId]);
        $this->Customer->AddressCustomers->deleteAll(['id_customer' => $customerId]);
        $this->Customer->Feedbacks->deleteAll(['customer_id' => $customerId]);

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->removeCustomerNameFromAllActionLogs($customer->firstname . ' ' . $customer->lastname);
        $this->ActionLog->removeCustomerNameFromAllActionLogs($customer->lastname . ' ' . $customer->firstname);
        $this->ActionLog->removeCustomerEmailFromAllActionLogs($customer->email);

        $this->deleteUploadedImage($customerId, Configure::read('app.htmlHelper')->getCustomerThumbsPath());

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        if ($isOwnProfile) {
            $message = __d('admin', 'Your_account_has_been_deleted_successfully.');
            $redirectUrl = Configure::read('app.slugHelper')->getHome();
        } else {
            $message = __d('admin', '{0}_has_deleted_an_account.', [$this->identity->name]);
            $redirectUrl = $this->getRequest()->getData('referer');
        }
        $this->ActionLog->customSave('customer_deleted', $this->identity->getId(), $customer->id_customer, 'customers', $message);
        $this->Flash->success($message);

        if ($isOwnProfile) {
            $this->identity->logout();
        }

        $this->set([
            'status' => 1,
            'msg' => 'ok',
            'redirectUrl' => $redirectUrl
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'redirectUrl']);

    }

    public function profile()
    {
        $this->set('title_for_layout', __d('admin', 'Edit_my_profile'));
        $this->_processForm($this->identity->getId());
        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($customerId)
    {
        if ($customerId === null) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_profile'));
        $this->_processForm($customerId);
        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    private function _processForm($customerId)
    {

        $isOwnProfile = $this->identity->getId() == $customerId;
        $this->set('isOwnProfile', $isOwnProfile);

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all',
        conditions: [
            'Customers.id_customer' => $customerId
        ],
        contain: [
            'AddressCustomers'
        ])->first();

        if (empty($customer)) {
            throw new NotFoundException('customer not found');
        }

        $this->setFormReferer();

        if (empty($this->getRequest()->getData())) {
            $this->set('customer', $customer);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $this->setRequest($this->getRequest()->withData('Customers.email', $this->getRequest()->getData('Customers.address_customer.email')));
        $this->setRequest($this->getRequest()->withData('Customers.address_customer.firstname', $this->getRequest()->getData('Customers.firstname')));
        $this->setRequest($this->getRequest()->withData('Customers.address_customer.lastname', $this->getRequest()->getData('Customers.lastname')));

        $this->setRequest($this->getRequest()->withoutData('Customers.active'));
        $this->setRequest($this->getRequest()->withoutData('Customers.id_default_group'));

        $customer = $this->Customer->patchEntity(
            $customer,
            $this->getRequest()->getData(),
            [
                'validate' => 'edit',
                'associated' => [
                    'AddressCustomers'
                ]
            ]
            );

        if ($customer->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('customer', $customer);
            $this->render('edit');
        } else {
            $this->Customer->save(
                $customer,
                [
                    'associated' => [
                        'AddressCustomers'
                    ]
                ]
                );

            if (!empty($this->getRequest()->getData('Customers.tmp_image'))) {
                $this->saveUploadedImage($customer->id_customer, $this->getRequest()->getData('Customers.tmp_image'), Configure::read('app.htmlHelper')->getCustomerThumbsPath(), Configure::read('app.customerImageSizes'));
            }

            if (!empty($this->getRequest()->getData('Customers.delete_image'))) {
                $this->deleteUploadedImage($customer->id_customer, Configure::read('app.htmlHelper')->getCustomerThumbsPath());
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            if ($isOwnProfile) {
                $message = __d('admin', 'Your_profile_was_changed.');
            } else {
                $message = __d('admin', 'The_profile_of_{0}_was_changed.', ['<b>' . $customer->name . '</b>']);
            }
            $this->ActionLog->customSave('customer_profile_changed', $this->identity->getId(), $customer->id_customer, 'customers', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $customer->id_customer);

            if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getCustomerProfile()) {
                $this->renewAuthSession();
            }

            $this->redirect($this->getPreparedReferer());
        }

        $this->set('customer', $customer);
    }

    private function generateTermsOfUsePdf()
    {
        $pdfWriter = new TermsOfUsePdfWriterService();
        return $pdfWriter->writeAttachment();
    }

    public function changeStatus($customerId, $status, $sendEmail)
    {
        if (! in_array($status, [
            APP_OFF,
            APP_ON
        ])) {
            throw new RecordNotFoundException('status needs to be 0 or 1');
        }

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all',
        conditions: [
            'Customers.id_customer' => $customerId
        ],
        contain: [
            'AddressCustomers'
        ])->first();

        $customer->active = $status;
        $this->Customer->save($customer);

        $message = __d('admin', 'The_member_{0}_has_been_deactivated_succesfully.', ['<b>' . $customer->name . '</b>']);
        $actionLogType = 'customer_set_inactive';
        if ($status) {
            $message = __d('admin', 'The_member_{0}_has_been_activated_succesfully.', ['<b>' . $customer->name . '</b>']);
            $actionLogType = 'customer_set_active';
        }

        if ($sendEmail) {
            $newPassword = $this->Customer->setNewPassword($customer->id_customer);

            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('customer_activated');
            $email->setTo($customer->email)
            ->setSubject(__d('admin', 'Your_account_was_activated'))
            ->setViewVars([
                'identity' => $this->identity,
                'data' => $customer,
                'newsletterCustomer' => $customer,
                'newPassword' => $newPassword,
            ]);

            if (Configure::read('app.termsOfUseEnabled')) {
                $email->addAttachments([__d('admin', 'Filename_Terms-of-use').'.pdf' => ['data' => $this->generateTermsOfUsePdf(), 'mimetype' => 'application/pdf']]);
            }
            $email->addToQueue();

            $message = __d('admin', 'The_member_{0}_has_been_activated_succesfully_and_the_member_was_notified_by_email.', ['<b>' . $customer->name . '</b>']);
        }

        $this->Flash->success($message);

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave($actionLogType, $this->identity->getId(), $customerId, 'customer', $message);

        $this->redirect($this->referer());
    }

    public function editComment()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $customerId = $this->getRequest()->getData('customerId');
        $customerComment = htmlspecialchars_decode($this->getRequest()->getData('customerComment'));

        $this->Customer = $this->getTableLocator()->get('Customers');
        $oldCustomer = $this->Customer->find('all',
        conditions: [
            'Customers.id_customer' => $customerId
        ],
        contain: [
            'AddressCustomers'
        ])->first();

        $this->Customer->AddressCustomers->save(
            $this->Customer->AddressCustomers->patchEntity(
                $oldCustomer->address_customer,
                [
                    'comment' => $customerComment
                ]
                )
            );

        $this->Flash->success(__d('admin', 'The_comment_was_changed_successfully.'));

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('customer_comment_changed', $this->identity->getId(), $customerId, 'customers', __d('admin', 'The_comment_of_the_member_{0}_was_changed:', ['<b>' . $oldCustomer->name . '</b>']) . ' <div class="changed">' . $customerComment . ' </div>');

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    public function creditBalanceSum()
    {
        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getLastDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $this->Payment = $this->getTableLocator()->get('Payments');
        $customerTable = $this->getTableLocator()->get('Customers');

        $paymentProductDelta = $customerTable->getProductBalanceForCustomers(APP_ON);
        $paymentDepositDelta = $customerTable->getDepositBalanceForCustomers(APP_ON);
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_credits_of_activated_members'),
            'count' => count($customerTable->getCustomerIdsWithStatus(APP_ON)),
            'credit_balance' => $paymentProductDelta + $paymentDepositDelta,
            'payment_deposit_delta' => $paymentDepositDelta * -1
        ];

        $paymentProductDelta = $customerTable->getProductBalanceForCustomers(APP_OFF);
        $paymentDepositDelta = $customerTable->getDepositBalanceForCustomers(APP_OFF);
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_credits_of_deactivated_members'),
            'count' => count($customerTable->getCustomerIdsWithStatus(APP_OFF)),
            'credit_balance' => $paymentProductDelta + $paymentDepositDelta,
            'payment_deposit_delta' => $paymentDepositDelta * -1
        ];

        $paymentProductDelta = $customerTable->getProductBalanceForDeletedCustomers();
        $paymentDepositDelta = $customerTable->getDepositBalanceForDeletedCustomers();
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_credits_of_deleted_members'),
            'count' => 0,
            'credit_balance' => $paymentProductDelta + $paymentDepositDelta,
            'payment_deposit_delta' => ($paymentDepositDelta * -1) + 0,
        ];

        $paymentDepositDelta = $this->Payment->getManufacturerDepositMoneySum();
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_deposit_compensation_payments_for_manufactures'),
            'count' => 0,
            'credit_balance' => 0,
            'payment_deposit_delta' => ($paymentDepositDelta * -1) + 0,
        ];

        $this->set('customers', $customers);

        $sums = [
            'credit_balance' => 0,
            'deposit_delta' => 0,
            'product_delta' => 0,
        ];
        foreach($customers as $customer) {
            $sums['credit_balance'] += $customer['credit_balance'] ?? 0;
            $sums['deposit_delta'] += $customer['payment_deposit_delta'] ?? 0;
            $sums['product_delta'] += $customer['payment_product_delta'] ?? 0;
        }

        $this->set('sums', $sums);

        $this->set('title_for_layout', __d('admin', 'Credit_and_deposit_balance'));
    }

    public function index()
    {
        $active = 1; // default value
        if (in_array('active', array_keys($this->getRequest()->getQueryParams()))) {
            $active = h($this->getRequest()->getQuery('active'));
        }
        $this->set('active', $active);

        $year = h($this->getRequest()->getQuery('year'));
        if (!in_array('year', array_keys($this->getRequest()->getQueryParams()))) {
            $year = date('Y');
        }
        $this->set('year', $year);

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');

        $firstOrderYear = $this->OrderDetail->getFirstOrderYear();
        $this->set('firstOrderYear', $firstOrderYear);

        $lastOrderYear = $this->OrderDetail->getLastOrderYear();
        $this->set('lastOrderYear', $lastOrderYear);

        $years = null;
        if ($lastOrderYear !== false && $firstOrderYear !== false) {
            $years = Configure::read('app.timeHelper')->getAllYearsUntilThisYear($lastOrderYear, $firstOrderYear, __d('admin', 'Member_fee') . ' ');
        }
        $this->set('years', $years);

        $conditions = [];
        if ($active != 'all') {
            $conditions['Customers.active'] = $active;
        }

        if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
            $newsletter = h($this->getRequest()->getQuery('newsletter'));
            if (!in_array('newsletter', array_keys($this->getRequest()->getQueryParams()))) {
                $newsletter = '';
            }
            $this->set('newsletter', $newsletter);
            if ($newsletter != '') {
                $conditions['Customers.newsletter_enabled'] = $newsletter;
            }
        }

        $this->Customer = $this->getTableLocator()->get('Customers');

        $conditions[] = $this->Customer->getConditionToExcludeHostingUser();

        $this->Customer->dropManufacturersInNextFind();

        $contain = [
            'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
        ];

        if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
            $contain[] = 'Feedbacks';
        }

        $query = $this->Customer->find('all',
        conditions: $conditions,
        contain: $contain);
        $query = $this->Customer->addCustomersNameForOrderSelect($query);
        $query->select($this->Customer);
        $query->select($this->Customer->AddressCustomers);
        if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
            $query->select($this->Customer->Feedbacks);
        }

        $query->select([
            'credit_balance' => 'Customers.id_customer', // add fake field to make custom sort icon work and avoid "Column not found: 1054 Unknown column"
            'last_pickup_day' => 'Customers.id_customer',
            'member_fee' => 'Customers.id_customer',
        ]);
        $query->select($this->Customer->AddressCustomers);
        $customers = $this->paginate($query, [
            'sortableFields' => [
                'CustomerNameForOrder',
                'Customers.id_default_group',
                'Customers.id_customer',
                'Customers.email',
                'Customers.active',
                'Customers.email_order_reminder_enabled',
                'Customers.check_credit_reminder_enabled',
                'Customers.date_add',
                'Customers.newsletter_enabled',
                'Feedbacks.modified',
                'credit_balance',
                'member_fee',
                'last_pickup_day',
            ],
            'order' => $this->Customer->getCustomerOrderClause(),
        ]);

        $i = 0;
        $this->Payment = $this->getTableLocator()->get('Payments');

        foreach ($customers as $customer) {
            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $customer->credit_balance = $this->Customer->getCreditBalance($customer->id_customer);
            }
            $customer->different_pickup_day_count = $this->OrderDetail->getDifferentPickupDayCountByCustomerId($customer->id_customer);
            $customer->last_pickup_day = $this->OrderDetail->getLastPickupDay($customer->id_customer);
            $customer->last_pickup_day_sort = '';
            if (!is_null($customer->last_pickup_day)) {
                $customer->last_pickup_day_sort = $customer->last_pickup_day->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
            }
            $customer->member_fee = $this->OrderDetail->getMemberFee($customer->id_customer, $year);
            $i ++;
        }

        if (in_array('sort', array_keys($this->getRequest()->getQueryParams())) 
            && in_array($this->getRequest()->getQuery('sort'), ['credit_balance', 'member_fee', 'last_pickup_day',])) {
            $path = '{n}.' .$this->getRequest()->getQuery('sort');
            $type = 'numeric';
            if ($this->getRequest()->getQuery('sort') == 'last_pickup_day') {
                $path .= '_sort';
                $type = 'locale';
            }
            $customers = Hash::sort($customers->toArray(), $path, $this->getRequest()->getQuery('direction'), [
                'type' => $type,
                'ignoreCase' => true,
            ]);
        }

        $this->set('customers', $customers);

        $this->set('title_for_layout', __d('admin', 'Members'));
    }
}
