<?php

namespace App\Model\Table;

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
            $manufacturers[$orderDetail->order_detail->product->manufacturer->id_manufacturer] = $orderDetail->order_detail->product->manufacturer->name;
        }
        return $manufacturers;
    }
}
