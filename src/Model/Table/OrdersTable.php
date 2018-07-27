<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Validation\Validator;

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
class OrdersTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->hasMany('OrderDetails', [
            'foreignKey' => 'id_order'
        ]);
        $this->hasOne('TimebasedCurrencyOrders', [
            'foreignKey' => 'id_order'
        ]);
        $this->setPrimaryKey('id_order');
    }

    public function getCountByCustomerId($customerId)
    {
        $conditions = [
            'id_customer' => $customerId
        ];
        $conditions[] = $this->getOrderStateCondition(Configure::read('app.htmlHelper')->getOrderStateIds());
        $orderCount = $this->find('all', [
            'conditions' => $conditions
        ])->count();
        return $orderCount;
    }

    public function getOrderParams($customerId, $orderStates, $dateFrom, $dateTo, $orderId, $appAuth)
    {
        $conditions = [];

        if ($dateFrom != '') {
            $conditions[] = 'DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        }
        if ($dateTo != '') {
            $conditions[] = 'DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'';
        }

        $group = [];

        if ($orderStates != '') {
            $conditions[] = $this->getOrderStateCondition($orderStates);
        }

        if ($customerId != '') {
            $conditions['Customers.id_customer'] = $customerId;
        }

        // customers are only allowed to see their own data
        if ($appAuth->isCustomer()) {
            $conditions['Customers.id_customer'] = $appAuth->getUserId();
        }

        if ($orderId != '') {
            $conditions['Orders.id_order'] = $orderId;
        }

        $contain = ['Customers', 'TimebasedCurrencyOrders'];

        $fields = [];
        $orderParams = [
            'conditions' => $conditions,
            'order' => Configure::read('app.htmlHelper')->getCustomerOrderBy(),
            'fields' => $fields,
            'contain' => $contain
        ];
        return $orderParams;
    }

    public function updateSums($order)
    {

        $query = $this->OrderDetails->find('all');
        $query->select(['sumPriceExcl' => $query->func()->sum('OrderDetails.total_price_tax_excl')]);
        $query->select(['sumPriceIncl' => $query->func()->sum('OrderDetails.total_price_tax_incl')]);
        $query->select(['sumDeposit' => $query->func()->sum('OrderDetails.deposit')]);

        $query->group('OrderDetails.id_order');
        $query->having(['OrderDetails.id_order' => $order->id_order]);

        $orderDetails = $query->first();

        // if last order_detail was deleted, $orderDetails is empty => avoid notices
        if (empty($orderDetails)) {
            $sumPriceIncl = 0;
            $sumPriceExcl = 0;
            $sumDeposit = 0;
        } else {
            $sumPriceIncl = $orderDetails['sumPriceIncl'];
            $sumPriceExcl = $orderDetails['sumPriceExcl'];
            $sumDeposit = $orderDetails['sumDeposit'];
        }

        $order2update = [
            'total_paid' => $sumPriceIncl,
            'total_paid_tax_incl' => $sumPriceIncl,
            'total_paid_tax_excl' => $sumPriceExcl,
            'total_deposit' => $sumDeposit
        ];

        $this->save(
            $this->patchEntity($order, $order2update)
        );
    }
}
