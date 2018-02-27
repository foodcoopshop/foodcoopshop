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
        $this->setPrimaryKey('id_order');
    }
    
    public function validationCart(Validator $validator)
    {
        $validator->equals('cancellation_terms_accepted', 1, 'Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.');
        $validator->equals('general_terms_and_conditions_accepted', 1, 'Bitte akzeptiere die AGB.');
        $validator->allowEmpty('comment');
        $validator->maxLength('comment', 500, 'Bitte gib maximal 500 Zeichen ein.');
        return $validator;
    }

    public $states = [
        'actual' => 3,
        'paid' => 2,
        'closed' => 5
    ];

    public function getOrderStateCondition($orderStates)
    {
        if ($orderStates == '' || empty($orderStates) || empty($orderStates[0])) {
            return false;
        }
        if (!is_array($orderStates)) {
            $orderStates = [$orderStates];
        }
        $condition = 'Orders.current_state IN (' . join(', ', $orderStates) . ')';
        return $condition;
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

    public function getSumProduct($customerId)
    {
        $conditions = [
            'Orders.id_customer' => $customerId,
            'Orders.current_state IN (' . ORDER_STATE_CASH_FREE . ', ' . ORDER_STATE_OPEN . ')'
        ];

        $query = $this->find('all', [
            'conditions' => $conditions
        ]);
        $query->select(
            ['SumTotalPaid' => $query->func()->sum('Orders.total_paid')]
        );
        
        return $query->toArray()[0]['SumTotalPaid'];
    }

    public function getSumDeposit($customerId)
    {
        $conditions = [
            'Orders.id_customer' => $customerId,
            'Orders.current_state IN (' . ORDER_STATE_CASH_FREE . ', ' . ORDER_STATE_OPEN . ')',
            'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('app.depositPaymentCashlessStartDate') . '\''
        ];

        $query = $this->find('all', [
            'conditions' => $conditions
        ]);
        $query->select(
            ['SumTotalDeposit' => $query->func()->sum('Orders.total_deposit')]
        );
        
        return $query->toArray()[0]['SumTotalDeposit'];
    }

    public function getOrderParams($customerId, $orderStates, $dateFrom, $dateTo, $orderId, $appAuth)
    {
        $conditions = [];

        if ($dateFrom != '') {
            $conditions[] = 'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        }
        if ($dateTo != '') {
            $conditions[] = 'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'';
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

        $fields = [];
        $orderParams = [
            'conditions' => $conditions,
            'order' => Configure::read('app.htmlHelper')->getCustomerOrderBy(),
            'fields' => $fields,
            'contain' => ['Customers']
        ];
        return $orderParams;
    }

    public function recalculateOrderDetailPricesInOrder($order)
    {
        $orderId = $order->id_order;
        
        $query = $this->OrderDetails->find('all');
        $query->select(['sumPriceExcl' => $query->func()->sum('OrderDetails.total_price_tax_excl')]);
        $query->select(['sumPriceIncl' => $query->func()->sum('OrderDetails.total_price_tax_incl')]);
        $query->select(['sumDeposit' => $query->func()->sum('OrderDetails.deposit')]);
        
        $query->group('OrderDetails.id_order');
        $query->having(['OrderDetails.id_order' => $orderId]);
        
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

        // update table orders
        $this->save(
            $this->patchEntity($order, $order2update)
        );
    }
}
