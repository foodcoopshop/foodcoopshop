<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class OrderDetailsTable extends AppTable
{

    public function initialize(array $config): void
    {
        $this->setTable('order_detail');
        parent::initialize($config);
        $this->setPrimaryKey('id_order_detail');

        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->hasOne('OrderDetailTaxes', [
            'foreignKey' => 'id_order_detail'
        ]);
        $this->belongsTo('PickupDayEntities', [
            'className' => 'PickupDays', // field has same name and would clash
            'foreignKey' => [
                'id_customer'
            ]
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'type' => 'INNER'
        ]);
        $this->belongsTo('ProductAttributes', [
            'foreignKey' => 'product_attribute_id'
        ]);
        $this->hasOne('TimebasedCurrencyOrderDetails', [
            'foreignKey' => 'id_order_detail'
        ]);
        $this->hasOne('OrderDetailUnits', [
            'foreignKey' => 'id_order_detail'
        ]);
        $this->belongsTo('CartProducts', [
            'foreignKey' => 'id_cart_product'
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationPickupDay(Validator $validator)
    {
        $validator->notEquals('pickup_day', '1970-01-01', __('The_pickup_day_is_not_valid.'));
        $validator = $this->getAllowOnlyOneWeekdayValidator($validator, 'pickup_day', __('The_pickup_day'));
        return $validator;
    }

    public function getOrderDetailsForOrderListPreview($pickupDay)
    {
        $query = $this->find('all', [
            'conditions' => [
                'OrderDetails.pickup_day = \'' . $pickupDay . '\'',
            ],
            'contain' => [
                'Products'
            ]
        ]);
        return $query;
    }

    public function getOrderDetailsForSendingOrderLists($pickupDay, $cronjobRunDay)
    {
        $cronjobRunDayWeekday = date('w', strtotime($cronjobRunDay));
        $query = $this->find('all', [
            'contain' => [
                'Products',
                'Products.StockAvailables',
                'ProductAttributes.StockAvailables'
            ]
        ]);
        $query->where(['OrderDetails.order_state' => ORDER_STATE_ORDER_PLACED]);
        $query->where(function ($exp, $query) use ($cronjobRunDayWeekday, $cronjobRunDay, $pickupDay) {
            return $exp->or_([
                '(Products.delivery_rhythm_type <> "individual" AND Products.delivery_rhythm_send_order_list_weekday = ' . $cronjobRunDayWeekday . ')
                 AND OrderDetails.pickup_day = "' . $pickupDay . '"',
                '(Products.delivery_rhythm_type = "individual" AND Products.delivery_rhythm_send_order_list_day = "' . $cronjobRunDay . '" AND OrderDetails.pickup_day = Products.delivery_rhythm_first_delivery_day)'
            ]);
        });
        return $query;
    }

    /**
     * @param int $customerId
     * @return array
     */
    public function getLastOrderDetailsForDropdown($customerId)
    {

        $ordersToLoad = 3;

        $foundOrders = 0;
        $result = [];

        $i = 0;
        while($foundOrders < $ordersToLoad) {

            $dateFrom = strtotime('- '.$i * 7 . 'day', strtotime(Configure::read('app.timeHelper')->getOrderPeriodFirstDay(Configure::read('app.timeHelper')->getCurrentDay())));
            $dateTo = strtotime('- '.$i * 7 . 'day', strtotime(Configure::read('app.timeHelper')->getOrderPeriodLastDay(Configure::read('app.timeHelper')->getCurrentDay())));

            // stop trying to search for valid orders if year is 2013
            if (date('Y', $dateFrom) == '2013') {
                break;
            }

            $orderDetails = $this->getOrderDetailQueryForPeriodAndCustomerId($dateFrom, $dateTo, $customerId);

            if (count($orderDetails) > 0) {
                $deliveryDay = Configure::read('app.timeHelper')->formatToDateShort(date('Y-m-d', Configure::read('app.timeHelper')->getDeliveryDay($dateTo)));
                $result[$deliveryDay] = __('Pickup_day') . ' ' . $deliveryDay . ' - ' . __('{0,plural,=1{1_product} other{#_products}}', [count($orderDetails)]);
                $foundOrders++;
            }

            $i++;

        }

        return $result;

    }

    public function getGroupedFutureOrdersByCustomerId($customerId)
    {
        $query = $this->find('all', [
            'fields' => ['OrderDetails.pickup_day'],
            'conditions' => [
                'OrderDetails.id_customer' => $customerId,
                'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\') > DATE_FORMAT(NOW(), \'%Y-%m-%d\')'
            ],
            'order' => [
                'OrderDetails.pickup_day' => 'ASC'
            ]
        ]);
        $query->select(
            ['orderDetailsCount' => $query->func()->count('OrderDetails.pickup_day')]
        );
        $query->group('OrderDetails.pickup_day');
        return $query->toArray();
    }

    public function updateOrderState($dateFrom, $dateTo, $oldOrderStates, $newOrderState, $manufacturerId, $orderDetailIds = [])
    {

        // update with condition on association does not work with ->update or ->updateAll
        $orderDetails = $this->find('all', [
            'conditions' => [
                'Products.id_manufacturer' => $manufacturerId,
                'OrderDetails.order_state IN (' . join(', ', $oldOrderStates) . ')'
            ],
            'contain' => [
                'Products'
            ]
        ]);

        if (!empty($orderDetailIds)) {
            $orderDetails->where(['OrderDetails.id_order_detail IN (' . join(', ', $orderDetailIds) . ')']);
        } else {
            $orderDetails->where([
                'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'',
                'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\''
            ]);
        }

        foreach($orderDetails as $orderDetail) {
            $this->save(
                $this->patchEntity(
                    $orderDetail,
                    [
                        'order_state' => $newOrderState
                    ]
                )
            );
        }

    }

    public function getOrderDetailQueryForPeriodAndCustomerId($dateFrom, $dateTo, $customerId)
    {
        $cartsAssociation = $this->getAssociation('CartProducts')->getAssociation('Carts');
        $cartsAssociation->setJoinType('INNER');
        $cartsAssociation->setConditions([
            'Carts.cart_type' => CartsTable::CART_TYPE_WEEKLY_RHYTHM,
            'Carts.status' => APP_OFF
        ]);
        $conditions = [
            'OrderDetails.id_customer' => $customerId,
        ];

        $conditions[] = 'DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate(
            date('Y-m-d', $dateFrom)
        ).'\'';

        $conditions[] = 'DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate(
            date('Y-m-d', $dateTo)
        ).'\'';

        $orderDetails = $this->find('all', [
            'conditions' => $conditions,
            'order' => [
                'OrderDetails.created' => 'DESC'
            ],
            'contain' => [
                'CartProducts.Carts',
                'Products.Manufacturers'
            ]
        ])->toArray();

        return $orderDetails;
    }

    public function deleteOrderDetail($orderDetail)
    {
        $this->delete($orderDetail);

        if (!empty($orderDetail->order_detail_tax)) {
            $this->OrderDetailTaxes->delete($orderDetail->order_detail_tax);
        }

        if (!empty($orderDetail->timebased_currency_order_detail)) {
            $this->TimebasedCurrencyOrderDetails->delete($orderDetail->timebased_currency_order_detail);
        }

        if (!empty($orderDetail->order_detail_unit)) {
            $this->OrderDetailUnits->delete($orderDetail->order_detail_unit);
        }

    }

    /**
     * @param int $manufacturerId
     * @param boolean $groupByMonth
     * @return array
     */
    public function getDepositSum($manufacturerId, $groupByMonth)
    {

        $sql =  'SELECT SUM(od.deposit) as sumDepositDelivered ';
        if ($groupByMonth) {
            $sql .= ', DATE_FORMAT(od.pickup_day, \'%Y-%c\') as monthAndYear ';
        }
        $sql .= 'FROM '.$this->tablePrefix.'order_detail od ';
        $sql .= 'LEFT JOIN '.$this->tablePrefix.'product p ON p.id_product = od.product_id ';
        $sql .= 'WHERE p.id_manufacturer = :manufacturerId ';
        $sql .= 'AND DATE_FORMAT(od.pickup_day, \'%Y-%m-%d\') >= :depositForManufacturersStartDate ';
        if ($groupByMonth) {
            $sql .= 'GROUP BY monthAndYear ';
            $sql .= 'ORDER BY monthAndYear DESC;';
        } else {
            $sql .= 'ORDER BY od.pickup_day DESC;';
        }
        $params = [
            'manufacturerId' => $manufacturerId,
            'depositForManufacturersStartDate' => Configure::read('app.depositForManufacturersStartDate')
        ];

        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $orderDetails = $statement->fetchAll('assoc');

        return $orderDetails;
    }

    /**
     * @param int $manufacturerId
     * @param $dateFrom
     * @param $dateTo
     * @return float
     */
    public function getOpenOrderDetailSum($manufacturerId, $dateFrom)
    {
        $sql = 'SELECT SUM(od.total_price_tax_incl) as sumOrderDetail ';
        $sql .= 'FROM '.$this->tablePrefix.'order_detail od ';
        $sql .= 'LEFT JOIN '.$this->tablePrefix.'product p ON p.id_product = od.product_id ';
        $sql .= 'WHERE p.id_manufacturer = :manufacturerId ';
        $sql .= 'AND od.order_state NOT IN (' . ORDER_STATE_BILLED_CASHLESS.',' . ORDER_STATE_BILLED_CASH . ') ';
        $sql .= 'AND DATE_FORMAT(od.pickup_day, \'%Y-%m-%d\') = :dateFrom ';
        $sql .= 'GROUP BY p.id_manufacturer ';
        $params = [
            'manufacturerId' => $manufacturerId,
            'dateFrom' => Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom)
        ];

        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $orderDetails = $statement->fetchAll('assoc');

        if (isset($orderDetails[0])) {
            return $orderDetails[0]['sumOrderDetail'];
        } else {
            return 0;
        }
    }

    private function prepareSumProduct($customerId)
    {
        $conditions = [
            'OrderDetails.id_customer' => $customerId,
            'OrderDetails.order_state IN (' . join(',', Configure::read('app.htmlHelper')->getOrderStatesCashless()) . ')'
        ];
        $query = $this->find('all', [
            'conditions' => $conditions
        ]);

        return $query;
    }

    public function getCountByCustomerId($customerId)
    {
        $conditions = [
            'OrderDetails.id_customer' => $customerId
        ];
        $query = $this->find('all', [
            'conditions' => $conditions
        ]);
        return $query->count();
    }

    public function getMonthlySumProductByCustomer($customerId)
    {
        $query = $this->prepareSumProduct($customerId);
        $query->contain('TimebasedCurrencyOrderDetails');
        $query->group('MonthAndYear');
        $query->select([
            'SumTotalPaid' => $query->func()->sum('OrderDetails.total_price_tax_incl'),
            'SumDeposit' => $query->func()->sum('OrderDetails.deposit'),
            'SumTimebasedCurrencySeconds' => $query->func()->sum('TimebasedCurrencyOrderDetails.seconds'),
            'MonthAndYear' => 'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%c\')'
        ]);
        return $query->toArray();
    }

    public function getMonthlySumProductByManufacturer($manufacturerId, $year)
    {
        $conditions = [];
        if ($manufacturerId != 'all') {
            $conditions['Products.id_manufacturer'] = $manufacturerId;
        } else {
            // do not show any non-associated products that might be found in database
            $conditions[] = 'Products.id_manufacturer > 0';
        }
        if ($year != '') {
            $conditions[] = 'DATE_FORMAT(OrderDetails.pickup_day, \'%Y\') = ' . $year;
        }

        $query = $this->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Products'
            ]
        ]);
        $query->group('MonthAndYear');
        $query->select([
            'SumTotalPaid' => $query->func()->sum('OrderDetails.total_price_tax_incl'),
            'MonthAndYear' => 'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%c\')'
        ]);
        return $query;
    }

    public function getSumProduct($customerId)
    {
        $query = $this->prepareSumProduct($customerId);
        $query->select(
            ['SumTotalPaid' => $query->func()->sum('OrderDetails.total_price_tax_incl')]
        );
        return $query->toArray()[0]['SumTotalPaid'];
    }

    public function getSumDeposit($customerId)
    {
        $conditions = [
            'OrderDetails.id_customer' => $customerId,
            $this->getOrderStateCondition(Configure::read('app.htmlHelper')->getOrderStatesCashless()),
            'DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\') >= \'' . Configure::read('app.depositPaymentCashlessStartDate') . '\''
        ];

        $query = $this->find('all', [
            'conditions' => $conditions
        ]);
        $query->select(
            ['SumTotalDeposit' => $query->func()->sum('OrderDetails.deposit')]
        );

        return $query->toArray()[0]['SumTotalDeposit'];
    }

    public function getOrderStateCondition($orderStates)
    {
        if ($orderStates == '' || empty($orderStates) || empty($orderStates[0])) {
            return false;
        }
        if (!is_array($orderStates)) {
            $orderStates = [$orderStates];
        }
        $condition = 'OrderDetails.order_state IN (' . join(', ', $orderStates) . ')';
        return $condition;
    }

    public function getVariableMemberFeeReducedPrice($price, $variableMemberFee)
    {
        return $price * (100 - $variableMemberFee) / 100;
    }

    public function prepareOrderDetailsGroupedByProduct($orderDetails)
    {
        $preparedOrderDetails = [];
        foreach ($orderDetails as $orderDetail) {
            $key = $orderDetail->product_id;
            $preparedOrderDetails[$key]['sum_price'] = $orderDetail->sum_price;
            $preparedOrderDetails[$key]['sum_amount'] = $orderDetail->sum_amount;
            $preparedOrderDetails[$key]['sum_deposit'] = $orderDetail->sum_deposit;
            $preparedOrderDetails[$key]['product_id'] = $key;
            $preparedOrderDetails[$key]['name'] = $orderDetail->product->name;
            $preparedOrderDetails[$key]['manufacturer_id'] = $orderDetail->product->id_manufacturer;
            $preparedOrderDetails[$key]['manufacturer_name'] = $orderDetail->product->manufacturer->name;
            @$preparedOrderDetails[$key]['timebased_currency_order_detail_seconds_sum'] = $orderDetail->timebased_currency_order_detail_seconds_sum;
        }
        return $preparedOrderDetails;
    }

    public function prepareOrderDetailsGroupedByManufacturer($orderDetails)
    {
        $preparedOrderDetails = [];
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        foreach ($orderDetails as $orderDetail) {
            $key = $orderDetail->product->id_manufacturer;
            $preparedOrderDetails[$key]['sum_price'] = $orderDetail->sum_price;
            $preparedOrderDetails[$key]['sum_amount'] = $orderDetail->sum_amount;
            $preparedOrderDetails[$key]['sum_deposit'] = $orderDetail->sum_deposit;
            $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($orderDetail->product->manufacturer->variable_member_fee);
            $preparedOrderDetails[$key]['variable_member_fee'] = $variableMemberFee;
            $preparedOrderDetails[$key]['manufacturer_id'] = $key;
            $preparedOrderDetails[$key]['name'] = $orderDetail->product->manufacturer->name;
            @$preparedOrderDetails[$key]['timebased_currency_order_detail_seconds_sum'] = $orderDetail->timebased_currency_order_detail_seconds_sum;
        }

        foreach($preparedOrderDetails as &$pod) {
            $pod['reduced_price'] = $this->getVariableMemberFeeReducedPrice($pod['sum_price'], $pod['variable_member_fee']);
        }

        return $preparedOrderDetails;
    }

    /**
     * $param $orderDetails is already grouped!
     * @return array|boolean
     */
    public function prepareOrderDetailsGroupedByCustomer($orderDetails)
    {
        $preparedOrderDetails = [];
        foreach ($orderDetails as $orderDetail) {
            $key = $orderDetail->id_customer;
            $preparedOrderDetails[$key]['sum_price'] = $orderDetail->sum_price;
            $preparedOrderDetails[$key]['sum_amount'] = $orderDetail->sum_amount;
            $preparedOrderDetails[$key]['sum_deposit'] = $orderDetail->sum_deposit;
            $preparedOrderDetails[$key]['order_detail_count'] = $orderDetail->order_detail_count;
            $preparedOrderDetails[$key]['customer_id'] = $key;
            $preparedOrderDetails[$key]['name'] = Configure::read('app.htmlHelper')->getNameRespectingIsDeleted($orderDetail->customer);
            $preparedOrderDetails[$key]['email'] = '';
            if ($orderDetail->customer) {
                $preparedOrderDetails[$key]['email'] = $orderDetail->customer->email;
            }
            $productsPickedUp = false;
            if (!empty($orderDetail->pickup_day_entity)) {
                $preparedOrderDetails[$key]['comment'] = $orderDetail->pickup_day_entity->comment;
                $preparedOrderDetails[$key]['products_picked_up_tmp'] = $orderDetail->pickup_day_entity->products_picked_up;
            }
            @$preparedOrderDetails[$key]['timebased_currency_order_detail_seconds_sum'] = $orderDetail->timebased_currency_order_detail_seconds_sum;
            if (isset($preparedOrderDetails[$key]['products_picked_up_tmp']) && $preparedOrderDetails[$key]['products_picked_up_tmp']) {
                $productsPickedUp = true;
                $preparedOrderDetails[$key]['row_class'] = ['selected'];
            }
            $preparedOrderDetails[$key]['products_picked_up'] = $productsPickedUp;
            unset($preparedOrderDetails[$key]['products_picked_up_tmp']);
        }

        foreach($preparedOrderDetails as &$orderDetail) {
            $orderDetail['order_detail_count'] = $this->getCountByCustomerId($orderDetail['customer_id']);
        }
        return $preparedOrderDetails;
    }

    public function getOrderDetailParams($appAuth, $manufacturerId, $productId, $customerId, $pickupDay, $orderDetailId, $deposit)
    {
        $conditions = [];

        if (count($pickupDay) == 2) {
            $conditions[] = 'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[0]) . '\'';
            $conditions[] = 'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[1]) . '\'';
        } else {
            $conditions[] = 'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\') = \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[0]) . '\'';
        }

        if ($productId != '') {
            $conditions['OrderDetails.product_id'] = $productId;
        }

        if ($orderDetailId != '') {
            $conditions['OrderDetails.id_order_detail'] = $orderDetailId;
        }

        if ($deposit != '') {
            $conditions[] = 'OrderDetails.deposit > 0';
        }

        $contain = [
            'Customers',
            'Products.Manufacturers.AddressManufacturers',
            'TimebasedCurrencyOrderDetails',
            'OrderDetailUnits'
        ];

        if ($customerId != '') {
            $conditions['OrderDetails.id_customer'] = $customerId;
        }

        if ($manufacturerId != '') {
            $conditions['Products.id_manufacturer'] = $manufacturerId;
        }

        // override params that manufacturer is not allowed to change
        if ($appAuth->isManufacturer()) {
            $conditions['Products.id_manufacturer'] = $appAuth->getManufacturerId();
            if ($customerId =! '') {
                unset($conditions['OrderDetails.id_customer']);
            }
        }

        // customers are only allowed to see their own data
        if ($appAuth->isCustomer()) {
            $conditions['OrderDetails.id_customer'] = $appAuth->getUserId();
        }

        $odParams = [
            'conditions' => $conditions,
            'contain' => $contain
        ];

        return $odParams;
    }
}
