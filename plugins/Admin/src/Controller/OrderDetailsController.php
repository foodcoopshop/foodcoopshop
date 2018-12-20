<?php

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use App\Lib\Error\Exception\InvalidParameterException;
use App\Mailer\AppEmail;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use App\Model\Table\OrderDetailsTable;

/**
* FoodCoopShop - The open source software for your foodcoop
*
* Licensed under The MIT License
* For full copyright and license information, please see the LICENSE.txt
* Redistributions of files must retain the above copyright notice.
*
* @since         FoodCoopShop 1.0.0
* @license       http://www.opensource.org/licenses/mit-license.php MIT License
* @author        Mario Rothauer <office@foodcoopshop.com>
* @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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
                    'Products'
                ]
            ])->first();
            if (!empty($orderDetail)) {
                if ($this->AppAuth->isManufacturer() && $orderDetail->product->id_manufacturer == $this->AppAuth->getManufacturerId()) {
                    return true;
                }
                if ($this->AppAuth->isCustomer() && $orderDetail->id_customer == $this->AppAuth->getUserId()) {
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
    
    /**
     * this url is called if instant order is initialized
     * saves the desired user in session
     */
    public function initInstantOrder($customerId)
    {
        if (! $customerId) {
            throw new RecordNotFoundException('customerId not passed');
        }
        
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $instantOrderCustomer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
            'contain' => [
                'AddressCustomers'
            ]
        ])->first();
        if (! empty($instantOrderCustomer)) {
            $this->getRequest()->getSession()->write('Auth.instantOrderCustomer', $instantOrderCustomer);
        } else {
            $this->Flash->error(__d('admin', 'No_member_found_with_id_{0}.', [$customerId]));
        }
        $this->redirect('/');
    }

    public function iframeStartPage()
    {
        $this->set('title_for_layout', __d('admin', 'Instant_order'));
    }
    
    public function orderDetailsAsPdf()
    {
        
        $pickupDay = [$this->getRequest()->getQuery('pickupDay')];
        
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $odParams = $this->OrderDetail->getOrderDetailParams($this->AppAuth, '', '', '', $pickupDay, '', '');
        $contain = $odParams['contain'];
        $this->OrderDetail->getAssociation('PickupDayEntities')->setConditions([
            'PickupDayEntities.pickup_day' => Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[0])
        ]);
        $contain[] = 'PickupDayEntities';
        $orderDetails = $this->OrderDetail->find('all', [
            'conditions' => $odParams['conditions'],
            'contain' => $contain
        ])->toArray();
        
        $customerName = [];
        $manufacturerName = [];
        $productName = [];
        foreach($orderDetails as $orderDetail) {
            $customerName[] = StringComponent::slugify($orderDetail->customer->name);
            $manufacturerName[] = StringComponent::slugify($orderDetail->product->manufacturer->name);
            $productName[] = StringComponent::slugify($orderDetail->product_name);
        }
        array_multisort(
            $customerName, SORT_ASC,
            $manufacturerName, SORT_ASC,
            $productName, SORT_ASC,
            $orderDetails
        );
        
        $preparedOrderDetails = [];
        foreach($orderDetails as $orderDetail) {
            @$preparedOrderDetails[$orderDetail->id_customer][] = $orderDetail;
        }

        $this->set('orderDetails', $preparedOrderDetails);
        
    }
    
    public function index()
    {

        if (!empty($this->getRequest()->getQuery('message'))) {
            $this->Flash->success($this->getRequest()->getQuery('message'));
            $this->redirect($this->referer());
        }
        
        // for filter from action logs page
        $orderDetailId = '';
        if (! empty($this->getRequest()->getQuery('orderDetailId'))) {
            $orderDetailId = $this->getRequest()->getQuery('orderDetailId');
        }

        $legacyCall = false;
        $pickupDay = [];
        if ($orderDetailId == '') {
            if (in_array('pickupDay', array_keys($this->getRequest()->getQueryParams()))) {
                $pickupDay = $this->getRequest()->getQuery('pickupDay');
                $explodedPickupDay = explode(',', $pickupDay[0]); // param can be passed comma separated
                if (count($explodedPickupDay) == 2) {
                    $pickupDay = $explodedPickupDay;
                }
            } else {
                // default value
                $pickupDay[0] = Configure::read('app.timeHelper')->getPreselectedDeliveryDayForOrderDetails(Configure::read('app.timeHelper')->getCurrentDay());
            }
            // START legacy code - can be safely removed in v3
            // assures that old links (before v2.2) in emails to the financial responsible person still work after v2.2
            if ($this->getRequest()->getQuery('dateFrom')) {
                $legacyCall = true;
                $pickupDay[0] = $this->getRequest()->getQuery('dateFrom');
            }
            if ($this->getRequest()->getQuery('dateTo')) {
                $pickupDay[1] = $this->getRequest()->getQuery('dateTo');
            }
            // END legacy code
        }
        
        $pickupDay = Configure::read('app.timeHelper')->sortArrayByDate($pickupDay);
        $this->set('pickupDay', $pickupDay);

        $manufacturerId = '';
        if (! empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = $this->getRequest()->getQuery('manufacturerId');
        }
        $this->set('manufacturerId', $manufacturerId);

        $deposit = '';
        if (! empty($this->getRequest()->getQuery('deposit'))) {
            $deposit = $this->getRequest()->getQuery('deposit');
        }
        $this->set('deposit', $deposit);

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
        if ($this->AppAuth->isManufacturer() && $groupBy != 'product') {
            $groupBy = '';
        }

        // legacy: still allow old variable "groupByManufacturer"
        if (! empty($this->getRequest()->getQuery('groupByManufacturer'))) {
            $groupBy = 'manufacturer';
        }

        $this->set('groupBy', $groupBy);

        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $odParams = $this->OrderDetail->getOrderDetailParams($this->AppAuth, $manufacturerId, $productId, $customerId, $pickupDay, $orderDetailId, $deposit);

        $contain = $odParams['contain'];
        if (($groupBy == 'customer' || $groupBy == '') && count($pickupDay) == 1) {
            $this->OrderDetail->getAssociation('PickupDayEntities')->setConditions([
                'PickupDayEntities.pickup_day' => Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[0])
            ]);
            $contain[] = 'PickupDayEntities';
        }

        if ($legacyCall) {
            foreach($odParams['conditions'] as &$condition) {
                $condition = preg_replace('/OrderDetails.pickup_day/', 'OrderDetails.created', $condition);
            }
        }
        
        $query = $this->OrderDetail->find('all', [
            'conditions' => $odParams['conditions'],
            'contain' => $contain,
        ]);

        if (in_array('excludeCreatedLastMonth', array_keys($this->getRequest()->getQueryParams()))) {
            $query->where(['DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[0]) . '\'']);
        }
        
        $orderDetails = $this->paginate($query, [
            'sortWhitelist' => [
                'OrderDetails.product_amount', 'OrderDetails.product_name', 'OrderDetails.total_price_tax_incl', 'OrderDetails.deposit', 'OrderDetails.order_state', 'OrderDetails.pickup_day', 'Manufacturers.name', 'Customers.' . Configure::read('app.customerMainNamePart'), 'OrderDetailUnits.product_quantity_in_units'
            ]
        ])->toArray();
        
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        
        $orderDetails = $this->prepareGroupedOrderDetails($orderDetails, $groupBy, $pickupDay);
        $this->set('orderDetails', $orderDetails);

        $timebasedCurrencyOrderDetailInList = false;
        $sums = [
            'records_count' => 0,
            'amount' => 0,
            'price' => 0,
            'deposit' => 0,
            'units' => [],
            'reduced_price' => 0
        ];
        foreach($orderDetails as $orderDetail) {
            @$sums['records_count']++;
            if ($groupBy == '') {
                @$sums['price'] += $orderDetail->total_price_tax_incl;
                @$sums['amount'] += $orderDetail->product_amount;
                @$sums['deposit'] += $orderDetail->deposit;
            } else {
                @$sums['price'] += $orderDetail['sum_price'];
                @$sums['amount'] += $orderDetail['sum_amount'];
                if ($groupBy == 'manufacturer') {
                    @$sums['reduced_price'] += $orderDetail['reduced_price'];
                }
                @$sums['deposit'] += $orderDetail['sum_deposit'];
            }
            if (!empty($orderDetail->order_detail_unit)) {
                @$sums['units'][$orderDetail->order_detail_unit->unit_name] += $orderDetail->order_detail_unit->product_quantity_in_units;
            }
            if (!empty($orderDetail->timebased_currency_order_detail) || !empty($orderDetail['timebased_currency_order_detail_seconds_sum'])) {
                $timebasedCurrencyOrderDetailInList = true;
            }
        }
        $this->set('timebasedCurrencyOrderDetailInList', $timebasedCurrencyOrderDetailInList);
        $this->set('sums', $sums);
        
        // extract all email addresses for button
        $emailAddresses = [];
        if ($groupBy == '') {
            $emailAddresses = $query->all()->extract('customer.email')->toArray();
        }
        if ($groupBy == 'customer') {
            $emailAddresses = Hash::extract($orderDetails, '{n}.email');
        }
        $emailAddresses = array_unique($emailAddresses);
        $this->set('emailAddresses', $emailAddresses);

        $groupByForDropdown = [
            'product' => __d('admin', 'Group_by_product')
        ];
        if (!$this->AppAuth->isManufacturer()) {
            $groupByForDropdown['customer'] = __d('admin', 'Group_by_member');
            $groupByForDropdown['manufacturer'] = __d('admin', 'Group_by_manufacturer');
        }
        $this->set('groupByForDropdown', $groupByForDropdown);
        $this->set('customersForDropdown', $this->OrderDetail->Customers->getForDropdown());
        $this->set('manufacturersForDropdown', $this->OrderDetail->Products->Manufacturers->getForDropdown());

        if (!$this->AppAuth->isManufacturer()) {
            $filter = [];
            if ($this->AppAuth->isCustomer()) {
                $filter = ['Customers.id_customer' => $this->AppAuth->getUserId()];
            }
            $customersForInstantOrderDropdown = $this->OrderDetail->Customers->getForDropdown(false, 'id_customer', $this->AppAuth->isSuperadmin(), $filter);
            $this->set('customersForInstantOrderDropdown', $customersForInstantOrderDropdown);
        }

        $this->set('title_for_layout', __d('admin', 'Orders'));
    }
    
    private function prepareGroupedOrderDetails($orderDetails, $groupBy)
    {
        
        switch ($groupBy) {
            case 'customer':
                $preparedOrderDetails = $this->OrderDetail->prepareOrderDetailsGroupedByCustomer($orderDetails);
                $sortField = $this->getSortFieldForGroupedOrderDetails('name');
                break;
            case 'manufacturer':
                $preparedOrderDetails = $this->OrderDetail->prepareOrderDetailsGroupedByManufacturer($orderDetails);
                $sortField = $this->getSortFieldForGroupedOrderDetails('name');
                break;
            case 'product':
                $preparedOrderDetails = $this->OrderDetail->prepareOrderDetailsGroupedByProduct($orderDetails);
                $sortField = $this->getSortFieldForGroupedOrderDetails('manufacturer_name');
                break;
            default:
                $deliveryDay = [];
                $manufacturerName = [];
                $productName = [];
                foreach ($orderDetails as $orderDetail) {
                    $orderDetail->quantityInUnitsNotYetChanged = false;
                    if (!empty($orderDetail->order_detail_unit)) {
                        if (round($orderDetail->order_detail_unit->product_quantity_in_units, 3) == round($orderDetail->order_detail_unit->quantity_in_units * $orderDetail->product_amount, 3)) {
                            $orderDetail->quantityInUnitsNotYetChanged = true;
                        }
                    }
                    $deliveryDay[] = $orderDetail->pickup_day;
                    $manufacturerName[] = StringComponent::slugify($orderDetail->product->manufacturer->name);
                    $productName[] = StringComponent::slugify($orderDetail->product_name);
                }
                if (!in_array('sort', array_keys($this->getRequest()->getQueryParams()))) {
                    array_multisort(
                        $deliveryDay, SORT_ASC,
                        $manufacturerName, SORT_ASC,
                        $productName, SORT_ASC,
                        $orderDetails
                    );
                }
                break;
        }
        
        if (isset($sortField)) {
            $sortDirection = $this->getSortDirectionForGroupedOrderDetails();
            $orderDetails = Hash::sort($preparedOrderDetails, '{n}.' . $sortField, $sortDirection);
        }
        
        return $orderDetails;
        
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
                'Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'OrderDetailTaxes',
                'OrderDetailUnits',
                'TimebasedCurrencyOrderDetails'
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
            $email->viewBuilder()->setTemplate('Admin.order_detail_quantity_changed');
            $email->setTo($oldOrderDetail->customer->email)
            ->setSubject(__d('admin', 'Weight_adapted') . ': ' . $oldOrderDetail->product_name)
            ->setViewVars([
                'oldOrderDetail' => $oldOrderDetail,
                'newProductQuantityInUnits' => $productQuantity,
                'newOrderDetail' => $newOrderDetail,
                'appAuth' => $this->AppAuth
            ]);

            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->customer->name . '</b>']);

            // never send email to manufacturer if bulk orders are allowed
            $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail->product->manufacturer->bulk_orders_allowed);
            $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_price_changed_notification);

            if (! $this->AppAuth->isManufacturer() && ! $bulkOrdersAllowed && $oldOrderDetail->total_price_tax_incl > 0.00 && $sendOrderedProductPriceChangedNotification) {
                $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                    '<b>' . $oldOrderDetail->customer->name . '</b>',
                    '<b>' . $oldOrderDetail->product->manufacturer->name . '</b>'
                ]);
                $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
            }

            $email->send();
            
            $message .= $emailMessage;
            
        }

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
                'Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'TimebasedCurrencyOrderDetails',
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
        $email->viewBuilder()->setTemplate('Admin.order_detail_amount_changed');
        $email->setTo($oldOrderDetail->customer->email)
        ->setSubject(__d('admin', 'Ordered_amount_adapted') . ': ' . $oldOrderDetail->product_name)
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editAmountReason' => $editAmountReason
        ]);

        $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->customer->name . '</b>']);

        // never send email to manufacturer if bulk orders are allowed
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail->product->manufacturer->bulk_orders_allowed);
        $sendOrderedProductAmountChangedNotification = $this->Manufacturer->getOptionSendOrderedProductAmountChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_amount_changed_notification);

        if (! $this->AppAuth->isManufacturer() && $oldOrderDetail->order_state == ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER && ! $bulkOrdersAllowed && $sendOrderedProductAmountChangedNotification) {
            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                '<b>' . $oldOrderDetail->customer->name . '</b>',
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
                'Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'OrderDetailTaxes',
                'TimebasedCurrencyOrderDetails',
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
        $email->viewBuilder()->setTemplate('Admin.order_detail_price_changed');
        $email->setTo($oldOrderDetail->customer->email)
        ->setSubject(__d('admin', 'Ordered_price_adapted') . ': ' . $oldOrderDetail->product_name)
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editPriceReason' => $editPriceReason
        ]);

        $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->customer->name . '</b>']);

        // never send email to manufacturer if bulk orders are allowed
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($oldOrderDetail->product->manufacturer->bulk_orders_allowed);
        $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_price_changed_notification);
        if (! $this->AppAuth->isManufacturer() && ! $bulkOrdersAllowed && $oldOrderDetail->total_price_tax_incl > 0.00 && $sendOrderedProductPriceChangedNotification) {
            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                '<b>' . $oldOrderDetail->customer->name . '</b>',
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
    
    public function editPickupDay()
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        $orderDetailIds = $this->getRequest()->getData('orderDetailIds');
        $pickupDay = $this->getRequest()->getData('pickupDay');
        $pickupDay = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);
        $changePickupDayReason = htmlspecialchars_decode(strip_tags(trim($this->getRequest()->getData('changePickupDayReason')), '<strong><b>'));
        
        try {
            if (empty($orderDetailIds)) {
                throw new InvalidParameterException('error - no order detail id passed');
            }
            $errorMessages = [];
            if ($changePickupDayReason == '') {
                $errorMessages[] = __d('admin', 'Please_enter_why_pickup_day_is_changed.');
            }
            
            $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
            $orderDetails = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail IN' => $orderDetailIds
                ],
                'contain' => [
                    'Customers',
                    'Products.Manufacturers'
                ]
            ]);
            if ($orderDetails->count() != count($orderDetailIds)) {
                throw new InvalidParameterException('error - order details wrong');
            }
            
            $oldPickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($orderDetails->toArray()[0]->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'))));
            $newPickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($pickupDay));
            
            // validate only once for the first order detail
            $entity = $this->OrderDetail->patchEntity(
                $orderDetails->toArray()[0],
                [
                    'pickup_day' => $pickupDay
                ],
                [
                    'validate' => 'pickupDay'
                ]
            );
            if ($entity->hasErrors()) {
                $errorMessages = array_merge($errorMessages, $this->OrderDetail->getAllValidationErrors($entity));
            }
            if (!empty($errorMessages)) {
                throw new InvalidParameterException(join('<br />', $errorMessages));
            }
            
            $customers = [];
            foreach ($orderDetails as $orderDetail) {
                $entity = $this->OrderDetail->patchEntity(
                    $orderDetail,
                    [
                        'pickup_day' => $pickupDay
                    ]
                );
                $this->OrderDetail->save($entity);
                @$customers[$orderDetail->id_customer][] = $orderDetail;
            }
            
            foreach($customers as $orderDetails) {
                $email = new AppEmail();
                $email->viewBuilder()->setTemplate('Admin.order_detail_pickup_day_changed');
                $email->setTo($orderDetails[0]->customer->email)
                ->setSubject(__d('admin', 'The_pickup_day_of_your_order_was_changed_to').': ' . $newPickupDay)
                ->setViewVars([
                    'orderDetails' => $orderDetails,
                    'customer' => $orderDetails[0]->customer,
                    'appAuth' => $this->AppAuth,
                    'oldPickupDay' => $oldPickupDay,
                    'newPickupDay' => $newPickupDay,
                    'changePickupDayReason' => $changePickupDayReason
                ]);
                $email->send();
            }
            
            $message = __d('admin', 'The_pickup_day_of_{0,plural,=1{1_product} other{#_products}}_was_changed_successfully_to_{1}_and_{2,plural,=1{1_customer} other{#_customers}}_were_notified.', [count($orderDetailIds), '<b>'.$newPickupDay.'</b>', count($customers)]);
            $this->Flash->success($message);
            
            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('order_detail_pickup_day_changed', $this->AppAuth->getUserId(), 0, 'order_details', $message . ' Ids: ' . join(', ', $orderDetailIds));
            
            $this->set('data', [
                'result' => [],
                'status' => true,
                'msg' => 'ok'
            ]);
            
            $this->set('_serialize', 'data');
            
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }
        
    }
    
    public function editPickupDayComment()
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        $customerId = $this->getRequest()->getData('customerId');
        $pickupDay = $this->getRequest()->getData('pickupDay');
        $pickupDay = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);
        $pickupDayComment = htmlspecialchars_decode(strip_tags(trim($this->getRequest()->getData('pickupDayComment')), '<strong><b>'));
        
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'id_customer' => $customerId
            ]
        ])->first();
        
        $this->PickupDay = TableRegistry::getTableLocator()->get('PickupDays');
        $result = $this->PickupDay->insertOrUpdate(
            [
                'customer_id' => $customerId,
                'pickup_day' => $pickupDay
            ],
            [
                'comment' => $pickupDayComment
            ]
        );
        
        $this->Flash->success(__d('admin', 'The_comment_was_changed_successfully.'));
        
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('order_comment_changed', $this->AppAuth->getUserId(), $customerId, 'customers', __d('admin', 'The_pickup_day_comment_of_{0}_was_changed:', [$customer->name]) . ' <div class="changed">' . $pickupDayComment . ' </div>');
        
        $this->set('data', [
            'result' => $result,
            'status' => !empty($result),
            'msg' => 'ok'
        ]);
        
        $this->set('_serialize', 'data');
    }
    
    public function changeProductsPickedUp()
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        $customerIds = $this->getRequest()->getData('customerIds');
        $state = $this->getRequest()->getData('state');
        $pickupDay = $this->getRequest()->getData('pickupDay');
        $pickupDay = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);
        
        $this->PickupDay = TableRegistry::getTableLocator()->get('PickupDays');
        $this->PickupDay->setPrimaryKey(['customer_id', 'pickup_day']);
        
        foreach($customerIds as $customerId) {
            $result = $this->PickupDay->insertOrUpdate(
                [
                    'customer_id' => $customerId,
                    'pickup_day' => $pickupDay
                ],
                [
                    'products_picked_up' => $state
                ]
            );
        }
        
        $message = '';
        if (empty($result)) {
            $message = __d('admin', 'Errors_while_saving!');
        }
        
        $redirectUrl = '';
        if (preg_match('/customerId\='.$customerIds[0].'/', $this->referer())) {
            $redirectUrl = '/admin/order-details?pickupDay[]='.$this->getRequest()->getData('pickupDay').'&groupBy=customer';
        }
        
        $this->set('data', [
            'pickupDay' => $pickupDay,
            'result' => $result,
            'status' => !empty($result),
            'redirectUrl' => $redirectUrl,
            'msg' => $message
        ]);
        
        $this->set('_serialize', 'data');
        
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
                    'Customers',
                    'Products.StockAvailables',
                    'Products.Manufacturers',
                    'Products.Manufacturers.AddressManufacturers',
                    'ProductAttributes.StockAvailables',
                    'OrderDetailTaxes',
                    'TimebasedCurrencyOrderDetails',
                    'OrderDetailUnits'
                ]
            ])->first();

            $message = __d('admin', 'Product_{0}_from_manufacturer_{1}_with_a_price_of_{2}_ordered_on_{3}_was_successfully_cancelled.', [
                '<b>' . $orderDetail->product_name . '</b>',
                '<b>' . $orderDetail->product->manufacturer->name . '</b>',
                Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->total_price_tax_incl),
                $orderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'))
            ]);

            $this->OrderDetail->deleteOrderDetail($orderDetail);

            $newQuantity = $this->increaseQuantityForProduct($orderDetail, $orderDetail->product_amount * 2);

            // send email to customer
            $email = new AppEmail();
            $email->viewBuilder()->setTemplate('Admin.order_detail_deleted');
            $email->setTo($orderDetail->customer->email)
            ->setSubject(__d('admin', 'Product_was_cancelled').': ' . $orderDetail->product_name)
            ->setViewVars([
                'orderDetail' => $orderDetail,
                'appAuth' => $this->AppAuth,
                'cancellationReason' => $cancellationReason
            ]);

            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $orderDetail->customer->name . '</b>']);

            // never send email to manufacturer if bulk orders are allowed
            $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail->product->manufacturer->bulk_orders_allowed);
            $sendOrderedProductDeletedNotification = $this->Manufacturer->getOptionSendOrderedProductDeletedNotification($orderDetail->product->manufacturer->send_ordered_product_deleted_notification);

            if (! $this->AppAuth->isManufacturer() && $orderDetail->order_state == ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER && ! $bulkOrdersAllowed && $sendOrderedProductDeletedNotification) {
                $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                    '<b>' . $orderDetail->customer->name . '</b>',
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
                'Customers',
                'Products.StockAvailables',
                'Products.Manufacturers',
                'ProductAttributes.StockAvailables',
                'OrderDetailUnits'
            ]
        ])->first();

        return $newOrderDetail;
    }

    private function changeTimebasedCurrencyOrderDetailPrice($object, $oldOrderDetail, $newPrice, $amount)
    {
        if (!empty($object->timebased_currency_order_detail)) {
            $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
            $this->TimebasedCurrencyOrderDetail->changePrice($object, $newPrice, $amount);
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
