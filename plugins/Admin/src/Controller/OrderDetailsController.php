<?php

namespace Admin\Controller;

use App\Mailer\AppEmail;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use App\Model\Table\OrderDetailsTable;

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
        switch ($this->getRequest()->getParam('action')) {
            case 'delete':
            case 'editProductPrice':
            case 'editProductAmount':
                if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                    return true;
                }
                /*
                 * START customer/manufacturer OWNER check
                 * param orderDetailId / orderDetailIds is passed via ajaxCall
                 */
                if (!empty($this->getRequest()->getData('orderDetailIds'))) {
                    $accessAllowed = false;
                    foreach ($this->getRequest()->getData('orderDetailIds') as $orderDetailId) {
                        $accessAllowed |= $this->checkOrderDetailIdAccess($orderDetailId);
                    }
                    return $accessAllowed;
                }
                if (!empty($this->getRequest()->getData('orderDetailId'))) {
                    return $this->checkOrderDetailIdAccess($this->getRequest()->getData('orderDetailId'));
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
            $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
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
            'OrderDetails.product_amount' => 'sum_amount',
            'OrderDetails.deposit' => 'sum_deposit'
        ];
        if (!empty($this->getRequest()->getQuery('sort')) && isset($sortMatches[$this->getRequest()->getQuery('sort')])) {
            $sortField = $sortMatches[$this->getRequest()->getQuery('sort')];
        }
        return $sortField;
    }

    /**
     * @return string
     */
    private function getSortDirectionForGroupedOrderDetails()
    {
        $sortDirection = 'ASC';
        if (!empty($this->getRequest()->getQuery('direction') && in_array($this->getRequest()->getQuery('direction'), ['asc', 'desc']))) {
            $sortDirection = $this->getRequest()->getQuery('direction');
        }
        return $sortDirection;
    }

    public function index()
    {

        // for filter from action logs page
        $orderDetailId = '';
        if (! empty($this->getRequest()->getQuery('orderDetailId'))) {
            $orderDetailId = $this->getRequest()->getQuery('orderDetailId');
        }

        $dateFrom = '';
        $dateTo = '';
        if ($orderDetailId == '') {
            $dateFrom = Configure::read('app.timeHelper')->getOrderPeriodFirstDay(Configure::read('app.timeHelper')->getCurrentDay());
            if (! empty($this->getRequest()->getQuery('dateFrom'))) {
                $dateFrom = $this->getRequest()->getQuery('dateFrom');
            }
            $dateTo = Configure::read('app.timeHelper')->getOrderPeriodLastDay(Configure::read('app.timeHelper')->getCurrentDay());
            if (! empty($this->getRequest()->getQuery('dateTo'))) {
                $dateTo = $this->getRequest()->getQuery('dateTo');
            }
        }
        $this->set('dateFrom', $dateFrom);
        $this->set('dateTo', $dateTo);

        $manufacturerId = '';
        if (! empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = $this->getRequest()->getQuery('manufacturerId');
        }
        $this->set('manufacturerId', $manufacturerId);

        $orderId = '';
        if (! empty($this->getRequest()->getQuery('orderId'))) {
            $orderId = $this->getRequest()->getQuery('orderId');
        }
        $this->set('orderId', $orderId);

        $deposit = '';
        if (! empty($this->getRequest()->getQuery('deposit'))) {
            $deposit = $this->getRequest()->getQuery('deposit');
        }
        $this->set('deposit', $deposit);

        $orderStates = Configure::read('app.htmlHelper')->getOrderStateIds();
        if ($this->AppAuth->isManufacturer()) {
            $orderStates = ORDER_STATE_OPEN;
        }

        $orderStates = Configure::read('app.htmlHelper')->getOrderStateIds();
        if (in_array('orderStates', array_keys($this->getRequest()->getQueryParams()))) {
            $orderStates = $this->getRequest()->getQuery('orderStates');
            if ($orderStates == '') {
                $orderStates = [];
            }
        }
        // legacy cakephp2: param was called "orderState" and contained csv data
        if (in_array('orderState', array_keys($this->getRequest()->getQueryParams()))) {
            $orderStates = explode(', ', $this->getRequest()->getQuery('orderState'));
        }
        $this->set('orderStates', $orderStates);

        $productId = '';
        if (! empty($this->getRequest()->getQuery('productId'))) {
            $productId = $this->getRequest()->getQuery('productId');
        }
        $this->set('productId', $productId);

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = $this->getRequest()->getQuery('customerId');
        }
        $this->set('customerId', $customerId);

        $groupBy = '';
        if (! empty($this->getRequest()->getQuery('groupBy'))) {
            $groupBy = $this->getRequest()->getQuery('groupBy');
        }

        // legacy: still allow old variable "groupByManufacturer"
        if (! empty($this->getRequest()->getQuery('groupByManufacturer'))) {
            $groupBy = 'manufacturer';
        }

        $this->set('groupBy', $groupBy);

        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $odParams = $this->OrderDetail->getOrderDetailParams($this->AppAuth, $manufacturerId, $productId, $customerId, $orderStates, $dateFrom, $dateTo, $orderDetailId, $orderId, $deposit);

        $query = $this->OrderDetail->find('all', [
            'conditions' => $odParams['conditions'],
            'contain' => $odParams['contain']
        ]);

        $orderDetails = $this->paginate($query, [
            'sortWhitelist' => [
                'OrderDetails.product_amount', 'OrderDetails.product_name', 'OrderDetails.total_price_tax_incl', 'OrderDetails.deposit', 'OrderDetails.current_state', 'Orders.date_add', 'Manufacturers.name', 'Customers.' . Configure::read('app.customerMainNamePart')
            ],
            'order' => [
                // first param needs to be included in sortWhitelist!
                'Orders.date_add' => 'DESC',
                'Products.id_manufacturer' => 'ASC',
                'OrderDetails.product_name' => 'ASC'
            ]
        ])->toArray();

        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');

        switch ($groupBy) {
            case 'manufacturer':
                $preparedOrderDetails = [];
                foreach ($orderDetails as $orderDetail) {
                    $key = $orderDetail->product->id_manufacturer;
                    @$preparedOrderDetails[$key]['sum_price'] += $orderDetail->total_price_tax_incl;
                    @$preparedOrderDetails[$key]['sum_amount'] += $orderDetail->product_amount;
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
                    @$preparedOrderDetails[$key]['sum_amount'] += $orderDetail->product_amount;
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
                    $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
                    $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail->product->manufacturer->bulk_orders_allowed);
                    $orderDetail->bulkOrdersAllowed = $bulkOrdersAllowed;
                    $orderDetail->row_class = [];
                    if ($bulkOrdersAllowed) {
                        $orderDetail->row_class[] = 'deactivated';
                    }
                    $i ++;
                }
                break;
        }

        $this->set('orderDetails', $orderDetails);
        
        $timebasedCurrencyOrderInList = false;
        foreach($orderDetails as $orderDetail) {
            if (!empty($orderDetail->timebased_currency_order_detail)) {
                $timebasedCurrencyOrderInList = true;
                break;
            }
        }
        $this->set('timebasedCurrencyOrderInList', $timebasedCurrencyOrderInList);

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
        
        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $productQuantity = trim($this->getRequest()->getData('productQuantity'));
        $productQuantity = Configure::read('app.numberHelper')->replaceCommaWithDot($productQuantity);

        if (! is_numeric($orderDetailId) || ! is_numeric($productQuantity) || $productQuantity < 0) {
            $message = 'input format wrong';
            $this->log($message);
            die(json_encode([
                'status' => 0,
                'msg' => $message
            ]));
        }
        
        $productQuantity = floatval($productQuantity);
        
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Orders',
                'Orders.Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'OrderDetailTaxes',
                'TimebasedCurrencyOrderDetails',
                'Orders.TimebasedCurrencyOrders'
            ]
        ])->first();
        
        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $newProductPrice = $oldOrderDetail->total_price_tax_incl / $oldOrderDetail->quantity_in_units * $productQuantity;
        $newOrderDetail = $this->changeOrderDetailPrice($object, $newProductPrice, $object->product_amount);

        $this->changeOrderDetailQuantity($object, $productQuantity);
        
        $message = 'Das Gewicht des bestellten Produktes <b>' . $oldOrderDetail->product_name . '</b> (Anzahl: ' . $oldOrderDetail->product_amount . ') wurde erfolgreich von ' . Configure::read('app.htmlHelper')->formatAsDecimal($object->quantity_in_units) . ' ' . $oldOrderDetail->unit_name . ' auf ' . Configure::read('app.htmlHelper')->formatAsDecimal($productQuantity) . ' ' . $oldOrderDetail->unit_name . ' korrigiert ';
        
        // send email to customer
        $email = new AppEmail();
        $email->setTemplate('Admin.order_detail_quantity_changed')
        ->setTo($oldOrderDetail->order->customer->email)
        ->setSubject('Gewicht korrigiert: ' . $oldOrderDetail->product_name)
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth
        ]);
        
        $message .= ' und eine E-Mail an <b>' . $oldOrderDetail->order->customer->name . '</b>';
        
        // never send email to manufacturer if bulk orders are allowed
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail->product->manufacturer->bulk_orders_allowed);
        $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_price_changed_notification);
        
        if (! $this->AppAuth->isManufacturer() && ! $bulkOrdersAllowed && $oldOrderDetail->total_price_tax_incl > 0.00 && $sendOrderedProductPriceChangedNotification) {
            $message .= ' sowie an den Hersteller <b>' . $oldOrderDetail->product->manufacturer->name . '</b>';
            $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
        }
        
        $email->send();
        
        $message .= ' versendet.';
        
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('order_detail_product_quantity_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);
        $this->Flash->success($message);
        
        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
        
    }

    public function editProductAmount()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $productAmount = trim($this->getRequest()->getData('productAmount'));
        $editAmountReason = strip_tags(html_entity_decode($this->getRequest()->getData('editAmountReason')));

        if (! is_numeric($orderDetailId) || ! is_numeric($productAmount) || $productAmount < 1) {
            $message = 'input format wrong';
            $this->log($message);
            die(json_encode([
                'status' => 0,
                'msg' => $message
            ]));
        }

        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Orders',
                'Orders.Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'TimebasedCurrencyOrderDetails',
                'Orders.TimebasedCurrencyOrders'
            ]
        ])->first();

        $productPrice = $oldOrderDetail->total_price_tax_incl / $oldOrderDetail->product_amount * $productAmount;

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $newOrderDetail = $this->changeOrderDetailPrice($object, $productPrice, $productAmount);
        $newAmount = $this->increaseQuantityForProduct($newOrderDetail, $object->product_amount);

        $productQuantity = $oldOrderDetail->quantity_in_units / $oldOrderDetail->product_amount * $productAmount;
        $this->changeOrderDetailQuantity($object, $productQuantity);
        
        if (!empty($object->timebased_currency_order_detail)) {
            $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
            $this->TimebasedCurrencyOrderDetail->changePrice($object, $productPrice, $productAmount);
            $this->TimebasedCurrencyOrder = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrders');
            $this->TimebasedCurrencyOrder->updateSums($oldOrderDetail->order);
        }
        
        $message = 'Die Anzahl des bestellten Produktes <b>' . $oldOrderDetail->product_name . '" </b> wurde erfolgreich von ' . $oldOrderDetail->product_amount . ' auf ' . $productAmount . ' geändert';

        // send email to customer
        $email = new AppEmail();
        $email->setTemplate('Admin.order_detail_amount_changed')
        ->setTo($oldOrderDetail->order->customer->email)
        ->setSubject('Bestellte Anzahl korrigiert: ' . $oldOrderDetail->product_name)
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editAmountReason' => $editAmountReason
        ]);

        $message .= ' und eine E-Mail an <b>' . $oldOrderDetail->order->customer->name . '</b>';

        // never send email to manufacturer if bulk orders are allowed
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail->product->manufacturer->bulk_orders_allowed);
        $sendOrderedProductAmountChangedNotification = $this->Manufacturer->getOptionSendOrderedProductAmountChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_amount_changed_notification);

        // only send email to manufacturer on the days between orderSend and delivery (normally wednesdays, thursdays and fridays)
        $weekday = date('N');
        if (! $this->AppAuth->isManufacturer() && in_array($weekday, Configure::read('app.timeHelper')->getWeekdaysBetweenOrderSendAndDelivery()) && ! $bulkOrdersAllowed && $sendOrderedProductAmountChangedNotification) {
            $message .= ' sowie an den Hersteller <b>' . $oldOrderDetail->product->manufacturer->name . '</b>';
            $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
        }

        $email->send();

        $message .= ' versendet.';

        if ($editAmountReason != '') {
            $message .= ' Grund: <b>"' . $editAmountReason . '"</b>';
        }

        $message .= ' Der Lagerstand wurde auf ' . Configure::read('app.htmlHelper')->formatAsDecimal($newAmount, 0) . ' erhöht.';

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('order_detail_product_amount_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);

        $this->Flash->success($message);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    public function editProductPrice()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $editPriceReason = strip_tags(html_entity_decode($this->getRequest()->getData('editPriceReason')));

        $productPrice = trim($this->getRequest()->getData('productPrice'));
        $productPrice = Configure::read('app.numberHelper')->replaceCommaWithDot($productPrice);

        if (! is_numeric($orderDetailId) || ! is_numeric($productPrice) || $productPrice < 0) {
            $message = 'input format wrong';
            $this->log($message);
            die(json_encode([
                'status' => 0,
                'msg' => $message
            ]));
        }

        $productPrice = floatval($productPrice);

        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Orders',
                'Orders.Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'OrderDetailTaxes',
                'TimebasedCurrencyOrderDetails',
                'Orders.TimebasedCurrencyOrders'
            ]
        ])->first();

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $newOrderDetail = $this->changeOrderDetailPrice($object, $productPrice, $object->product_amount);
        
        $message = 'Der Preis des bestellten Produktes <b>' . $oldOrderDetail->product_name . '</b> (Anzahl: ' . $oldOrderDetail->product_amount . ') wurde erfolgreich von ' . Configure::read('app.htmlHelper')->formatAsDecimal($oldOrderDetail->total_price_tax_incl) . ' auf ' . Configure::read('app.htmlHelper')->formatAsDecimal($productPrice) . ' korrigiert ';
        
        if (!empty($object->timebased_currency_order_detail)) {
            $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
            $this->TimebasedCurrencyOrderDetail->changePrice($object, $productPrice, $object->product_amount);
            $this->TimebasedCurrencyOrder = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrders');
            $this->TimebasedCurrencyOrder->updateSums($oldOrderDetail->order);
        }
        
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
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
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

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
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

        $orderDetailIds = $this->getRequest()->getData('orderDetailIds');
        $cancellationReason = strip_tags(html_entity_decode($this->getRequest()->getData('cancellationReason')));

        if (!(is_array($orderDetailIds))) {
            die(json_encode([
                'status' => 0,
                'msg' => 'param needs to be an array, given: ' . $orderDetailIds
            ]));
        }

        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $flashMessage = '';
        foreach ($orderDetailIds as $orderDetailId) {
            $orderDetail = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail' => $orderDetailId
                ],
                'contain' => [
                    'Orders.Customers',
                    'Products.StockAvailables',
                    'Products.Manufacturers',
                    'Products.Manufacturers.AddressManufacturers',
                    'ProductAttributes.StockAvailables',
                    'OrderDetailTaxes',
                    'TimebasedCurrencyOrderDetails',
                    'Orders.TimebasedCurrencyOrders'
                ]
            ])->first();

            $message = 'Produkt <b>' . $orderDetail->product_name . '</b> ' . Configure::read('app.htmlHelper')->formatAsEuro($orderDetail->total_price_tax_incl) . ' aus Bestellung Nr. ' . $orderDetail->id_order . ' vom ' . $orderDetail->order->date_add->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort')) . ' wurde erfolgreich storniert';

            $this->OrderDetail->deleteOrderDetail($orderDetail);
            $this->OrderDetail->Orders->updateSums($orderDetail->order);
            
            if (!empty($orderDetail->timebased_currency_order_detail)) {
                $this->TimebasedCurrencyOrder = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrders');
                $this->TimebasedCurrencyOrder->updateSums($orderDetail->order);
            }
            
            $newQuantity = $this->increaseQuantityForProduct($orderDetail, $orderDetail->product_amount * 2);

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
            $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail->product->manufacturer->bulk_orders_allowed);
            $sendOrderedProductDeletedNotification = $this->Manufacturer->getOptionSendOrderedProductDeletedNotification($orderDetail->product->manufacturer->send_ordered_product_deleted_notification);

            // only send email to manufacturer on the days between orderSend and delivery (normally wednesdays, thursdays and fridays)
            $weekday = date('N');
            if (! $this->AppAuth->isManufacturer() && in_array($weekday, Configure::read('app.timeHelper')->getWeekdaysBetweenOrderSendAndDelivery()) && ! $bulkOrdersAllowed && $sendOrderedProductDeletedNotification) {
                $message .= ' sowie an den Hersteller <b>' . $orderDetail->product->manufacturer->name . '</b>';
                $email->addCC($orderDetail->product->manufacturer->address_manufacturer->email);
            }

            $email->send();

            $message .= ' versendet.';
            if ($cancellationReason != '') {
                $message .= ' Grund: <b>"' . $cancellationReason . '"</b>';
            }

            $message .= ' Der Lagerstand wurde um ' . $orderDetail->product_amount . ' auf ' . Configure::read('app.htmlHelper')->formatAsDecimal($newQuantity, 0) . ' erhöht.';

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
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
    
    /**
     * @param OrderDetailsTable $oldOrderDetail
     * @param float $productQuantity
     */
    private function changeOrderDetailQuantity($oldOrderDetail, $productQuantity)
    {
        $orderDetail2save = [
            'quantity_in_units' => $productQuantity
        ];
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity($oldOrderDetail, $orderDetail2save)
        );
    }

    private function changeOrderDetailPrice($oldOrderDetail, $productPrice, $productAmount)
    {

        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');

        $unitPriceExcl = $this->OrderDetail->Products->getNetPrice($oldOrderDetail->product_id, $productPrice / $productAmount);
        $unitTaxAmount = $this->OrderDetail->Products->getUnitTax($productPrice, $unitPriceExcl, $productAmount);
        $totalTaxAmount = $unitTaxAmount * $productAmount;
        $totalPriceTaxExcl = $productPrice - $totalTaxAmount;

        // update order details
        $orderDetail2save = [
            'total_price_tax_incl' => $productPrice,
            'total_price_tax_excl' => $totalPriceTaxExcl,
            'product_amount' => $productAmount,
            'deposit' => $oldOrderDetail->deposit / $oldOrderDetail->product_amount * $productAmount
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

        $this->OrderDetail->Orders->updateSums($newOrderDetail);

        return $newOrderDetail;
    }

    private function increaseQuantityForProduct($orderDetail, $orderDetailAmountBeforeAmountChange)
    {

        // order detail references a product attribute
        if (!empty($orderDetail->product_attribute->stock_available)) {
            $stockAvailableObject = $orderDetail->product_attribute->stock_available;
        } else {
            $stockAvailableObject = $orderDetail->product->stock_available;
        }

        $quantity = $stockAvailableObject->quantity;

        // do the acutal updates for increasing quantity
        $this->StockAvailable = TableRegistry::getTableLocator()->get('StockAvailables');
        $originalPrimaryKey = $this->StockAvailable->getPrimaryKey();
        $this->StockAvailable->setPrimaryKey('id_stock_available');
        $newQuantity = $quantity + $orderDetailAmountBeforeAmountChange - $orderDetail->product_amount;
        $patchedEntity = $this->StockAvailable->patchEntity(
            $stockAvailableObject,
            [
                'quantity' => $newQuantity
            ]
        );
        $this->StockAvailable->save($patchedEntity);
        $this->StockAvailable->setPrimaryKey($originalPrimaryKey);

        return $newQuantity;
    }
}
