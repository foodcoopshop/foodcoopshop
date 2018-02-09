<?php

namespace Admin\Controller;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * OrdersController
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
class OrdersController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return ! $this->AppAuth->isManufacturer();
    }

    public function recalculateOrderDetailPricesInOrder($orderId)
    {
        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ]
        ])->first();
        $order['OrderDetails']['id_order'] = $orderId;
        $this->Order->recalculateOrderDetailPricesInOrder($order);
    }

    public function editComment()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderId = $this->params['data']['orderId'];
        $orderComment = htmlspecialchars_decode(strip_tags(trim($this->params['data']['orderComment']), '<strong><b>'));

        $oldOrder = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ]
        ])->first();

        $order2update = [
            'comment' => $orderComment
        ];
        $this->Order->id = $oldOrder['Orders']['id_order'];
        $this->Order->save($order2update);

        $this->Flash->success('Der Kommentar wurde erfolgreich geändert.');

        $this->ActionLog = TableRegistry::get('ActionLogs');
        $this->ActionLog->customSave('order_comment_changed', $this->AppAuth->getUserId(), $orderId, 'orders', 'Der Kommentar der Bestellung Nr. ' . $oldOrder['Orders']['id_order'] . ' von '.$oldOrder['Customers']['firstname'] . ' ' . $oldOrder['Customers']['lastname'].' wurde geändert: <br /><br /> alt: <div class="changed">' . $oldOrder['Orders']['comment'] . '</div>neu: <div class="changed">' . $orderComment . ' </div>');

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    public function ordersAsPdf()
    {
        if (empty($this->request->getQuery('orderIds'))) {
            throw new RecordNotFoundException('wrong order id set');
        }

        $this->Order->hasMany['OrderDetails']['order'] = [
            'OrderDetails.product_name' => 'ASC'
        ];
        $orders = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order IN(' . $this->request->getQuery('orderIds') . ')'
            ],
            'order' => Configure::read('app.htmlHelper')->getCustomerOrderBy()
        ]);

        if (empty($orders)) {
            throw new RecordNotFoundException('no orders found');
        }

        $this->set('orders', $orders);
    }

    public function correctShopOrder()
    {
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->params->query['url']);

        if ($orderId > 0) {
            $order = $this->Order->find('all', [
                'conditions' => [
                    'Orders.id_order' => $orderId
                ],
                'order' => [
                    'Orders.date_add' => 'DESC'
                ]
            ])->first();

            $newDate = Configure::read('app.timeHelper')->getDateForShopOrder(Configure::read('app.timeHelper')->getCurrentDay());
            $order2update = [
                'date_add' => $newDate,
                'current_state' => Configure::read('appDb.FCS_SHOP_ORDER_DEFAULT_STATE')
            ];
            $this->Order->id = $orderId;
            $this->Order->save($order2update);

            $message = 'Sofort-Bestellung Nr. (' . $order['Orders']['id_order'] . ') für ' . $order['Customers']['name'] . ' erfolgreich erstellt und rückdatiert auf den ' . Configure::read('app.timeHelper')->formatToDateShort($newDate) . '. Der Hersteller wurde informiert, sofern er die Benachrichtigung nicht selbst deaktiviert hat.';

            $this->ActionLog = TableRegistry::get('ActionLogs');
            $this->ActionLog->customSave('orders_shop_added', $this->AppAuth->getUserId(), $orderId, 'orders', $message);
            $this->Flash->success($message);

            $this->request->getSession()->write('highlightedRowId', $orderId);
            $this->redirect($this->referer());
        } else {
            die('order id not correct: ' + $orderId);
        }
    }

    public function changeOrderStateToClosed()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderIds = $this->params['data']['orderIds'];
        $orderIds = array_unique($orderIds);
        $orderState = $this->params['data']['orderState'];

        foreach ($orderIds as $orderId) {
            // update table order
            $this->Order->id = $orderId;
            $this->Order->save([
                'current_state' => $orderState
            ]);
        }

        $message = count($orderIds) . ' Bestellungen wurden erfolgreich abgeschlossen';
        $this->ActionLog = TableRegistry::get('ActionLogs');
        $this->ActionLog->customSave('orders_closed', $this->AppAuth->getUserId(), 0, 'orders', $message . ': ' . join(', ', $orderIds));

        $this->Flash->success($message . '.');

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    public function changeOrderState()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderIds = $this->params['data']['orderIds'];
        $orderState = $this->params['data']['orderState'];

        foreach ($orderIds as $orderId) {
            $oldOrder = $this->Order->find('all', [
                'conditions' => [
                    'Orders.id_order' => $orderId
                ]
            ])->first();

            // update table order
            $this->Order->id = $orderId;
            $this->Order->save([
                'current_state' => $orderState
            ]);
        }

        $this->ActionLog = TableRegistry::get('ActionLogs');

        $message = 'Der Bestellstatus der Bestellung' . (count($orderIds) == 1 ? '' : 'en') . ' ' . join(', ', array_reverse($orderIds)) . ' von ' . $oldOrder['Customers']['name'] . ' wurde' . (count($orderIds) == 1 ? '' : 'n') . ' erfolgreich auf "' . Configure::read('app.htmlHelper')->getOrderStates()[$orderState] . '" geändert.';
        $this->ActionLog->customSave('orders_state_changed', $this->AppAuth->getUserId(), $orderId, 'orders', $message);

        $this->Flash->success($message);

        // always redirect to orders (and keep some filters)
        $redirectUrl = '';
        $refererParams = explode('/', parse_url($this->referer())['path']);
        $redirectUrlItems = [
            'admin',
            'orders',
            'index'
        ];
        foreach ($refererParams as $param) {
            $p = explode(':', $param);
            if (in_array($p[0], [
                'dateFrom',
                'dateTo',
                'orderState'
            ])) {
                $redirectUrlItems[] = $param;
            }
        }
        $redirectUrl = '/' . implode('/', $redirectUrlItems);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok',
            'redirectUrl' => $redirectUrl
        ]));
    }

    public function index()
    {

        // for filter from action logs page
        $orderId = '';
        if (! empty($this->request->getQuery('orderId'))) {
            $orderId = $this->request->getQuery('orderId');
        }

        $dateFrom = '';
        if ($orderId == '') {
            $dateFrom = Configure::read('app.timeHelper')->getOrderPeriodFirstDay(Configure::read('app.timeHelper')->getCurrentDay());
        }
        if (! empty($this->request->getQuery('dateFrom'))) {
            $dateFrom = $this->request->getQuery('dateFrom');
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = '';
        if ($orderId == '') {
            $dateTo = Configure::read('app.timeHelper')->getOrderPeriodLastDay(Configure::read('app.timeHelper')->getCurrentDay());
        }
        if (! empty($this->request->getQuery('dateTo'))) {
            $dateTo = $this->request->getQuery('dateTo');
        }
        $this->set('dateTo', $dateTo);

        $orderStates = Configure::read('app.htmlHelper')->getOrderStateIds();
        if (! empty($this->request->getQuery('orderStates'))) {
            $orderStates = $this->request->getQuery('orderStates');
        }
        $this->set('orderStates', $orderStates);

        $customerId = '';
        if (! empty($this->request->getQuery('customerId'))) {
            $customerId = $this->request->getQuery('customerId');
        }
        $this->set('customerId', $customerId);

        $groupByCustomer = 0;
        if (! empty($this->request->getQuery('groupByCustomer'))) {
            $groupByCustomer = $this->request->getQuery('groupByCustomer');
        }
        $this->set('groupByCustomer', $groupByCustomer);

        $this->Order = TableRegistry::get('Orders');
        $orderParams = $this->Order->getOrderParams($customerId, $orderStates, $dateFrom, $dateTo, $groupByCustomer, $orderId, $this->AppAuth);

        $query = $this->Order->find('all', [
            'conditions' => $orderParams['conditions'],
            'contain' => $orderParams['contain'],
            'order' => $orderParams['order'],
            'group' => $orderParams['group']
        ]);

        $orders = $this->paginate($query)->toArray();
        foreach ($orders as $order) {
            $order->customer->order_count = $this->Order->getCountByCustomerId($order->id_customer);
        }
        $this->set('orders', $orders);

        $this->set('customersForDropdown', $this->Order->Customers->getForDropdown(false, 'id_customer', $this->AppAuth->isSuperadmin()));

        $this->set('title_for_layout', 'Bestellungen');
    }

    public function iframeStartPage()
    {
    }

    /**
     * this url is called if shop order (sofortbestellung) is initialized
     * saves the desired user in session
     */
    public function initShopOrder($customerId)
    {
        if (! $customerId) {
            throw new RecordNotFoundException('customerId not passed');
        }

        $this->Customer = TableRegistry::get('Customers');
        $shopOrderCustomer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
            'contain' => [
                'AddressCustomers'
            ]
        ])->first();
        if (! empty($shopOrderCustomer)) {
            $this->request->getSession()->write('Auth.shopOrderCustomer', $shopOrderCustomer);
        } else {
            $this->Flash->error('Es wurde kein Mitglied mit der Id <b>' . $customerId . '</b> gefunden.');
        }
        $this->redirect('/');
    }

    public function editDate()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderId = $this->params['data']['orderId'];
        $date = $this->params['data']['date'];

        $oldOrder = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ]
        ])->first();

        $order2update = [
            'date_add' => $date
        ];
        $this->Order->id = $orderId;
        $this->Order->save($order2update);

        $message = 'Die Bestellung ' . $orderId . ' von ' . $oldOrder['Customers']['name'] . ' wurde vom ' . Configure::read('app.timeHelper')->formatToDateShort($oldOrder['Orders']['date_add']) . ' auf den ' . Configure::read('app.timeHelper')->formatToDateShort($date) . ' rückdatiert.';
        $this->ActionLog = TableRegistry::get('ActionLogs');
        $this->ActionLog->customSave('orders_date_changed', $this->AppAuth->getUserId(), $orderId, 'orders', $message);

        $this->Flash->success($message);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }
}
