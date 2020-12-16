<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Validation\Validator;
use App\Lib\Error\Exception\InvalidParameterException;

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
class PaymentsTable extends AppTable
{

    public function initialize(array $config): void
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
        $validator->notEmptyString('amount', __('Please_enter_a_number.'));
        $validator->numeric('amount', __('Please_enter_a_correct_number.'));
        $validator->greaterThanOrEqual('amount', 0.01, __('The_amount_(money)_needs_to_be_greater_than_0.'));
        $validator->allowEmptyDate('date_add');
        $validator->add('date_add', 'allowed-only-today-or-before', [
            'rule' => function ($value, $context) {
                $formattedValue = date(Configure::read('DateFormat.DatabaseAlt'), strtotime($value));
                if ($formattedValue >Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                    return false;
                }
                return true;
            },
            'message' => __('The_date_must_not_be_a_future_date.'),
        ]);

        return $validator;
    }

    public function validationCsvImport(Validator $validator)
    {
        $validator = $this->validationAdd($validator);
        $validator->requirePresence('amount', true, __('Please_enter_a_correct_amount.'));
        $validator->requirePresence('date', true, __('Please_enter_a_correct_date.'));
        $validator->requirePresence('id_customer', true, __('Please_select_a_customer.'));
        $validator->numeric('id_customer', __('Please_select_a_customer.'));
        return $validator;
    }

    public function isAlreadyImported(string $transactionText, string $date): bool
    {
        $alreadyImported = $this->find('all', [
            'conditions' => [
                'transaction_text' => $transactionText,
                'date_transaction_add' => new FrozenTime($date),
                'status' => APP_ON,
            ]
        ])->count() > 0;
        return $alreadyImported;
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

    public function getManufacturerDepositSumByCalendarWeekAndType($type)
    {
        if (!in_array($type, ['empty_glasses', 'money'])) {
            throw new InvalidParameterException('wrong type: was ' . $type);
        }
        $conditions = $this->getManufacturerDepositConditions();
        $conditions['Payments.text'] = $type;

        $query = $this->find('all', [
            'conditions' => $conditions
        ]);

        $formattedDate = 'DATE_FORMAT(Payments.date_add, "%Y-%u")';
        $query->select([
            'YearWeek' => $formattedDate,
            'SumAmount' => $query->func()->sum('Payments.amount')
        ]);
        $query->group($formattedDate);
        $result = $query->toArray();

        return $result;
    }

    public function getCustomerDepositNotBilled($customerId)
    {
        $payments = $this->find('all', [
            'conditions' => [
                'Payments.status' => APP_ON,
                '(Payments.invoice_id IS NULL OR Payments.invoice_id = 0)',
                'Payments.type' => 'deposit',
                'Payments.id_manufacturer' => 0,
                'Payments.id_customer' => $customerId,
            ]
        ])->toArray();
        return $payments;
    }

    public function getCustomerDepositSumByCalendarWeek()
    {
        $query = $this->find('all', [
            'conditions' => [
                'Payments.status' => APP_ON,
                'Payments.type' => 'deposit',
                'Payments.id_manufacturer' => 0,
            ]
        ]);
        $formattedDate = 'DATE_FORMAT(Payments.date_add, "%Y-%u")';
        $query->select([
            'YearWeek' => $formattedDate,
            'SumAmount' => $query->func()->sum('Payments.amount')
        ]);
        $query->group($formattedDate);
        $result = $query->toArray();

        return $result;
    }

    /**
     * @return float
     */
    public function getManufacturerDepositMoneySum()
    {

        $conditions = $this->getManufacturerDepositConditions();
        $conditions['Payments.text'] = 'money';

        $query = $this->find('all', [
            'conditions' => $conditions
        ]);

        $query->select(['sumManufacturerMoneyDeposit' => $query->func()->sum('Payments.amount')]);
        $query->group('Payments.text');
        $result = $query->toArray();

        if (isset($result[0])) {
            return $result[0]['sumManufacturerMoneyDeposit'];
        }

        return 0;
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
