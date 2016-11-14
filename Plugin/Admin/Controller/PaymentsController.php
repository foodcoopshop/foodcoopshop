<?php
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
        switch ($this->action) {
            case 'product':
                return Configure::read('htmlHelper')->paymentIsCashless() && $this->AppAuth->loggedIn() && ! $this->AppAuth->isManufacturer();
                break;
            case 'member_fee':
                return Configure::read('app.memberFeeEnabled') && $this->AppAuth->loggedIn() && ! $this->AppAuth->isManufacturer();
                break;
            case 'add':
            case 'changeState':
                return $this->AppAuth->loggedIn();
            default:
                return $this->AppAuth->loggedIn() && ! $this->AppAuth->isManufacturer();
                break;
        }
    }

    public function beforeFilter()
    {
        $this->loadModel('CakePayment');
        $this->loadModel('Customer');
        $this->loadModel('Manufacturer');
        parent::beforeFilter();
    }

    public function add()
    {
        $this->autoRender = false;

        $type = trim($this->params['data']['type']);
        if (! in_array($type, array(
            'product',
            'deposit',
            'member_fee',
            'member_fee_flexible'
        ))) {
            $message = 'payment type nicht korrekt: ' . $type;
            $this->log($message);
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }

        $amount = $this->params['data']['amount'];
        
        if (preg_match('/\-/', $amount)) {
            $message = 'Ein negativer Betrag ist nicht erlaubt: ' . $amount;
            $this->log($message);
            die(json_encode(array('status'=>0,'msg'=>$message)));
        }
        
        $amount = preg_replace('/[^0-9,.]/', '', $amount);
        $amount = floatval(str_replace(',', '.', $amount));

        $text = '';
        if (isset($this->params['data']['text'])) {
            $text = strip_tags(html_entity_decode($this->params['data']['text']));
        }

        $message = Configure::read('htmlHelper')->getPaymentText($type);
        if ($type == 'product') {
            $customerId = $this->AppAuth->getUserId();
        }
        if ($type == 'member_fee') {
            $customerId = $this->AppAuth->getUserId();
            $text = implode(',', $this->params['data']['months_range']);
        }
        
        $actionLogType = $type;
        
        if (in_array($type, array(
            'deposit',
            'member_fee_flexible'
        ))) {
            
            // payments to deposits can be added to customers or manufacturers
            $customerId = (int) $this->params['data']['customerId'];
            if ($customerId > 0) {
                $userType = 'customer';
                $this->Customer->recursive = - 1;
                $customer = $this->Customer->find('first', array(
                    'conditions' => array(
                        'Customer.id_customer' => $customerId
                    )
                ));
                $message .= ' für ' . $customer['Customer']['name'];
                if (empty($customer)) {
                    $message = 'customer id not correct: ' . $customerId;
                    $this->log($message);
                    die(json_encode(array(
                        'status' => 0,
                        'msg' => $message
                    )));
                }
            }
            
            $manufacturerId = (int) $this->params['data']['manufacturerId'];
            if ($manufacturerId > 0) {
                $userType = 'manufacturer';
                $this->Manufacturer->recursive = - 1;
                $manufacturer = $this->Manufacturer->find('first', array(
                    'conditions' => array(
                        'Manufacturer.id_manufacturer' => $manufacturerId
                    )
                ));
                $message = 'Pfand-Rücknahme ('.Configure::read('htmlHelper')->getManufacturerDepositPaymentText($text).')';
                $message .= ' für ' . $manufacturer['Manufacturer']['name'];
                if (empty($manufacturer)) {
                    $message = 'manufacturer id not correct: ' . $manufacturerId;
                    $this->log($message);
                    die(json_encode(array(
                        'status' => 0,
                        'msg' => $message
                    )));
                }
            }
            
            if ($type == 'deposit') {
                $actionLogType .= '_'.$userType;
            }
            
        }

        // add entry in table cake_payments
        $this->CakePayment->id = null; // force insert
        $this->CakePayment->save(array(
            'status' => APP_ON,
            'type' => $type,
            'id_customer' => $customerId,
            'id_manufacturer' => isset($manufacturerId) ? $manufacturerId : 0,
            'date_add' => date('Y-m-d H:i:s'),
            'date_changed' => date('Y-m-d H:i:s'),
            'amount' => $amount,
            'text' => $text
        ));

        $this->loadModel('CakeActionLog');
        $message .= ' wurde erfolgreich eingetragen: ' . Configure::read('htmlHelper')->formatAsEuro($amount);

        if ($type == 'member_fee') {
            $message .= ', für ' . Configure::read('htmlHelper')->getMemberFeeTextForFrontend($text);
        }

        $this->CakeActionLog->customSave('payment_' . $actionLogType . '_added', $this->AppAuth->getUserId(), $this->CakePayment->getLastInsertId(), 'payments', $message);

        if (in_array($actionLogType, array('deposit_customer', 'deposit_manufacturer', 'member_fee_flexible'))) {
            $message .= ' Der Betrag ist ';
            switch ($actionLogType) {
                case 'deposit_customer':
                    $message .= 'im Guthaben-System von ' . $customer['Customer']['name'];
                    break;
                case 'deposit_manufacturer':
                    $message .= 'im Pfandkonto von ' . $manufacturer['Manufacturer']['name'];
                    break;
                case 'member_fee_flexible':
                    $message .= 'im Mitgliedsbeitrags-System von ' . $customer['Customer']['name'];
                    break;
            }
            $message .= ' eingetragen worden und kann dort wieder gelöscht werden.';
        }
        
        $this->AppSession->setFlashMessage($message);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
        
    }

    public function changeState()
    {
        $this->autoRender = false;
        $paymentId = $this->params['data']['paymentId'];

        $payment = $this->CakePayment->find('first', array(
            'conditions' => array(
                'CakePayment.id' => $paymentId
            )
        ));

        if (empty($payment)) {
            $message = 'payment id not correct: ' . $paymentId;
            $this->log($message);
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }
        
        // TODO add payment owner check (also for manufacturers!)

        // update table cake_payments
        $this->CakePayment->id = $paymentId;
        $this->CakePayment->save(array(
            'status' => APP_DEL,
            'date_changed' => date('Y-m-d H:i:s')
        ));

        $this->loadModel('CakeActionLog');

        $message = 'Die Zahlung wurde erfolgreich gelöscht.';
        
        $actionLogType = $payment['CakePayment']['type'];
        if ($payment['CakePayment']['type'] == 'deposit') {
            $userType = 'customer';
            if ($payment['CakePayment']['id_manufacturer'] > 0) {
                $userType = 'manufacturer';
            }
            $actionLogType .= '_'.$userType;
        }
        
        $this->CakeActionLog->customSave('payment_' . $actionLogType . '_deleted', $this->AppAuth->getUserId(), $paymentId, 'payments', $message . ' (PaymentId: ' . $paymentId . ')');

        $this->AppSession->setFlashMessage($message);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }

    public function member_fee()
    {
        $this->allowedPaymentTypes = array(
            'member_fee'
        );
        $sumMemberFeeFlexbile = 0;
        if (Configure::read('app.memberFeeFlexibleEnabled')) {
            $this->allowedPaymentTypes = array(
                'member_fee',
                'member_fee_flexible'
            );
            $sumMemberFeeFlexbile = $this->CakePayment->getSum($this->AppAuth->getUserId(), 'member_fee_flexible');
            $this->set('sumMemberFeeFlexible', $sumMemberFeeFlexbile);
        }
        $this->set('title_for_layout', 'Mitgliedsbeitrag');

        $this->Customer->unbindModel(array(
            'hasMany' => 'PaidCashFreeOrders'
        ));
        $this->preparePayments();

        $sumMemberFee = $sumMemberFeeFlexbile + $this->CakePayment->getSum($this->AppAuth->getUserId(), 'member_fee');
        $this->set('sumMemberFee', $sumMemberFee);
    }

    public function product()
    {
        $this->set('title_for_layout', 'Guthaben');

        $this->allowedPaymentTypes = array(
            'product',
            'deposit'
        );
        if (! Configure::read('app.isDepositPaymentCashless')) {
            $this->allowedPaymentTypes = array(
                'product'
            );
        }

        $this->preparePayments();

        $this->set('creditBalance', $this->AppAuth->getCreditBalance());
    }

    private function preparePayments()
    {
        $this->Customer->hasMany['CakePayments']['conditions'][] = 'CakePayments.type IN ("' . join('", "', $this->allowedPaymentTypes) . '")';

        $customer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.id_customer' => $this->AppAuth->getUserId()
            )
        ));

        $payments = array();
        foreach ($customer['CakePayments'] as $payment) {

            $text = Configure::read('htmlHelper')->getPaymentText($payment['type']);
            if ($payment['type'] == 'member_fee') {
                $text .= ' für: ' . Configure::read('htmlHelper')->getMemberFeeTextForFrontend($payment['text']);
            } else {
                $text .= (! empty($payment['text']) ? ': "' . $payment['text'] . '"' : '');
            }

            $payments[] = array(
                'date' => $payment['date_add'],
                'amount' => $payment['amount'],
                'deposit' => 0,
                'type' => $payment['type'],
                'text' => $text,
                'payment_id' => $payment['id']
            );
        }

        if (! empty($customer['PaidCashFreeOrders'])) {
            foreach ($customer['PaidCashFreeOrders'] as $order) {
                $payments[] = array(
                    'date' => $order['date_add'],
                    'amount' => $order['total_paid'] * - 1,
                    'deposit' => strtotime($order['date_add']) > strtotime(Configure::read('app.depositPaymentCashlessStartDate')) ? $order['total_deposit'] * - 1 : 0,
                    'type' => 'order',
                    'text' => Configure::read('htmlHelper')->link('Bestellung ' . $order['reference'] . ' (' . Configure::read('htmlHelper')->getOrderStates()[$order['current_state']] . ')', '/admin/order_details/index/dateFrom:' . Configure::read('timeHelper')->formatToDateShort($order['date_add']) . '/dateTo:' . Configure::read('timeHelper')->formatToDateShort($order['date_add']) . '/reference:' . $order['reference'] . '/customerId:' . $order['id_customer'], array(
                        'title' => 'Bestellung anzeigen'
                    )),
                    'payment_id' => null
                );
            }
        }

        $payments = Set::sort($payments, '{n}.date', 'desc');
        $this->set('payments', $payments);
    }
}
