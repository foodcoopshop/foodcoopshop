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
            case 'overview':
                return Configure::read('htmlHelper')->paymentIsCashless() && $this->AppAuth->loggedIn() && ! $this->AppAuth->isManufacturer();
                break;
            case 'my_member_fee':
                return Configure::read('app.memberFeeEnabled') && $this->AppAuth->loggedIn() && ! $this->AppAuth->isManufacturer();
                break;
            case 'product':
                // allow redirects for legacy links
                if (empty($this->params['named']['customerId'])) {
                    $this->redirect(Configure::read('slugHelper')->getMyCreditBalance());
                }
                return $this->AppAuth->isSuperadmin();
                break;
            case 'member_fee':
                if (empty($this->params['named']['customerId'])) {
                    $this->redirect(Configure::read('slugHelper')->getMyMemberFeeBalance());
                }
                return $this->AppAuth->isSuperadmin();
                break;
            case 'edit';
                return $this->AppAuth->isSuperadmin();
                break;
            case 'add':
            case 'changeState':
                return $this->AppAuth->loggedIn();
                break;
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
    
    public function edit($paymentId) {
        
        $this->setFormReferer();
        
        $unsavedPayment = $this->CakePayment->find('first', array(
            'conditions' => array(
                'CakePayment.id' => $paymentId,
                'CakePayment.type' => 'product'
            )
        ));
        
        if (empty($unsavedPayment)) {
            throw new MissingActionException('payment not found');
        }
        
        // START prepare email preview
        $requestForEmailTemplate = $unsavedPayment;
        $requestForEmailTemplate['CakePayment']['approval'] = 1;
        $requestForEmailTemplate['CakePayment']['approval_comment'] = 'Hier wird dein Kommentar angezeigt.';
        $email = new AppEmail();
        $email->template('Admin.payment_status_changed')
          ->emailFormat('html')
          ->to($unsavedPayment['Customer']['email'])
          ->viewVars(array(
            'appAuth' => $this->AppAuth,
            'data' => $unsavedPayment,
            'newStatusAsString' => Configure::read('htmlHelper')->getApprovalStates()[1],
            'request' => $requestForEmailTemplate
        ));
        $requestForEmailTemplate['CakePayment']['approval'] = -1;
        $this->set('emailTemplateOk', $email->_renderTemplates(null)['html']);
        $email->viewVars(array('newStatusAsString' => Configure::read('htmlHelper')->getApprovalStates()[-1]));
        $email->viewVars(array('request' => $requestForEmailTemplate));
        $this->set('emailTemplateNotOk', $email->_renderTemplates(null)['html']);
        // END prepare email preview
        
        $this->set('unsavedPayment', $unsavedPayment);
        $this->set('paymentId', $paymentId);
        $this->set('title_for_layout', 'Guthaben-Aufladung überprüfen');
        
        if (empty($this->request->data)) {
            $this->request->data = $unsavedPayment;
        } else {
        
            // validate data - do not use $this->CakePayment->saveAll()
            $this->CakePayment->id = $paymentId;
            $this->CakePayment->set($this->request->data['CakePayment']);
        
            $errors = array();
            $this->CakePayment->validator()['approval'] = $this->CakePayment->getNumberRangeConfigurationRule(-1,1);
            
            if (! $this->CakePayment->validates()) {
                $errors = array_merge($errors, $this->CakePayment->validationErrors);
            }
        
            if (empty($errors)) {
        
                $this->loadModel('CakeActionLog');
        
                $this->request->data['CakePayment']['date_changed'] = date('Y-m-d H:i:s');
                $this->request->data['CakePayment']['changed_by'] = $this->AppAuth->getUserId();
                
                $this->CakePayment->save($this->request->data['CakePayment'], array(
                    'validate' => false
                ));
        
                switch($this->request->data['CakePayment']['approval']) {
                    case -1;
                        $actionLogType = 'payment_product_approval_not_ok';
                        break;
                    case 0;
                        $actionLogType = 'payment_product_approval_open';
                        break;
                    case 1;
                        $actionLogType = 'payment_product_approval_ok';
                        break;
                }
        
                $newStatusAsString = Configure::read('htmlHelper')->getApprovalStates()[$this->request->data['CakePayment']['approval']];
                
                $message = 'Der Status der Guthaben-Aufladung für '.$this->request->data['Customer']['name'].' wurde erfolgreich auf <b>' .$newStatusAsString.'</b> geändert';
                if ($this->request->data['CakePayment']['send_email']) {
                    $email->subject('Der Status deiner Guthaben-Aufladung wurde auf "'.$newStatusAsString.'" geändert.');
                    $email->viewVars(array(
                        'appAuth' => $this->AppAuth,
                        'data' => $unsavedPayment,
                        'newStatusAsString' => $newStatusAsString,
                        'request' => $this->request->data
                    ));
                    $email->send();
                    $message .= ' und eine E-Mail an das Mitglied verschickt';
                }
                
                $this->CakeActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->CakePayment->id, 'payments', $message.' (PaymentId: ' . $this->CakePayment->id.').');
                $this->AppSession->setFlashMessage($message.'.');
        
                $this->redirect($this->data['referer']);
                
            } else {
                $this->AppSession->setFlashError('Beim Speichern sind ' . count($errors) . ' Fehler aufgetreten!');
            }
        }
        
    }

    public function add()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $type = trim($this->params['data']['type']);
        if (! in_array($type, array(
            'product',
            'deposit',
            'payback',
            'member_fee',
            'member_fee_flexible'
        ))) {
            $message = 'payment type not correct: ' . $type;
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

        if ($type == 'product' && $amount > Configure::read('app.db_config_FCS_PAYMENT_PRODUCT_MAXIMUM')) {
            $message = 'Der Maximalwert pro Aufladung ist ' . Configure::read('app.db_config_FCS_PAYMENT_PRODUCT_MAXIMUM');
            $this->log($message);
            die(json_encode(array('status'=>0,'msg'=>$message)));
        }
        
        $text = '';
        if (isset($this->params['data']['text'])) {
            $text = strip_tags(html_entity_decode($this->params['data']['text']));
        }

        $message = Configure::read('htmlHelper')->getPaymentText($type);
        if (in_array($type, array('product', 'payback'))) {
            $customerId = (int) $this->params['data']['customerId'];
        }
        if ($type == 'member_fee') {
            $customerId = (int) $this->params['data']['customerId'];
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
                    $msg = 'customer id not correct: ' . $customerId;
                    $this->log($msg);
                    die(json_encode(array(
                        'status' => 0,
                        'msg' => $msg
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
                    $msg = 'manufacturer id not correct: ' . $manufacturerId;
                    $this->log($msg);
                    die(json_encode(array(
                        'status' => 0,
                        'msg' => $msg
                    )));
                }
            }
            
            if ($type == 'deposit') {
                $actionLogType .= '_'.$userType;
            }
            
        }

        
        // payments paybacks, product and member_fee can also be placed for other users
        if (in_array($type, array(
            'product',
            'payback',
            'member_fee'
        ))) {
            
            $this->Customer->recursive = - 1;
            $customer = $this->Customer->find('first', array(
                'conditions' => array(
                    'Customer.id_customer' => $customerId
                )
            ));
            if ($this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $customerId) {
                $message .= ' für ' . $customer['Customer']['name'];
            }
            // security check
            if (!$this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $customerId) {
                $msg = 'user without superadmin privileges tried to insert payment for another user: ' . $customerId;
                $this->log($msg);
                die(json_encode(array(
                    'status' => 0,
                    'msg' => $msg
                )));
            }
            if (empty($customer)) {
                $msg = 'customer id not correct: ' . $customerId;
                $this->log($msg);
                die(json_encode(array(
                    'status' => 0,
                    'msg' => $msg
                )));
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
        $this->RequestHandler->renderAs($this, 'ajax');
        
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
        
        $actionLogType = $payment['CakePayment']['type'];
        if ($payment['CakePayment']['type'] == 'deposit') {
            $userType = 'customer';
            if ($payment['CakePayment']['id_manufacturer'] > 0) {
                $userType = 'manufacturer';
            }
            $actionLogType .= '_'.$userType;
        }
        

        $message = 'Die Zahlung (' . Configure::read('htmlHelper')->formatAsEuro($payment['CakePayment']['amount']). ', '. Configure::read('htmlHelper')->getPaymentText($payment['CakePayment']['type']) .')';
        
        if ($this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $payment['CakePayment']['id_customer']) {
            if (isset($payment['Customer']['name'])) {
                $username = $payment['Customer']['name'];
            } else {
                $username = $payment['Manufacturer']['name'];
            }
            $message .= ' von ' . $username;
        }
        
        $message .= ' wurde erfolgreich gelöscht.';
        
        $this->CakeActionLog->customSave('payment_' . $actionLogType . '_deleted', $this->AppAuth->getUserId(), $paymentId, 'payments', $message . ' (PaymentId: ' . $paymentId . ')');

        $this->AppSession->setFlashMessage($message);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }
    
    /**
     * $this->customerId needs to be set in calling method
     * @return int
     */
    private function getCustomerId()
    {
        $customerId = '';
        if (isset($this->request->named['customerId'])) {
            $customerId = $this->request->named['customerId'];
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

    public function my_member_fee()
    {
        $this->customerId = $this->AppAuth->getUserId();
        $this->paymentType = 'member_fee';
        $this->member_fee();
        $this->render('member_fee');
    }
    
    public function member_fee()
    {
                
        $this->paymentType = 'member_fee';
        $this->set('title_for_layout', 'Mitgliedsbeitrag');
        
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

        $this->Customer->unbindModel(array(
            'hasMany' => 'PaidCashFreeOrders'
        ));
        $this->preparePayments();

        $sumMemberFee = $sumMemberFeeFlexbile + $this->CakePayment->getSum($this->AppAuth->getUserId(), 'member_fee');
        $this->set('sumMemberFee', $sumMemberFee);
    }

    public function product()
    {
        
        $this->paymentType = 'product';
        $this->set('title_for_layout', 'Guthaben');

        $this->allowedPaymentTypes = array(
            'product',
            'payback',
            'deposit'
        );
        if (! Configure::read('app.isDepositPaymentCashless')) {
            $this->allowedPaymentTypes = array(
                'product',
                'payback'
            );
        }

        $this->preparePayments();

        $this->set('creditBalance', $this->Customer->getCreditBalance($this->getCustomerId()));
    }

    private function preparePayments()
    {
        $this->Customer->hasMany['CakePayments']['conditions'][] = 'CakePayments.type IN ("' . join('", "', $this->allowedPaymentTypes) . '")';

        $customer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.id_customer' => $this->getCustomerId()
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
                'year' => Configure::read('timeHelper')->getYearFromDbDate($payment['date_add']),
                'amount' => $payment['amount'],
                'deposit' => 0,
                'type' => $payment['type'],
                'text' => $text,
                'payment_id' => $payment['id'],
                'approval' => $payment['approval'],
                'approval_comment' => $payment['approval_comment']
            );
        }

        if (! empty($customer['PaidCashFreeOrders'])) {
            foreach ($customer['PaidCashFreeOrders'] as $order) {
                $payments[] = array(
                    'date' => $order['date_add'],
                    'year' => Configure::read('timeHelper')->getYearFromDbDate($order['date_add']),
                    'amount' => $order['total_paid'] * - 1,
                    'deposit' => strtotime($order['date_add']) > strtotime(Configure::read('app.depositPaymentCashlessStartDate')) ? $order['total_deposit'] * - 1 : 0,
                    'type' => 'order',
                    'text' => Configure::read('htmlHelper')->link('Bestellung Nr. ' . $order['id_order'] . ' (' . Configure::read('htmlHelper')->getOrderStates()[$order['current_state']] . ')', '/admin/order_details/index/dateFrom:' . Configure::read('timeHelper')->formatToDateShort($order['date_add']) . '/dateTo:' . Configure::read('timeHelper')->formatToDateShort($order['date_add']) . '/orderId:' . $order['id_order'] . '/customerId:' . $order['id_customer'], array(
                        'title' => 'Bestellung anzeigen'
                    )),
                    'payment_id' => null
                );
            }
        }

        $payments = Set::sort($payments, '{n}.date', 'desc');
        $this->set('payments', $payments);
        $this->set('customerId', $this->getCustomerId());
        
        $this->set('column_title', $this->viewVars['title_for_layout']);
        
        $title = $this->viewVars['title_for_layout'];
        if (in_array($this->action, array('product', 'member_fee'))) {
            $title .= ' von ' . $customer['Customer']['name'];
        }
        $this->set('title_for_layout',  $title);
        
        $this->set('paymentType',  $this->paymentType);
        
    }
}
