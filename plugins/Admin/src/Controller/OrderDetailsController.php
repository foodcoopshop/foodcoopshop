<?php

use Admin\Controller\AdminAppController;
use App\Mailer\AppEmail;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

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
        switch ($this->request->action) {
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
            $orderDetail = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail' => $orderDetailId
                ]
            ])->first();
            if (!empty($orderDetail)) {
                if ($this->AppAuth->isManufacturer() && $orderDetail['Products']['id_manufacturer'] == $this->AppAuth->getManufacturerId()) {
                    return true;
                }
                if ($this->AppAuth->isCustomer() && $orderDetail['Orders']['id_customer'] == $this->AppAuth->getUserId()) {
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
        $sortMatches = [
            'OrderDetails.product_name' => 'name',
            'Manufacturers.name' => $manufacturerNameField,
            'OrderDetails.total_price_tax_incl' => 'sum_price',
            'OrderDetails.product_quantity' => 'sum_amount',
            'OrderDetails.deposit' => 'sum_deposit'
        ];
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
        if (!empty($this->params['named']['direction']) && in_array($this->params['named']['direction'], ['asc', 'desc'])) {
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
            $dateFrom = Configure::read('app.timeHelper')->getOrderPeriodFirstDay(Configure::read('app.timeHelper')->getCurrentDay());
            if (! empty($this->params['named']['dateFrom'])) {
                $dateFrom = $this->params['named']['dateFrom'];
            }
            $dateTo = Configure::read('app.timeHelper')->getOrderPeriodLastDay(Configure::read('app.timeHelper')->getCurrentDay());
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

        $orderState = Configure::read('app.htmlHelper')->getOrderStateIdsAsCsv();
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

        $this->Paginator->settings = array_merge([
            'conditions' => $odParams['conditions'],
            'contain' => $odParams['contain'],
            'order' => [
                'Products.id_manufacturer' => 'ASC',
                'Orders.date_add' => 'DESC',
                'OrderDetails.product_name' => 'ASC'
            ]
        ], $this->Paginator->settings);

        $orderDetails = $this->Paginator->paginate('OrderDetails');

        $this->Manufacturer = TableRegistry::get('Manufacturers');

        switch ($groupBy) {
            case 'manufacturer':
                $preparedOrderDetails = [];
                foreach ($orderDetails as $orderDetail) {
                    $key = $orderDetail['Products']['id_manufacturer'];
                    @$preparedOrderDetails[$key]['sum_price'] += $orderDetail['OrderDetails']['total_price_tax_incl'];
                    @$preparedOrderDetails[$key]['sum_amount'] += $orderDetail['OrderDetails']['product_quantity'];
                    $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($orderDetail['Products']['Manufacturers']['variable_member_fee']);
                    $preparedOrderDetails[$key]['variable_member_fee'] = $variableMemberFee;
                    @$preparedOrderDetails[$key]['sum_deposit'] += $orderDetail['OrderDetails']['deposit'];
                    $preparedOrderDetails[$key]['manufacturer_id'] = $key;
                    $preparedOrderDetails[$key]['name'] = $orderDetail['Products']['Manufacturers']['name'];
                }
                $sortField = $this->getSortFieldForGroupedOrderDetails('name');
                $sortDirection = $this->getSortDirectionForGroupedOrderDetails();
                $preparedOrderDetails = Hash::sort($preparedOrderDetails, '{n}.' . $sortField, $sortDirection);
                $orderDetails = $preparedOrderDetails;
                break;
            case 'product':
                $preparedOrderDetails = [];
                foreach ($orderDetails as $orderDetail) {
                    $key = $orderDetail['OrderDetails']['product_id'];
                    @$preparedOrderDetails[$key]['sum_price'] += $orderDetail['OrderDetails']['total_price_tax_incl'];
                    @$preparedOrderDetails[$key]['sum_amount'] += $orderDetail['OrderDetails']['product_quantity'];
                    @$preparedOrderDetails[$key]['sum_deposit'] += $orderDetail['OrderDetails']['deposit'];
                    $preparedOrderDetails[$key]['product_id'] = $key;
                    $preparedOrderDetails[$key]['name'] = $orderDetail['Products']['ProductLangs']['name'];
                    $preparedOrderDetails[$key]['manufacturer_id'] = $orderDetail['Products']['Manufacturers']['id_manufacturer'];
                    $preparedOrderDetails[$key]['manufacturer_name'] = $orderDetail['Products']['Manufacturers']['name'];
                }
                $sortField = $this->getSortFieldForGroupedOrderDetails('manufacturer_name');
                $sortDirection = $this->getSortDirectionForGroupedOrderDetails();
                $preparedOrderDetails = Hash::sort($preparedOrderDetails, '{n}.' . $sortField, $sortDirection);
                $orderDetails = $preparedOrderDetails;
                break;
            default:
                $i = 0;
                foreach ($orderDetails as $orderDetail) {
                    $this->Manufacturer = TableRegistry::get('Manufacturers');
                    $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail['Products']['Manufacturers']['bulk_orders_allowed']);
                    $orderDetails[$i]['bulkOrdersAllowed'] = $bulkOrdersAllowed;
                    $orderDetails[$i]['rowClass'] = [];
                    if ($bulkOrdersAllowed) {
                        $orderDetails[$i]['rowClass'][] = 'deactivated';
                    }
                    $i ++;
                }
                break;
        }

        $this->set('orderDetails', $orderDetails);

        $groupByForDropdown = ['product' => 'Gruppieren nach Produkt'];
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
            die(json_encode([
                'status' => 0,
                'msg' => $message
            ]));
        }

        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Orders',
                'Orders.Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.Address'
            ]
        ])->first();

        $productPrice = $oldOrderDetail['OrderDetails']['total_price_tax_incl'] / $oldOrderDetail['OrderDetails']['product_quantity'] * $productQuantity;

        $newOrderDetail = $this->changeOrderDetailPrice($oldOrderDetail, $productPrice, $productQuantity);
        $newQuantity = $this->increaseQuantityForProduct($newOrderDetail, $oldOrderDetail['OrderDetails']['product_quantity']);

        $message = 'Die Anzahl des bestellten Produktes <b>' . $oldOrderDetail['OrderDetails']['product_name'] . '" </b> wurde erfolgreich von ' . $oldOrderDetail['OrderDetails']['product_quantity'] . ' auf ' . $productQuantity . ' geändert';

        // send email to customer
        $email = new AppEmail();
        $email->setTemplate('Admin.order_detail_quantity_changed')
        ->setTo($oldOrderDetail['Orders']['Customers']['email'])
        ->setSubject('Bestellte Anzahl korrigiert: ' . $oldOrderDetail['OrderDetails']['product_name'])
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editQuantityReason' => $editQuantityReason
        ]);

        $message .= ' und eine E-Mail an <b>' . $oldOrderDetail['Orders']['Customers']['name'] . '</b>';

        // never send email to manufacturer if bulk orders are allowed
        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail['Products']['Manufacturers']['bulk_orders_allowed']);
        $sendOrderedProductQuantityChangedNotification = $this->Manufacturer->getOptionSendOrderedProductQuantityChangedNotification($oldOrderDetail['Products']['Manufacturers']['send_ordered_product_quantity_changed_notification']);

        // only send email to manufacturer on the days between orderSend and delivery (normally wednesdays, thursdays and fridays)
        $weekday = date('N');
        if (! $this->AppAuth->isManufacturer() && in_array($weekday, Configure::read('app.timeHelper')->getWeekdaysBetweenOrderSendAndDelivery()) && ! $bulkOrdersAllowed && $sendOrderedProductQuantityChangedNotification) {
            $message .= ' sowie an den Hersteller <b>' . $oldOrderDetail['Products']['Manufacturers']['name'] . '</b>';
            $email->addCC($oldOrderDetail['Products']['Manufacturers']['Addresses']['email']);
        }

        $email->send();

        $message .= ' versendet.';

        if ($editQuantityReason != '') {
            $message .= ' Grund: <b>"' . $editQuantityReason . '"</b>';
        }

        $message .= ' Der Warenbestand wurde auf ' . Configure::read('app.htmlHelper')->formatAsDecimal($newQuantity, 0) . ' erhöht.';

        $this->ActionLog = TableRegistry::get('ActionLogs');
        $this->ActionLog->customSave('order_detail_product_quantity_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);

        $this->Flash->success($message);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
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
            die(json_encode([
                'status' => 0,
                'msg' => $message
            ]));
        }

        $productPrice = floatval($productPrice);

        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Orders',
                'Orders.Customer',
                'Products.Manufacturer',
                'Products.Manufacturer.Address'
            ]
        ])->first();

        $newOrderDetail = $this->changeOrderDetailPrice($oldOrderDetail, $productPrice, $oldOrderDetail['OrderDetails']['product_quantity']);

        $message = 'Der Preis des bestellten Produktes "' . $oldOrderDetail['OrderDetails']['product_name'] . '" (Anzahl: ' . $oldOrderDetail['OrderDetails']['product_quantity'] . ') wurde erfolgreich von ' . Configure::read('app.htmlHelper')->formatAsDecimal($oldOrderDetail['OrderDetails']['total_price_tax_incl']) . ' auf ' . Configure::read('app.htmlHelper')->formatAsDecimal($productPrice) . ' korrigiert ';

        // send email to customer
        $email = new AppEmail();
        $email->setTemplate('Admin.order_detail_price_changed')
        ->setTo($oldOrderDetail['Orders']['Customers']['email'])
        ->setSubject('Preis korrigiert: ' . $oldOrderDetail['OrderDetails']['product_name'])
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editPriceReason' => $editPriceReason
        ]);

        $message .= ' und eine E-Mail an <b>' . $oldOrderDetail['Orders']['Customers']['name'] . '</b>';

        // never send email to manufacturer if bulk orders are allowed
        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail['Products']['Manufacturers']['bulk_orders_allowed']);
        $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail['Products']['Manufacturers']['send_ordered_product_price_changed_notification']);

        if (! $this->AppAuth->isManufacturer() && ! $bulkOrdersAllowed && $oldOrderDetail['OrderDetails']['total_price_tax_incl'] > 0.00 && $sendOrderedProductPriceChangedNotification) {
            $message .= ' sowie an den Hersteller <b>' . $oldOrderDetail['Products']['Manufacturers']['name'] . '</b>';
            $email->addCC($oldOrderDetail['Products']['Manufacturers']['Addresses']['email']);
        }

        $email->send();

        $message .= ' versendet.';
        if ($editPriceReason != '') {
            $message .= ' Grund: <b>"' . $editPriceReason . '"</b>';
        }

        $this->ActionLog = TableRegistry::get('ActionLogs');
        $this->ActionLog->customSave('order_detail_product_price_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);
        $this->Flash->success($message);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
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
            die(json_encode([
                'status' => 0,
                'msg' => 'param needs to be an array, given: ' . $orderDetailIds
            ]));
        }

        $flashMessage = '';
        foreach ($orderDetailIds as $orderDetailId) {
            $orderDetail = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail' => $orderDetailId
                ],
                'contain' => [
                    'Orders',
                    'Orders.Customer',
                    'Products.StockAvailable',
                    'Products.Manufacturer',
                    'Products.Manufacturer.Address',
                    'ProductAttributes.StockAvailable'
                ]
            ])->first();

            $message = 'Produkt "' . $orderDetail['OrderDetails']['product_name'] . '" (' . Configure::read('app.htmlHelper')->formatAsEuro($orderDetail['OrderDetails']['total_price_tax_incl']) . ' aus Bestellung Nr. ' . $orderDetail['Orders']['id_order'] . ' vom ' . Configure::read('app.timeHelper')->formatToDateNTimeLong($orderDetail['Orders']['date_add']) . ' wurde erfolgreich storniert';

            // delete row
            $this->OrderDetail->deleteOrderDetail($orderDetailId);

            // update sum in table orders
            $this->OrderDetail->Order->recalculateOrderDetailPricesInOrder($orderDetail);

            $newQuantity = $this->increaseQuantityForProduct($orderDetail, $orderDetail['OrderDetails']['product_quantity'] * 2);

            // send email to customer
            $email = new AppEmail();
            $email->setTemplate('Admin.order_detail_deleted')
            ->setTo($orderDetail['Orders']['Customers']['email'])
            ->setSubject('Produkt kann nicht geliefert werden: ' . $orderDetail['OrderDetails']['product_name'])
            ->setViewVars([
                'orderDetail' => $orderDetail,
                'appAuth' => $this->AppAuth,
                'cancellationReason' => $cancellationReason
            ]);

            $message .= ' und eine E-Mail an <b>' . $orderDetail['Orders']['Customers']['name'] . '</b>';

            // never send email to manufacturer if bulk orders are allowed
            $this->Manufacturer = TableRegistry::get('Manufacturers');
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail['Products']['Manufacturers']['bulk_orders_allowed']);
            $sendOrderedProductDeletedNotification = $this->Manufacturer->getOptionSendOrderedProductDeletedNotification($orderDetail['Products']['Manufacturers']['send_ordered_product_deleted_notification']);

            // only send email to manufacturer on the days between orderSend and delivery (normally wednesdays, thursdays and fridays)
            $weekday = date('N');
            if (! $this->AppAuth->isManufacturer() && in_array($weekday, Configure::read('app.timeHelper')->getWeekdaysBetweenOrderSendAndDelivery()) && ! $bulkOrdersAllowed && $sendOrderedProductDeletedNotification) {
                $message .= ' sowie an den Hersteller <b>' . $orderDetail['Products']['Manufacturers']['name'] . '</b>';
                $email->addCC($orderDetail['Products']['Manufacturers']['Addresses']['email']);
            }

            $email->send();

            $message .= ' versendet.';
            if ($cancellationReason != '') {
                $message .= ' Grund: <b>"' . $cancellationReason . '"</b>';
            }

            $message .= ' Der Warenbestand wurde um ' . $orderDetail['OrderDetails']['product_quantity'] . ' auf ' . Configure::read('app.htmlHelper')->formatAsDecimal($newQuantity, 0) . ' erhöht.';

            $this->ActionLog = TableRegistry::get('ActionLogs');
            $this->ActionLog->customSave('order_detail_cancelled', $this->AppAuth->getUserId(), $orderDetail['OrderDetails']['product_id'], 'products', $message);
        }


        $flashMessage = $message;
        $orderDetailsCount = count($orderDetailIds);
        $productString = $orderDetailsCount == 1 ? 'Produkt wurde' : 'Produkte wurden';
        if ($orderDetailsCount > 1) {
            $flashMessage =  $orderDetailsCount . ' ' . $productString . ' erfolgreich storniert.';
        }
        $this->Flash->success($flashMessage);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    private function changeOrderDetailPrice($oldOrderDetail, $productPrice, $productQuantity)
    {
        $unitPriceExcl = $this->OrderDetail->Product->getNetPrice($oldOrderDetail['OrderDetails']['product_id'], $productPrice / $productQuantity);
        $unitTaxAmount = $this->OrderDetail->Product->getUnitTax($productPrice, $unitPriceExcl, $productQuantity);
        $totalTaxAmount = $unitTaxAmount * $productQuantity;
        $totalPriceTaxExcl = $productPrice - $totalTaxAmount;

        // update order details
        $orderDetail2save = [
            'total_price_tax_incl' => $productPrice,
            'total_price_tax_excl' => $totalPriceTaxExcl,
            'unit_price_tax_incl' => $productPrice / $productQuantity,
            'unit_price_tax_excl' => round($unitPriceExcl, 2),
            'product_price' => $unitPriceExcl,
            'product_quantity' => $productQuantity,
            'deposit' => $oldOrderDetail['OrderDetails']['deposit'] / $oldOrderDetail['OrderDetails']['product_quantity'] * $productQuantity
        ];
        $this->OrderDetail->id = $oldOrderDetail['OrderDetails']['id_order_detail'];
        $this->OrderDetail->save($orderDetail2save);

        // update order detail tax for invoices
        $odt2save = [
            'unit_amount' => $unitTaxAmount,
            'total_amount' => $totalTaxAmount
        ];
        $this->OrderDetail->OrderDetailTax->id = $oldOrderDetail['OrderDetails']['id_order_detail'];
        $this->OrderDetail->OrderDetailTax->save($odt2save);

        // update sum in orders
        $newOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $oldOrderDetail['OrderDetails']['id_order_detail']
            ],
            'contain' => [
                'Orders',
                'Orders.Customer',
                'Products.StockAvailable',
                'Products.Manufacturer',
                'ProductAttribute.StockAvailable'
            ]
        ])->first();

        $this->OrderDetail->Order->recalculateOrderDetailPricesInOrder($newOrderDetail);

        return $newOrderDetail;
    }

    private function increaseQuantityForProduct($orderDetail, $orderDetailQuantityBeforeQuantityChange)
    {

        $stockAvailableObject = $this->OrderDetail->Product;
        $stockAvailableIndex = 'Products';

        // if attribute, the following index exists:
        if (! empty($orderDetail['ProductAttributes']['StockAvailables'])) {
            $stockAvailableObject = $this->OrderDetail->ProductAttribute;
            $stockAvailableIndex = 'ProductAttributes';
        }

        // do the acutal updates for increasing quantity
        if (isset($stockAvailableObject) && isset($stockAvailableIndex)) {
            $backedUpPrimaryKey = $stockAvailableObject->StockAvailable->primaryKey;
            $stockAvailableObject->StockAvailable->primaryKey = 'id_stock_available'; // primary key was already set in model... works :-)
            $stockAvailableObject->StockAvailable->id = $orderDetail[$stockAvailableIndex]['StockAvailables']['id_stock_available'];
            $newQuantity = $orderDetail[$stockAvailableIndex]['StockAvailables']['quantity'] + $orderDetailQuantityBeforeQuantityChange - $orderDetail['OrderDetails']['product_quantity'];
            $stockAvailableObject->StockAvailable->save([
                'quantity' => $newQuantity
            ]);
            $stockAvailableObject->StockAvailable->primaryKey = $backedUpPrimaryKey;
        }

        return $newQuantity;
    }
}
