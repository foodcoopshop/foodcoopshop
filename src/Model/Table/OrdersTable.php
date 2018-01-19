<?php

namespace App\Model\Table;
use Cake\Core\Configure;

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

    public $primaryKey = 'id_order';

    public $actsAs = [
        'Content'
    ];

    public $states = [
        'actual' => 3,
        'paid' => 2,
        'closed' => 5
    ];

    public $belongsTo = [
        'Customers' => [
            'foreignKey' => 'id_customer'
        ]
    ];

    public $hasMany = [
        'OrderDetails' => [
            'className' => 'OrderDetails',
            'foreignKey' => 'id_order'
        ]
    ];

    private function getOrderStateCondition($orderState)
    {
        $orderStates = explode(',', $orderState);
        $condition = 'Orders.current_state IN (' . join(', ', $orderStates) . ')';
        return $condition;
    }

    public function getCountByCustomerId($customerId)
    {
        $conditions = [
            $this->name . '.id_customer' => $customerId
        ];
        $conditions[] = $this->getOrderStateCondition(Configure::read('AppConfig.htmlHelper')->getOrderStateIdsAsCsv());
        $orderCount = $this->find('count', [
            'conditions' => $conditions
        ]);
        return $orderCount;
    }

    public function getSumProduct($customerId)
    {
        $conditions = [
            'Orders.id_customer' => $customerId,
            'Orders.current_state IN (' . ORDER_STATE_CASH_FREE . ', ' . ORDER_STATE_OPEN . ')'
        ];

        $ordersSum = $this->find('all', [
            'fields' => [
                'SUM(total_paid) as SumTotalPaid'
            ],
            'conditions' => $conditions
        ]);

        return $ordersSum[0][0]['SumTotalPaid'];
    }

    public function getSumDeposit($customerId)
    {
        $conditions = [
            'Orders.id_customer' => $customerId,
            'Orders.current_state IN (' . ORDER_STATE_CASH_FREE . ', ' . ORDER_STATE_OPEN . ')',
            'DATE_FORMAT(Order.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('AppConfig.depositPaymentCashlessStartDate') . '\''
        ];

        $ordersSum = $this->find('all', [
            'fields' => [
                'SUM(total_deposit) as SumTotalDeposit'
            ],
            'conditions' => $conditions
        ]);

        return $ordersSum[0][0]['SumTotalDeposit'];
    }

    public function getOrderParams($customerId, $orderState, $dateFrom, $dateTo, $groupByCustomer, $orderId, $appAuth)
    {
        $conditions = [];

        if ($dateFrom != '') {
            $conditions[] = 'DATE_FORMAT(Order.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('AppConfig.timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        }
        if ($dateTo != '') {
            $conditions[] = 'DATE_FORMAT(Order.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('AppConfig.timeHelper')->formatToDbFormatDate($dateTo) . '\'';
        }

        $group = [];

        if ($orderState != '') {
            $conditions[] = $this->getOrderStateCondition($orderState);
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

        $fields = [
            'Orders.*',
            'Customers.*'
        ];
        $contain = [
            'OrderDetails'
        ];
        if ($groupByCustomer) {
            $fields[] = 'SUM(Order.total_paid) AS Order_total_paid';
            $fields[] = 'COUNT(Order.total_paid) AS Order_count';
            $fields[] = 'SUM(Order.total_deposit) AS Order_total_deposit';
            $group[] = 'Customers.id_customer';
        }

        $orderParams = [
            'conditions' => $conditions,
            'order' => Configure::read('AppConfig.htmlHelper')->getCustomerOrderBy(),
            'fields' => $fields,
            'group' => $group,
            'contain' => $contain
        ];
        return $orderParams;
    }

    public function recalculateOrderDetailPricesInOrder($order)
    {
        $orderId = $order['OrderDetails']['id_order'];

        // get new sums
        $this->OrderDetails->recursive = - 1;
        $orderDetails = $this->OrderDetails->find('first', [
            'fields' => [
                'SUM(OrderDetails.total_price_tax_excl) AS sumPriceExcl',
                'SUM(OrderDetails.total_price_tax_incl) AS sumPriceIncl',
                'SUM(OrderDetails.deposit) AS sumDeposit'
            ],
            'group' => 'OrderDetails.id_order HAVING OrderDetails.id_order = ' . $orderId
        ]);

        // if last order_detail was deleted, $orderDetails is empty => avoid notices
        if (empty($orderDetails)) {
            $sumPriceIncl = 0;
            $sumPriceExcl = 0;
            $sumDeposit = 0;
        } else {
            $sumPriceIncl = $orderDetails[0]['sumPriceIncl'];
            $sumPriceExcl = $orderDetails[0]['sumPriceExcl'];
            $sumDeposit = $orderDetails[0]['sumDeposit'];
        }

        $order2update = [
            'total_paid' => $sumPriceIncl,
            'total_paid_tax_incl' => $sumPriceIncl,
            'total_paid_tax_excl' => $sumPriceExcl,
            'total_deposit' => $sumDeposit
        ];

        // update table orders
        $this->id = $orderId;
        $this->save($order2update);
    }
}
