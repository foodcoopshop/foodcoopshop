<?php

namespace Admin\Controller;
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
                if (!empty($this->request->getData('orderDetailIds'))) {
                    $accessAllowed = false;
                    foreach ($this->request->getData('orderDetailIds') as $orderDetailId) {
                        $accessAllowed |= $this->checkOrderDetailIdAccess($orderDetailId);
                    }
                    return $accessAllowed;
                }
                if (!empty($this->request->getData('orderDetailId'))) {
                    return $this->checkOrderDetailIdAccess($this->request->getData('orderDetailId'));
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
            $this->OrderDetail = TableRegistry::get('OrderDetails');
            $orderDetail = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail' => $orderDetailId
                ],
                'contain' => [
                    'Products',
                    'Orders'
                    
                ]
            ])->first();
            if (!empty($orderDetail)) {
                if ($this->AppAuth->isManufacturer() && $orderDetail->product->id_manufacturer == $this->AppAuth->getManufacturerId()) {
                    return true;
                }
                if ($this->AppAuth->isCustomer() && $orderDetail->order->id_customer == $this->AppAuth->getUserId()) {
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
        if (!empty($this->request->getQuery('sort')) && isset($sortMatches[$this->request->getQuery('sort')])) {
            $sortField = $sortMatches[$this->request->getQuery('sort')];
        }
        return $sortField;
    }

    /**
     * @return string
     */
    private function getSortDirectionForGroupedOrderDetails()
    {
        $sortDirection = 'ASC';
        if (!empty($this->request->getQuery('direction') && in_array($this->request->getQuery('direction'), ['asc', 'desc']))) {
            $sortDirection = $this->request->getQuery('direction');
        }
        return $sortDirection;
    }

    public function index()
    {

        // for filter from action logs page
        $orderDetailId = '';
        if (! empty($this->request->getQuery('orderDetailId'))) {
            $orderDetailId = $this->request->getQuery('orderDetailId');
        }

        $dateFrom = '';
        $dateTo = '';
        if ($orderDetailId == '') {
            $dateFrom = Configure::read('app.timeHelper')->getOrderPeriodFirstDay(Configure::read('app.timeHelper')->getCurrentDay());
            if (! empty($this->request->getQuery('dateFrom'))) {
                $dateFrom = $this->request->getQuery('dateFrom');
            }
            $dateTo = Configure::read('app.timeHelper')->getOrderPeriodLastDay(Configure::read('app.timeHelper')->getCurrentDay());
            if (! empty($this->request->getQuery('dateTo'))) {
                $dateTo = $this->request->getQuery('dateTo');
            }
        }
        $this->set('dateFrom', $dateFrom);
        $this->set('dateTo', $dateTo);

        $manufacturerId = '';
        if (! empty($this->request->getQuery('manufacturerId'))) {
            $manufacturerId = $this->request->getQuery('manufacturerId');
        }
        $this->set('manufacturerId', $manufacturerId);

        $orderId = '';
        if (! empty($this->request->getQuery('orderId'))) {
            $orderId = $this->request->getQuery('orderId');
        }
        $this->set('orderId', $orderId);

        $deposit = '';
        if (! empty($this->request->getQuery('deposit'))) {
            $deposit = $this->request->getQuery('deposit');
        }
        $this->set('deposit', $deposit);

        $orderStates = Configure::read('app.htmlHelper')->getOrderStateIds();
        if ($this->AppAuth->isManufacturer()) {
            $orderStates = ORDER_STATE_OPEN;
        }

        $orderStates = Configure::read('app.htmlHelper')->getOrderStateIds();
        if (in_array('orderStates', array_keys($this->request->getQueryParams()))) {
            $orderStates = $this->request->getQuery('orderStates');
            if ($orderStates == '') {
                $orderStates = [];
            }
        }
        // legacy cakephp2: param was called "orderState" and contained csv data
        if (in_array('orderState', array_keys($this->request->getQueryParams()))) {
            $orderStates = explode(', ', $this->request->getQuery('orderState'));
        }
        $this->set('orderStates', $orderStates);

        $productId = '';
        if (! empty($this->request->getQuery('productId'))) {
            $productId = $this->request->getQuery('productId');
        }
        $this->set('productId', $productId);

        $customerId = '';
        if (! empty($this->request->getQuery('customerId'))) {
            $customerId = $this->request->getQuery('customerId');
        }
        $this->set('customerId', $customerId);

        $groupBy = '';
        if (! empty($this->request->getQuery('groupBy'))) {
            $groupBy = $this->request->getQuery('groupBy');
        }

        // legacy: still allow old variable "groupByManufacturer"
        if (! empty($this->request->getQuery('groupByManufacturer'))) {
            $groupBy = 'manufacturer';
        }

        $this->set('groupBy', $groupBy);

        $this->OrderDetail = TableRegistry::get('OrderDetails');
        $odParams = $this->OrderDetail->getOrderDetailParams($this->AppAuth, $manufacturerId, $productId, $customerId, $orderStates, $dateFrom, $dateTo, $orderDetailId, $orderId, $deposit);

        $query = $this->OrderDetail->find('all', [
            'conditions' => $odParams['conditions'],
            'contain' => $odParams['contain']
        ]);

        $orderDetails = $this->paginate($query, [
            'sortWhitelist' => [
                'OrderDetails.product_quantity', 'OrderDetails.product_name', 'OrderDetails.total_price_tax_incl', 'OrderDetails.deposit', 'OrderDetails.current_state', 'Manufacturers.name', 'Customers.' . Configure::read('app.customerMainNamePart')
            ],
            'order' => [
                'Products.id_manufacturer' => 'ASC',
                'Orders.date_add' => 'DESC',
                'OrderDetails.product_name' => 'ASC'
            ]
        ])->toArray();

        $this->Manufacturer = TableRegistry::get('Manufacturers');

        switch ($groupBy) {
            case 'manufacturer':
                $preparedOrderDetails = [];
                foreach ($orderDetails as $orderDetail) {
                    $key = $orderDetail->product->id_manufacturer;
                    @$preparedOrderDetails[$key]['sum_price'] += $orderDetail->total_price_tax_incl;
                    @$preparedOrderDetails[$key]['sum_amount'] += $orderDetail->product_quantity;
                    $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($orderDetail->product->manufacturer->variable_member_fee);
                    $preparedOrderDetails[$key]['variable_member_fee'] = $variableMemberFee;
                    @$preparedOrderDetails[$key]['sum_deposit'] += $orderDetail->deposit;
                    $preparedOrderDetails[$key]['manufacturer_id'] = $key;
                    $preparedOrderDetails[$key]['name'] = $orderDetail->product->manufacturer->name;
                }
                $sortField = $this->getSortFieldForGroupedOrderDetails('name');
                $sortDirection = $this->getSortDirectionForGroupedOrderDetails();
                $preparedOrderDetails = Hash::sort($preparedOrderDetails, '{n}.' . $sortField, $sortDirection);
                $orderDetails = $preparedOrderDetails;
                break;
            case 'product':
                $preparedOrderDetails = [];
                foreach ($orderDetails as $orderDetail) {
                    $key = $orderDetail->product_id;
                    @$preparedOrderDetails[$key]['sum_price'] += $orderDetail->total_price_tax_incl;
                    @$preparedOrderDetails[$key]['sum_amount'] += $orderDetail->product_quantity;
                    @$preparedOrderDetails[$key]['sum_deposit'] += $orderDetail->deposit;
                    $preparedOrderDetails[$key]['product_id'] = $key;
                    $preparedOrderDetails[$key]['name'] = $orderDetail->product->product_lang->name;
                    $preparedOrderDetails[$key]['manufacturer_id'] = $orderDetail->product->manufacturer->id_manufacturer;
                    $preparedOrderDetails[$key]['manufacturer_name'] = $orderDetail->product->manufacturer->name;
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
                    $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail->product->manufacturer->bulk_orders_allowed);
                    $orderDetail->bulkOrdersAllowed = $bulkOrdersAllowed;
                    $orderDetail->rowClass = [];
                    if ($bulkOrdersAllowed) {
                        $orderDetail->rowClass[] = 'deactivated';
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
        $this->set('customersForDropdown', $this->OrderDetail->Orders->Customers->getForDropdown());
        $this->set('manufacturersForDropdown', $this->OrderDetail->Products->Manufacturers->getForDropdown());

        if (!$this->AppAuth->isManufacturer()) {
            $this->set('customersForShopOrderDropdown', $this->OrderDetail->Orders->Customers->getForDropdown(false, 'id_customer', $this->AppAuth->isSuperadmin()));
        }

        $this->set('title_for_layout', 'Bestellte Produkte');
    }

    public function editProductQuantity()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderDetailId = (int) $this->request->getData('orderDetailId');
        $productQuantity = trim($this->request->getData('productQuantity'));
        $editQuantityReason = strip_tags(html_entity_decode($this->request->getData('editQuantityReason')));

        if (! is_numeric($orderDetailId) || ! is_numeric($productQuantity) || $productQuantity < 1) {
            $message = 'input format wrong';
            $this->log($message);
            die(json_encode([
                'status' => 0,
                'msg' => $message
            ]));
        }

        $this->OrderDetail = TableRegistry::get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Orders',
                'Orders.Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers'
            ]
        ])->first();

        $productPrice = $oldOrderDetail->total_price_tax_incl / $oldOrderDetail->product_quantity * $productQuantity;

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $newOrderDetail = $this->changeOrderDetailPrice($object, $productPrice, $productQuantity);
        $newQuantity = $this->increaseQuantityForProduct($newOrderDetail, $object->product_quantity);

        $message = 'Die Anzahl des bestellten Produktes <b>' . $oldOrderDetail->product_name . '" </b> wurde erfolgreich von ' . $oldOrderDetail->product_quantity . ' auf ' . $productQuantity . ' geändert';
        
        // send email to customer
        $email = new AppEmail();
        $email->setTemplate('Admin.order_detail_quantity_changed')
        ->setTo($oldOrderDetail->order->customer->email)
        ->setSubject('Bestellte Anzahl korrigiert: ' . $oldOrderDetail->product_name)
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editQuantityReason' => $editQuantityReason
        ]);

        $message .= ' und eine E-Mail an <b>' . $oldOrderDetail->order->customer->name . '</b>';

        // never send email to manufacturer if bulk orders are allowed
        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail->product->manufacturer->bulk_orders_allowed);
        $sendOrderedProductQuantityChangedNotification = $this->Manufacturer->getOptionSendOrderedProductQuantityChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_quantity_changed_notification);

        // only send email to manufacturer on the days between orderSend and delivery (normally wednesdays, thursdays and fridays)
        $weekday = date('N');
        if (! $this->AppAuth->isManufacturer() && in_array($weekday, Configure::read('app.timeHelper')->getWeekdaysBetweenOrderSendAndDelivery()) && ! $bulkOrdersAllowed && $sendOrderedProductQuantityChangedNotification) {
            $message .= ' sowie an den Hersteller <b>' . $oldOrderDetail->product->manufacturer->name . '</b>';
            $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
        }

        $email->send();

        $message .= ' versendet.';

        if ($editQuantityReason != '') {
            $message .= ' Grund: <b>"' . $editQuantityReason . '"</b>';
        }

        $message .= ' Der Lagerstand wurde auf ' . Configure::read('app.htmlHelper')->formatAsDecimal($newQuantity, 0) . ' erhöht.';

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

        $orderDetailId = (int) $this->request->getData('orderDetailId');
        $editPriceReason = strip_tags(html_entity_decode($this->request->getData('editPriceReason')));

        $productPrice = trim($this->request->getData('productPrice'));
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

        $this->OrderDetail = TableRegistry::get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Orders',
                'Orders.Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'OrderDetailTaxes'
            ]
        ])->first();

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $newOrderDetail = $this->changeOrderDetailPrice($object, $productPrice, $object->product_quantity);

        $message = 'Der Preis des bestellten Produktes <b>' . $oldOrderDetail->product_name . '</b> (Anzahl: ' . $oldOrderDetail->product_quantity . ') wurde erfolgreich von ' . Configure::read('app.htmlHelper')->formatAsDecimal($oldOrderDetail->total_price_tax_incl) . ' auf ' . Configure::read('app.htmlHelper')->formatAsDecimal($productPrice) . ' korrigiert ';

        // send email to customer
        $email = new AppEmail();
        $email->setTemplate('Admin.order_detail_price_changed')
        ->setTo($oldOrderDetail->order->customer->email)
        ->setSubject('Preis korrigiert: ' . $oldOrderDetail->product_name)
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editPriceReason' => $editPriceReason
        ]);

        $message .= ' und eine E-Mail an <b>' . $oldOrderDetail->order->customer->name . '</b>';

        // never send email to manufacturer if bulk orders are allowed
        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail->product->manufacturer->bulk_orders_allowed);
        $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_price_changed_notification);

        if (! $this->AppAuth->isManufacturer() && ! $bulkOrdersAllowed && $oldOrderDetail->total_price_tax_incl > 0.00 && $sendOrderedProductPriceChangedNotification) {
            $message .= ' sowie an den Hersteller <b>' . $oldOrderDetail->product->manufacturer->name . '</b>';
            $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
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

        $orderDetailIds = $this->request->getData('orderDetailIds');
        $cancellationReason = strip_tags(html_entity_decode($this->request->getData('cancellationReason')));

        if (!(is_array($orderDetailIds))) {
            die(json_encode([
                'status' => 0,
                'msg' => 'param needs to be an array, given: ' . $orderDetailIds
            ]));
        }

        $this->OrderDetail = TableRegistry::get('OrderDetails');
        $flashMessage = '';
        foreach ($orderDetailIds as $orderDetailId) {
            $orderDetail = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail' => $orderDetailId
                ],
                'contain' => [
                    'Orders',
                    'Orders.Customers',
                    'Products.StockAvailables',
                    'Products.Manufacturers',
                    'Products.Manufacturers.AddressManufacturers',
                    'ProductAttributes.StockAvailables',
                    'OrderDetailTaxes'
                ]
            ])->first();

            $message = 'Produkt <b>' . $orderDetail->product_name . '</b> ' . Configure::read('app.htmlHelper')->formatAsEuro($orderDetail->total_price_tax_incl) . ' aus Bestellung Nr. ' . $orderDetail->id_order . ' vom ' . $orderDetail->order->date_add->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort')) . ' wurde erfolgreich storniert';

            // delete row
            $this->OrderDetail->deleteOrderDetail($orderDetail);

            // update sum in table orders
            $this->OrderDetail->Orders->recalculateOrderDetailPricesInOrder($orderDetail->order);

            $newQuantity = $this->increaseQuantityForProduct($orderDetail, $orderDetail->product_quantity * 2);

            // send email to customer
            $email = new AppEmail();
            $email->setTemplate('Admin.order_detail_deleted')
            ->setTo($orderDetail->order->customer->email)
            ->setSubject('Produkt wurde storniert: ' . $orderDetail->product_name)
            ->setViewVars([
                'orderDetail' => $orderDetail,
                'appAuth' => $this->AppAuth,
                'cancellationReason' => $cancellationReason
            ]);

            $message .= ' und eine E-Mail an <b>' . $orderDetail->order->customer->name . '</b>';

            // never send email to manufacturer if bulk orders are allowed
            $this->Manufacturer = TableRegistry::get('Manufacturers');
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail->product->manufacturer->bulk_orders_allowed);
            $sendOrderedProductDeletedNotification = $this->Manufacturer->getOptionSendOrderedProductDeletedNotification($orderDetail->product->manufacturer->send_ordered_product_deleted_notification);

            // only send email to manufacturer on the days between orderSend and delivery (normally wednesdays, thursdays and fridays)
            $weekday = date('N');
            if (! $this->AppAuth->isManufacturer() && in_array($weekday, Configure::read('app.timeHelper')->getWeekdaysBetweenOrderSendAndDelivery()) && ! $bulkOrdersAllowed && $sendOrderedProductDeletedNotification) {
                $message .= ' sowie an den Hersteller <b>' . $orderDetail->product->manfacturer->name . '</b>';
                $email->addCC($orderDetail->product->manufacturer->address_manufacturer->email);
            }

            $email->send();

            $message .= ' versendet.';
            if ($cancellationReason != '') {
                $message .= ' Grund: <b>"' . $cancellationReason . '"</b>';
            }

            $message .= ' Der Lagerstand wurde um ' . $orderDetail->product_quantity . ' auf ' . Configure::read('app.htmlHelper')->formatAsDecimal($newQuantity, 0) . ' erhöht.';

            $this->ActionLog = TableRegistry::get('ActionLogs');
            $this->ActionLog->customSave('order_detail_cancelled', $this->AppAuth->getUserId(), $orderDetail->product_id, 'products', $message);
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
        
        $this->OrderDetail = TableRegistry::get('OrderDetails');
        
        $unitPriceExcl = $this->OrderDetail->Products->getNetPrice($oldOrderDetail->product_id, $productPrice / $productQuantity);
        $unitTaxAmount = $this->OrderDetail->Products->getUnitTax($productPrice, $unitPriceExcl, $productQuantity);
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
            'deposit' => $oldOrderDetail->deposit / $oldOrderDetail->product_quantity * $productQuantity
        ];
        
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity($oldOrderDetail, $orderDetail2save)
        );

        // update order_detail_tax for invoices
        if (!empty($oldOrderDetail->order_detail_tax)) {
            $orderDetailTax2save = [
                'unit_amount' => $unitTaxAmount,
                'total_amount' => $totalTaxAmount
            ];
            $this->OrderDetail->OrderDetailTaxes->id = $oldOrderDetail->id_order_detail;
            $this->OrderDetail->OrderDetailTaxes->save(
                $this->OrderDetail->OrderDetailTaxes->patchEntity($oldOrderDetail->order_detail_tax, $orderDetailTax2save)
            );
        }
        
        // update sum in orders
        $newOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $oldOrderDetail->id_order_detail
            ],
            'contain' => [
                'Orders',
                'Orders.Customers',
                'Products.StockAvailables',
                'Products.Manufacturers',
                'ProductAttributes.StockAvailables'
            ]
        ])->first();

        $this->OrderDetail->Orders->recalculateOrderDetailPricesInOrder($newOrderDetail);

        return $newOrderDetail;
    }

    private function increaseQuantityForProduct($orderDetail, $orderDetailQuantityBeforeQuantityChange)
    {
        
        // order detail references a product attribute
        if (!empty($orderDetail->product_attribute->stock_available)) {
            $stockAvailableObject = $orderDetail->product_attribute->stock_available;
        } else {
            $stockAvailableObject = $orderDetail->product->stock_available;
        }
        
        $quantity = $stockAvailableObject->quantity;
        
        // do the acutal updates for increasing quantity
        $this->StockAvailable = TableRegistry::get('StockAvailables');
        $originalPrimaryKey = $this->StockAvailable->getPrimaryKey();
        $this->StockAvailable->setPrimaryKey('id_stock_available');
        $newQuantity = $quantity + $orderDetailQuantityBeforeQuantityChange - $orderDetail->product_quantity;
        $patchedEntity = $this->StockAvailable->patchEntity($stockAvailableObject,
            [
                'quantity' => $newQuantity
            ]
        );
        $this->StockAvailable->save($patchedEntity);
        $this->StockAvailable->setPrimaryKey($originalPrimaryKey);

        return $newQuantity;
    }
}
