<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Query;

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
class InvoicesTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->hasMany('InvoiceTaxes', [
            'foreignKey' => 'invoice_id',
        ]);
    }

    public function getDataForCustomerInvoice($customerId)
    {

        $customersTable = FactoryLocator::get('Table')->get('Customers');
        $customer = $customersTable->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId,
            ],
            'contain' => [
                'AddressCustomers',
                'ActiveOrderDetails' => function (Query $q) {
                    $q->order([
                        'ActiveOrderDetails.product_name' => 'ASC',
                        'ActiveOrderDetails.id_order_detail' => 'ASC',
                    ]);
                    return $q;
                },
                'ActiveOrderDetails.OrderDetailTaxes',
                'ActiveOrderDetails.OrderDetailUnits',
                'ActiveOrderDetails.Taxes',
            ]
        ])->first();

        // prepare correct weight if price per unit was used
        foreach($customer->active_order_details as $orderDetail) {
            if (!empty($orderDetail->order_detail_unit)) {
                $orderDetail->product_name .= ', ' . Configure::read('app.numberHelper')->formatUnitAsDecimal($orderDetail->order_detail_unit->product_quantity_in_units) . $orderDetail->order_detail_unit->unit_name;
            }
        }

        // prepare delivered deposit
        $orderDetailTable = FactoryLocator::get('Table')->get('OrderDetails');
        $orderedDeposit = $returnedDeposit = ['deposit_incl' => 0, 'deposit_excl' => 0, 'deposit_tax' => 0, 'deposit_amount' => 0];
        foreach($customer->active_order_details as $orderDetail) {
            if ($orderDetail->deposit > 0) {
                $orderedDeposit['deposit_incl'] += $orderDetail->deposit;
                $orderedDeposit['deposit_excl'] += $orderDetailTable->getDepositNet($orderDetail->deposit, $orderDetail->product_amount);
                $orderedDeposit['deposit_tax'] += $orderDetailTable->getDepositTax($orderDetail->deposit, $orderDetail->product_amount);
                $orderedDeposit['deposit_amount'] += $orderDetail->product_amount;
            }
        }
        $customer->ordered_deposit = $orderedDeposit;

        // prepare returned deposit
        $paymentsTable = FactoryLocator::get('Table')->get('Payments');
        $deposits = $paymentsTable->getCustomerDepositNotBilled($customerId);
        foreach($deposits as $deposit) {
            $returnedDeposit['deposit_incl'] += $deposit->amount * -1;
            $returnedDeposit['deposit_excl'] += $orderDetailTable->getDepositNet($deposit->amount, 1) * -1;
            $returnedDeposit['deposit_tax'] += $orderDetailTable->getDepositTax($deposit->amount, 1) * -1;
            $returnedDeposit['deposit_amount']++;
        }
        $customer->returned_deposit = $returnedDeposit;

        // prepare tax sums
        $taxRates = [];
        $defaultArray = [
            'sum_price_excl' => 0,
            'sum_tax' => 0,
            'sum_price_incl' => 0,
        ];
        foreach($customer->active_order_details as $orderDetail) {
            if (empty($orderDetail->tax)) {
                $taxRate = 0;
            } else {
                $taxRate = $orderDetail->tax->rate;
            }
            $taxRate = Configure::read('app.numberHelper')->formatTaxRate($taxRate);
            if (!isset($taxRates[$taxRate])) {
                $taxRates[$taxRate] = $defaultArray;
            }
            $taxRates[$taxRate]['sum_price_excl'] += $orderDetail->total_price_tax_excl;
            $taxRates[$taxRate]['sum_tax'] += $orderDetail->order_detail_tax->total_amount;
            $taxRates[$taxRate]['sum_price_incl'] += $orderDetail->total_price_tax_incl;
        }

        $depositVatRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'));
        $depositVatRate = Configure::read('app.numberHelper')->formatTaxRate($depositVatRate);

        if (!isset($taxRates[$depositVatRate])) {
            $taxRates[$depositVatRate] = $defaultArray;
        }
        $taxRates[$depositVatRate]['sum_price_excl'] += $orderedDeposit['deposit_excl'] + $returnedDeposit['deposit_excl'];
        $taxRates[$depositVatRate]['sum_tax'] += $orderedDeposit['deposit_tax'] + $returnedDeposit['deposit_tax'];
        $taxRates[$depositVatRate]['sum_price_incl'] += $orderedDeposit['deposit_incl'] + $returnedDeposit['deposit_incl'];

        ksort($taxRates);

        if (count($taxRates) == 1) {
            $taxRates = false;
        }
        $customer->tax_rates = $taxRates;

        // prepare sums
        $sumPriceIncl = 0;
        $sumPriceExcl = 0;
        $sumTax = 0;
        foreach ($customer->active_order_details as $orderDetail) {
            $sumPriceIncl += $orderDetail->total_price_tax_incl;
            $sumPriceExcl += $orderDetail->total_price_tax_excl;
            $sumTax += $orderDetail->order_detail_tax->total_amount;
        }

        $sumPriceIncl += $customer->ordered_deposit['deposit_incl'];
        $sumPriceExcl += $customer->ordered_deposit['deposit_excl'];
        $sumTax += $customer->ordered_deposit['deposit_tax'];

        $sumPriceIncl += $customer->returned_deposit['deposit_incl'];
        $sumPriceExcl += $customer->returned_deposit['deposit_excl'];
        $sumTax += $customer->returned_deposit['deposit_tax'];

        $customer->sumPriceIncl = $sumPriceIncl;
        $customer->sumPriceExcl = $sumPriceExcl;
        $customer->sumTax = $sumTax;

        $customer->new_invoice_necessary = !empty($customer->active_order_details) && $customer->ordered_deposit['deposit_amount'] + $customer->returned_deposit['deposit_amount'] > 0;

        return $customer;

    }

    public function getLastInvoiceForCustomer()
    {
        $lastInvoice = $this->find('all', [
            'conditions' => [
                'id_customer > 0',
            ],
            'order' => [
                'id' => 'DESC'
            ]
        ])->first();
        return $lastInvoice;
    }

    public function getNextInvoiceNumberForCustomer($currentYear, $lastInvoice)
    {

        $increasingNumberOfLastInvoice = 1;

        if (! empty($lastInvoice)) {
            $explodedInvoiceNumber = explode('-', $lastInvoice->invoice_number);
            $yearOfLastInvoice = $explodedInvoiceNumber[0];
            if ($currentYear == $yearOfLastInvoice) {
                $increasingNumberOfLastInvoice = (int) $explodedInvoiceNumber[1] + 1;
            }
        }

        $newIncreasingInvoiceNumber = $this->formatInvoiceNumberWithLeadingZeros($increasingNumberOfLastInvoice, 6);

        $newInvoiceNumber = $currentYear . '-' . $newIncreasingInvoiceNumber;
        return $newInvoiceNumber;

    }

    public function getNextInvoiceNumberForManufacturer($invoices)
    {
        $invoiceNumber = 1;
        if (! empty($invoices)) {
            $invoiceNumber = (int) $invoices[0]->invoice_number + 1;
        }
        $newInvoiceNumber = $this->formatInvoiceNumberWithLeadingZeros($invoiceNumber, 4);
        return $newInvoiceNumber;
    }

    /**
     * turns eg 24 into 0024
     */
    private function formatInvoiceNumberWithLeadingZeros($invoiceNumber, $zeroCount)
    {
        return str_pad($invoiceNumber, $zeroCount, '0', STR_PAD_LEFT);
    }

}
