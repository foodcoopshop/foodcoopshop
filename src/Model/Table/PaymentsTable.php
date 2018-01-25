<?php

namespace App\Model\Table;

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
class PaymentsTable extends AppTable
{

    public $belongsTo = [
        'Customers' => [
            'foreignKey' => 'id_customer'
        ],
        'Manufacturers' => [
            'foreignKey' => 'id_manufacturer'
        ],
        'CreatedBy' => [
            'className' => 'Customers',
            'foreignKey' => 'created_by'
        ],
        'ChangedBy' => [
            'className' => 'Customers',
            'foreignKey' => 'changed_by'
        ],
    ];

    private function getManufacturerDepositConditions($manufacturerId = null)
    {
        $conditions = [
            'Payments.status' => APP_ON,
            'Payments.id_customer' => 0
        ];
        if (!is_null($manufacturerId)) {
            $conditions['Payments.id_manufacturer'] = $manufacturerId;
        }
        $conditions['Payments.type'] = 'deposit';
        return $conditions;
    }

    /**
     * @param int $manufacturerId
     * @param string $monthAndYear
     * @return array
     */
    public function getManufacturerDepositsByMonth($manufacturerId, $monthAndYear)
    {
        $conditions = $this->getManufacturerDepositConditions($manufacturerId);
        $conditions['DATE_FORMAT(Payment.date_add, \'%Y-%c\')'] = $monthAndYear;

        $paymentSum = $this->find('all', [
            'fields' => 'Payments.*',
            'conditions' => $conditions,
            'order' => ['Payments.date_add' => 'DESC'],
        ]);

        return $paymentSum;
    }

    /**
     * @return float
     */
    public function getManufacturerDepositMoneySum()
    {

        $conditions = $this->getManufacturerDepositConditions();
        $conditions['Payments.text'] = 'money';

        $paymentSum = $this->find('all', [
            'fields' => 'SUM(amount) as sumManufacturerMoneyDeposit',
            'conditions' => $conditions,
            'order' => ['Payments.date_add' => 'DESC'],
        ]);

        return $paymentSum[0]['sumManufacturerMoneyDeposit'];
    }

    /**
     * @param int $manufacturerId
     * @param boolean $groupByMonth
     * @return array
     */
    public function getMonthlyDepositSumByManufacturer($manufacturerId, $groupByMonth)
    {

        $conditions = $this->getManufacturerDepositConditions($manufacturerId);

        $fields = [
            'SUM(amount) as sumDepositReturned'
        ];
        if ($groupByMonth) {
            $fields[] = 'DATE_FORMAT(Payment.date_add, \'%Y-%c\') as monthAndYear';
        }
        $paymentSum = $this->find('all', [
            'fields' => $fields,
            'conditions' => $conditions,
            'order' => $groupByMonth ? ['monthAndYear' => 'DESC'] : ['Payments.date_add' => 'DESC'],
            'group' => $groupByMonth ? 'monthAndYear' : null
        ]);

        return $paymentSum;
    }

    /**
     * @param int $customerId
     * @param string $type
     * @return float
     */
    public function getSum($customerId, $type)
    {
        $conditions = [
            'Payments.id_customer' => $customerId,
            'Payments.id_manufacturer' => 0,
            'Payments.status' => APP_ON
        ];

        $conditions['Payments.type'] = $type;

        $query = $this->find('all', [
            'conditions' => $conditions
        ]);
        $query->select(
            ['SumAmount' => $query->func()->sum('Payments.amount')]
        );
        return $query->toArray()[0]['SumAmount'];
    }
}
