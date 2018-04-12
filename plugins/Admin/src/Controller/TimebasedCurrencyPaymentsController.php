<?php

namespace Admin\Controller;

use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TimebasedCurrencyPaymentsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->request->action) {
            case 'myPaymentsCustomer':
                return $this->AppAuth->isTimebasedCurrencyEnabledForCustomer();
                break;
            case 'add':
            case 'delete':
                return $this->AppAuth->isTimebasedCurrencyEnabledForCustomer() || $this->AppAuth->isSuperadmin();
                break;
            case 'myPaymentsManufacturer':
            case 'myPaymentDetailsManufacturer':
                return $this->AppAuth->isTimebasedCurrencyEnabledForManufacturer();
                break;
            case 'edit':
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isTimebasedCurrencyEnabledForManufacturer();
                break;
            case 'paymentsManufacturer':
            case 'paymentsDetailsSuperadmin':
                return $this->AppAuth->isSuperadmin();
                break;
            default:
                return parent::isAuthorized($user);
                break;
        }
    }

    public function beforeFilter(Event $event)
    {
        $this->TimebasedCurrencyPayment = TableRegistry::get('TimebasedCurrencyPayments');
        $this->TimebasedCurrencyOrder = TableRegistry::get('TimebasedCurrencyOrders');
        $this->TimebasedCurrencyOrderDetail = TableRegistry::get('TimebasedCurrencyOrderDetails');
        parent::beforeFilter($event);
    }
    
    public function delete()
    {
        $this->RequestHandler->renderAs($this, 'ajax');
        
        $paymentId = $this->request->getData('paymentId');
        
        $payment = $this->TimebasedCurrencyPayment->find('all', [
            'conditions' => [
                'TimebasedCurrencyPayments.id' => $paymentId,
                'TimebasedCurrencyPayments.approval <> ' . APP_ON
            ],
            'contain' => [
                'Customers'
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
        
        $this->TimebasedCurrencyPayment->save(
            $this->TimebasedCurrencyPayment->patchEntity(
                $payment,
                [
                    'status' => APP_DEL
                ]
            )
        );
        
        $this->ActionLog = TableRegistry::get('ActionLogs');
        $message = 'Die Zeit-Eintragung';
        if ($payment->working_day) {
            $message .= ' für den ' . $payment->working_day->i18nFormat(Configure::read('DateFormat.de.DateLong2')) . ' ';
        }
        $message .= ' <b>(' . Configure::read('app.timebasedCurrencyHelper')->formatSecondsToTimebasedCurrency($payment->seconds). ')</b> ';
        
        if ($this->AppAuth->getUserId() != $payment->id_customer) {
            $message .= ' von ' . $payment->customer->name;
        }
        $message .= ' wurde erfolgreich gelöscht.';
        
        $this->ActionLog->customSave('timebased_currency_payment_deleted', $this->AppAuth->getUserId(), $paymentId, 'timebased_currency_payments', $message . ' (PaymentId: ' . $paymentId . ')');
        $this->Flash->success($message);
        
        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }
    
    public function _processForm($payment, $isEditMode)
    {
        
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);
        
        if (empty($this->request->getData())) {
            $this->set('payment', $payment);
            return;
        }
        
        $this->loadComponent('Sanitize');
        $this->request->data = $this->Sanitize->trimRecursive($this->request->getData());
        $this->request->data = $this->Sanitize->stripTagsRecursive($this->request->getData(), ['approval_comment', 'text']);
        
        if (!empty($this->request->getData('TimebasedCurrencyPayments.working_day'))) {
            $this->request->data['TimebasedCurrencyPayments']['working_day'] = new Time($this->request->getData('TimebasedCurrencyPayments.working_day'));
        }
        
        $this->request->data['TimebasedCurrencyPayments']['seconds'] = $this->request->getData('TimebasedCurrencyPayments.hours') * 3600 + $this->request->getData('TimebasedCurrencyPayments.minutes') * 60;
        $this->request->data['TimebasedCurrencyPayments']['modified_by'] = $this->AppAuth->getUserId();
        
        $payment = $this->TimebasedCurrencyPayment->patchEntity($payment, $this->request->getData());
        if (!empty($payment->getErrors())) {
            $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            $this->set('payment', $payment);
            $this->render('edit');
        } else {
            $payment = $this->TimebasedCurrencyPayment->save($payment);
            
            if (!$isEditMode) {
                $messageSuffix = 'erstellt';
                $actionLogType = 'timebased_currency_payment_added';
            } else {
                $messageSuffix = 'geändert';
                $actionLogType = 'timebased_currency_payment_changed';
            }
            
            $this->ActionLog = TableRegistry::get('ActionLogs');
            $message = 'Die Zeiteintragung ';
            if ($payment->working_day) {
                $message .= ' für den ' . $payment->working_day->i18nFormat(Configure::read('DateFormat.de.DateLong2')) . ' ';
            }
            $message .= '<b>(' . Configure::read('app.timebasedCurrencyHelper')->formatSecondsToTimebasedCurrency($payment->seconds) . ')</b>';
            
            if ($this->AppAuth->getUserId() != $payment->id_customer) {
                $this->Customer = TableRegistry::get('Customers');
                $customer = $this->Customer->find('all', [
                    'conditions' => [
                        'Customers.id_customer' => $payment->id_customer
                    ],
                ])->first();
                    
                $message .= ' ';
                if ($isEditMode) {
                    $message .= 'von';
                } else {
                    $message .= 'für';
                }
                $message .= ' ' . $customer->name;
            }
            
            $message .= ' wurde ' . $messageSuffix . '.';
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $payment->id, 'payments', $message);
            $this->Flash->success($message);
            
            $this->request->getSession()->write('highlightedRowId', $payment->id);
            $this->redirect($this->request->getData('referer'));
        }
        
        $this->set('payment', $payment);
    }
    
    public function add($customerId)
    {
        $payment = $this->TimebasedCurrencyPayment->newEntity(
            [
                'active' => APP_ON,
                'id_customer' => $this->AppAuth->isSuperadmin() ? $customerId : $this->AppAuth->getUserUid(),
                'created_by' => $this->AppAuth->getUserId(),
                'working_day' => Configure::read('app.timeHelper')->getCurrentDay()
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', 'Zeit-Eintragung erstellen');
        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $manufacturersForDropdown = $this->Manufacturer->getTimebasedCurrencyManufacturersForDropdown();
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        $this->_processForm($payment, false);
        
        if (empty($this->request->getData())) {
            $this->render('edit');
        }
        
    }
    
    public function edit($paymentId)
    {
        if ($paymentId === null) {
            throw new NotFoundException;
        }
        
        $payment = $this->TimebasedCurrencyPayment->find('all', [
            'conditions' => [
                'TimebasedCurrencyPayments.id' => $paymentId
            ]
        ])->first();
        
        $payment->hours = (int) ($payment->seconds / 3600);
        $payment->minutes = (int) ($payment->seconds % 3600 / 60);
        
        if (empty($payment)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', 'Zeit-Eintragung bearbeiten');
        $this->_processForm($payment, true);
    }
    
    private function paymentListManufacturer($manufacturerId)
    {
        $customersFromOrderDetails = $this->TimebasedCurrencyOrderDetail->getUniqueCustomers($manufacturerId);
        $customersFromPayments = $this->TimebasedCurrencyPayment->getUniqueCustomers($manufacturerId);
        
        $customers = array_merge($customersFromOrderDetails, $customersFromPayments);
        $customers = array_unique($customers);
        
        $payments = [];
        foreach($customers as $customer) {
            $payment = [];
            $payment['customer'] = $customer;
            $payment['manufacturerId'] = $manufacturerId;
            $payment['unapprovedCount'] = $this->TimebasedCurrencyPayment->getUnapprovedCount($manufacturerId, $customer->id_customer);
            $payment['secondsDone'] = $this->TimebasedCurrencyPayment->getSum($manufacturerId, $customer->id_customer) * -1;
            $payment['secondsOpen'] = $this->TimebasedCurrencyOrderDetail->getSum($manufacturerId, $customer->id_customer);
            $payment['creditBalance'] = $payment['secondsOpen'] + $payment['secondsDone'];
            $payments[] = $payment;
        }
        
        $this->set('payments', $payments);
        
        $sumUnapprovedPaymentsCount = $this->TimebasedCurrencyPayment->getUnapprovedCount($manufacturerId);
        $this->set('sumUnapprovedPaymentsCount', $sumUnapprovedPaymentsCount);
        
        $sumPayments = $this->TimebasedCurrencyPayment->getSum($manufacturerId);
        $this->set('sumPayments', $sumPayments * -1);
        
        $sumOrders = $this->TimebasedCurrencyOrderDetail->getSum($manufacturerId);
        $this->set('sumOrders', $sumOrders);
        
        $creditBalance = $this->TimebasedCurrencyOrderDetail->getCreditBalance($manufacturerId);
        $this->set('creditBalance', $creditBalance);
    }
    
    public function paymentsManufacturer($manufacturerId)
    {
        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
        ])->first();
        $this->set('paymentBalanceTitle', 'Kontostand von ' . $manufacturer->name);
        $this->set('title_for_layout', Configure::read('app.timebasedCurrencyHelper')->getName() . ' von ' . $manufacturer->name);
        $this->paymentListManufacturer($manufacturerId);
        $this->render('paymentsManufacturer');
    }
    
    public function myPaymentsManufacturer()
    {
        $this->set('title_for_layout', 'Mein ' . Configure::read('app.timebasedCurrencyHelper')->getName());
        $this->set('paymentBalanceTitle', 'Mein Kontostand');
        $this->paymentListManufacturer($this->AppAuth->getManufacturerId());
        $this->render('paymentsManufacturer');
    }
    
    public function myPaymentsCustomer()
    {
        $this->set('showAddForm', true);
        $this->set('isDeleteAllowedGlobally', true);
        $this->set('isEditAllowedGlobally', false);
        $this->set('title_for_layout', 'Mein ' . Configure::read('app.timebasedCurrencyHelper')->getName());
        $this->paymentListCustomer(null, $this->AppAuth->getUserId());
        $this->set('paymentBalanceTitle', 'Mein Kontostand');
        $this->set('helpText', [
            'Hier kannst du deine Zeit-Eintragungen erstellen und löschen.',
            'Du siehst auch, wenn der Hersteller Korrekturen vorgenommen bzw. Kommentare zu deinen Zeit-Eintragungen erstellt hat.',
            'Durchgestrichene Zeit-Eintragungen wurden vom Hersteller gesperrt und zählen nicht zur Summe. Du kannst sie korrigieren, indem du sie löschst und anschließend neu erstellst.'
        ]);
        $this->render('paymentsCustomer');
    }
    
    public function myPaymentDetailsManufacturer($customerId)
    {
        $this->Customer = TableRegistry::get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
        ])->first();
        
        $this->set('showAddForm', false);
        $this->set('isDeleteAllowedGlobally', false);
        $this->set('isEditAllowedGlobally', true);
        $this->set('title_for_layout', Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . 'konto von ' . $customer->name);
        $this->set('paymentBalanceTitle', 'Kontostand von ' . $customer->name);
        $this->set('helpText', [
            'Hier kannst du die Zeit-Eintragungen von ' . $customer->name . ' bestätigen und gegebenfalls bearbeiten.',
            'Durchgestrichene Zeit-Eintragungen wurden vom Mitglied gelöscht und zählen nicht zur Summe.'
        ]);
        $this->paymentListCustomer($this->AppAuth->getManufacturerId(), $customerId);
        $this->render('paymentsCustomer');
    }
    
    public function paymentDetailsSuperadmin($manufacturerId = null, $customerId)
    {
        $this->Customer = TableRegistry::get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
        ])->first();
        
        if ($manufacturerId > 0) {
            $this->Manufacturer = TableRegistry::get('Manufacturers');
            $manufacturer = $this->Manufacturer->find('all', [
                'conditions' => [
                    'Manufacturers.id_manufacturer' => $manufacturerId
                ],
            ])->first();
        }
        
        $this->set('showAddForm', true);
        $this->set('isDeleteAllowedGlobally', true);
        $this->set('isEditAllowedGlobally', true);
        $titleForLayout = Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . 'konto von ' . $customer->name;
        if (!empty($manufacturer)) {
            $titleForLayout .= ' für ' . $manufacturer->name;
        }
        $this->set('title_for_layout', $titleForLayout);
        $this->set('paymentBalanceTitle', 'Kontostand von ' . $customer->name);
        $this->set('helpText', [
            'Hier kannst du die Zeit-Eintragungen von ' . $customer->name . ' bestätigen und gegebenfalls bearbeiten.',
            'Durchgestrichene Zeit-Eintragungen wurden vom Mitglied gelöscht und zählen nicht zur Summe.'
        ]);
        $this->paymentListCustomer($manufacturerId, $customerId);
        $this->render('paymentsCustomer');
    }
    
    private function paymentListCustomer($manufacturerId = null, $customerId)
    {
     
        $timebasedCurrencyOrders = $this->TimebasedCurrencyOrderDetail->getOrders($manufacturerId, $customerId);
        
        $payments = [];
        foreach($timebasedCurrencyOrders as $orderId => $timebasedCurrencyOrder) {
            $payments[] = [
                'dateRaw' => $timebasedCurrencyOrder['order']->date_add,
                'date' => $timebasedCurrencyOrder['order']->date_add->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                'year' => $timebasedCurrencyOrder['order']->date_add->i18nFormat(Configure::read('DateFormat.de.Year')),
                'secondsOpen' => $timebasedCurrencyOrder['SumSeconds'] * - 1,
                'secondsDone' => null,
                'type' => 'order',
                'approval' => '',
                'approvalComment' => null,
                'isDeleteAllowed' => null,
                'isEditAllowed' => null,
                'showDeletedRecords' => null,
                'text' => Configure::read('app.htmlHelper')->link(
                    'Bestellung Nr. ' . $orderId . ' (' . 
                        Configure::read('app.htmlHelper')->getOrderStates()[$timebasedCurrencyOrder['order']->current_state] . ')',
                        '/admin/order-details/?dateFrom=' . $timebasedCurrencyOrder['order']->date_add->i18nFormat(Configure::read('DateFormat.de.DateLong2')) . 
                        '&dateTo=' . $timebasedCurrencyOrder['order']->date_add->i18nFormat(Configure::read('DateFormat.de.DateLong2')) .
                        '&orderId=' . $orderId . '&customerId=' . $timebasedCurrencyOrder['order']->id_customer, [
                    'title' => 'Bestellung anzeigen'
                ]),
                'manufacturerName' => '',
                'status' => null,
                'paymentId' => null
            ];
        }
        
        $conditions = [
            'TimebasedCurrencyPayments.id_customer' => $customerId
        ];
        if ($manufacturerId) {
            $conditions['TimebasedCurrencyPayments.id_manufacturer'] = $manufacturerId;
        }
        $showDeletedRecords = $this->AppAuth->isManufacturer() ? true : false;
        if (!$showDeletedRecords) {
            $conditions['TimebasedCurrencyPayments.status'] = APP_ON;
        }
        $timebasedCurrencyPayments = $this->TimebasedCurrencyPayment->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Manufacturers'
            ]
        ]);
        foreach($timebasedCurrencyPayments as $timebasedCurrencyPayment) {
            $payments[] = [
                'dateRaw' => $timebasedCurrencyPayment->created,
                'date' => $timebasedCurrencyPayment->created->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                'year' => $timebasedCurrencyPayment->created->i18nFormat(Configure::read('DateFormat.de.Year')),
                'workingDay' => $timebasedCurrencyPayment->working_day,
                'secondsOpen' => null,
                'secondsDone' => $timebasedCurrencyPayment->seconds,
                'type' => 'payment',
                'approval' => $timebasedCurrencyPayment->approval,
                'approvalComment' => $timebasedCurrencyPayment->approval_comment,
                'isDeleteAllowed' => $timebasedCurrencyPayment->approval <= APP_OFF,
                'isEditAllowed' => $timebasedCurrencyPayment->status > APP_DEL,
                'showDeletedRecords' => $showDeletedRecords,
                'text' => $timebasedCurrencyPayment->text . ($timebasedCurrencyPayment->status == APP_DEL ? 'Dieser Datensatz wurde gelöscht, die Anzahl der eintragenen Stunden zählen nicht zur Summe.' : ''),
                'manufacturerName' => $timebasedCurrencyPayment->manufacturer->name,
                'status' => $timebasedCurrencyPayment->status,
                'paymentId' => $timebasedCurrencyPayment->id
            ];
        }
        
        $payments = Hash::sort($payments, '{n}.date', 'desc');
        $this->set('payments', $payments);
        
        $sumPayments = $this->TimebasedCurrencyPayment->getSum($manufacturerId, $customerId);
        $this->set('sumPayments', $sumPayments);
        
        $sumOrders = $this->TimebasedCurrencyOrderDetail->getSum($manufacturerId, $customerId);
        $this->set('sumOrders', $sumOrders * -1);
        
        $creditBalance = $sumPayments - $sumOrders;
        $this->set('creditBalance', $creditBalance);
        
        $this->set('customerId', $customerId);
        
    }
    
}
