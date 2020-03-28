<?php
namespace Admin\Controller;

use App\Lib\Error\Exception\InvalidParameterException;
use App\Mailer\AppMailer;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\ForbiddenException;

/**
 * CustomersController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class CustomersController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'generateMemberCards':
                return Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin());
                break;
            case 'generateMyMemberCard':
                return Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isCustomer());
                break;
            case 'edit':
            case 'creditBalanceSum':
                return $this->AppAuth->isSuperadmin();
                break;
            case 'profile':
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isCustomer();
                break;
            case 'delete':
                return $this->AppAuth->isSuperadmin();
                break;
            case 'changePassword':
                return $this->AppAuth->user();
                break;
            default:
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
                break;
        }
    }
    
    public function generateMyMemberCard()
    {
        $customerId = $this->AppAuth->getUserId();
        $this->prepareGenerateMemberCards($customerId);
    }
    
    public function generateMemberCards()
    {
        $customerIds = h($this->getRequest()->getQuery('customerIds'));
        $customerIds = explode(',', $customerIds);
        $this->prepareGenerateMemberCards($customerIds);
    }
    
    private function prepareGenerateMemberCards($customerIds)
    {
        if (empty($customerIds)) {
            throw new InvalidParameterException('no customer id passed');
        }
        
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $this->Customer->dropManufacturersInNextFind();
        $customers = $this->Customer->find('all', [
            'fields' => [
                'bar_code' => $this->AppAuth->getAuthenticate('BarCode')->getIdentifierField($this->Customer)
            ],
            'conditions' => [
                'Customers.id_customer IN' => $customerIds
            ],
            'order' => [
                'Customers.' . Configure::read('app.customerMainNamePart') => 'ASC'
            ],
            'contain' => [
                'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
            ]
        ]);
        $customers->select($this->Customer);
        $customers->select($this->Customer->AddressCustomers);
        $this->set('customers', $customers);
    }

    public function ajaxEditGroup()
    {
        $customerId = (int) $this->getRequest()->getData('customerId');
        $groupId = (int) $this->getRequest()->getData('groupId');

        $this->RequestHandler->renderAs($this, 'json');
        
        if (! in_array($groupId, array_keys(Configure::read('app.htmlHelper')->getAuthDependentGroups($this->AppAuth->getGroupId())))) {
            $message = 'user group not allowed: ' . $groupId;
            $this->log($message);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $oldCustomer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ]
        ])->first();

        // eg. member is not allowed to change groupId of admin, not even to set a groupid he would be allowed to (member)
        if ($this->AppAuth->getGroupId() < $oldCustomer->id_default_group) {
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
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('customer_group_changed', $this->AppAuth->getUserId(), $customerId, 'customers', $messageString);

        $this->set([
            'status' => 1,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status']);
    }

    public function changePassword()
    {
        $this->set('title_for_layout', __d('admin', 'Change_password'));

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $this->AppAuth->getUserId()
            ]
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

            if ($this->AppAuth->isManufacturer()) {
                $message = __d('admin', 'The_manufacturer_{0}_has_changed_his_password.', ['<b>' . $this->AppAuth->getManufacturerName() . '</b>']);
                $actionLogType = 'manufacturer_password_changed';
                $actionLogId = $this->AppAuth->getManufacturerId();
                $actionLogModel = 'manufacturers';
            } else {
                $message = __d('admin', 'The_member_{0}_has_changed_his_password.', ['<b>' . $this->AppAuth->getUsername() . '</b>']);
                $actionLogType = 'customer_password_changed';
                $actionLogId = $this->AppAuth->getUserId();
                $actionLogModel = 'customers';
            }

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $actionLogId, $actionLogModel, $message);
            $this->Flash->success(__d('admin', 'Your_new_password_has_been_saved_successfully.'));
            $this->redirect($this->referer());
        }

        $this->set('customer', $customer);
    }


    public function delete($customerId)
    {
        $this->RequestHandler->renderAs($this, 'json');

        $isOwnProfile = $this->AppAuth->getUserId() == $customerId;

        if (!$this->AppAuth->isSuperadmin()) {
            throw new ForbiddenException('deleting user ' . $customerId . 'denied');
        }

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $this->Payment = TableRegistry::getTableLocator()->get('Payments');

        try {

            $customer = $this->Customer->find('all', [
                'conditions' => [
                    'Customers.id_customer' => $customerId
                ],
                'contain' => [
                    'Manufacturers',
                    'ActiveOrderDetails'
                ]
            ])->first();

            if (empty($customer)) {
                throw new RecordNotFoundException('customer ' . $customerId . ' not found');
            }

            $errors = [];
            $openOrderDetails = count($customer->active_order_details);
            if ($openOrderDetails > 0) {
                $errors[] = __d('admin', 'Amount_of_orders_where_the_invoice_has_not_been_sent_yet_to_the_manufacturer:'). ' '. $openOrderDetails . '.';
            }

            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $creditBalance = $this->Customer->getCreditBalance($customerId);
                if ($creditBalance != 0) {
                    $errors[] = __d('admin', 'The_credit_is') . ' ' . Configure::read('app.numberHelper')->formatAsCurrency($creditBalance) . '. ' . __d('admin', 'It_needs_to_be_zero.');
                }
            }

            $notApprovedPaymentsCount = $this->Payment->find('all', [
                'conditions' => [
                    'id_customer' => $customerId,
                    'approval < ' => APP_ON,
                    'status' => APP_ON,
                    'type' => 'product',
                    'DATE_FORMAT(date_add, \'%Y\') >= DATE_FORMAT(NOW(), \'%Y\') - 2' // check only last full 2 years (eg. payment of 02.02.2018 is checked on 12.11.2020) 
                ]
            ])->count();
            if ($notApprovedPaymentsCount > 0) {
                $errors[] = __d('admin', 'Amount_of_not_approved_payments_within_the_last_2_years:'). ' '. $notApprovedPaymentsCount . '.';
            }
            
            $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
            $timebasedCurrencyCreditBalance = $this->TimebasedCurrencyOrderDetail->getCreditBalance(null, $customerId);
            if ($timebasedCurrencyCreditBalance != 0) {
                $errors[] = __d('admin', 'The_credit_of_the_paying_with_time_account_is:') . ' ' . Configure::read('app.timebasedCurrencyHelper')->formatSecondsToTimebasedCurrency($timebasedCurrencyCreditBalance).'. ' . __d('admin', 'It_needs_to_be_zero.');
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
                throw new Exception($errorString);
            }
        } catch (Exception $e) {
            $this->sendAjaxError($e);
        }

        $this->Customer->deleteAll(['id_customer' => $customerId]);
        $this->Customer->AddressCustomers->deleteAll(['id_customer' => $customerId]);

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->ActionLog->removeCustomerNameFromAllActionLogs($customer->firstname . ' ' . $customer->lastname);
        $this->ActionLog->removeCustomerNameFromAllActionLogs($customer->lastname . ' ' . $customer->firstname);
        $this->ActionLog->removeCustomerEmailFromAllActionLogs($customer->email);
        
        $this->deleteUploadedImage($customerId, Configure::read('app.htmlHelper')->getCustomerThumbsPath(), Configure::read('app.customerImageSizes'));

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        if ($isOwnProfile) {
            $message = __d('admin', 'Your_account_has_been_deleted_successfully.');
            $redirectUrl = Configure::read('app.slugHelper')->getHome();
        } else {
            $message = __d('admin', '{0}_has_deleted_an_account.', [$this->AppAuth->getUsername()]);
            $redirectUrl = $this->getRequest()->getData('referer');
        }
        $this->ActionLog->customSave('customer_deleted', $this->AppAuth->getUserId(), $customer->id_customer, 'customers', $message);
        $this->Flash->success($message);

        if ($isOwnProfile) {
            $this->AppAuth->logout();
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
        $this->_processForm($this->AppAuth->getUserId());
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

        $isOwnProfile = $this->AppAuth->getUserId() == $customerId;
        $this->set('isOwnProfile', $isOwnProfile);

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
            'contain' => [
                'AddressCustomers'
            ]
        ])->first();

        $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
        $timebasedCurrencyCreditBalance = $this->TimebasedCurrencyOrderDetail->getCreditBalance(null, $customerId) * -1;
        $this->set('timebasedCurrencyCreditBalance', $timebasedCurrencyCreditBalance);
        $this->set('timebasedCurrencyDisableOptionAllowed', $timebasedCurrencyCreditBalance >= 0);

        $this->setFormReferer();

        if (empty($this->getRequest()->getData())) {
            $this->set('customer', $customer);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $this->setRequest($this->getRequest()->withData('Customers.email', $this->getRequest()->getData('Customers.address_customer.email')));
        $this->setRequest($this->getRequest()->withData('Customers.address_customer.firstname', $this->getRequest()->getData('Customers.firstname')));
        $this->setRequest($this->getRequest()->withData('Customers.address_customer.lastname', $this->getRequest()->getData('Customers.lastname')));

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
                $this->deleteUploadedImage($customer->id_customer, Configure::read('app.htmlHelper')->getCustomerThumbsPath(), Configure::read('app.customerImageSizes'));
            }
            
            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            if ($isOwnProfile) {
                $message = __d('admin', 'Your_profile_was_changed.');
            } else {
                $message = __d('admin', 'The_profile_of_{0}_was_changed.', ['<b>' . $customer->name . '</b>']);
            }
            $this->ActionLog->customSave('customer_profile_changed', $this->AppAuth->getUserId(), $customer->id_customer, 'customers', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $customer->id_customer);

            if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getCustomerProfile()) {
                $this->renewAuthSession();
            }

            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('customer', $customer);
    }

    /**
     * generates pdf on-the-fly
     */
    private function generateTermsOfUsePdf($customer)
    {
        $this->set('customer', $customer);
        $this->set('saveParam', 'I');
        $this->RequestHandler->renderAs($this, 'pdf');
        $response = $this->render('generateTermsOfUsePdf');
        return $response->__toString();
    }

    public function changeStatus($customerId, $status, $sendEmail)
    {
        if (! in_array($status, [
            APP_OFF,
            APP_ON
        ])) {
            throw new RecordNotFoundException('status needs to be 0 or 1');
        }

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
            'contain' => [
                'AddressCustomers'
            ]
        ])->first();

        $this->Customer->save(
            $this->Customer->patchEntity(
                $customer,
                [
                    'active' => $status
                ]
            )
        );

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
                ->setSubject(__d('admin', 'The_account_was_activated'))
                ->setViewVars([
                'appAuth' => $this->AppAuth,
                'data' => $customer,
                'newPassword' => $newPassword
                ]);

            if (Configure::read('app.termsOfUseEnabled')) {
                $email->addAttachments([__d('admin', 'Filename_Terms-of-use').'.pdf' => ['data' => $this->generateTermsOfUsePdf($customer), 'mimetype' => 'application/pdf']]);
            }
            $email->send();

            $message = __d('admin', 'The_member_{0}_has_been_activated_succesfully_and_the_member_was_notified_by_email.', ['<b>' . $customer->name . '</b>']);
        }

        $this->Flash->success($message);

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $customerId, 'customer', $message);

        $this->redirect($this->referer());
    }

    public function editComment()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $customerId = $this->getRequest()->getData('customerId');
        $customerComment = htmlspecialchars_decode($this->getRequest()->getData('customerComment'));

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $oldCustomer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
            'contain' => [
                'AddressCustomers'
            ]
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

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('customer_comment_changed', $this->AppAuth->getUserId(), $customerId, 'customers', __d('admin', 'The_comment_of_the_member_{0}_was_changed:', ['<b>' . $oldCustomer->name . '</b>']) . ' <div class="changed">' . $customerComment . ' </div>');

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

        $this->Payment = TableRegistry::getTableLocator()->get('Payments');

        $paymentProductDelta = $this->Customer->getProductBalanceForCustomers(APP_ON);
        $paymentDepositDelta = $this->Customer->getDepositBalanceForCustomers(APP_ON);
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_credits_of_activated_members'),
            'count' => count($this->Customer->getCustomerIdsWithStatus(APP_ON)),
            'credit_balance' => $paymentProductDelta + $paymentDepositDelta,
            'payment_deposit_delta' => $paymentDepositDelta
        ];

        $paymentProductDelta = $this->Customer->getProductBalanceForCustomers(APP_OFF);
        $paymentDepositDelta = $this->Customer->getDepositBalanceForCustomers(APP_OFF);
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_credits_of_deactivated_members'),
            'count' => count($this->Customer->getCustomerIdsWithStatus(APP_OFF)),
            'credit_balance' => $paymentProductDelta + $paymentDepositDelta,
            'payment_deposit_delta' => $paymentDepositDelta
        ];

        $paymentProductDelta = $this->Customer->getProductBalanceForDeletedCustomers();
        $paymentDepositDelta = $this->Customer->getDepositBalanceForDeletedCustomers();
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_credits_of_deleted_members'),
            'count' => 0,
            'credit_balance' => $paymentProductDelta + $paymentDepositDelta,
            'payment_deposit_delta' => $paymentDepositDelta
        ];

        $paymentDepositDelta = $this->Payment->getManufacturerDepositMoneySum();
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_deposit_compensation_payments_for_manufactures'),
            'count' => 0,
            'credit_balance' => 0,
            'payment_deposit_delta' => $paymentDepositDelta
        ];

        $this->set('customers', $customers);

        $sums = [];
        foreach($customers as $customer) {
            @$sums['credit_balance'] += $customer['credit_balance'];
            @$sums['deposit_delta'] += $customer['payment_deposit_delta'];
            @$sums['product_delta'] += $customer['payment_product_delta'];
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

        $validOrdersCountFrom = ''; // default value
        if (!empty($this->getRequest()->getQuery('validOrdersCountFrom'))) {
            $validOrdersCountFrom = h($this->getRequest()->getQuery('validOrdersCountFrom'));
        }
        $this->set('validOrdersCountFrom', $validOrdersCountFrom);

        $validOrdersCountTo = ''; // default value
        if (!empty($this->getRequest()->getQuery('validOrdersCountTo'))) {
            $validOrdersCountTo = h($this->getRequest()->getQuery('validOrdersCountTo'));
        }
        $this->set('validOrdersCountTo', $validOrdersCountTo);

        $dateFrom = '';
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = '';
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $conditions = [];
        if ($active != 'all') {
            $conditions = [
                'Customers.active' => $active
            ];
        }

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');

        $conditions[] = $this->Customer->getConditionToExcludeHostingUser();

        $this->Customer->dropManufacturersInNextFind();
        
        $validOrderDetailsConditions = [];
        if ($dateFrom != '') {
            $validOrderDetailsConditions[] = 'DATE_FORMAT(ValidOrderDetails.pickup_day, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom).'\'';
        }
        if ($dateTo != '') {
            $validOrderDetailsConditions[] = 'DATE_FORMAT(ValidOrderDetails.pickup_day, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo).'\'';
        }
        $query = $this->Customer->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
                'ValidOrderDetails' => [
                    'conditions' => $validOrderDetailsConditions,
                    'sort' => ['ValidOrderDetails.pickup_day' => 'DESC']
                ]
            ]
        ]);
        
        $customers = $this->paginate($query, [
            'sortWhitelist' => [
                'Customers.' . Configure::read('app.customerMainNamePart'), 'Customers.id_default_group', 'Customers.id_default_group', 'Customers.email', 'Customers.active', 'Customers.email_order_reminder', 'Customers.date_add', 'Customers.timebased_currency_enabled'
            ],
            'order' => [
                'Customers.' . Configure::read('app.customerMainNamePart') => 'ASC'
            ]
        ])->toArray();

        // extract all email addresses for button
        $emailAddresses = [];
        $emailAddresses = $query->all()->extract('email')->toArray();
        $emailAddresses = array_unique($emailAddresses);
        $this->set('emailAddresses', $emailAddresses);
        
        $i = 0;
        $this->Payment = TableRegistry::getTableLocator()->get('Payments');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        
        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
            $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
        }

        foreach ($customers as $customer) {
            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $customer->credit_balance = $this->Customer->getCreditBalance($customer->id_customer);
                if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
                    $customer->timebased_currency_credit_balance = $this->TimebasedCurrencyOrderDetail->getCreditBalance(null, $customer->id_customer);
                }
            }
            $customer->order_detail_count = count($customer->valid_order_details);
            if (!empty($validOrderDetailsConditions) && $customer->order_detail_count == 0) {
                unset($customers[$i]);
            }
            $i ++;
        }
        
        $this->set('customers', $customers);

        $this->set('title_for_layout', __d('admin', 'Members'));
    }
}
