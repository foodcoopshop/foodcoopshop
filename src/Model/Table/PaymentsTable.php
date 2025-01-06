<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Validation\Validator;
use App\Model\Traits\NumberRangeValidatorTrait;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PaymentsTable extends AppTable
{

    use NumberRangeValidatorTrait;

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

    public function validationEdit(Validator $validator): Validator
    {
        return $this->getNumberRangeValidator($validator, 'approval', -1, 1);
    }

    public function validationAdd(Validator $validator): Validator
    {
        $validator->notEmptyString('amount', __('Please_enter_a_number.'));
        $validator->numeric('amount', __('Please_enter_a_correct_number.'));
        $validator->greaterThanOrEqual('amount', 0.01, __('The_amount_(money)_needs_to_be_greater_than_0.'));
        $validator->allowEmptyDate('date_add');
        $validator->add('date_add', 'allowed-only-today-or-before', [
            'rule' => function ($value, $context) {
                if ($value == 0) {
                    return true;
                }
                $formattedValue = date(Configure::read('DateFormat.DatabaseAlt'), strtotime($value));
                if ($formattedValue > Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                    return false;
                }
                return true;
            },
            'message' => __('The_date_must_not_be_a_future_date.'),
        ]);

        return $validator;
    }

    public function validationCsvImportUpload(Validator $validator): Validator
    {
        $validator = $this->validationAdd($validator);
        $validator->requirePresence('amount', true, __('Please_enter_a_correct_amount.'));
        $validator->requirePresence('date', true, __('Please_enter_a_correct_date.'));
        return $validator;
    }

    public function validationCsvImportSave(Validator $validator): Validator
    {
        $validator = $this->validationAdd($validator);
        $validator->requirePresence('id_customer', true, __('Please_select_a_customer.'));
        $validator->greaterThan('id_customer', 0, __('Please_select_a_customer.'));
        return $validator;
    }

    public function isAlreadyImported(string $transactionText, string $date): bool
    {
        $alreadyImported = $this->find('all', conditions: [
            'date_transaction_add' => new DateTime($date),
            'status' => APP_ON,
        ])
        ->where(function ($exp, $query) use ($transactionText) {
            return $exp->or([
                'transaction_text' => $transactionText,
                // as I found no way to restore the � in the database, I made a workaround:
                // the or condition is needed for the case that the transaction text contains a �
                // which was the case until june 2023 when BankingReader->csvHasIsoFormat was introduced
                'REPLACE(transaction_text, "�", "") =' => str_replace(
                    ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'],
                    [ '',  '',  '',  '',  '',  '',  ''],
                    $transactionText,
                ),
            ]);
        })->count() > 0;

        return $alreadyImported;
    }

    private function getManufacturerDepositConditions($manufacturerId = null): array
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

    public function getManufacturerDepositsByMonth($manufacturerId, $monthAndYear): SelectQuery
    {
        $paymentSum = $this->find('all',
        conditions: $this->getManufacturerDepositConditions($manufacturerId),
        order: [
            'Payments.date_add' => 'DESC',
        ]);
        $paymentSum->where(function (QueryExpression $exp) use ($monthAndYear) {
            return $exp->eq('DATE_FORMAT(Payments.date_add, \'%Y-%c\')', $monthAndYear);
        });
        return $paymentSum;
    }

    public function getManufacturerDepositSumByCalendarWeekAndType($type): array
    {
        if (!in_array($type, [Payment::TEXT_EMPTY_GLASSES, Payment::TEXT_MONEY])) {
            throw new \Exception('wrong type: was ' . $type);
        }
        $conditions = $this->getManufacturerDepositConditions();
        $conditions['Payments.text'] = $type;

        $query = $this->find('all', conditions: $conditions);

        $formattedDate = 'DATE_FORMAT(Payments.date_add, "%Y-%u")';
        $query->select([
            'YearWeek' => $formattedDate,
            'SumAmount' => $query->func()->sum('Payments.amount'),
        ]);
        $query->groupBy($formattedDate);
        $result = $query->toArray();

        return $result;
    }

    public function getCustomerDepositNotBilled($customerId): array
    {
        $payments = $this->find('all', conditions: [
            'Payments.status' => APP_ON,
            '(Payments.invoice_id IS NULL OR Payments.invoice_id = 0)',
            'Payments.type' => 'deposit',
            'Payments.id_manufacturer' => 0,
            'Payments.id_customer' => $customerId,
        ])->toArray();
        return $payments;
    }

    public function getCustomerDepositSumByCalendarWeek(): array
    {
        $query = $this->find('all', conditions: [
            'Payments.status' => APP_ON,
            'Payments.type' => 'deposit',
            'Payments.id_manufacturer' => 0,
        ]);
        $formattedDate = 'DATE_FORMAT(Payments.date_add, "%Y-%u")';
        $query->select([
            'YearWeek' => $formattedDate,
            'SumAmount' => $query->func()->sum('Payments.amount'),
        ]);
        $query->groupBy($formattedDate);
        $result = $query->toArray();

        return $result;
    }

    public function getManufacturerDepositMoneySum(): float|int
    {

        $conditions = $this->getManufacturerDepositConditions();
        $conditions['Payments.text'] = Payment::TEXT_MONEY;

        $query = $this->find('all', conditions: $conditions);

        $query->select(['sumManufacturerMoneyDeposit' => $query->func()->sum('Payments.amount')]);
        $query->groupBy('Payments.text');
        $result = $query->toArray();

        if (isset($result[0])) {
            return $result[0]['sumManufacturerMoneyDeposit'];
        }

        return 0;
    }

    public function getMonthlyDepositSumByManufacturer($manufacturerId, $groupByMonth): array
    {

        $conditions = $this->getManufacturerDepositConditions($manufacturerId);

        $query = $this->find('all',
        conditions: $conditions,
        order: $groupByMonth ? ['monthAndYear' => 'DESC'] : ['Payments.date_add' => 'DESC'],
        group: $groupByMonth ? 'monthAndYear' : null);

        $query->select(
            ['sumDepositReturned' => $query->func()->sum('Payments.amount')],
        );
        if ($groupByMonth) {
            $query->select(
                ['monthAndYear' => 'DATE_FORMAT(Payments.date_add, \'%Y-%c\')'],
            );
        }

        return $query->toArray();
    }

    public function onInvoiceCancellation($payments): void
    {
        foreach($payments  as $payment) {
            $payment->invoice_id = null;
            $this->save($payment);
        }
    }

    public function linkReturnedDepositWithInvoice($data, $invoiceId): void
    {
        foreach($data->returned_deposit['entities'] as $payment) {
            // important to get a fresh payment entity as amount field could be changed for cancellation invoices
            $payment = $this->get($payment->id);
            $payment->invoice_id = $invoiceId;
            $this->save($payment);
        }
    }

    public function getSum($customerId, $type): float
    {
        $conditions = [
            'Payments.id_customer' => $customerId,
            'Payments.id_manufacturer' => 0,
            'Payments.status' => APP_ON,
        ];

        $conditions['Payments.type'] = $type;

        $query = $this->find('all', conditions: $conditions);
        $query->select(
            ['SumAmount' => $query->func()->sum('Payments.amount')]
        );
        return (float) $query->toArray()[0]['SumAmount'];
    }
}
