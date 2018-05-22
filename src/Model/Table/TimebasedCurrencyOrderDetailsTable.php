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
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TimebasedCurrencyOrderDetailsTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('timebased_currency_order_detail');
        parent::initialize($config);
        $this->hasOne('OrderDetails', [
            'foreignKey' => 'id_order_detail'
        ]);
        $this->setPrimaryKey('id_order_detail');
    }
    
    /**
     * @param OrderDetail $orderDetail
     * @param float $price
     * @param int $amount
     */
    public function changePrice($orderDetail, $price, $amount)
    {
        
        $manufacturerTable = TableRegistry::getTableLocator()->get('Manufacturers');
        
        $maxPercentage = $orderDetail->timebased_currency_order_detail->max_percentage;
        $grossProductPricePerUnit = $price / (100 - $maxPercentage) * 100 / $amount;
        $netProductPricePerUnit = $this->OrderDetails->Products->getNetPrice($orderDetail->product_id, $grossProductPricePerUnit);
        
        $this->save(
            $this->patchEntity(
                $orderDetail->timebased_currency_order_detail,
                [
                    'money_incl' => round($manufacturerTable->getTimebasedCurrencyMoney($grossProductPricePerUnit, $maxPercentage), 2) * $amount,
                    'money_excl' => round($manufacturerTable->getTimebasedCurrencyMoney($netProductPricePerUnit, $maxPercentage), 2) * $amount,
                    'seconds' => $manufacturerTable->getCartTimebasedCurrencySeconds($grossProductPricePerUnit, $maxPercentage) * $amount
                ]
            )
        );
    }
    
    /**
     * @param int $manufacturerId
     * @return array
     */
    public function getUniqueCustomerIds($manufacturerId)
    {
        $customersFromOrderDetails = $this->find('all', [
            'conditions' => [
                'Products.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'OrderDetails.Products'
            ]
        ]);
        $result = [];
        foreach($customersFromOrderDetails as $customersFromOrderDetail) {
            $result[] = $customersFromOrderDetail->order_detail->order->id_customer;
        }
        $result = array_unique($result);
        return $result;
    }
    
    /**
     * @param array $results
     * @return array
     */
    public function addTimebasedCurrencyDataToInvoiceData($results)
    {
        $timebasedCurrencyAwareResults = [];
        
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        
        foreach($results as $result) {
            $timebasedCurrencyAwareResult = $result;
            $timebasedCurrencyOrderDetail = $this->find('all', [
                'conditions' => [
                    'TimebasedCurrencyOrderDetails.id_order_detail' => $result['OrderDetailId']
                ]
            ])->first();
            if (!empty($timebasedCurrencyOrderDetail)) {
                $timebasedCurrencyAwareResult['OrderDetailPriceExcl'] = $result['OrderDetailPriceExcl'] + $timebasedCurrencyOrderDetail->money_excl;
                $timebasedCurrencyAwareResult['OrderDetailPriceIncl'] = $result['OrderDetailPriceIncl'] + $timebasedCurrencyOrderDetail->money_incl;
                $timebasedCurrencyAwareResult['OrderDetailTaxAmount'] = $this->Product->getUnitTax($timebasedCurrencyAwareResult['OrderDetailPriceIncl'], $timebasedCurrencyAwareResult['OrderDetailPriceExcl'] / $result['OrderDetailAmount'], $result['OrderDetailAmount']) * $result['OrderDetailAmount']; 
                $timebasedCurrencyAwareResult['OrderDetailTimebasedCurrencyPriceInclAmount'] = $timebasedCurrencyOrderDetail->money_incl;
                $timebasedCurrencyAwareResult['HasTimebasedCurrency'] = true;
            }
            $timebasedCurrencyAwareResult['HasTimebasedCurrency'] = false;
            $timebasedCurrencyAwareResults[] = $timebasedCurrencyAwareResult;
        }
        return $timebasedCurrencyAwareResults;
    }
    
    /**
     * @param int $customerId
     * @return array manufacturers where $customerId has ordered with timebased currency
     */
    public function getManufacturersForDropdown($customerId)
    {
        $query = $this->find('all', [
            'conditions' => [
                'Orders.id_customer' => $customerId
            ],
            'contain' => [
                'OrderDetails.Orders',
                'OrderDetails.Products.Manufacturers'
            ]
        ]);
        
        $manufacturers = [];
        foreach($query as $orderDetail) {
            $manufacturers[$orderDetail->order_detail->product->id_manufacturer] = $orderDetail->order_detail->product->manufacturer->name;
        }
        return $manufacturers;
    }
    
    /**
     * @param int $manufacturerId
     * @param int $customerId
     * @return \Cake\ORM\Query
     */
    private function getFilteredQuery($manufacturerId, $customerId)
    {
        if ($manufacturerId) {
            $productsAssociation = $this->getAssociation('OrderDetails')->getAssociation('Products');
            $productsAssociation->setJoinType('INNER'); // necessary to apply condition
            $productsAssociation->setConditions([
                'Products.id_manufacturer' => $manufacturerId
            ]);
        }
        
        $conditions = [];
        $conditions[] = $this->OrderDetails->Orders->getOrderStateCondition(Configure::read('app.htmlHelper')->getOrderStateIds());
        
        if ($customerId) {
            $conditions['Orders.id_customer'] = $customerId;
        }
        
        $query = $this->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'OrderDetails.Orders',
                'OrderDetails.Products'
            ]
        ]);
        return $query;
    }
    
    /**
     * @param int $manufacturerId
     * @param int $customerId
     * @return array
     */
    public function getOrders($manufacturerId = null, $customerId = null)
    {
        $query = $this->getFilteredQuery($manufacturerId, $customerId);
        $orders = [];
        // having is not attachable to associations, so sum up and prepare result manually
        foreach($query as $orderDetail) {
            @$orders[$orderDetail->order_detail->id_order]['SumSeconds'] += $orderDetail->seconds;
            @$orders[$orderDetail->order_detail->id_order]['order'] = $orderDetail->order_detail->order;
        }
        return $orders;
    }
    
    /**
     * @param int $manufacturerId
     * @param int $customerId
     * @return float
     */
    public function getCreditBalance($manufacturerId = null, $customerId = null)
    {
        $timebasedCurrencyPayment = TableRegistry::getTableLocator()->get('TimebasedCurrencyPayments');
        $creditBalance = $this->getSum($manufacturerId, $customerId) - $timebasedCurrencyPayment->getSum($manufacturerId, $customerId);
        return $creditBalance;
    }
    
    /**
     * @param int $manufacturerId
     * @param int $customerId
     * @return int
     */
    public function getSum($manufacturerId = null, $customerId = null)
    {
        $query = $this->getFilteredQuery($manufacturerId, $customerId);
        $query->select(
            ['SumSeconds' => $query->func()->sum('TimebasedCurrencyOrderDetails.seconds')]
        );
        
        return $query->toArray()[0]['SumSeconds'];
    }
    
}
