<?php

namespace Admin\Controller;
use App\Mailer\AppEmail;
use Cake\Datasource\Exception\RecordNotFoundException;
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
        switch ($this->request->action) {
            case 'overview':
                return Configure::read('app.htmlHelper')->paymentIsCashless() && $this->AppAuth->user() && ! $this->AppAuth->isManufacturer();
                break;
            case 'myMemberFee':
                return Configure::read('app.memberFeeEnabled') && $this->AppAuth->user() && ! $this->AppAuth->isManufacturer();
                break;
            case 'product':
                // allow redirects for legacy links
                if (empty($this->request->getQuery('customerId'))) {
                    $this->redirect(Configure::read('app.slugHelper')->getMyCreditBalance());
                }
                return $this->AppAuth->isSuperadmin();
                break;
            case 'memberFee':
                if (empty($this->request->getQuery('customerId'))) {
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
        $this->Payment = TableRegistry::get('Payments');
        $this->Customer = TableRegistry::get('Customers');
        $this->Manufacturer = TableRegistry::get('Manufacturers');
        parent::beforeFilter($event);
    }

    public function previewEmail($paymentId, $approval)
    {

        $payment = $this->Payment->find('all', [
            'conditions' => [
                'Payments.id' => $paymentId,
                'Payments.type' => 'product'
            ]
        ])->first();
        if (empty($payment)) {
            throw new RecordNotFoundException('payment not found');
        }

        if (!in_array($approval, [1,-1])) {
            throw new RecordNotFoundException('approval not implemented');
        }

        $payment['Payments']['approval'] = $approval;
        $payment['Payments']['approval_comment'] = 'Hier wird dein Kommentar angezeigt.';
        $email = new AppEmail();
        $email->setTemplate('Admin.payment_status_changed')
            ->setTo($payment['Customers']['email'])
            ->setViewVars([
                'appAuth' => $this->AppAuth,
                'data' => $payment,
                'newStatusAsString' => Configure::read('app.htmlHelper')->getApprovalStates()[$approval],
                'request' => $payment
            ]);
        $html = $email->getHtmlMessage();
        if ($html != '') {
            echo $html;
            exit;
        }
    }

    public function edit($paymentId)
    {

        $this->setFormReferer();

        $unsavedPayment = $this->Payment->find('all', [
            'conditions' => [
                'Payments.id' => $paymentId,
                'Payments.type' => 'product'
            ]
        ])->first();

        if (empty($unsavedPayment)) {
            throw new RecordNotFoundException('payment not found');
        }

        $this->set('unsavedPayment', $unsavedPayment);
        $this->set('paymentId', $paymentId);
        $this->set('title_for_layout', 'Guthaben-Aufladung überprüfen');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedPayment;
        } else {
            // validate data - do not use $this->Payment->saveAll()
            $this->Payment->id = $paymentId;
            $this->Payment->set($this->request->data['Payments']);

            $errors = [];
            $this->Payment->validator()['approval'] = $this->Payment->getNumberRangeConfigurationRule(-1, 1);

            if (! $this->Payment->validates()) {
                $errors = array_merge($errors, $this->Payment->validationErrors);
            }

            if (empty($errors)) {
                $this->ActionLog = TableRegistry::get('ActionLogs');

                $this->request->data['Payments']['date_changed'] = date('Y-m-d H:i:s');
                $this->request->data['Payments']['changed_by'] = $this->AppAuth->getUserId();

                $this->Payment->save($this->request->data['Payments'], [
                    'validate' => false
                ]);

                switch ($this->request->data['Payments']['approval']) {
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

                $newStatusAsString = Configure::read('app.htmlHelper')->getApprovalStates()[$this->request->data['Payments']['approval']];

                $message = 'Der Status der Guthaben-Aufladung für '.$this->request->data['Customers']['name'].' wurde erfolgreich auf <b>' .$newStatusAsString.'</b> geändert';
                if ($this->request->data['Payments']['send_email']) {
                    $email = new AppEmail();
                    $email->setTemplate('Admin.payment_status_changed')
                        ->setTo($unsavedPayment['Customers']['email'])
                        ->setSubject('Der Status deiner Guthaben-Aufladung wurde auf "'.$newStatusAsString.'" geändert.')
                        ->setViewVars([
                            'appAuth' => $this->AppAuth,
                            'data' => $unsavedPayment,
                            'newStatusAsString' => $newStatusAsString,
                            'request' => $this->request->data
                        ]);
                    $email->send();
                    $message .= ' und eine E-Mail an das Mitglied verschickt';
                }

                $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->Payment->id, 'payments', $message.' (PaymentId: ' . $this->Payment->id.').');
                $this->Flash->success($message.'.');

                $this->request->getSession()->write('highlightedRowId', $this->Payment->id);

                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind ' . count($errors) . ' Fehler aufgetreten!');
            }
        }
    }

    public function add()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $type = trim($this->params['data']['type']);
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

        $amount = $this->params['data']['amount'];

        if (preg_match('/^\-/', $amount)) {
            $message = 'Ein negativer Betrag ist nicht erlaubt: ' . $amount;
            $this->log($message);
            die(json_encode(['status'=>0,'msg'=>$message]));
        }

        $amount = preg_replace('/[^0-9,.]/', '', $amount);
        $amount = floatval(str_replace(',', '.', $amount));

        if ($type == 'product' && $amount > Configure::read('appDb.FCS_PAYMENT_PRODUCT_MAXIMUM')) {
            $message = 'Der Maximalwert pro Aufladung ist ' . Configure::read('appDb.FCS_PAYMENT_PRODUCT_MAXIMUM');
            $this->log($message);
            die(json_encode(['status'=>0,'msg'=>$message]));
        }

        $text = '';
        if (isset($this->params['data']['text'])) {
            $text = strip_tags(html_entity_decode($this->params['data']['text']));
        }

        $message = Configure::read('app.htmlHelper')->getPaymentText($type);
        if (in_array($type, ['product', 'payback'])) {
            $customerId = (int) $this->params['data']['customerId'];
        }
        if ($type == 'member_fee') {
            $customerId = (int) $this->params['data']['customerId'];
            $text = implode(',', $this->params['data']['months_range']);
        }

        $actionLogType = $type;

        if (in_array($type, [
            'deposit',
            'member_fee_flexible'
        ])) {
            // payments to deposits can be added to customers or manufacturers
            $customerId = (int) $this->params['data']['customerId'];
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
                $message .= ' für ' . $customer['Customers']['name'];
            }

            $manufacturerId = (int) $this->params['data']['manufacturerId'];

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
                $message .= ' für ' . $manufacturer['Manufacturers']['name'];
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
                $message .= ' für ' . $customer['Customers']['name'];
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
        $this->Payment->id = null; // force insert
        $this->Payment->save([
            'status' => APP_ON,
            'type' => $type,
            'id_customer' => $customerId,
            'id_manufacturer' => isset($manufacturerId) ? $manufacturerId : 0,
            'date_add' => date('Y-m-d H:i:s'),
            'date_changed' => date('Y-m-d H:i:s'),
            'amount' => $amount,
            'text' => $text,
            'created_by' => $this->AppAuth->getUserId(),
            'approval_comment' => ''  // column type text cannot have a default value, must be set explicitly even if unused
        ]);

        $this->ActionLog = TableRegistry::get('ActionLogs');
        $message .= ' wurde erfolgreich eingetragen: ' . Configure::read('app.htmlHelper')->formatAsEuro($amount);

        if ($type == 'member_fee') {
            $message .= ', für ' . Configure::read('app.htmlHelper')->getMemberFeeTextForFrontend($text);
        }

        $this->ActionLog->customSave('payment_' . $actionLogType . '_added', $this->AppAuth->getUserId(), $this->Payment->getLastInsertId(), 'payments', $message);

        if (in_array($actionLogType, ['deposit_customer', 'deposit_manufacturer', 'member_fee_flexible'])) {
            $message .= ' Der Betrag ist ';
            switch ($actionLogType) {
                case 'deposit_customer':
                    $message .= 'im Guthaben-System von ' . $customer['Customers']['name'];
                    break;
                case 'deposit_manufacturer':
                    $message .= 'im Pfandkonto von ' . $manufacturer['Manufacturers']['name'];
                    break;
                case 'member_fee_flexible':
                    $message .= 'im Mitgliedsbeitrags-System von ' . $customer['Customers']['name'];
                    break;
            }
            $message .= ' eingetragen worden und kann dort wieder gelöscht werden.';
        }

        $this->Flash->success($message);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok',
            'amount' => $amount,
            'paymentId' => $this->Payment->getLastInsertId()
        ]));
    }

    public function changeState()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $paymentId = $this->params['data']['paymentId'];

        $payment = $this->Payment->find('all', [
            'conditions' => [
                'Payments.id' => $paymentId,
                'Payments.approval <> ' . APP_ON
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

        // update table payments
        $this->Payment->id = $paymentId;
        $this->Payment->save([
            'status' => APP_DEL,
            'date_changed' => date('Y-m-d H:i:s')
        ]);

        $this->ActionLog = TableRegistry::get('ActionLogs');

        $actionLogType = $payment['Payments']['type'];
        if ($payment['Payments']['type'] == 'deposit') {
            $userType = 'customer';
            if ($payment['Payments']['id_manufacturer'] > 0) {
                $userType = 'manufacturer';
            }
            $actionLogType .= '_'.$userType;
        }


        $message = 'Die Zahlung (' . Configure::read('app.htmlHelper')->formatAsEuro($payment['Payments']['amount']). ', '. Configure::read('app.htmlHelper')->getPaymentText($payment['Payments']['type']) .')';

        if ($this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $payment['Payments']['id_customer']) {
            if (isset($payment['Customers']['name'])) {
                $username = $payment['Customers']['name'];
            } else {
                $username = $payment['Manufacturers']['name'];
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
        $sumMemberFeeFlexbile = 0;
        if (Configure::read('app.memberFeeFlexibleEnabled')) {
            $this->allowedPaymentTypes = [
                'member_fee',
                'member_fee_flexible'
            ];
            $sumMemberFeeFlexbile = $this->Payment->getSum($this->AppAuth->getUserId(), 'member_fee_flexible');
            $this->set('sumMemberFeeFlexible', $sumMemberFeeFlexbile);
        }

        $this->Customer->unbindModel([
            'hasMany' => 'PaidCashFreeOrders'
        ]);
        $this->preparePayments();

        $sumMemberFee = $sumMemberFeeFlexbile + $this->Payment->getSum($this->AppAuth->getUserId(), 'member_fee');
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
        $this->Customer->hasMany['Payments']['conditions'][] = 'Payments.type IN ("' . join('", "', $this->allowedPaymentTypes) . '")';

        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $this->getCustomerId()
            ]
        ])->first();

        $payments = [];
        if (!empty($customer['Payments'])) {
            foreach ($customer['Payments'] as $payment) {
                $text = Configure::read('app.htmlHelper')->getPaymentText($payment['type']);
                if ($payment['type'] == 'member_fee') {
                    $text .= ' für: ' . Configure::read('app.htmlHelper')->getMemberFeeTextForFrontend($payment['text']);
                } else {
                    $text .= (! empty($payment['text']) ? ': "' . $payment['text'] . '"' : '');
                }

                $payments[] = [
                    'date' => $payment['date_add'],
                    'year' => Configure::read('app.timeHelper')->getYearFromDbDate($payment['date_add']),
                    'amount' => $payment['amount'],
                    'deposit' => 0,
                    'type' => $payment['type'],
                    'text' => $text,
                    'payment_id' => $payment['id'],
                    'approval' => $payment['approval'],
                    'approval_comment' => $payment['approval_comment']
                ];
            }
        }

        if (! empty($customer['PaidCashFreeOrders'])) {
            foreach ($customer['PaidCashFreeOrders'] as $order) {
                $payments[] = [
                    'date' => $order['date_add'],
                    'year' => Configure::read('app.timeHelper')->getYearFromDbDate($order['date_add']),
                    'amount' => $order['total_paid'] * - 1,
                    'deposit' => strtotime($order['date_add']) > strtotime(Configure::read('app.depositPaymentCashlessStartDate')) ? $order['total_deposit'] * - 1 : 0,
                    'type' => 'order',
                    'text' => Configure::read('app.htmlHelper')->link('Bestellung Nr. ' . $order['id_order'] . ' (' . Configure::read('app.htmlHelper')->getOrderStates()[$order['current_state']] . ')', '/admin/order_details/index/dateFrom:' . Configure::read('app.timeHelper')->formatToDateShort($order['date_add']) . '/dateTo:' . Configure::read('app.timeHelper')->formatToDateShort($order['date_add']) . '/orderId:' . $order['id_order'] . '/customerId:' . $order['id_customer'], [
                        'title' => 'Bestellung anzeigen'
                    ]),
                    'payment_id' => null
                ];
            }
        }

        $payments = Hash::sort($payments, '{n}.date', 'desc');
        $this->set('payments', $payments);
        $this->set('customerId', $this->getCustomerId());

        $this->set('column_title', $this->viewVars['title_for_layout']);

        $title = $this->viewVars['title_for_layout'];
        if (in_array($this->request->action, ['product', 'member_fee'])) {
            $title .= ' von ' . $customer['Customers']['name'];
        }
        $this->set('title_for_layout', $title);

        $this->set('paymentType', $this->paymentType);
    }
}
