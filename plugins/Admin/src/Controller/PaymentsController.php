<?php

namespace Admin\Controller;

use App\Mailer\AppMailer;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenDate;
use Cake\I18n\Time;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use App\Lib\Error\Exception\InvalidParameterException;

/**
 * PaymentsController
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
class PaymentsController extends AdminAppController
{
    
    public $customerId;

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'overview':
                return Configure::read('app.htmlHelper')->paymentIsCashless() && $this->AppAuth->user() && ! $this->AppAuth->isManufacturer();
                break;
            case 'myMemberFee':
                return Configure::read('app.memberFeeEnabled') && $this->AppAuth->user() && ! $this->AppAuth->isManufacturer();
                break;
            case 'product':
                return $this->AppAuth->isSuperadmin();
                break;
            case 'memberFee':
                if (empty($this->getRequest()->getQuery('customerId'))) {
                    $this->redirect(Configure::read('app.slugHelper')->getMyMemberFeeBalance());
                }
                return $this->AppAuth->isSuperadmin();
                break;
            case 'edit':
            case 'previewEmail':
                return $this->AppAuth->isSuperadmin();
                break;
            case 'add':
                if (Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
                    return $this->AppAuth->user();
                } else {
                    return $this->AppAuth->isSuperadmin();
                }
                break;
            case 'changeState':
                return $this->AppAuth->user();
                break;
            default:
                return $this->AppAuth->user() && ! $this->AppAuth->isManufacturer();
                break;
        }
    }

    public function beforeFilter(EventInterface $event)
    {
        $this->Payment = TableRegistry::getTableLocator()->get('Payments');
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        parent::beforeFilter($event);
    }

    public function previewEmail($paymentId, $approval)
    {

        $payment = $this->Payment->find('all', [
            'conditions' => [
                'Payments.id' => $paymentId,
                'Payments.type' => 'product'
            ],
            'contain' => [
                'Customers'
            ]
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
                'appAuth' => $this->AppAuth,
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

        $payment = $this->Payment->find('all', [
            'conditions' => [
                'Payments.id' => $paymentId,
                'Payments.type' => 'product'
            ],
            'contain' => [
                'Customers',
                'ChangedByCustomers'
            ]
        ])->first();

        if (empty($payment)) {
            throw new RecordNotFoundException('payment not found');
        }

        if (empty($this->getRequest()->getData())) {
            $this->set('payment', $payment);
            return;
        }

        $payment = $this->Payment->patchEntity(
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
            $payment = $this->Payment->patchEntity(
                $payment,
                [
                    'date_changed' => Time::now(),
                    'changed_by' => $this->AppAuth->getUserId()
                ]
            );
            $payment = $this->Payment->save($payment);

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            switch ($payment->approval) {
                case -1:
                    $actionLogType = 'payment_product_approval_not_ok';
                    break;
                case 0:
                    $actionLogType = 'payment_product_approval_open';
                    break;
                case 1:
                    $actionLogType = 'payment_product_approval_ok';
                    break;
            }

            $newStatusAsString = Configure::read('app.htmlHelper')->getApprovalStates()[$payment->approval];

            $message = __d('admin', 'The_status_of_the_credit_upload_for_{0}_was_successfully_changed_to_{1}.', ['<b>'.$payment->customer->name.'</b>', '<b>' .$newStatusAsString.'</b>']);

            if ($payment->send_email) {
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('Admin.payment_status_changed');
                $email->setTo($payment->customer->email)
                    ->setSubject(__d('admin', 'The_status_of_your_credit_upload_was_successfully_changed_to_{0}.', ['"' .$newStatusAsString.'"']))
                    ->setViewVars([
                        'appAuth' => $this->AppAuth,
                        'data' => $payment->customer,
                        'newStatusAsString' => $newStatusAsString,
                        'payment' => $payment
                    ]);
                $email->send();
                $message = __d('admin', 'The_status_of_the_credit_upload_for_{0}_was_successfully_changed_to_{1}_and_an_email_was_sent_to_the_member.', ['<b>'.$payment->customer->name.'</b>', '<b>' .$newStatusAsString.'</b>']);
            }

            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $payment->id, 'payments', $message . ' (PaymentId: ' . $payment->id.')');
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $payment->id);

            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('payment', $payment);
    }

    public function add()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $type = trim($this->getRequest()->getData('type'));
        if (! in_array($type, [
            'product',
            'deposit',
            'payback',
            'member_fee',
            'member_fee_flexible'
        ])) {
            $message = 'payment type not correct: ' . $type;
            $this->log($message);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        
        $amount = $this->getRequest()->getData('amount');
        $amount = Configure::read('app.numberHelper')->parseFloatRespectingLocale($amount);

        try {
            $entity = $this->Payment->newEntity(
                ['amount' => $amount],
                ['validate' => 'add']
            );
            if ($entity->hasErrors()) {
                throw new InvalidParameterException($this->Payment->getAllValidationErrors($entity)[0]);
            }
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $text = '';
        if (!empty($this->getRequest()->getData('text'))) {
            $text = strip_tags(html_entity_decode($this->getRequest()->getData('text')));
        }

        $message = Configure::read('app.htmlHelper')->getPaymentText($type);
        if (in_array($type, ['product', 'payback'])) {
            $customerId = (int) $this->getRequest()->getData('customerId');
        }
        if ($type == 'member_fee') {
            $customerId = (int) $this->getRequest()->getData('customerId');
            $text = implode(',', $this->getRequest()->getData('months_range'));
        }

        $actionLogType = $type;

        if (in_array($type, [
            'deposit',
            'member_fee_flexible'
        ])) {
            // payments to deposits can be added to customers or manufacturers
            $customerId = (int) $this->getRequest()->getData('customerId');
            if ($customerId > 0) {
                $userType = 'customer';
                $customer = $this->Customer->find('all', [
                    'conditions' => [
                        'Customers.id_customer' => $customerId
                    ]
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
                $manufacturer = $this->Manufacturer->find('all', [
                    'conditions' => [
                        'Manufacturers.id_manufacturer' => $manufacturerId
                    ]
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
                $actionLogType .= '_'.$userType;
            }
        }

        // payments paybacks, product and member_fee can also be placed for other users
        if (in_array($type, [
            'product',
            'payback',
            'member_fee'
        ])) {
            $customer = $this->Customer->find('all', [
                'conditions' => [
                    'Customers.id_customer' => $customerId
                ]
            ])->first();
            if ($this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $customerId) {
                $message .= ' ' . __d('admin', 'for') . ' ' . $customer->name;
            }
            // security check
            if (!$this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $customerId) {
                $msg = 'user without superadmin privileges tried to insert payment for another user: ' . $customerId;
                $this->log($msg);
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

        // add entry in table payments
        $newPayment = $this->Payment->save(
            $this->Payment->newEntity(
                [
                    'status' => APP_ON,
                    'type' => $type,
                    'id_customer' => $customerId,
                    'id_manufacturer' => isset($manufacturerId) ? $manufacturerId : 0,
                    'date_add' => Time::now(),
                    'date_changed' => Time::now(),
                    'amount' => $amount,
                    'text' => $text,
                    'created_by' => $this->AppAuth->getUserId(),
                ]
            )
        );

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $message .= ' ' . __d('admin', 'was_added_successfully:_{0}', ['<b>' . Configure::read('app.numberHelper')->formatAsCurrency($amount).'</b>']);

        if ($type == 'member_fee') {
            $message .= ', ' . __d('admin', 'for') . ' ' . Configure::read('app.htmlHelper')->getMemberFeeTextForFrontend($text);
        }

        $this->ActionLog->customSave('payment_' . $actionLogType . '_added', $this->AppAuth->getUserId(), $newPayment->id, 'payments', $message);

        if (in_array($actionLogType, ['deposit_customer', 'deposit_manufacturer', 'member_fee_flexible'])) {
            $message .= '. ';
            switch ($actionLogType) {
                case 'deposit_customer':
                    $message .= __d('admin', 'The_amount_was_added_to_the_credit_system_of_{0}_and_can_be_deleted_there.', ['<b>'.$customer->name.'</b>']);
                    break;
                case 'deposit_manufacturer':
                    $message .= __d('admin', 'The_amount_was_added_to_the_deposit_account_of_{0}_and_can_be_deleted_there.', ['<b>'.$manufacturer->name.'</b>']);
                    break;
                case 'member_fee_flexible':
                    $message .= __d('admin', 'The_amount_was_added_to_the_member_fee_system_of_{0}_and_can_be_deleted_there.', ['<b>'.$customer->name.'</b>']);
                    break;
            }
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
        $this->RequestHandler->renderAs($this, 'json');

        $paymentId = $this->getRequest()->getData('paymentId');

        $payment = $this->Payment->find('all', [
            'conditions' => [
                'Payments.id' => $paymentId,
                'Payments.approval <> ' . APP_ON
            ],
            'contain' => [
                'Customers',
                'Manufacturers'
            ]
        ])->first();

        if (empty($payment)) {
            $message = 'payment id ('.$paymentId.') not correct or already approved (approval: 1)';
            $this->log($message);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        // TODO add payment owner check (also for manufacturers!)
        $this->Payment->save(
            $this->Payment->patchEntity(
                $payment,
                [
                    'status' => APP_DEL,
                    'date_changed' => Time::now()
                ]
            )
        );

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');

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
        if ($this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $payment->id_customer) {
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

        $this->ActionLog->customSave('payment_' . $actionLogType . '_deleted', $this->AppAuth->getUserId(), $paymentId, 'payments', $message . ' (PaymentId: ' . $paymentId . ')');

        $this->Flash->success($message);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    /**
     * $this->customerId needs to be set in calling method
     * @return int
     */
    private function getCustomerId()
    {
        $customerId = '';
        if (!empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        } if ($this->customerId > 0) {
            $customerId = $this->customerId;
        }
        return $customerId;
    }

    public function overview()
    {
        $this->customerId = $this->AppAuth->getUserId();
        $this->paymentType = 'product';
        
        if (!Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
            $customer = $this->Customer->find('all', [
                'conditions' => [
                    'Customers.id_customer' => $this->customerId
                ],
                'fields' => [
                    'personalTransactionCode' => $this->Customer->getPersonalTransactionCodeField()
                ]
            ])->first();
            $this->set('personalTransactionCode', $customer->personalTransactionCode);
        }
        
        $this->product();
        $this->render('product');
    }

    public function myMemberFee()
    {
        $this->customerId = $this->AppAuth->getUserId();
        $this->paymentType = 'member_fee';
        $this->memberFee();
        $this->render('member_fee');
    }

    public function memberFee()
    {

        $this->paymentType = 'member_fee';
        $this->set('title_for_layout', __d('admin', 'Member_fee'));

        $this->allowedPaymentTypes = [
            'member_fee'
        ];
        $this->preparePayments();
        $sumMemberFee = $this->Payment->getSum($this->AppAuth->getUserId(), 'member_fee');
        $this->set('sumMemberFee', $sumMemberFee);
    }

    public function product()
    {

        $this->paymentType = 'product';
        $this->set('title_for_layout', __d('admin', 'Credit'));

        $this->allowedPaymentTypes = [
            'product',
            'payback',
            'deposit'
        ];
        if (! Configure::read('app.isDepositPaymentCashless')) {
            $this->allowedPaymentTypes = [
                'product',
                'payback'
            ];
        }

        $this->preparePayments();
        $this->set('creditBalance', $this->Customer->getCreditBalance($this->getCustomerId()));
    }

    private function preparePayments()
    {
        $paymentsAssociation = $this->Customer->getAssociation('Payments');
        $paymentsAssociation->setConditions(
            array_merge(
                $paymentsAssociation->getConditions(),
                ['type IN' => $this->allowedPaymentTypes]
            )
        );

        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $this->getCustomerId()
            ],
            'contain' => [
               'Payments'
            ]
        ])->first();

        $payments = [];
        if (!empty($customer->payments)) {
            foreach ($customer->payments as $payment) {
                $text = Configure::read('app.htmlHelper')->getPaymentText($payment->type);
                if ($payment->type == 'member_fee') {
                    $text .= ' '.__d('admin', 'for').': ' . Configure::read('app.htmlHelper')->getMemberFeeTextForFrontend($payment->text);
                } else {
                    $text .= (! empty($payment->text) ? ': "' . $payment->text . '"' : '');
                }

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
                    'approval_comment' => $payment->approval_comment
                ];
            }
        }

        if ($this->paymentType == 'product') {
            $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
            $orderDetailsGroupedByMonth = $this->OrderDetail->getMonthlySumProductByCustomer($this->getCustomerId());
            
            if (! empty($orderDetailsGroupedByMonth)) {
                foreach ($orderDetailsGroupedByMonth as $orderDetail) {
                    $monthAndYear = explode('-', $orderDetail['MonthAndYear']);
                    $frozenDateFrom = FrozenDate::create($monthAndYear[0], $monthAndYear[1], 1);
                    $lastDayOfMonth = Configure::read('app.timeHelper')->getLastDayOfGivenMonth($orderDetail['MonthAndYear']);
                    $frozenDateTo = FrozenDate::create($monthAndYear[0], $monthAndYear[1], $lastDayOfMonth);
                    $payments[] = [
                        'dateRaw' => $frozenDateFrom,
                        'date' => $frozenDateFrom->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                        'year' => $monthAndYear[0],
                        'amount' => $orderDetail['SumTotalPaid'] * - 1,
                        'deposit' => strtotime($frozenDateFrom->i18nFormat(Configure::read('DateFormat.DatabaseWithTime'))) > strtotime(Configure::read('app.depositPaymentCashlessStartDate')) ? $orderDetail['SumDeposit'] * - 1 : 0,
                        'type' => 'order',
                        'text' => Configure::read('app.htmlHelper')->link(
                            __d('admin', 'Orders') . ' ' . Configure::read('app.timeHelper')->getMonthName($monthAndYear[1]) . ' ' . $monthAndYear[0],
                            '/admin/order-details/?pickupDay[]=' . $frozenDateFrom->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) .
                            '&pickupDay[]=' . $frozenDateTo->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . 
                            '&customerId=' . $this->getCustomerId(),
                            [
                                'title' => __d('admin', 'Show_order')
                            ]
                        ),
                        'payment_id' => null,
                        'timebased_currency_sum_seconds' => $orderDetail['SumTimebasedCurrencySeconds']
                    ];
                }
            }
    
            $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
            $timebasedCurrencySum = $this->TimebasedCurrencyOrderDetail->getSum(null, $this->getCustomerId());
            $timebasedCurrencyOrderDetailInList = $timebasedCurrencySum > 0;
            $this->set('timebasedCurrencyOrderDetailInList', $timebasedCurrencyOrderDetailInList);
        }

        $payments = Hash::sort($payments, '{n}.date', 'desc');
        $this->set('payments', $payments);
        $this->set('customerId', $this->getCustomerId());

        $this->set('column_title', $this->viewBuilder()->getVars()['title_for_layout']);

        $title = $this->viewBuilder()->getVars()['title_for_layout'];
        if (in_array($this->getRequest()->getParam('action'), ['product', 'member_fee'])) {
            $title .= ' '.__d('admin', 'of_{0}', [$customer->name]);
        }
        $this->set('title_for_layout', $title);

        $this->set('paymentType', $this->paymentType);
    }
}
