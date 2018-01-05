<?php
/**
 * OrderDetail
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
class OrderDetail extends AppModel
{

    public $useTable = 'order_detail';

    public $primaryKey = 'id_order_detail';

    public $actsAs = array(
        'Containable'
    );

    public $belongsTo = array(
        'Order' => array(
            'foreignKey' => 'id_order'
        ),
        'OrderDetailTax' => array(
            'foreignKey' => 'id_order_detail'
        ),
        'Product' => array(
            'foreignKey' => 'product_id', // !sic, id_ vertauscht
            'type' => 'INNER'
        ) // for manufacturer name filter
    ,
        'ProductAttribute' => array(
            'foreignKey' => 'product_attribute_id'
        )
    );

    public function deleteOrderDetail($orderDetailId)
    {
        $this->delete($orderDetailId, false);
        $this->OrderDetailTax->delete($orderDetailId, false);
    }

    private function getOrderStateCondition($orderState)
    {
        $orderStates = explode(',', $orderState);
        $condition = 'Order.current_state IN (' . join(', ', $orderStates) . ')';
        return $condition;
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
        $params = array(
            'manufacturerId' => $manufacturerId,
            'depositForManufacturersStartDate' => Configure::read('app.depositForManufacturersStartDate')
        );
        $orderDetails = $this->getDataSource()->fetchAll($sql, $params);
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
        $sql .= 'AND DATE_FORMAT(o.date_add, \'%d.%m.%Y\') <= :dateTo ';
        $sql .= 'GROUP BY p.id_manufacturer ';
        $params = array(
            'manufacturerId' => $manufacturerId,
            'orderStateOpen' => ORDER_STATE_OPEN,
            'dateFrom' => Configure::read('timeHelper')->formatToDbFormatDate($dateFrom),
            'dateTo' => Configure::read('timeHelper')->formatToDbFormatDate($dateTo),
        );
        $orderDetails = $this->getDataSource()->fetchAll($sql, $params);
        if (isset($orderDetails[0])) {
            return $orderDetails[0][0]['sumOrderDetail'];
        } else {
            return 0;
        }
    }

    public function getOrderDetailParams($appAuth, $manufacturerId, $productId, $customerId, $orderState, $dateFrom, $dateTo, $orderDetailId, $orderId, $deposit)
    {
        $conditions = array();

        if ($dateFrom != '') {
            $conditions[] = 'DATE_FORMAT(Order.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        }
        if ($dateTo != '') {
            $conditions[] = 'DATE_FORMAT(Order.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('timeHelper')->formatToDbFormatDate($dateTo) . '\'';
        }

        if ($orderState != '') {
            $conditions[] = $this->getOrderStateCondition($orderState);
        }

        if ($productId != '') {
            $conditions['OrderDetail.product_id'] = $productId;
        }

        if ($orderDetailId != '') {
            $conditions['OrderDetail.id_order_detail'] = $orderDetailId;
        }

        if ($orderId != '') {
            $conditions['Order.id_order'] = $orderId;
        }

        if ($deposit != '') {
            $conditions[] = 'OrderDetail.deposit > 0';
        }

        $contain = array(
            'Order',
            'Order.Customer',
            'Product.Manufacturer.Address',
            'Product.ProductLang'
        );

        if ($customerId != '') {
            $conditions['Order.id_customer'] = $customerId;
        }

        if ($manufacturerId != '') {
            $conditions['Product.id_manufacturer'] = $manufacturerId;
        }

        // override params that manufacturer is not allowed to change
        if ($appAuth->isManufacturer()) {
            $conditions['Product.id_manufacturer'] = $appAuth->getManufacturerId();
            if ($customerId =! '') {
                unset($conditions['Order.id_customer']);
            }
        }

        // customers are only allowed to see their own data
        if ($appAuth->isCustomer()) {
            $conditions['Order.id_customer'] = $appAuth->getUserId();
        }

        $odParams = array(
            'conditions' => $conditions,
            'contain' => $contain
        );

        return $odParams;
    }
}
