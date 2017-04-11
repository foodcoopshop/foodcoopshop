<?php
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
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'Order.id_order' => $orderId
            )
        ));
        $order['OrderDetail']['id_order'] = $orderId;
        $this->Order->recalculateOrderDetailPricesInOrder($order);
    }

    public function ordersAsPdf()
    {
        if (empty($this->params['named']['orderIds'])) {
            throw new MissingActionException('wrong order id set');
        }

        $this->Order->recursive = 3;
        $this->Order->hasMany['OrderDetails']['order'] = array(
            'OrderDetails.product_name' => 'ASC'
        );
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'Order.id_order IN(' . $this->params['named']['orderIds'] . ')'
            ),
            'order' => Configure::read('htmlHelper')->getCustomerOrderBy()
        ));

        if (empty($orders)) {
            throw new MissingActionException('no orders found');
        }

        $this->set('orders', $orders);
    }

    public function correctShopOrder()
    {
        $orderId = Configure::read('htmlHelper')->getOrderIdFromCartFinishedUrl($this->params->query['url']);

        if ($orderId > 0) {
            $order = $this->Order->find('first', array(
                'conditions' => array(
                    'Order.id_order' => $orderId
                ),
                'order' => array(
                    'Order.date_add' => 'DESC'
                )
            ));

            $newDate = Configure::read('timeHelper')->getDateForShopOrder();
            $order2update = array(
                'date_add' => $newDate,
                'current_state' => Configure::read('app.db_config_FCS_SHOP_ORDER_DEFAULT_STATE')
            );
            $this->Order->id = $orderId;
            $this->Order->save($order2update);

            $message = 'Sofort-Bestellung Nr. (' . $order['Order']['id_order'] . ') für ' . $order['Customer']['name'] . ' erfolgreich erstellt und rückdatiert auf den ' . Configure::read('timeHelper')->formatToDateShort($newDate) . '.';

            $this->loadModel('CakeActionLog');
            $this->CakeActionLog->customSave('orders_shop_added', $this->AppAuth->getUserId(), $orderId, 'orders', $message);
            $this->AppSession->setFlashMessage($message);

            $this->AppSession->write('highlightedRowId', $orderId);
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
            $this->Order->save(array(
                'current_state' => $orderState
            ));
        }

        $message = count($orderIds) . ' Bestellungen wurden erfolgreich abgeschlossen';
        $this->loadModel('CakeActionLog');
        $this->CakeActionLog->customSave('orders_closed', $this->AppAuth->getUserId(), 0, 'orders', $message . ': ' . join(', ', $orderIds));

        $this->AppSession->setFlashMessage($message . '.');

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }

    public function changeOrderState()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderIds = $this->params['data']['orderIds'];
        $orderState = $this->params['data']['orderState'];

        foreach ($orderIds as $orderId) {
            $oldOrder = $this->Order->find('first', array(
                'conditions' => array(
                    'Order.id_order' => $orderId
                )
            ));

            // update table order
            $this->Order->id = $orderId;
            $this->Order->save(array(
                'current_state' => $orderState
            ));
        }

        $this->loadModel('CakeActionLog');

        $message = 'Der Bestellstatus der Bestellung' . (count($orderIds) == 1 ? '' : 'en') . ' ' . join(', ', array_reverse($orderIds)) . ' von ' . $oldOrder['Customer']['name'] . ' wurde' . (count($orderIds) == 1 ? '' : 'n') . ' erfolgreich auf "' . Configure::read('htmlHelper')->getOrderStates()[$orderState] . '" geändert.';
        $this->CakeActionLog->customSave('orders_state_changed', $this->AppAuth->getUserId(), $orderId, 'orders', $message);

        $this->AppSession->setFlashMessage($message);

        // always redirect to orders (and keep some filters)
        $redirectUrl = '';
        $refererParams = explode('/', parse_url($this->referer())['path']);
        $redirectUrlItems = array(
            'admin',
            'orders',
            'index'
        );
        foreach ($refererParams as $param) {
            $p = explode(':', $param);
            if (in_array($p[0], array(
                'dateFrom',
                'dateTo',
                'orderState'
            ))) {
                $redirectUrlItems[] = $param;
            }
        }
        $redirectUrl = '/' . implode('/', $redirectUrlItems);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok',
            'redirectUrl' => $redirectUrl
        )));
    }

    public function index()
    {

        // for filter from action logs page
        $orderId = '';
        if (! empty($this->params['named']['orderId'])) {
            $orderId = $this->params['named']['orderId'];
        }

        $dateFrom = '';
        if ($orderId == '') {
            $dateFrom = Configure::read('timeHelper')->getOrderPeriodFirstDay();
        }
        if (! empty($this->params['named']['dateFrom'])) {
            $dateFrom = $this->params['named']['dateFrom'];
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = '';
        if ($orderId == '') {
            $dateTo = Configure::read('timeHelper')->getOrderPeriodLastDay();
        }
        if (! empty($this->params['named']['dateTo'])) {
            $dateTo = $this->params['named']['dateTo'];
        }
        $this->set('dateTo', $dateTo);

        $orderState = Configure::read('htmlHelper')->getOrderStateIdsAsCsv();
        if (! empty($this->params['named']['orderState'])) {
            $orderState = $this->params['named']['orderState'];
        }
        $this->set('orderState', $orderState);

        $customerId = '';
        if (! empty($this->params['named']['customerId'])) {
            $customerId = $this->params['named']['customerId'];
        }
        $this->set('customerId', $customerId);

        $groupByCustomer = 0;
        if (! empty($this->params['named']['groupByCustomer'])) {
            $groupByCustomer = $this->params['named']['groupByCustomer'];
        }
        $this->set('groupByCustomer', $groupByCustomer);

        $orderParams = $this->Order->getOrderParams($customerId, $orderState, $dateFrom, $dateTo, $groupByCustomer, $orderId, $this->AppAuth);

        $this->Paginator->settings = array_merge(array(
            'conditions' => $orderParams['conditions'],
            'contain' => $orderParams['contain'],
            'order' => $orderParams['order'],
            'fields' => $orderParams['fields'],
            'group' => $orderParams['group']
        ), $this->Paginator->settings);

        $this->Order->virtualFields = $this->Order->Customer->virtualFields; // to get related virtual field "Customer.name"

        $orders = $this->Paginator->paginate('Order');
        foreach ($orders as &$order) {
            $order['Customer']['order_count'] = $this->Order->getCountByCustomerId($order['Order']['id_customer']);
        }
        $this->set('orders', $orders);

        $this->set('customersForDropdown', $this->Order->Customer->getForDropdown(false, 'email', $this->AppAuth->isSuperadmin()));

        $this->set('title_for_layout', 'Bestellungen');
    }

    public function iframeStartPage()
    {
    }

    /**
     * this url is called if shop order (sofortbestellung) is initialized
     * saves the desired user in session
     */
    public function initShopOrder($shopOrderEmail)
    {
        if (! $shopOrderEmail) {
            throw new MissingActionException('shopOrderEmail not passed');
        }

        $this->loadModel('Customer');
        $this->Customer->recursive = - 1;
        $shopOrderCustomer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.email' => $shopOrderEmail
            )
        ));
        if (! empty($shopOrderCustomer)) {
            $this->AppSession->write('Auth.shopOrderCustomer', $shopOrderCustomer);
        } else {
            $this->AppSession->setFlashError('Es wurde kein Mitglied mit der E-Mail-Adresse <b>' . $shopOrderEmail . '</b> gefunden.');
        }

        $this->redirect('/');
    }

    public function editDate()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderId = $this->params['data']['orderId'];
        $date = $this->params['data']['date'];

        $oldOrder = $this->Order->find('first', array(
            'conditions' => array(
                'Order.id_order' => $orderId
            )
        ));

        $order2update = array(
            'date_add' => $date
        );
        $this->Order->id = $orderId;
        $this->Order->save($order2update);

        $message = 'Die Bestellung ' . $orderId . ' von ' . $oldOrder['Customer']['name'] . ' wurde vom ' . Configure::read('timeHelper')->formatToDateShort($oldOrder['Order']['date_add']) . ' auf den ' . Configure::read('timeHelper')->formatToDateShort($date) . ' rückdatiert.';
        $this->loadModel('CakeActionLog');
        $this->CakeActionLog->customSave('orders_date_changed', $this->AppAuth->getUserId(), $orderId, 'orders', $message);

        $this->AppSession->setFlashMessage($message);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }
}
