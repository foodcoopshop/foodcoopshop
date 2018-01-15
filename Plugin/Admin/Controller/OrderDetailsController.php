<?php
/**
 * OrderDetailsController
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
class OrderDetailsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->action) {
            case 'delete':
            case 'editProductPrice':
            case 'editProductQuantity':
                if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                    return true;
                }
                /*
                 * START customer/manufacturer OWNER check
                 * param orderDetailId / orderDetailIds is passed via ajaxCall
                 */
                if (!empty($this->params['data']['orderDetailIds'])) {
                    $accessAllowed = false;
                    foreach ($this->params['data']['orderDetailIds'] as $orderDetailId) {
                        $accessAllowed |= $this->checkOrderDetailIdAccess($orderDetailId);
                    }
                    return $accessAllowed;
                }
                if (!empty($this->params['data']['orderDetailId'])) {
                    return $this->checkOrderDetailIdAccess($this->params['data']['orderDetailId']);
                }
                return false;
            default:
                return parent::isAuthorized($user);
                break;
        }
    }

    /**
     * @param int $orderDetailId
     * @return boolean
     */
    private function checkOrderDetailIdAccess($orderDetailId)
    {
        if ($this->AppAuth->isCustomer() || $this->AppAuth->isManufacturer()) {
            $orderDetail = $this->OrderDetail->find('first', array(
                'conditions' => array(
                    'OrderDetail.id_order_detail' => $orderDetailId
                )
            ));
            if (!empty($orderDetail)) {
                if ($this->AppAuth->isManufacturer() && $orderDetail['Product']['id_manufacturer'] == $this->AppAuth->getManufacturerId()) {
                    return true;
                }
                if ($this->AppAuth->isCustomer() && $orderDetail['Order']['id_customer'] == $this->AppAuth->getUserId()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string $manufacturerNameField
     * @return string
     */
    private function getSortFieldForGroupedOrderDetails($manufacturerNameField)
    {
        $sortField = 'name';
        $sortMatches = array(
            'OrderDetail.product_name' => 'name',
            'Manufacturer.name' => $manufacturerNameField,
            'OrderDetail.total_price_tax_incl' => 'sum_price',
            'OrderDetail.product_quantity' => 'sum_amount',
            'OrderDetail.deposit' => 'sum_deposit'
        );
        if (!empty($this->params['named']['sort']) && isset($sortMatches[$this->params['named']['sort']])) {
            $sortField = $sortMatches[$this->params['named']['sort']];
        }
        return $sortField;
    }

    /**
     * @return string
     */
    private function getSortDirectionForGroupedOrderDetails()
    {
        $sortDirection = 'ASC';
        if (!empty($this->params['named']['direction']) && in_array($this->params['named']['direction'], array('asc', 'desc'))) {
            $sortDirection = $this->params['named']['direction'];
        }
        return $sortDirection;
    }

    public function index()
    {

        // for filter from action logs page
        $orderDetailId = '';
        if (! empty($this->params['named']['orderDetailId'])) {
            $orderDetailId = $this->params['named']['orderDetailId'];
        }

        $dateFrom = '';
        $dateTo = '';
        if ($orderDetailId == '') {
            $dateFrom = Configure::read('timeHelper')->getOrderPeriodFirstDay(Configure::read('timeHelper')->getCurrentDay());
            if (! empty($this->params['named']['dateFrom'])) {
                $dateFrom = $this->params['named']['dateFrom'];
            }
            $dateTo = Configure::read('timeHelper')->getOrderPeriodLastDay(Configure::read('timeHelper')->getCurrentDay());
            if (! empty($this->params['named']['dateTo'])) {
                $dateTo = $this->params['named']['dateTo'];
            }
        }
        $this->set('dateFrom', $dateFrom);
        $this->set('dateTo', $dateTo);

        $manufacturerId = '';
        if (! empty($this->params['named']['manufacturerId'])) {
            $manufacturerId = $this->params['named']['manufacturerId'];
        }
        $this->set('manufacturerId', $manufacturerId);

        $orderId = '';
        if (! empty($this->params['named']['orderId'])) {
            $orderId = $this->params['named']['orderId'];
        }
        $this->set('orderId', $orderId);

        $deposit = '';
        if (! empty($this->params['named']['deposit'])) {
            $deposit = $this->params['named']['deposit'];
        }
        $this->set('deposit', $deposit);

        $orderState = Configure::read('htmlHelper')->getOrderStateIdsAsCsv();
        if ($this->AppAuth->isManufacturer()) {
            $orderState = ORDER_STATE_OPEN;
        }
        if (! empty($this->params['named']['orderState'])) {
            $orderState = $this->params['named']['orderState'];
        }
        $this->set('orderState', $orderState);

        $productId = '';
        if (! empty($this->params['named']['productId'])) {
            $productId = $this->params['named']['productId'];
        }
        $this->set('productId', $productId);

        $customerId = '';
        if (! empty($this->params['named']['customerId'])) {
            $customerId = $this->params['named']['customerId'];
        }
        $this->set('customerId', $customerId);

        $groupBy = '';
        if (! empty($this->params['named']['groupBy'])) {
            $groupBy = $this->params['named']['groupBy'];
        }

        // legacy: still allow old variable "groupByManufacturer"
        if (! empty($this->params['named']['groupByManufacturer'])) {
            $groupBy = 'manufacturer';
        }

        $this->set('groupBy', $groupBy);

        $odParams = $this->OrderDetail->getOrderDetailParams($this->AppAuth, $manufacturerId, $productId, $customerId, $orderState, $dateFrom, $dateTo, $orderDetailId, $orderId, $deposit);

        $this->Paginator->settings = array_merge(array(
            'conditions' => $odParams['conditions'],
            'contain' => $odParams['contain'],
            'order' => array(
                'Product.id_manufacturer' => 'ASC',
                'Order.date_add' => 'DESC',
                'OrderDetail.product_name' => 'ASC'
            )
        ), $this->Paginator->settings);

        $orderDetails = $this->Paginator->paginate('OrderDetail');

        $this->loadModel('Manufacturer');

        switch ($groupBy) {
            case 'manufacturer':
                $preparedOrderDetails = array();
                foreach ($orderDetails as $orderDetail) {
                    $key = $orderDetail['Product']['id_manufacturer'];
                    @$preparedOrderDetails[$key]['sum_price'] += $orderDetail['OrderDetail']['total_price_tax_incl'];
                    @$preparedOrderDetails[$key]['sum_amount'] += $orderDetail['OrderDetail']['product_quantity'];
                    $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($orderDetail['Product']['Manufacturer']['variable_member_fee']);
                    $preparedOrderDetails[$key]['variable_member_fee'] = $variableMemberFee;
                    @$preparedOrderDetails[$key]['sum_deposit'] += $orderDetail['OrderDetail']['deposit'];
                    $preparedOrderDetails[$key]['manufacturer_id'] = $key;
                    $preparedOrderDetails[$key]['name'] = $orderDetail['Product']['Manufacturer']['name'];
                }
                $sortField = $this->getSortFieldForGroupedOrderDetails('name');
                $sortDirection = $this->getSortDirectionForGroupedOrderDetails();
                $preparedOrderDetails = Set::sort($preparedOrderDetails, '{n}.' . $sortField, $sortDirection);
                $orderDetails = $preparedOrderDetails;
                break;
            case 'product':
                $preparedOrderDetails = array();
                foreach ($orderDetails as $orderDetail) {
                    $key = $orderDetail['OrderDetail']['product_id'];
                    @$preparedOrderDetails[$key]['sum_price'] += $orderDetail['OrderDetail']['total_price_tax_incl'];
                    @$preparedOrderDetails[$key]['sum_amount'] += $orderDetail['OrderDetail']['product_quantity'];
                    @$preparedOrderDetails[$key]['sum_deposit'] += $orderDetail['OrderDetail']['deposit'];
                    $preparedOrderDetails[$key]['product_id'] = $key;
                    $preparedOrderDetails[$key]['name'] = $orderDetail['Product']['ProductLang']['name'];
                    $preparedOrderDetails[$key]['manufacturer_id'] = $orderDetail['Product']['Manufacturer']['id_manufacturer'];
                    $preparedOrderDetails[$key]['manufacturer_name'] = $orderDetail['Product']['Manufacturer']['name'];
                }
                $sortField = $this->getSortFieldForGroupedOrderDetails('manufacturer_name');
                $sortDirection = $this->getSortDirectionForGroupedOrderDetails();
                $preparedOrderDetails = Set::sort($preparedOrderDetails, '{n}.' . $sortField, $sortDirection);
                $orderDetails = $preparedOrderDetails;
                break;
            default:
                $i = 0;
                foreach ($orderDetails as $orderDetail) {
                    $this->loadModel('Manufacturer');
                    $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail['Product']['Manufacturer']['bulk_orders_allowed']);
                    $orderDetails[$i]['bulkOrdersAllowed'] = $bulkOrdersAllowed;
                    $orderDetails[$i]['rowClass'] = array();
                    if ($bulkOrdersAllowed) {
                        $orderDetails[$i]['rowClass'][] = 'deactivated';
                    }
                    $i ++;
                }
                break;
        }

        $this->set('orderDetails', $orderDetails);

        $groupByForDropdown = array('product' => 'Gruppieren nach Produkt');
        if (!$this->AppAuth->isManufacturer()) {
            $groupByForDropdown['manufacturer'] = 'Gruppieren nach Hersteller';
        }
        $this->set('groupByForDropdown', $groupByForDropdown);
        $this->set('customersForDropdown', $this->OrderDetail->Order->Customer->getForDropdown());
        $this->set('manufacturersForDropdown', $this->OrderDetail->Product->Manufacturer->getForDropdown());

        if (!$this->AppAuth->isManufacturer()) {
            $this->set('customersForShopOrderDropdown', $this->OrderDetail->Order->Customer->getForDropdown(false, 'id_customer', $this->AppAuth->isSuperadmin()));
        }

        $this->set('title_for_layout', 'Bestellte Produkte');
    }

    public function editProductQuantity()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderDetailId = (int) $this->params['data']['orderDetailId'];
        $productQuantity = trim($this->params['data']['productQuantity']);
        $editQuantityReason = strip_tags(html_entity_decode($this->params['data']['editQuantityReason']));

        if (! is_numeric($orderDetailId) || ! is_numeric($productQuantity) || $productQuantity < 1) {
            $message = 'input format wrong';
            $this->log($message);
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }

        $this->OrderDetail->recursive = 2;
        $oldOrderDetail = $this->OrderDetail->find('first', array(
            'conditions' => array(
                'OrderDetail.id_order_detail' => $orderDetailId
            ),
            'contain' => array(
                'Order',
                'Order.Customer',
                'Product.Manufacturer',
                'Product.Manufacturer.Address'
            )
        ));

        $productPrice = $oldOrderDetail['OrderDetail']['total_price_tax_incl'] / $oldOrderDetail['OrderDetail']['product_quantity'] * $productQuantity;

        $newOrderDetail = $this->changeOrderDetailPrice($oldOrderDetail, $productPrice, $productQuantity);
        $newQuantity = $this->increaseQuantityForProduct($newOrderDetail, $oldOrderDetail['OrderDetail']['product_quantity']);

        $message = 'Die Anzahl des bestellten Produktes <b>' . $oldOrderDetail['OrderDetail']['product_name'] . '" </b> wurde erfolgreich von ' . $oldOrderDetail['OrderDetail']['product_quantity'] . ' auf ' . $productQuantity . ' geändert';

        // send email to customer
        $email = new AppEmail();
        $email->template('Admin.order_detail_quantity_changed')
        ->to($oldOrderDetail['Order']['Customer']['email'])
        ->emailFormat('html')
        ->subject('Bestellte Anzahl korrigiert: ' . $oldOrderDetail['OrderDetail']['product_name'])
        ->viewVars(array(
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editQuantityReason' => $editQuantityReason
        ));

        $message .= ' und eine E-Mail an <b>' . $oldOrderDetail['Order']['Customer']['name'] . '</b>';

        // never send email to manufacturer if bulk orders are allowed
        $this->loadModel('Manufacturer');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail['Product']['Manufacturer']['bulk_orders_allowed']);
        $sendOrderedProductQuantityChangedNotification = $this->Manufacturer->getOptionSendOrderedProductQuantityChangedNotification($oldOrderDetail['Product']['Manufacturer']['send_ordered_product_quantity_changed_notification']);

        // only send email to manufacturer on the days between orderSend and delivery (normally wednesdays, thursdays and fridays)
        $weekday = date('N');
        if (! $this->AppAuth->isManufacturer() && in_array($weekday, Configure::read('timeHelper')->getWeekdaysBetweenOrderSendAndDelivery()) && ! $bulkOrdersAllowed && $sendOrderedProductQuantityChangedNotification) {
            $message .= ' sowie an den Hersteller <b>' . $oldOrderDetail['Product']['Manufacturer']['name'] . '</b>';
            $email->addCC($oldOrderDetail['Product']['Manufacturer']['Address']['email']);
        }

        $email->send();

        $message .= ' versendet.';

        if ($editQuantityReason != '') {
            $message .= ' Grund: <b>"' . $editQuantityReason . '"</b>';
        }

        $message .= ' Der Warenbestand wurde auf ' . Configure::read('htmlHelper')->formatAsDecimal($newQuantity, 0) . ' erhöht.';

        $this->loadModel('ActionLog');
        $this->ActionLog->customSave('order_detail_product_quantity_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);

        $this->Flash->success($message);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }

    public function editProductPrice()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderDetailId = (int) $this->params['data']['orderDetailId'];
        $editPriceReason = strip_tags(html_entity_decode($this->params['data']['editPriceReason']));

        $productPrice = trim($this->params['data']['productPrice']);
        $productPrice = str_replace(',', '.', $productPrice);

        if (! is_numeric($orderDetailId) || ! is_numeric($productPrice) || $productPrice < 0) {
            $message = 'input format wrong';
            $this->log($message);
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }

        $productPrice = floatval($productPrice);

        $oldOrderDetail = $this->OrderDetail->find('first', array(
            'conditions' => array(
                'OrderDetail.id_order_detail' => $orderDetailId
            ),
            'contain' => array(
                'Order',
                'Order.Customer',
                'Product.Manufacturer',
                'Product.Manufacturer.Address'
            )
        ));

        $newOrderDetail = $this->changeOrderDetailPrice($oldOrderDetail, $productPrice, $oldOrderDetail['OrderDetail']['product_quantity']);

        $message = 'Der Preis des bestellten Produktes "' . $oldOrderDetail['OrderDetail']['product_name'] . '" (Anzahl: ' . $oldOrderDetail['OrderDetail']['product_quantity'] . ') wurde erfolgreich von ' . Configure::read('htmlHelper')->formatAsDecimal($oldOrderDetail['OrderDetail']['total_price_tax_incl']) . ' auf ' . Configure::read('htmlHelper')->formatAsDecimal($productPrice) . ' korrigiert ';

        // send email to customer
        $email = new AppEmail();
        $email->template('Admin.order_detail_price_changed')
        ->to($oldOrderDetail['Order']['Customer']['email'])
        ->emailFormat('html')
        ->subject('Preis korrigiert: ' . $oldOrderDetail['OrderDetail']['product_name'])
        ->viewVars(array(
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editPriceReason' => $editPriceReason
        ));

        $message .= ' und eine E-Mail an <b>' . $oldOrderDetail['Order']['Customer']['name'] . '</b>';

        // never send email to manufacturer if bulk orders are allowed
        $this->loadModel('Manufacturer');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail['Product']['Manufacturer']['bulk_orders_allowed']);
        $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail['Product']['Manufacturer']['send_ordered_product_price_changed_notification']);

        if (! $this->AppAuth->isManufacturer() && ! $bulkOrdersAllowed && $oldOrderDetail['OrderDetail']['total_price_tax_incl'] > 0.00 && $sendOrderedProductPriceChangedNotification) {
            $message .= ' sowie an den Hersteller <b>' . $oldOrderDetail['Product']['Manufacturer']['name'] . '</b>';
            $email->addCC($oldOrderDetail['Product']['Manufacturer']['Address']['email']);
        }

        //$email->send();

        $message .= ' versendet.';
        if ($editPriceReason != '') {
            $message .= ' Grund: <b>"' . $editPriceReason . '"</b>';
        }

        $this->loadModel('ActionLog');
        $this->ActionLog->customSave('order_detail_product_price_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);
        $this->Flash->success($message);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }

    /**
     * @param array $orderDetailIds
     */
    public function delete()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderDetailIds = $this->params['data']['orderDetailIds'];
        $cancellationReason = strip_tags(html_entity_decode($this->params['data']['cancellationReason']));

        if (!(is_array($orderDetailIds))) {
            die(json_encode(array(
                'status' => 0,
                'msg' => 'param needs to be an array, given: ' . $orderDetailIds
            )));
        }

        $flashMessage = '';
        foreach ($orderDetailIds as $orderDetailId) {
            $orderDetail = $this->OrderDetail->find('first', array(
                'conditions' => array(
                    'OrderDetail.id_order_detail' => $orderDetailId
                ),
                'contain' => array(
                    'Order',
                    'Order.Customer',
                    'Product.StockAvailable',
                    'Product.Manufacturer',
                    'Product.Manufacturer.Address',
                    'ProductAttribute.StockAvailable'
                )
            ));

            $message = 'Produkt "' . $orderDetail['OrderDetail']['product_name'] . '" (' . Configure::read('htmlHelper')->formatAsEuro($orderDetail['OrderDetail']['total_price_tax_incl']) . ' aus Bestellung Nr. ' . $orderDetail['Order']['id_order'] . ' vom ' . Configure::read('timeHelper')->formatToDateNTimeLong($orderDetail['Order']['date_add']) . ' wurde erfolgreich storniert';

            // delete row
            $this->OrderDetail->deleteOrderDetail($orderDetailId);

            // update sum in table orders
            $this->OrderDetail->Order->recalculateOrderDetailPricesInOrder($orderDetail);

            $newQuantity = $this->increaseQuantityForProduct($orderDetail, $orderDetail['OrderDetail']['product_quantity'] * 2);

            // send email to customer
            $email = new AppEmail();
            $email->template('Admin.order_detail_deleted')
            ->emailFormat('html')
            ->to($orderDetail['Order']['Customer']['email'])
            ->subject('Produkt kann nicht geliefert werden: ' . $orderDetail['OrderDetail']['product_name'])
            ->viewVars(array(
                'orderDetail' => $orderDetail,
                'appAuth' => $this->AppAuth,
                'cancellationReason' => $cancellationReason
            ));

            $message .= ' und eine E-Mail an <b>' . $orderDetail['Order']['Customer']['name'] . '</b>';

            // never send email to manufacturer if bulk orders are allowed
            $this->loadModel('Manufacturer');
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail['Product']['Manufacturer']['bulk_orders_allowed']);
            $sendOrderedProductDeletedNotification = $this->Manufacturer->getOptionSendOrderedProductDeletedNotification($orderDetail['Product']['Manufacturer']['send_ordered_product_deleted_notification']);

            // only send email to manufacturer on the days between orderSend and delivery (normally wednesdays, thursdays and fridays)
            $weekday = date('N');
            if (! $this->AppAuth->isManufacturer() && in_array($weekday, Configure::read('timeHelper')->getWeekdaysBetweenOrderSendAndDelivery()) && ! $bulkOrdersAllowed && $sendOrderedProductDeletedNotification) {
                $message .= ' sowie an den Hersteller <b>' . $orderDetail['Product']['Manufacturer']['name'] . '</b>';
                $email->addCC($orderDetail['Product']['Manufacturer']['Address']['email']);
            }

            $email->send();

            $message .= ' versendet.';
            if ($cancellationReason != '') {
                $message .= ' Grund: <b>"' . $cancellationReason . '"</b>';
            }

            $message .= ' Der Warenbestand wurde um ' . $orderDetail['OrderDetail']['product_quantity'] . ' auf ' . Configure::read('htmlHelper')->formatAsDecimal($newQuantity, 0) . ' erhöht.';

            $this->loadModel('ActionLog');
            $this->ActionLog->customSave('order_detail_cancelled', $this->AppAuth->getUserId(), $orderDetail['OrderDetail']['product_id'], 'products', $message);
        }


        $flashMessage = $message;
        $orderDetailsCount = count($orderDetailIds);
        $productString = $orderDetailsCount == 1 ? 'Produkt wurde' : 'Produkte wurden';
        if ($orderDetailsCount > 1) {
            $flashMessage =  $orderDetailsCount . ' ' . $productString . ' erfolgreich storniert.';
        }
        $this->Flash->success($flashMessage);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }

    private function changeOrderDetailPrice($oldOrderDetail, $productPrice, $productQuantity)
    {
        $unitPriceExcl = $this->OrderDetail->Product->getNetPrice($oldOrderDetail['OrderDetail']['product_id'], $productPrice / $productQuantity);
        $unitTaxAmount = $this->OrderDetail->Product->getUnitTax($productPrice, $unitPriceExcl, $productQuantity);
        $totalTaxAmount = $unitTaxAmount * $productQuantity;
        $totalPriceTaxExcl = $productPrice - $totalTaxAmount;

        // update order details
        $orderDetail2save = array(
            'total_price_tax_incl' => $productPrice,
            'total_price_tax_excl' => $totalPriceTaxExcl,
            'unit_price_tax_incl' => $productPrice / $productQuantity,
            'unit_price_tax_excl' => round($unitPriceExcl, 2),
            'product_price' => $unitPriceExcl,
            'product_quantity' => $productQuantity,
            'deposit' => $oldOrderDetail['OrderDetail']['deposit'] / $oldOrderDetail['OrderDetail']['product_quantity'] * $productQuantity
        );
        $this->OrderDetail->id = $oldOrderDetail['OrderDetail']['id_order_detail'];
        $this->OrderDetail->save($orderDetail2save);

        // update order detail tax for invoices
        $odt2save = array(
            'unit_amount' => $unitTaxAmount,
            'total_amount' => $totalTaxAmount
        );
        $this->OrderDetail->OrderDetailTax->id = $oldOrderDetail['OrderDetail']['id_order_detail'];
        $this->OrderDetail->OrderDetailTax->save($odt2save);

        // update sum in orders
        $newOrderDetail = $this->OrderDetail->find('first', array(
            'conditions' => array(
                'OrderDetail.id_order_detail' => $oldOrderDetail['OrderDetail']['id_order_detail']
            ),
            'contain' => array(
                'Order',
                'Order.Customer',
                'Product.StockAvailable',
                'Product.Manufacturer',
                'ProductAttribute.StockAvailable'
            )
        ));

        $this->OrderDetail->Order->recalculateOrderDetailPricesInOrder($newOrderDetail);

        return $newOrderDetail;
    }

    private function increaseQuantityForProduct($orderDetail, $orderDetailQuantityBeforeQuantityChange)
    {

        $stockAvailableObject = $this->OrderDetail->Product;
        $stockAvailableIndex = 'Product';

        // if attribute, the following index exists:
        if (! empty($orderDetail['ProductAttribute']['StockAvailable'])) {
            $stockAvailableObject = $this->OrderDetail->ProductAttribute;
            $stockAvailableIndex = 'ProductAttribute';
        }

        // do the acutal updates for increasing quantity
        if (isset($stockAvailableObject) && isset($stockAvailableIndex)) {
            $backedUpPrimaryKey = $stockAvailableObject->StockAvailable->primaryKey;
            $stockAvailableObject->StockAvailable->primaryKey = 'id_stock_available'; // primary key was already set in model... works :-)
            $stockAvailableObject->StockAvailable->id = $orderDetail[$stockAvailableIndex]['StockAvailable']['id_stock_available'];
            $newQuantity = $orderDetail[$stockAvailableIndex]['StockAvailable']['quantity'] + $orderDetailQuantityBeforeQuantityChange - $orderDetail['OrderDetail']['product_quantity'];
            $stockAvailableObject->StockAvailable->save(array(
                'quantity' => $newQuantity
            ));
            $stockAvailableObject->StockAvailable->primaryKey = $backedUpPrimaryKey;
        }

        return $newQuantity;
    }
}
