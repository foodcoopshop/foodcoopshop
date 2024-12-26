<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Mailer\AppMailer;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\View\JsonView;
use App\Services\SanitizeService;
use Cake\I18n\DateTime;
use Cake\I18n\Date;
use App\Model\Entity\Payment;

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
class PaymentsController extends AdminAppController
{

    protected array $allowedPaymentTypes = [];
    protected string $paymentType;

    public int|string $customerId;

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    public function previewEmail($paymentId, $approval)
    {

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payment = $paymentsTable->find('all',
        conditions: [
            $paymentsTable->aliasField('id') => $paymentId,
            $paymentsTable->aliasField('type') => Payment::TYPE_PRODUCT,
        ],
        contain: [
            'Customers'
        ])->first();
        if (empty($payment)) {
            throw new RecordNotFoundException('payment not found');
        }

        if (!in_array($approval, [1,-1])) {
            throw new RecordNotFoundException('approval not implemented');
        }

        $payment->approval = $approval;
        $payment->approval_comment = __d('admin', 'Your_comment_will_be_shown_here.');
        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('Admin.payment_status_changed');
        $email->setTo($payment->customer->email)
            ->setViewVars([
                'identity' => $this->identity,
                'data' => $payment->customer,
                'newStatusAsString' => Configure::read('app.htmlHelper')->getApprovalStates()[$approval],
                'payment' => $payment
            ]);
        echo $email->render()->getMessage()->getBodyString();
        exit;
    }

    public function edit($paymentId)
    {

        $this->set('title_for_layout', __d('admin', 'Check_credit_upload'));

        $this->setFormReferer();

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payment = $paymentsTable->find('all',
        conditions: [
            $paymentsTable->aliasField('id') => $paymentId,
            $paymentsTable->aliasField('type IN') => [
                Payment::TYPE_PRODUCT,
                Payment::TYPE_PAYBACK,
            ],
        ],
        contain: [
            'Customers',
            'ChangedByCustomers'
        ])->first();

        if (empty($payment)) {
            throw new RecordNotFoundException('payment not found');
        }

        if (empty($this->getRequest()->getData())) {
            $this->set('payment', $payment);
            return;
        }

        $payment = $paymentsTable->patchEntity(
            $payment,
            $this->getRequest()->getData(),
            [
                'validate' => 'edit'
            ]
        );

        if ($payment->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('payment', $payment);
        } else {
            $payment = $paymentsTable->patchEntity(
                $payment,
                [
                    'date_changed' => DateTime::now(),
                    'changed_by' => $this->identity->getId()
                ]
            );
            $payment = $paymentsTable->save($payment);

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $actionLogType = match($payment->approval) {
                -1 => 'payment_product_approval_not_ok',
                 0 => 'payment_product_approval_open',
                 1 => 'payment_product_approval_ok',
                 default => '',
            };

            $newStatusAsString = Configure::read('app.htmlHelper')->getApprovalStates()[$payment->approval];

            $message = __d('admin', 'The_status_of_the_credit_upload_for_{0}_was_successfully_changed_to_{1}.', ['<b>'.$payment->customer->name.'</b>', '<b>' .$newStatusAsString.'</b>']);

            if ($payment->send_email) {
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('Admin.payment_status_changed');
                $email->setTo($payment->customer->email)
                    ->setSubject(__d('admin', 'The_status_of_your_credit_upload_was_successfully_changed_to_{0}.', ['"' .$newStatusAsString.'"']))
                    ->setViewVars([
                        'identity' => $this->identity,
                        'data' => $payment->customer,
                        'newsletterCustomer' => $payment->customer,
                        'newStatusAsString' => $newStatusAsString,
                        'payment' => $payment
                    ]);
                $email->addToQueue();
                $message = __d('admin', 'The_status_of_the_credit_upload_for_{0}_was_successfully_changed_to_{1}_and_an_email_was_sent_to_the_member.', ['<b>'.$payment->customer->name.'</b>', '<b>' .$newStatusAsString.'</b>']);
            }

            $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $payment->id, 'payments', $message . ' (PaymentId: ' . $payment->id.')');
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $payment->id);

