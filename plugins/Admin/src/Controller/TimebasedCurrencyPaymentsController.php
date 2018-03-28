<?php

namespace Admin\Controller;

use Cake\Event\Event;
use Cake\Core\Configure;
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
            case 'add':
                return Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $this->AppAuth->user('timebased_currency_enabled');
                break;
            case 'myPaymentsManufacturer':
                return Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $this->AppAuth->isManufacturer() && $this->AppAuth->manufacturer->timebased_currency_enabled;
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
    
    public function add()
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        $time = $this->request->getData('time');
        $customerId = $this->request->getData('customerId');
        $manufacturerId = $this->request->getData('manufacturerId');
        
        $newPaymentEntity = $this->TimebasedCurrencyPayment->newEntity(
            [
                'status' => APP_ON,
                'id_customer' => $customerId,
                'id_manufacturer' => $manufacturerId,
                'time' => $time,
                'created_by' => $this->AppAuth->getUserId()
            ]
        );
        
        if (!empty($newPaymentEntity->getErrors())) {
            $status = 0;
            $message = 'Fehler!';
        } else {
            $newPayment = $this->TimebasedCurrencyPayment->save($newPaymentEntity);
            $message = 'hurra';
        }
        
        $this->set('data', [
            'status' => 1,
            'msg' => $message,
            'time' => $time,
            'paymentId' => $newPayment->id
        ]);
        
        $this->set('_serialize', 'data');
        
    }
    
    public function myPaymentsManufacturer()
    {
        $this->set('title_for_layout', 'Mein ' . Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . 'konto');
        
        $timebasedCurrencyOrderDetails = $this->TimebasedCurrencyOrderDetail->find('all', [
            'conditions' => [
                'Products.id_manufacturer' => $this->AppAuth->getManufacturerId()
            ],
            'contain' => [
                'OrderDetails.Products',
                'OrderDetails.Orders.Customers'
            ]
        ]);
        
        $payments = [];
        foreach($timebasedCurrencyOrderDetails as $timebasedCurrencyOrderDetail) {
            $payments[$timebasedCurrencyOrderDetail->order_detail->order->id_customer] = [
                'customerId' => $timebasedCurrencyOrderDetail->order_detail->order->customer->id_customer,
                'customerName' => $timebasedCurrencyOrderDetail->order_detail->order->customer->name
            ];
        }
        
        foreach($payments as &$payment) {
            $payment['text'] = '';
            $payment['timeDone'] = $this->TimebasedCurrencyPayment->getSumManufacturer($this->AppAuth->getManufacturerId(), $payment['customerId']);
            $payment['timeOpen'] = $this->TimebasedCurrencyOrder->getSumCustomer($payment['customerId'], $this->AppAuth->getManufacturerId()) * -1;
        }
        
        $this->set('payments', $payments);
        
        $sumPayments = $this->TimebasedCurrencyPayment->getSumManufacturer($this->AppAuth->getManufacturerId());
        $this->set('sumPayments', $sumPayments);
        
        $sumOrders = $this->TimebasedCurrencyOrder->getSumManufacturer($this->AppAuth->getManufacturerId());
        $this->set('sumOrders', $sumOrders * -1);
        
        $creditBalance = $sumPayments - $sumOrders;
        $this->set('creditBalance', $creditBalance);
        
        
    }
    
    public function myPaymentsCustomer()
    {
        $this->set('title_for_layout', 'Mein ' . Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . 'konto');
        
        $timebasedCurrencyOrders = $this->TimebasedCurrencyOrder->find('all', [
            'conditions' => [
                'Orders.id_customer' => $this->AppAuth->getUserId()
            ],
            'contain' => [
                'Orders'
            ]
        ]);
        
        $payments = [];
        foreach($timebasedCurrencyOrders as $timebasedCurrencyOrder) {
            $payments[] = [
                'dateRaw' => $timebasedCurrencyOrder->order->date_add,
                'date' => $timebasedCurrencyOrder->order->date_add->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                'year' => $timebasedCurrencyOrder->order->date_add->i18nFormat(Configure::read('DateFormat.de.Year')),
                'timeOpen' => $timebasedCurrencyOrder->time_sum * - 1,
                'timeDone' => null,
                'type' => 'order',
                'approval' => APP_OFF,
                'text' => Configure::read('app.htmlHelper')->link('Bestellung Nr. ' . $timebasedCurrencyOrder->order->id_order . ' (' . Configure::read('app.htmlHelper')->getOrderStates()[$timebasedCurrencyOrder->order->current_state] . ')', '/admin/order-details/?dateFrom=' . $timebasedCurrencyOrder->order->date_add->i18nFormat(Configure::read('DateFormat.de.DateLong2')) . '&dateTo=' . $timebasedCurrencyOrder->order->date_add->i18nFormat(Configure::read('DateFormat.de.DateLong2')) . '&orderId=' . $timebasedCurrencyOrder->order->id_order . '&customerId=' . $timebasedCurrencyOrder->order->id_customer, [
                    'title' => 'Bestellung anzeigen'
                ]),
                'manufacturerName' => '',
                'payment_id' => null
            ];
        }
        
        $timebasedCurrencyPayments = $this->TimebasedCurrencyPayment->find('all', [
            'conditions' => [
                'TimebasedCurrencyPayments.id_customer' => $this->AppAuth->getUserId()
            ],
            'contain' => [
                'Manufacturers'
            ]
        ]);
        foreach($timebasedCurrencyPayments as $timebasedCurrencyPayment) {
            $payments[] = [
                'dateRaw' => $timebasedCurrencyPayment->created,
                'date' => $timebasedCurrencyPayment->created->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                'year' => $timebasedCurrencyPayment->created->i18nFormat(Configure::read('DateFormat.de.Year')),
                'timeOpen' => null,
                'timeDone' => $timebasedCurrencyPayment->time,
                'type' => 'payment',
                'approval' => $timebasedCurrencyPayment->approval,
                'text' => '',
                'manufacturerName' => $timebasedCurrencyPayment->manufacturer->name,
                'payment_id' => $timebasedCurrencyPayment->id_payment
            ];
        }
        
        $payments = Hash::sort($payments, '{n}.date', 'desc');
        $this->set('payments', $payments);
        
        $sumPayments = $this->TimebasedCurrencyPayment->getSumCustomer($this->AppAuth->getUserId());
        $this->set('sumPayments', $sumPayments);
        
        $sumOrders = $this->TimebasedCurrencyOrder->getSumCustomer($this->AppAuth->getUserId());
        $this->set('sumOrders', $sumOrders * -1);
        
        $creditBalance = $sumPayments - $sumOrders;
        $this->set('creditBalance', $creditBalance);
        
        $manufacturersForDropdown = $this->TimebasedCurrencyOrderDetail->getManufacturersForDropdown($this->AppAuth->getUserId());
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        
    }

    
}
