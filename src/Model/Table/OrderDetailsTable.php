<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class OrderDetailsTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('order_detail');
        parent::initialize($config);
        $this->setPrimaryKey('id_order_detail');

        $this->belongsTo('Orders', [
            'foreignKey' => 'id_order'
        ]);
        $this->belongsTo('OrderDetailTaxes', [
            'foreignKey' => 'id_order_detail'
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
                $result[$deliveryDay] = __('Pick_up_day') . ' ' . $deliveryDay . ' - ' . count($orderDetails) . ' Produkt' . (count($orderDetails) == 1 ? '' : 'e');
                $foundOrders++;
            }
            
            $i++;
            
        }
        
        return $result;
        
    }
    
    public function getOrderDetailQueryForPeriodAndCustomerId($dateFrom, $dateTo, $customerId)
    {
        $conditions = [
            'Orders.id_customer' => $customerId,
            'RIGHT(Orders.date_add, 8) <> \'00:00:00\'' // exlude shop orders
        ];
        $conditions[] = 'Orders.current_state IN ('.ORDER_STATE_CASH_FREE.','.ORDER_STATE_CASH.','.ORDER_STATE_OPEN.')';
        
        $conditions[] = 'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate(
            date('Y-m-d', $dateFrom)
        ).'\'';
        
        $conditions[] = 'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate(
            date('Y-m-d', $dateTo)
        ).'\'';
        
        $orderDetails = $this->find('all', [
            'conditions' => $conditions,
            'order' => [
                'Orders.date_add' => 'DESC'
            ],
            'contain' => [
                'Orders',
                'Products.Manufacturers'
            ]
        ])->toArray();
        
        // manually remove products from bulk orders manufacturers
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $cleanedOrderDetails = [];
        foreach($orderDetails as $orderDetail) {
            $isBulkOrderManufacturer = $this->Manufacturer->getOptionBulkOrdersAllowed($orderDetail->product->manufacturer->bulk_orders_allowed);
            if (!$isBulkOrderManufacturer) {
                $cleanedOrderDetails[] = $orderDetail;
            }
        }
        
        return $cleanedOrderDetails;
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
            $sql .= ', DATE_FORMAT(o.date_add, \'%Y-%c\') as monthAndYear ';
        }
        $sql .= 'FROM '.$this->tablePrefix.'order_detail od ';
        $sql .= 'LEFT JOIN '.$this->tablePrefix.'orders o ON o.id_order = od.id_order ';
        $sql .= 'LEFT JOIN '.$this->tablePrefix.'product p ON p.id_product = od.product_id ';
        $sql .= 'WHERE p.id_manufacturer = :manufacturerId ';
        $sql .= 'AND DATE_FORMAT(o.date_add, \'%Y-%m-%d\') >= :depositForManufacturersStartDate ';
        if ($groupByMonth) {
            $sql .= 'GROUP BY monthAndYear ';
            $sql .= 'ORDER BY monthAndYear DESC;';
        } else {
            $sql .= 'ORDER BY o.date_add DESC;';
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
     * @param date $dateFrom
     * @param date $dateTo
     * @return float
     */
    public function getOpenOrderDetailSum($manufacturerId, $dateFrom, $dateTo)
    {
        $sql = 'SELECT SUM(od.total_price_tax_incl) as sumOrderDetail ';
        $sql .= 'FROM '.$this->tablePrefix.'order_detail od ';
        $sql .= 'LEFT JOIN '.$this->tablePrefix.'orders o ON o.id_order = od.id_order ';
        $sql .= 'LEFT JOIN '.$this->tablePrefix.'product p ON p.id_product = od.product_id ';
        $sql .= 'WHERE p.id_manufacturer = :manufacturerId ';
        $sql .= 'AND o.current_state = :orderStateOpen ';
        $sql .= 'AND DATE_FORMAT(o.date_add, \'%Y-%m-%d\') >= :dateFrom ';
        $sql .= 'AND DATE_FORMAT(o.date_add, \'%Y-%m-%d\') <= :dateTo ';
        $sql .= 'GROUP BY p.id_manufacturer ';
        $params = [
            'manufacturerId' => $manufacturerId,
            'orderStateOpen' => ORDER_STATE_OPEN,
            'dateFrom' => Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom),
            'dateTo' => Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo),
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

    public function getOrderDetailParams($appAuth, $manufacturerId, $productId, $customerId, $orderState, $dateFrom, $dateTo, $orderDetailId, $orderId, $deposit)
    {
        $conditions = [];

        if ($dateFrom != '') {
            $conditions[] = 'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        }
        if ($dateTo != '') {
            $conditions[] = 'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'';
        }

        if ($orderState != '') {
            $conditions[] = $this->Orders->getOrderStateCondition($orderState);
        }

        if ($productId != '') {
            $conditions['OrderDetails.product_id'] = $productId;
        }

        if ($orderDetailId != '') {
            $conditions['OrderDetails.id_order_detail'] = $orderDetailId;
        }

        if ($orderId != '') {
            $conditions['Orders.id_order'] = $orderId;
        }

        if ($deposit != '') {
            $conditions[] = 'OrderDetails.deposit > 0';
        }

        $contain = [
            'Orders',
            'Orders.Customers',
            'Products.Manufacturers.AddressManufacturers',
            'Products.ProductLangs',
            'TimebasedCurrencyOrderDetails',
            'OrderDetailUnits'
        ];
        
        if ($customerId != '') {
            $conditions['Orders.id_customer'] = $customerId;
        }

        if ($manufacturerId != '') {
            $conditions['Products.id_manufacturer'] = $manufacturerId;
        }

        // override params that manufacturer is not allowed to change
        if ($appAuth->isManufacturer()) {
            $conditions['Products.id_manufacturer'] = $appAuth->getManufacturerId();
            if ($customerId =! '') {
                unset($conditions['Orders.id_customer']);
            }
        }

        // customers are only allowed to see their own data
        if ($appAuth->isCustomer()) {
            $conditions['Orders.id_customer'] = $appAuth->getUserId();
        }

        $odParams = [
            'conditions' => $conditions,
            'contain' => $contain
        ];

        return $odParams;
    }
}
