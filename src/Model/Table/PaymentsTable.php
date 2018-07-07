<?php

namespace App\Model\Table;

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
class PaymentsTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'id_manufacturer'
        ]);
        $this->belongsTo('CreatedByCustomers', [
            'className' => 'Customers',
            'foreignKey' => 'created_by'
        ]);
        $this->belongsTo('ChangedByCustomers', [
            'className' => 'Customers',
            'foreignKey' => 'changed_by'
        ]);
    }

    public function validationEdit(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'approval', -1, 1);
    }

    public function validationAdd(Validator $validator)
    {
        $validator->notEmpty('amount', __('Please_enter_a_number.'));
        $validator->numeric('amount', __('Please_enter_a_correct_number.'));
        $validator->greaterThanOrEqual('amount', 0.01, __('The_amount_(money)_needs_to_be_greater_than_0.'));
        return $validator;
    }

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
        $conditions[] = 'DATE_FORMAT(Payments.date_add, \'%Y-%c\') = \'' . $monthAndYear . '\'';

        $paymentSum = $this->find('all', [
            'conditions' => $conditions,
            'order' => [
                'Payments.date_add' => 'DESC'
            ]
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

        $query = $this->find('all', [
            'conditions' => $conditions,
            'order' => ['Payments.date_add' => 'DESC'],
        ]);

        $query->select(
            ['sumManufacturerMoneyDeposit' => $query->func()->sum('Payments.amount')]
        );

        return $query->toArray()[0]['sumManufacturerMoneyDeposit'];
    }

    /**
     * @param int $manufacturerId
     * @param boolean $groupByMonth
     * @return array
     */
    public function getMonthlyDepositSumByManufacturer($manufacturerId, $groupByMonth)
    {

        $conditions = $this->getManufacturerDepositConditions($manufacturerId);

        $query = $this->find('all', [
            'conditions' => $conditions,
            'order' => $groupByMonth ? ['monthAndYear' => 'DESC'] : ['Payments.date_add' => 'DESC'],
            'group' => $groupByMonth ? 'monthAndYear' : null
        ]);

        $query->select(
            ['sumDepositReturned' => $query->func()->sum('Payments.amount')]
        );
        if ($groupByMonth) {
            $query->select(
                ['monthAndYear' => 'DATE_FORMAT(Payments.date_add, \'%Y-%c\')']
            );
        }

        return $query->toArray();
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
