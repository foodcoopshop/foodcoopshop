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
            case 'editProductQuantity':
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
                'OrderDetails.product_amount', 'OrderDetails.product_name', 'OrderDetails.total_price_tax_incl', 'OrderDetails.deposit', 'OrderDetails.current_state', 'Orders.date_add', 'Manufacturers.name', 'Customers.' . Configure::read('app.customerMainNamePart'), 'OrderDetailUnits.product_quantity_in_units'
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
                    $orderDetail->quantityInUnitsNotYetChanged = false;
                    if (!empty($orderDetail->order_detail_unit)) {
                        if (round($orderDetail->order_detail_unit->product_quantity_in_units, 3) == round($orderDetail->order_detail_unit->quantity_in_units * $orderDetail->product_amount, 3)) {
                            $orderDetail->quantityInUnitsNotYetChanged = true;
                        }
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

        $groupByForDropdown = ['product' => __d('admin', 'Group_by_product')];
        if (!$this->AppAuth->isManufacturer()) {
            $groupByForDropdown['manufacturer'] = __d('admin', 'Group_by_manufacturer');
        }
        $this->set('groupByForDropdown', $groupByForDropdown);
        $this->set('customersForDropdown', $this->OrderDetail->Orders->Customers->getForDropdown());
        $this->set('manufacturersForDropdown', $this->OrderDetail->Products->Manufacturers->getForDropdown());

        if (!$this->AppAuth->isManufacturer()) {
            $this->set('customersForInstantOrderDropdown', $this->OrderDetail->Orders->Customers->getForDropdown(false, 'id_customer', $this->AppAuth->isSuperadmin()));
        }

        $this->set('title_for_layout', __d('admin', 'Ordered_products'));
    }

    public function editProductQuantity()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $productQuantity = trim($this->getRequest()->getData('productQuantity'));
        $doNotChangePrice = $this->getRequest()->getData('doNotChangePrice');
        $productQuantity = Configure::read('app.numberHelper')->parseFloatRespectingLocale($productQuantity);

        if (! is_numeric($orderDetailId) || !$productQuantity || $productQuantity < 0) {
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
                'OrderDetailTaxes',
                'OrderDetailUnits',
                'TimebasedCurrencyOrderDetails',
                'Orders.TimebasedCurrencyOrders'
            ]
        ])->first();

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $objectOrderDetailUnit = clone $oldOrderDetail->order_detail_unit;

        if (!$doNotChangePrice) {
            $newProductPrice = $oldOrderDetail->order_detail_unit->price_incl_per_unit / $oldOrderDetail->order_detail_unit->unit_amount * $productQuantity;
            $newOrderDetail = $this->changeOrderDetailPrice($object, $newProductPrice, $object->product_amount);
            $this->changeTimebasedCurrencyOrderDetailPrice($object, $oldOrderDetail, $newProductPrice, $object->product_amount);
        }
        $this->changeOrderDetailQuantity($objectOrderDetailUnit, $productQuantity);

        $message = __d('admin', 'The_weight_of_the_ordered_product_{0}_(amount_{1})_was_successfully_apapted_from_{2}_to_{3}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            $oldOrderDetail->product_amount,
            Configure::read('app.numberHelper')->formatUnitAsDecimal($oldOrderDetail->order_detail_unit->product_quantity_in_units) . ' ' . $oldOrderDetail->order_detail_unit->unit_name,
            Configure::read('app.numberHelper')->formatUnitAsDecimal($productQuantity) . ' ' . $oldOrderDetail->order_detail_unit->unit_name
        ]);

        // send email to customer if price was changed
        if (!$doNotChangePrice) {
            $email = new AppEmail();
            $email->setTemplate('Admin.order_detail_quantity_changed')
            ->setTo($oldOrderDetail->order->customer->email)
            ->setSubject(__d('admin', 'Weight_adapted') . ': ' . $oldOrderDetail->product_name)
            ->setViewVars([
                'oldOrderDetail' => $oldOrderDetail,
                'newProductQuantityInUnits' => $productQuantity,
                'newOrderDetail' => $newOrderDetail,
                'appAuth' => $this->AppAuth
            ]);

            $emailMssage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->order->customer->name . '</b>']);

            // never send email to manufacturer if bulk orders are allowed
            $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail->product->manufacturer->bulk_orders_allowed);
            $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_price_changed_notification);

            if (! $this->AppAuth->isManufacturer() && ! $bulkOrdersAllowed && $oldOrderDetail->total_price_tax_incl > 0.00 && $sendOrderedProductPriceChangedNotification) {
                $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                    '<b>' . $oldOrderDetail->order->customer->name . '</b>',
                    '<b>' . $oldOrderDetail->product->manufacturer->name . '</b>'
                ]);
                $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
            }

            $email->send();
        }

        $message .= $emailMessage;

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
                'Orders.TimebasedCurrencyOrders',
                'OrderDetailUnits'
            ]
        ])->first();

        $productPrice = $oldOrderDetail->total_price_tax_incl / $oldOrderDetail->product_amount * $productAmount;

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $newOrderDetail = $this->changeOrderDetailPrice($object, $productPrice, $productAmount);
        $newAmount = $this->increaseQuantityForProduct($newOrderDetail, $oldOrderDetail->product_amount);

        if (!empty($object->order_detail_unit)) {
            $productQuantity = $oldOrderDetail->order_detail_unit->product_quantity_in_units / $oldOrderDetail->product_amount * $productAmount;
            $this->changeOrderDetailQuantity($object->order_detail_unit, $productQuantity);
        }

        $this->changeTimebasedCurrencyOrderDetailPrice($object, $oldOrderDetail, $productPrice, $productAmount);

        $message = __d('admin', 'The_amount_of_the_ordered_product_{0}_was_successfully_changed_from_{1}_to_{2}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            $oldOrderDetail->product_amount,
            $productAmount
        ]);

        // send email to customer
        $email = new AppEmail();
        $email->setTemplate('Admin.order_detail_amount_changed')
        ->setTo($oldOrderDetail->order->customer->email)
        ->setSubject(__d('admin', 'Ordered_amount_adapted') . ': ' . $oldOrderDetail->product_name)
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editAmountReason' => $editAmountReason
        ]);

        $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->order->customer->name . '</b>']);

        // never send email to manufacturer if bulk orders are allowed
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail->product->manufacturer->bulk_orders_allowed);
        $sendOrderedProductAmountChangedNotification = $this->Manufacturer->getOptionSendOrderedProductAmountChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_amount_changed_notification);

        // only send email to manufacturer on the days between orderSend and delivery (normally wednesdays, thursdays and fridays)
        $weekday = date('N');
        if (! $this->AppAuth->isManufacturer() && in_array($weekday, Configure::read('app.timeHelper')->getWeekdaysBetweenOrderSendAndDelivery()) && ! $bulkOrdersAllowed && $sendOrderedProductAmountChangedNotification) {
            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                '<b>' . $oldOrderDetail->order->customer->name . '</b>',
                '<b>' . $oldOrderDetail->product->manufacturer->name . '</b>'
            ]);
            $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
        }

        $email->send();

        $message .= $emailMessage;

        if ($editAmountReason != '') {
            $message .= ' ' . __d('admin', 'Reason') . ': <b>"' . $editAmountReason . '"</b>';
        }

        $message .= ' ' . __d('admin', 'The_stock_was_increased_to_{0}.', [
            Configure::read('app.numberHelper')->formatAsDecimal($newAmount, 0)
        ]);

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
        $productPrice = Configure::read('app.numberHelper')->parseFloatRespectingLocale($productPrice);

        if (! is_numeric($orderDetailId) || !$productPrice || $productPrice < 0) {
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
                'OrderDetailTaxes',
                'TimebasedCurrencyOrderDetails',
                'Orders.TimebasedCurrencyOrders'
            ]
        ])->first();

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $newOrderDetail = $this->changeOrderDetailPrice($object, $productPrice, $object->product_amount);

        $message = __d('admin', 'The_price_of_the_ordered_product_{0}_(amount_{1})_was_successfully_apapted_from_{2}_to_{3}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            $oldOrderDetail->product_amount,
            Configure::read('app.numberHelper')->formatAsDecimal($oldOrderDetail->total_price_tax_incl),
            Configure::read('app.numberHelper')->formatAsDecimal($productPrice)
        ]);

        $this->changeTimebasedCurrencyOrderDetailPrice($object, $oldOrderDetail, $productPrice, $object->product_amount);

        // send email to customer
        $email = new AppEmail();
        $email->setTemplate('Admin.order_detail_price_changed')
        ->setTo($oldOrderDetail->order->customer->email)
        ->setSubject(__d('admin', 'Ordered_price_adapted') . ': ' . $oldOrderDetail->product_name)
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editPriceReason' => $editPriceReason
        ]);

        $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->order->customer->name . '</b>']);

        // never send email to manufacturer if bulk orders are allowed
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail->product->manufacturer->bulk_orders_allowed);
        $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_price_changed_notification);
        if (! $this->AppAuth->isManufacturer() && ! $bulkOrdersAllowed && $oldOrderDetail->total_price_tax_incl > 0.00 && $sendOrderedProductPriceChangedNotification) {
            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                '<b>' . $oldOrderDetail->order->customer->name . '</b>',
                '<b>' . $oldOrderDetail->product->manufacturer->name . '</b>'
            ]);
            $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
        }

        $email->send();

        $message .= $emailMessage;

        if ($editPriceReason != '') {
            $message .= ' '.__d('admin', 'Reason').': <b>"' . $editPriceReason . '"</b>';
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
                    'Orders.TimebasedCurrencyOrders',
                    'OrderDetailUnits'
                ]
            ])->first();

            $message = __d('admin', 'Product_{0}_with_a_price_of_{1}_from_order_number_{2}_from_{3}_was_successfully_cancelled.', [
                '<b>' . $orderDetail->product_name . '</b>',
                Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->total_price_tax_incl),
                $orderDetail->id_order,
                $orderDetail->order->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'))
            ]);

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
            ->setSubject(__d('admin', 'Product_was_cancelled').': ' . $orderDetail->product_name)
            ->setViewVars([
                'orderDetail' => $orderDetail,
                'appAuth' => $this->AppAuth,
                'cancellationReason' => $cancellationReason
            ]);

            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $orderDetail->order->customer->name . '</b>']);

            // never send email to manufacturer if bulk orders are allowed
            $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail->product->manufacturer->bulk_orders_allowed);
            $sendOrderedProductDeletedNotification = $this->Manufacturer->getOptionSendOrderedProductDeletedNotification($orderDetail->product->manufacturer->send_ordered_product_deleted_notification);

            // only send email to manufacturer on the days between orderSend and delivery (normally wednesdays, thursdays and fridays)
            $weekday = date('N');
            if (! $this->AppAuth->isManufacturer() && in_array($weekday, Configure::read('app.timeHelper')->getWeekdaysBetweenOrderSendAndDelivery()) && ! $bulkOrdersAllowed && $sendOrderedProductDeletedNotification) {
                $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                    '<b>' . $orderDetail->order->customer->name . '</b>',
                    '<b>' . $orderDetail->product->manufacturer->name . '</b>'
                ]);
                $email->addCC($orderDetail->product->manufacturer->address_manufacturer->email);
            }

            $email->send();

            $message .= $emailMessage;

            if ($cancellationReason != '') {
                $message .= ' '.__d('admin', 'Reason').': <b>"' . $cancellationReason . '"</b>';
            }

            $message .= ' ' . __d('admin', 'The_stock_was_increased_to_{0}.', [
                Configure::read('app.numberHelper')->formatAsDecimal($newQuantity, 0)
            ]);

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('order_detail_cancelled', $this->AppAuth->getUserId(), $orderDetail->product_id, 'products', $message);
        }

        $flashMessage = $message;
        $orderDetailsCount = count($orderDetailIds);
        if ($orderDetailsCount > 1) {
            $flashMessage = $orderDetailsCount . ' ' . __d('admin', '{0,plural,=1{product_was_cancelled_succesfully.} other{products_were_cancelled_succesfully.}}', $orderDetailsCount);
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
    private function changeOrderDetailQuantity($oldOrderDetailUnit, $productQuantity)
    {
        $orderDetailUnit2save = [
            'product_quantity_in_units' => $productQuantity
        ];
        $patchedEntity = $this->OrderDetail->OrderDetailUnits->patchEntity($oldOrderDetailUnit, $orderDetailUnit2save);
        $this->OrderDetail->OrderDetailUnits->save($patchedEntity);
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
                'ProductAttributes.StockAvailables',
                'OrderDetailUnits'
            ]
        ])->first();

        $this->OrderDetail->Orders->updateSums($newOrderDetail);

        return $newOrderDetail;
    }

    private function changeTimebasedCurrencyOrderDetailPrice($object, $oldOrderDetail, $newPrice, $amount)
    {
        if (!empty($object->timebased_currency_order_detail)) {
            $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
            $this->TimebasedCurrencyOrderDetail->changePrice($object, $newPrice, $amount);
            $this->TimebasedCurrencyOrder = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrders');
            $this->TimebasedCurrencyOrder->updateSums($oldOrderDetail->order);
        }
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
