<?php

namespace Admin\Controller;

use App\Mailer\AppEmail;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TimebasedCurrencyPaymentsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
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
                if ($this->AppAuth->isTimebasedCurrencyEnabledForManufacturer()) {
                    $paymentId = (int) $this->getRequest()->getParam('pass')[0];
                    $payment = $this->TimebasedCurrencyPayment->find('all', [
                        'conditions' => [
                            'TimebasedCurrencyPayments.id' => $paymentId,
                            'TimebasedCurrencyPayments.id_manufacturer' => $this->AppAuth->getManufacturerId()
                        ]
                    ])->first();
                    
                    if (!empty($payment)) {
                        return true;
                    }
                }
                return $this->AppAuth->isSuperadmin();
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

    /**
     * $param Event $event
     * @see \App\Controller\AppController::beforeFilter()
     */
    public function beforeFilter(Event $event)
    {
        $this->TimebasedCurrencyPayment = TableRegistry::getTableLocator()->get('TimebasedCurrencyPayments');
        $this->TimebasedCurrencyOrder = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrders');
        $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
        parent::beforeFilter($event);
    }
    
    public function delete()
    {
        $this->RequestHandler->renderAs($this, 'ajax');
        
        $paymentId = $this->getRequest()->getData('paymentId');
        
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
        
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $message = 'Die Zeit-Eintragung';
        if ($payment->working_day) {
            $message .= ' für den ' . $payment->working_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . ' ';
        }
        $message .= ' <b>(' . Configure::read('app.timebasedCurrencyHelper')->formatSecondsToTimebasedCurrency($payment->seconds). ')</b> ';
        
        if ($this->AppAuth->getUserId() != $payment->id_customer) {
            $message .= ' von ' . $payment->customer->name;
        }
        $message .= ' wurde erfolgreich gelöscht';
        
        if ($this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $payment->id_customer) {
            $email = new AppEmail();
            $email->setTemplate('Admin.timebased_currency_payment_deleted')
            ->setTo($payment->customer->email)
            ->setSubject('Deine Zeit-Eintragung vom ' . $payment->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')) . ' wurde gelöscht.')
            ->setViewVars([
                'appAuth' => $this->AppAuth,
                'data' => $payment->customer,
                'payment' => $payment
            ]);
            $email->send();
            $message .= ' und eine E-Mail an '.$payment->customer->name.' verschickt';
        }
        $message .= '.';
        
        $this->ActionLog->customSave('timebased_currency_payment_deleted', $this->AppAuth->getUserId(), $paymentId, 'timebased_currency_payments', $message . ' (PaymentId: ' . $paymentId . ')');
        $this->Flash->success($message);
        
        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }
    
    /**
     * @param TimebasedCurrencyPayment $payment
     * @param boolean $isEditMode
     */
    public function _processForm($payment, $isEditMode)
    {
        
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);
        
        if (empty($this->getRequest()->getData())) {
            $this->set('payment', $payment);
            return;
        }
        
        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData(), ['approval_comment', 'text'])));
        
        if (!empty($this->getRequest()->getData('TimebasedCurrencyPayments.working_day'))) {
            $this->setRequest($this->getRequest()->withData('TimebasedCurrencyPayments.working_day', new Time($this->getRequest()->getData('TimebasedCurrencyPayments.working_day'))));
        }
        
        $this->setRequest($this->getRequest()->withData('TimebasedCurrencyPayments.seconds', $this->getRequest()->getData('TimebasedCurrencyPayments.hours') * 3600 + $this->getRequest()->getData('TimebasedCurrencyPayments.minutes') * 60));
        $this->setRequest($this->getRequest()->withData('TimebasedCurrencyPayments.modified_by', $this->AppAuth->getUserId()));
        
        $unchangedPaymentSeconds = $payment->seconds;
        $unchangedPaymentApproval = $payment->approval;
        
        $payment = $this->TimebasedCurrencyPayment->patchEntity($payment, $this->getRequest()->getData());
        
        if (!empty($payment->getErrors())) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('payment', $payment);
            $this->render('edit');
        } else {
            
            $payment = $this->TimebasedCurrencyPayment->save($payment);
            
            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'timebased_currency_payment_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'timebased_currency_payment_changed';
            }
            
            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $message = 'Die Zeiteintragung ';
            if ($payment->working_day) {
                $message .= ' für den ' . $payment->working_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . ' ';
            }
            $message .= '<b>(' . Configure::read('app.timebasedCurrencyHelper')->formatSecondsToTimebasedCurrency($payment->seconds) . ')</b>';
            
            if ($this->AppAuth->getUserId() != $payment->id_customer) {
                $this->Customer = TableRegistry::getTableLocator()->get('Customers');
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
            
            $message .= ' wurde ' . $messageSuffix;
            
            $sendEmailToCustomer = $isEditMode && ($unchangedPaymentSeconds != $payment->seconds) || ($unchangedPaymentApproval != -1 && $payment->approval == -1);
            if ($sendEmailToCustomer) {
                $email = new AppEmail();
                $email->setTemplate('Admin.timebased_currency_payment_information')
                ->setTo($payment->customer->email)
                ->setSubject('Wichtige Informationen zu deiner Zeit-Eintragung vom ' . $payment->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')))
                ->setViewVars([
                    'appAuth' => $this->AppAuth,
                    'data' => $payment->customer,
                    'unchangedPaymentSeconds' => $unchangedPaymentSeconds,
                    'unchangedPaymentApproval' => $unchangedPaymentApproval,
                    'payment' => $payment
                ]);
                $email->send();
                $message .= ' und eine E-Mail an '.$payment->customer->name.' verschickt';
            }
            $message .= '.';
            
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $payment->id, 'payments', $message);
            $this->Flash->success($message);
            
            $this->getRequest()->getSession()->write('highlightedRowId', $payment->id);
            $this->redirect($this->getRequest()->getData('referer'));
        }
        
        $this->set('payment', $payment);
    }
    
    /**
     * @param int $customerId
     */
    public function add($customerId)
    {
        
        if (!$this->AppAuth->isSuperadmin() && $this->AppAuth->getUserId() != $customerId) {
            $this->redirect(Configure::read('app.slugHelper')->getTimebasedCurrencyPaymentAdd($this->AppAuth->getUserId()));
        }
        
        $payment = $this->TimebasedCurrencyPayment->newEntity(
            [
                'active' => APP_ON,
                'id_customer' => $customerId,
                'created_by' => $this->AppAuth->getUserId(),
                'working_day' => Configure::read('app.timeHelper')->getCurrentDay(),
                'approval_comment' => ''  // column type text cannot have a default value, must be set explicitly even if unused
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', 'Zeit-Eintragung erstellen');
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturersForDropdown = $this->Manufacturer->getTimebasedCurrencyManufacturersForDropdown();
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        $this->_processForm($payment, false);
        
        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
        
    }
    
    /**
     * @param int $paymentId
     */
    public function edit($paymentId)
    {
        if ($paymentId === null) {
            throw new NotFoundException;
        }
        
        $payment = $this->TimebasedCurrencyPayment->find('all', [
            'conditions' => [
                'TimebasedCurrencyPayments.id' => $paymentId
            ],
            'contain' => [
                'Customers'
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
    
    /**
     * @param int $manufacturerId
     */
    private function paymentListManufacturer($manufacturerId)
    {
        $customerIdsFromOrderDetails = $this->TimebasedCurrencyOrderDetail->getUniqueCustomerIds($manufacturerId);
        $customerIdsFromPayments = $this->TimebasedCurrencyPayment->getUniqueCustomerIds($manufacturerId);
        
        $customerIds = array_merge($customerIdsFromOrderDetails, $customerIdsFromPayments);
        $customerIds = array_unique($customerIds);
        
        $payments = [];
        foreach($customerIds as $customerId) {
            $payment = [];
            $customer = $this->Customer->find('all', [
                'conditions' => [
                    'Customers.id_customer' => $customerId
                ],
                'contain' => [
                    'AddressCustomers'
                ]
            ])->first();
            $payment['customer'] = $customer;
            $payment['manufacturerId'] = $manufacturerId;
            $payment['customerId'] = $customerId;
            $payment['unapprovedCount'] = $this->TimebasedCurrencyPayment->getUnapprovedCount($manufacturerId, $customerId);
            $payment['secondsDone'] = $this->TimebasedCurrencyPayment->getSum($manufacturerId, $customerId) * -1;
            $payment['secondsOpen'] = $this->TimebasedCurrencyOrderDetail->getSum($manufacturerId, $customerId);
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
    
    /**
     * @param int $manufacturerId
     */
    public function paymentsManufacturer($manufacturerId)
    {
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
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
        $manufacturerId = $this->getRequest()->getQuery('manufacturerId');
        $this->set('showAddForm', true);
        $this->set('isDeleteAllowedGlobally', true);
        $this->set('isEditAllowedGlobally', false);
        $this->set('title_for_layout', 'Mein ' . Configure::read('app.timebasedCurrencyHelper')->getName() . ' für');
        $this->paymentListCustomer($manufacturerId, $this->AppAuth->getUserId());
        $this->set('paymentBalanceTitle', 'Mein Kontostand');
        $this->set('helpText', [
            'Hier kannst du deine Zeit-Eintragungen erstellen und löschen.',
            'Du siehst auch, wenn der Hersteller Anpassungen vorgenommen bzw. Kommentare zu deinen Zeit-Eintragungen erstellt hat.',
            'Durchgestrichene Zeit-Eintragungen wurden vom Hersteller gesperrt und zählen nicht zur Summe. Du kannst sie anpassen, indem du sie löschst und anschließend neu erstellst.'
        ]);
        $this->set('showManufacturerDropdown', true);
        $this->render('paymentsCustomer');
    }
    
    /**
     * @param int $customerId
     */
    public function myPaymentDetailsManufacturer($customerId)
    {
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
        ])->first();
        
        $customerName = Configure::read('app.htmlHelper')->getNameRespectingIsDeleted($customer);
        $this->set('showAddForm', false);
        $this->set('isDeleteAllowedGlobally', false);
        $this->set('isEditAllowedGlobally', true);
        $this->set('title_for_layout', Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . 'konto von ' . $customerName);
        $this->set('paymentBalanceTitle', 'Kontostand von ' . $customerName);
        $this->set('helpText', [
            'Hier kannst du die Zeit-Eintragungen von ' . $customerName . ' bestätigen und gegebenfalls bearbeiten.',
            'Durchgestrichene Zeit-Eintragungen wurden vom Mitglied gelöscht und zählen nicht zur Summe.'
        ]);
        $this->set('showManufacturerDropdown', false);
        $this->paymentListCustomer($this->AppAuth->getManufacturerId(), $customerId);
        $this->render('paymentsCustomer');
    }
    
    /**
     * @param int $customerId
     */
    public function paymentDetailsSuperadmin($customerId)
    {
        $manufacturerId = $this->getRequest()->getQuery('manufacturerId');
        
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
        ])->first();
        
        $this->set('showAddForm', true);
        $this->set('isDeleteAllowedGlobally', true);
        $this->set('isEditAllowedGlobally', true);
        $titleForLayout = Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . 'konto von ' . $customer->name . ' für';
        $this->set('title_for_layout', $titleForLayout);
        $this->set('paymentBalanceTitle', 'Kontostand von ' . $customer->name);
        $this->set('helpText', [
            'Hier kannst du die Zeit-Eintragungen von ' . $customer->name . ' bestätigen und gegebenfalls bearbeiten.',
            'Durchgestrichene Zeit-Eintragungen wurden vom Mitglied gelöscht und zählen nicht zur Summe.'
        ]);
        $this->set('showManufacturerDropdown', true);
        $this->paymentListCustomer($manufacturerId, $customerId);
        $this->render('paymentsCustomer');
    }
    
    /**
     * @param int $manufacturerId
     * @param int $customerId
     */
    private function paymentListCustomer($manufacturerId = null, $customerId)
    {
     
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturersForDropdown = $this->Manufacturer->getTimebasedCurrencyManufacturersForDropdown();
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        
        $timebasedCurrencyOrders = $this->TimebasedCurrencyOrderDetail->getOrders($manufacturerId, $customerId);
        
        $payments = [];
        foreach($timebasedCurrencyOrders as $orderId => $timebasedCurrencyOrder) {
            $payments[] = [
                'dateRaw' => $timebasedCurrencyOrder['order']->date_add,
                'date' => $timebasedCurrencyOrder['order']->date_add->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                'year' => $timebasedCurrencyOrder['order']->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Year')),
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
                        '/admin/order-details/?dateFrom=' . $timebasedCurrencyOrder['order']->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . 
                        '&dateTo=' . $timebasedCurrencyOrder['order']->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) .
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
                'year' => $timebasedCurrencyPayment->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Year')),
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
        $this->set('manufacturerId', $manufacturerId);
        
    }
    
}