            $this->redirect($this->getPreparedReferer());
        }

        $this->set('payment', $payment);
    }

    public function add()
    {
        $this->request = $this->request->withParam('_ext', 'json');
        $type = $this->getRequest()->getData('type');
        if (!is_null($type)) {
            $type = trim($type);
        }

        if (! in_array($type, [
            Payment::TYPE_PRODUCT,
            Payment::TYPE_PAYBACK,
            Payment::TYPE_DEPOSIT,
        ])) {
            $message = 'payment type not correct: ' . $type;
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));

        $amount = $this->getRequest()->getData('amount');
        $amount = Configure::read('app.numberHelper')->parseFloatRespectingLocale($amount);

        $dateAdd = $this->getRequest()->getData('dateAdd');

        $paymentsTable = $this->getTableLocator()->get('Payments');
        try {
            $entity = $paymentsTable->newEntity(
                [
                    'amount' => $amount,
                    'date_add' => $dateAdd,
                ],
                ['validate' => 'add']
            );
            if ($entity->hasErrors()) {
                throw new \Exception($paymentsTable->getAllValidationErrors($entity)[0]);
            }
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $text = '';
        if (!empty($this->getRequest()->getData('text'))) {
            $text = strip_tags(html_entity_decode($this->getRequest()->getData('text')));
        }

        $message = Configure::read('app.htmlHelper')->getPaymentText($type);
        if (in_array($type, [Payment::TYPE_PRODUCT, Payment::TYPE_PAYBACK])) {
            $customerId = (int) $this->getRequest()->getData('customerId');
        }

        $actionLogType = $type;

        if ($type == 'deposit') {
            // payments to deposits can be added to customers or manufacturers
            $customerId = (int) $this->getRequest()->getData('customerId');
            if ($customerId > 0) {
                $userType = 'customer';
                $customersTable = $this->getTableLocator()->get('Customers');
                $customer = $customersTable->find('all', conditions: [
                    $customersTable->aliasField('id_customer') => $customerId,
                ])->first();
                if (empty($customer)) {
                    $msg = 'customer id not correct: ' . $customerId;
                    $this->set([
                        'status' => 0,
                        'msg' => $msg,
                    ]);
                    $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
                    return;
                }
                $message .= ' ' . __d('admin', 'for') . ' ' . $customer->name;
            }

            $manufacturerId = (int) $this->getRequest()->getData('manufacturerId');

            if ($manufacturerId > 0) {
                $userType = 'manufacturer';
                $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
                $manufacturer = $manufacturersTable->find('all', conditions: [
                    $manufacturersTable->aliasField('id_manufacturer') => $manufacturerId,
                ])->first();

                if (empty($manufacturer)) {
                    $msg = 'manufacturer id not correct: ' . $manufacturerId;
                    $this->log($msg);
                    $this->set([
                        'status' => 0,
                        'msg' => $msg,
                    ]);
                    $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
                    return;
                }

                $message = __d('admin', 'Deposit_take_back') . ' ('.Configure::read('app.htmlHelper')->getManufacturerDepositPaymentText($text).')';
                $message .= ' ' . __d('admin', 'for') . ' ' . $manufacturer->name;
            }

            if ($type == 'deposit') {
                if (!isset($userType)) {
                    $msg = 'no userType set - payment cannot be saved';
                    $this->log($msg);
                    $this->set([
                        'status' => 0,
                        'msg' => $msg,
                    ]);
                    $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
                    return;
                }
                $actionLogType .= '_'.$userType;
            }
        }

        // payments paybacks and product can also be placed for other users
        if (in_array($type, [Payment::TYPE_PRODUCT, Payment::TYPE_PAYBACK]) && isset($customerId)) {
            $customersTable = $this->getTableLocator()->get('Customers');
            $customer = $customersTable->find('all', conditions: [
                $customersTable->aliasField('id_customer') => $customerId,
            ])->first();
            if ($this->identity->isSuperadmin() && $this->identity->getId() != $customerId) {
                $message .= ' ' . __d('admin', 'for') . ' ' . $customer->name;
            }
            // security check
            if (!$this->identity->isSuperadmin() && $this->identity->getId() != $customerId) {
                $msg = 'user without superadmin privileges tried to insert payment for another user: ' . $customerId;
                $this->set([
                    'status' => 0,
                    'msg' => $msg,
                ]);
                $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
                return;
            }
            if (empty($customer)) {
                $msg = 'customer id not correct: ' . $customerId;
                $this->log($msg);
                $this->set([
                    'status' => 0,
                    'msg' => $msg,
                ]);
                $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
                return;
            }
        }

        $dateAddForEntity = DateTime::now();
        $paymentPastDate = false;
        if ($dateAdd > 0) {
            $dateAddForEntity = Date::createFromFormat(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), Configure::read('app.timeHelper')->formatToDbFormatDate($dateAdd));
            $paymentPastDate = true;
        }
        if ($dateAddForEntity->isToday()) {
            $paymentPastDate = false;
            $dateAddForEntity = DateTime::now(); // always save time for today, even if it's explicitely passed
        }

        // add entry in table payments
        $paymentsTable = $this->getTableLocator()->get('Payments');
        $entity = $paymentsTable->newEntity(
            [
                'status' => APP_ON,
                'type' => $type,
                'id_customer' => $customerId ?? 0,
                'id_manufacturer' => $manufacturerId ?? 0,
                'date_add' => $dateAddForEntity,
                'date_changed' => DateTime::now(),
                'amount' => $amount,
                'text' => $text,
                'created_by' => $this->identity->getId(),
            ]
        );

        if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $type == 'product' && $this->identity->isSuperadmin()) {
            $entity->approval = APP_ON;
        }

        $newPayment = $paymentsTable->save($entity);
        $paymentPastDateMessage = '';
        if ($type == 'deposit' && $paymentPastDate) {
            $paymentPastDateMessage = ' ' . __d('admin', 'for_the') . ' <b>' . $dateAddForEntity->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . '</b>';
        }
        $message .= ' ' . __d('admin', 'was_added_successfully_{0}:_{1}', [
            $paymentPastDateMessage,
            '<b>' . Configure::read('app.numberHelper')->formatAsCurrency($amount).'</b>',
        ]);

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('payment_' . $actionLogType . '_added', $this->identity->getId(), $newPayment->id, 'payments', $message);

        if (in_array($actionLogType, ['deposit_customer', 'deposit_manufacturer']) && isset($customer) && isset($manufacturer)) {
            $message .= '. ';
            $message .= match($actionLogType) {
                'deposit_customer' => __d('admin', 'The_amount_was_added_to_the_credit_system_of_{0}_and_can_be_deleted_there.', ['<b>'.$customer->name.'</b>']),
                'deposit_manufacturer' => __d('admin', 'The_amount_was_added_to_the_deposit_account_of_{0}_and_can_be_deleted_there.', ['<b>'.$manufacturer->name.'</b>']),
                default => '',
            };
        }

        $this->Flash->success($message);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
            'amount' => $amount,
            'paymentId' => $newPayment->id,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'amount', 'paymentId']);

    }

    public function changeState()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $paymentId = $this->getRequest()->getData('paymentId');

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payment = $paymentsTable->find('all',
        conditions: [
            'Payments.id' => $paymentId,
            'Payments.approval <> ' . APP_ON
        ],
        contain: [
            'Customers',
            'Manufacturers',
        ])->first();

        if (empty($payment)) {
            $message = 'payment id ('.$paymentId.') not correct or already approved (approval: 1)';
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        // TODO add payment owner check (also for manufacturers!)
        $paymentsTable->save(
            $paymentsTable->patchEntity(
                $payment,
                [
                    'status' => APP_DEL,
                    'date_changed' => DateTime::now()
                ]
            )
        );

        $actionLogType = $payment->type;
        if ($payment->type == 'deposit') {
            $userType = 'customer';
            if ($payment->id_manufacturer > 0) {
                $userType = 'manufacturer';
            }
            $actionLogType .= '_'.$userType;
        }

        $message = __d('admin', 'The_payment_({0}_{1})_was_removed_successfully.', [
            Configure::read('app.numberHelper')->formatAsCurrency($payment->amount),
            Configure::read('app.htmlHelper')->getPaymentText($payment->type)]
        );
        if ($this->identity->isSuperadmin() && $this->identity->getId() != $payment->id_customer) {
            if (isset($payment->customer->name)) {
                $username = $payment->customer->name;
            } else {
                $username = $payment->manufacturer->name;
            }
            $message = __d('admin', 'The_payment_({0}_{1})_of_{2}_was_removed_successfully.', [
                Configure::read('app.numberHelper')->formatAsCurrency($payment->amount),
                Configure::read('app.htmlHelper')->getPaymentText($payment->type),
                $username
            ]);
        }

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('payment_' . $actionLogType . '_deleted', $this->identity->getId(), $paymentId, 'payments', $message . ' (PaymentId: ' . $paymentId . ')');

        $this->Flash->success($message);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    /**
     * $this->customerId needs to be set in calling method
     */
    private function getCustomerId(): int|string
    {
        $customerId = '';
        if (!empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        } if (isset($this->customerId) && $this->customerId > 0) {
            $customerId = $this->customerId;
        }
        return $customerId;
    }

    public function overview()
    {
        $this->customerId = $this->identity->getId();
        $this->paymentType = Payment::TYPE_PRODUCT;

        if (!Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
            $customersTable = $this->getTableLocator()->get('Customers');
            $personalTransactionCode = $customersTable->getPersonalTransactionCode($this->customerId);
            $this->set('personalTransactionCode', $personalTransactionCode);
        }

        $this->product();
        $this->render('product');
    }

    public function product()
    {

        $this->paymentType = Payment::TYPE_PRODUCT;
        $this->set('title_for_layout', __d('admin', 'Credit'));

        $this->allowedPaymentTypes = [
            Payment::TYPE_PRODUCT,
            Payment::TYPE_PAYBACK,
            Payment::TYPE_DEPOSIT,
        ];
        if (!Configure::read('app.htmlHelper')->paymentIsCashless()) {
            $this->allowedPaymentTypes = [
                Payment::TYPE_PRODUCT,
                Payment::TYPE_PAYBACK,
            ];
        }

        $this->preparePayments();
        $customersTable = $this->getTableLocator()->get('Customers');
        $this->set('creditBalance', $customersTable->getCreditBalance($this->getCustomerId()));

        if ($this->identity->isSuperadmin() && !Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
            $personalTransactionCode = $customersTable->getPersonalTransactionCode($this->getCustomerId());
            $this->set('personalTransactionCode', $personalTransactionCode);
        }

    }

    private function preparePayments()
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $paymentsAssociation = $customersTable->getAssociation('Payments');
        $paymentsAssociation->setConditions(
            array_merge(
                $paymentsAssociation->getConditions(),
                ['type IN' => $this->allowedPaymentTypes]
            )
        );

        $customer = $customersTable->find('all',
        conditions: [
            $customersTable->aliasField('id_customer') => $this->getCustomerId(),
        ],
        contain: [
           'Payments'
        ])->first();

        $payments = [];
        if (!empty($customer->payments)) {
            foreach ($customer->payments as $payment) {
                $text = Configure::read('app.htmlHelper')->getPaymentText($payment->type);
                $text .= (! empty($payment->text) ? ': "' . $payment->text . '"' : '');

                $payments[] = [
                    'dateRaw' => $payment->date_add,
                    'date' => $payment->date_add->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                    'year' => $payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Year')),
                    'amount' => $payment->amount,
                    'deposit' => 0,
                    'type' => $payment->type,
                    'text' => $text,
                    'payment_id' => $payment->id,
                    'approval' => $payment->approval,
                    'invoice_id' => $payment->invoice_id,
                    'approval_comment' => $payment->approval_comment
                ];
            }
        }

        if ($this->paymentType == Payment::TYPE_PRODUCT) {
            $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
            $orderDetailsGroupedByMonth = $orderDetailsTable->getMonthlySumProductByCustomer($this->getCustomerId());

            if (! empty($orderDetailsGroupedByMonth)) {
                foreach ($orderDetailsGroupedByMonth as $orderDetail) {
                    $monthAndYear = explode('-', $orderDetail['MonthAndYear']);
                    $monthAndYear[0] = (int) $monthAndYear[0];
                    $monthAndYear[1] = (int) $monthAndYear[1];
                    $dateFrom = Date::create($monthAndYear[0], $monthAndYear[1], 1);
                    $lastDayOfMonth = (int) Configure::read('app.timeHelper')->getLastDayOfGivenMonth($orderDetail['MonthAndYear']);
                    $dateTo = Date::create($monthAndYear[0], $monthAndYear[1], $lastDayOfMonth);
                    $payments[] = [
                        'dateRaw' => $dateFrom,
                        'date' => $dateFrom->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                        'year' => $monthAndYear[0],
                        'amount' => $orderDetail['SumTotalPaid'] * - 1,
                        'deposit' => strtotime($dateFrom->i18nFormat(Configure::read('DateFormat.DatabaseWithTime'))) > strtotime(Configure::read('app.depositPaymentCashlessStartDate')) ? $orderDetail['SumDeposit'] * - 1 : 0,
                        'type' => 'order',
                        'text' => Configure::read('app.htmlHelper')->link(
                            __d('admin', 'Orders') . ' ' . Configure::read('app.timeHelper')->getMonthName($monthAndYear[1]) . ' ' . $monthAndYear[0],
                            '/admin/order-details/?pickupDay[]=' . $dateFrom->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) .
                            '&pickupDay[]=' . $dateTo->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) .
                            '&customerId=' . $this->getCustomerId(),
                            [
                                'title' => __d('admin', 'Show_order')
                            ]
                        ),
                        'payment_id' => null,
                    ];
                }
            }
        }

        $payments = Hash::sort($payments, '{n}.date', 'desc');
        $this->set('payments', $payments);
        $this->set('customerId', $this->getCustomerId());

        $this->set('column_title', $this->viewBuilder()->getVars()['title_for_layout']);

        $title = $this->viewBuilder()->getVars()['title_for_layout'];
        if ($this->getRequest()->getParam('action') == 'product') {
            $title .= ' '.__d('admin', 'of_{0}', [$customer->name]);
        }
        $this->set('title_for_layout', $title);

        $this->set('paymentType', $this->paymentType);
    }
}
