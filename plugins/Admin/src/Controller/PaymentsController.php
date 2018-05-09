<?php

namespace Admin\Controller;

use App\Mailer\AppEmail;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PaymentsController extends AdminAppController
{

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
                // allow redirects for legacy links
                if (empty($this->getRequest()->getQuery('customerId'))) {
                    $this->redirect(Configure::read('app.slugHelper')->getMyCreditBalance());
                }
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
            case 'changeState':
                return $this->AppAuth->user();
                break;
            default:
                return $this->AppAuth->user() && ! $this->AppAuth->isManufacturer();
                break;
        }
    }

    public function beforeFilter(Event $event)
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
        $payment->approval_comment = 'Hier wird dein Kommentar angezeigt.';
        $email = new AppEmail();
        $email->setTemplate('Admin.payment_status_changed')
            ->setTo($payment->customer->email)
            ->setViewVars([
                'appAuth' => $this->AppAuth,
                'data' => $payment->customer,
                'newStatusAsString' => Configure::read('app.htmlHelper')->getApprovalStates()[$approval],
                'payment' => $payment
            ]);
        $html = $email->_renderTemplates(null)['html'];
        if ($html != '') {
            echo $html;
            exit;
        }
    }

    public function edit($paymentId)
    {

        $this->set('title_for_layout', 'Guthaben-Aufladung überprüfen');

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

        if (!empty($payment->getErrors())) {
            $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
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

            $message = 'Der Status der Guthaben-Aufladung für <b>'.$payment->customer->name.'</b> wurde erfolgreich auf <b>' .$newStatusAsString.'</b> geändert';

            if ($payment->send_email) {
                $email = new AppEmail();
                $email->setTemplate('Admin.payment_status_changed')
                    ->setTo($payment->customer->email)
                    ->setSubject('Der Status deiner Guthaben-Aufladung wurde auf "'.$newStatusAsString.'" geändert.')
                    ->setViewVars([
                        'appAuth' => $this->AppAuth,
                        'data' => $payment->customer,
                        'newStatusAsString' => $newStatusAsString,
                        'payment' => $payment
                    ]);
                $email->send();
                $message .= ' und eine E-Mail an das Mitglied verschickt';
            }

            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $payment->id, 'payments', $message.' (PaymentId: ' . $payment->id.').');
            $this->Flash->success($message.'.');

            $this->getRequest()->getSession()->write('highlightedRowId', $payment->id);

            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('payment', $payment);
    }

    public function add()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

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
            die(json_encode([
                'status' => 0,
                'msg' => $message
            ]));
        }

        $amount = $this->getRequest()->getData('amount');

        if (preg_match('/^\-/', $amount)) {
            $message = 'Ein negativer Betrag ist nicht erlaubt: ' . $amount;
            $this->log($message);
            die(json_encode(['status'=>0,'msg'=>$message]));
        }

        $amount = preg_replace('/[^0-9,.]/', '', $amount);
        $amount = floatval(Configure::read('app.numberHelper')->replaceCommaWithDot($amount));

        if ($type == 'product' && $amount > Configure::read('appDb.FCS_PAYMENT_PRODUCT_MAXIMUM')) {
            $message = 'Der Maximalwert pro Aufladung ist ' . Configure::read('appDb.FCS_PAYMENT_PRODUCT_MAXIMUM');
            $this->log($message);
            die(json_encode(['status'=>0,'msg'=>$message]));
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
                    $this->log($msg);
                    die(json_encode([
                        'status' => 0,
                        'msg' => $msg
                    ]));
                }
                $message .= ' für ' . $customer->name;
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
                    die(json_encode([
                        'status' => 0,
                        'msg' => $msg
                    ]));
                }

                $message = 'Pfand-Rücknahme ('.Configure::read('app.htmlHelper')->getManufacturerDepositPaymentText($text).')';
                $message .= ' für ' . $manufacturer->name;
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
                $message .= ' für ' . $customer->name;
            }
            // security check
            if (!$this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $customerId) {
                $msg = 'user without superadmin privileges tried to insert payment for another user: ' . $customerId;
                $this->log($msg);
                die(json_encode([
                    'status' => 0,
                    'msg' => $msg
                ]));
            }
            if (empty($customer)) {
                $msg = 'customer id not correct: ' . $customerId;
                $this->log($msg);
                die(json_encode([
                    'status' => 0,
                    'msg' => $msg
                ]));
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
                    'approval_comment' => ''  // column type text cannot have a default value, must be set explicitly even if unused
                ]
            )
        );

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $message .= ' wurde erfolgreich eingetragen: <b>' . Configure::read('app.htmlHelper')->formatAsEuro($amount).'</b>';

        if ($type == 'member_fee') {
            $message .= ', für ' . Configure::read('app.htmlHelper')->getMemberFeeTextForFrontend($text);
        }

        $this->ActionLog->customSave('payment_' . $actionLogType . '_added', $this->AppAuth->getUserId(), $newPayment->id, 'payments', $message);

        if (in_array($actionLogType, ['deposit_customer', 'deposit_manufacturer', 'member_fee_flexible'])) {
            $message .= '. Der Betrag ist ';
            switch ($actionLogType) {
                case 'deposit_customer':
                    $message .= 'im Guthaben-System von <b>' . $customer->name . '</b>';
                    break;
                case 'deposit_manufacturer':
                    $message .= 'im Pfandkonto von <b>' . $manufacturer->name . '</b>';
                    break;
                case 'member_fee_flexible':
                    $message .= 'im Mitgliedsbeitrags-System von <b>' . $customer->name . '</b>';
                    break;
            }
            $message .= ' eingetragen worden und kann dort wieder gelöscht werden.';
        }

        $this->Flash->success($message);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok',
            'amount' => $amount,
            'paymentId' => $newPayment->id
        ]));
    }

    public function changeState()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

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
            die(json_encode([
                'status' => 0,
                'msg' => $message
            ]));
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

        $message = 'Die Zahlung (' . Configure::read('app.htmlHelper')->formatAsEuro($payment->amount). ', '. Configure::read('app.htmlHelper')->getPaymentText($payment->type) .')';

        if ($this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $payment->id_customer) {
            if (isset($payment->customer->name)) {
                $username = $payment->customer->name;
            } else {
                $username = $payment->manufacturer->name;
            }
            $message .= ' von ' . $username;
        }

        $message .= ' wurde erfolgreich gelöscht.';

        $this->ActionLog->customSave('payment_' . $actionLogType . '_deleted', $this->AppAuth->getUserId(), $paymentId, 'payments', $message . ' (PaymentId: ' . $paymentId . ')');

        $this->Flash->success($message);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    /**
     * $this->customerId needs to be set in calling method
     * @return int
     */
    private function getCustomerId()
    {
        $customerId = '';
        if (!empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = $this->getRequest()->getQuery('customerId');
        } if ($this->customerId > 0) {
            $customerId = $this->customerId;
        }
        return $customerId;
    }

    public function overview()
    {
        $this->customerId = $this->AppAuth->getUserId();
        $this->paymentType = 'product';
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
        $this->set('title_for_layout', 'Mitgliedsbeitrag');

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
        $this->set('title_for_layout', 'Guthaben');

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

        $contain = ['Payments'];
        if (in_array('product', $this->allowedPaymentTypes)) {
            $contain[] = 'PaidCashFreeOrders';
            if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
                $contain[] = 'PaidCashFreeOrders.TimebasedCurrencyOrders';
            }
        }
        

        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $this->getCustomerId()
            ],
            'contain' => $contain
        ])->first();

        $payments = [];
        if (!empty($customer->payments)) {
            foreach ($customer->payments as $payment) {
                $text = Configure::read('app.htmlHelper')->getPaymentText($payment->type);
                if ($payment->type == 'member_fee') {
                    $text .= ' für: ' . Configure::read('app.htmlHelper')->getMemberFeeTextForFrontend($payment->text);
                } else {
                    $text .= (! empty($payment->text) ? ': "' . $payment->text . '"' : '');
                }

                $payments[] = [
                    'dateRaw' => $payment->date_add,
                    'date' => $payment->date_add->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                    'year' => $payment->date_add->i18nFormat(Configure::read('DateFormat.de.Year')),
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

        if (! empty($customer->paid_cash_free_orders)) {
            foreach ($customer->paid_cash_free_orders as $order) {
                $payments[] = [
                    'dateRaw' => $order->date_add,
                    'date' => $order->date_add->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                    'year' => $order->date_add->i18nFormat(Configure::read('DateFormat.de.Year')),
                    'amount' => $order->total_paid * - 1,
                    'deposit' => strtotime($order->date_add->i18nFormat(Configure::read('DateFormat.DatabaseWithTime'))) > strtotime(Configure::read('app.depositPaymentCashlessStartDate')) ? $order->total_deposit * - 1 : 0,
                    'type' => 'order',
                    'text' => Configure::read('app.htmlHelper')->link('Bestellung Nr. ' . $order->id_order . ' (' . Configure::read('app.htmlHelper')->getOrderStates()[$order['current_state']] . ')', '/admin/order-details/?dateFrom=' . $order['date_add']->i18nFormat(Configure::read('DateFormat.de.DateLong2')) . '&dateTo=' . $order->date_add->i18nFormat(Configure::read('DateFormat.de.DateLong2')) . '&orderId=' . $order->id_order . '&customerId=' . $order->id_customer, [
                        'title' => 'Bestellung anzeigen'
                    ]),
                    'payment_id' => null,
                    'timebased_currency_order' => isset($order->timebased_currency_order) ? $order->timebased_currency_order : null
                ];
            }
        }
        
        $timebasedCurrencyOrderInList = false;
        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
            if (!empty($customer->paid_cash_free_orders)) {
                foreach($customer->paid_cash_free_orders as $order) {
                    if (!empty($order->timebased_currency_order)) {
                        $timebasedCurrencyOrderInList = true;
                        break;
                    }
                }
            }
        }
        $this->set('timebasedCurrencyOrderInList', $timebasedCurrencyOrderInList);

        $payments = Hash::sort($payments, '{n}.date', 'desc');
        $this->set('payments', $payments);
        $this->set('customerId', $this->getCustomerId());

        $this->set('column_title', $this->viewVars['title_for_layout']);

        $title = $this->viewVars['title_for_layout'];
        if (in_array($this->getRequest()->getParam('action'), ['product', 'member_fee'])) {
            $title .= ' von ' . $customer->name;
        }
        $this->set('title_for_layout', $title);

        $this->set('paymentType', $this->paymentType);
    }
}
