<?php

namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
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
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer',
        ]);
        $this->hasMany('InvoiceTaxes', [
            'foreignKey' => 'invoice_id',
        ]);
        $this->hasMany('OrderDetails', [
            'foreignKey' => 'id_invoice',
        ]);
        $this->hasMany('Payments', [
            'foreignKey' => 'invoice_id',
        ]);
        $this->belongsTo('CancellationInvoices', [
            'className' => 'Invoices',
            'foreignKey' => 'cancellation_invoice_id',
        ]);
        $this->hasOne('CancelledInvoices', [
            'className' => 'Invoices',
            'foreignKey' => 'cancellation_invoice_id',
        ]);
    }

    public function getPreparedTaxRatesForSumTable($invoices)
    {

        $defaultArray = [
            'sum_price_excl' => 0,
            'sum_tax' => 0,
            'sum_price_incl' => 0,
        ];
        $taxRates = [
            'cashless' => [],
            'cash' => [],
            'total' => [],
        ];
        $taxRatesSums = [
            'cashless' => $defaultArray,
            'cash' => $defaultArray,
            'total' => $defaultArray,
        ];
        foreach($invoices as $invoice) {

            $taxRateType = $invoice->paid_in_cash ? 'cash' : 'cashless';

            foreach([$taxRateType, 'total'] as $trt) {

                foreach($invoice->invoice_taxes as $invoiceTax) {

                    $taxRate = Configure::read('app.numberHelper')->formatTaxRate($invoiceTax->tax_rate);
                    if (!isset($taxRates[$trt][$taxRate])) {
                        $taxRates[$trt][$taxRate] = $defaultArray;
                    }
                    $taxRates[$trt][$taxRate]['sum_price_excl'] += $invoiceTax->total_price_tax_excl;
                    $taxRates[$trt][$taxRate]['sum_tax'] += $invoiceTax->total_price_tax;
                    $taxRates[$trt][$taxRate]['sum_price_incl'] += $invoiceTax->total_price_tax_incl;

                    $taxRatesSums[$trt]['sum_price_excl'] += $invoiceTax->total_price_tax_excl;
                    $taxRatesSums[$trt]['sum_tax'] += $invoiceTax->total_price_tax;
                    $taxRatesSums[$trt]['sum_price_incl'] += $invoiceTax->total_price_tax_incl;

                }
            }
        }

        $taxRates['cashless'] = $this->clearZeroArray($taxRates['cashless']);
        $taxRates['cash'] = $this->clearZeroArray($taxRates['cash']);
        $taxRates['total'] = $this->clearZeroArray($taxRates['total']);

        ksort($taxRates['cashless']);
        ksort($taxRates['cash']);
        ksort($taxRates['total']);

        $result = [
            'taxRates' => $taxRates,
            'taxRatesSums' => $this->clearZeroArray($taxRatesSums),
        ];

        return $result;

    }

    public function getDataForCustomerInvoice($customerId, $currentDay)
    {

        $customersTable = FactoryLocator::get('Table')->get('Customers');
        $customer = $customersTable->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId,
            ],
            'contain' => [
                'AddressCustomers',
                'ActiveOrderDetails' => function (Query $q) use ($currentDay) {
                    $q->where(function (QueryExpression $exp, Query $q) use ($currentDay) {
                        return $exp->addCase(
                            [
                                $q->newExpr()->lte('DATE_FORMAT(ActiveOrderDetails.pickup_day, \'%Y-%m-%d\')', $currentDay),
                            ],
                        );
                    });
                    return $q;
                },
                'ActiveOrderDetails.OrderDetailTaxes',
                'ActiveOrderDetails.OrderDetailUnits',
                'ActiveOrderDetails.Taxes',
                'ActiveOrderDetails.Products.Manufacturers',
            ]
        ])->first();

        // fetch returned deposit
        $paymentsTable = FactoryLocator::get('Table')->get('Payments');
        $deposits = $paymentsTable->getCustomerDepositNotBilled($customerId);

        $preparedData = $this->prepareDataForCustomerInvoice($customer->active_order_details, $deposits, null);

        $customer->active_order_details = $preparedData['active_order_details'];
        $customer->ordered_deposit = $preparedData['ordered_deposit'];
        $customer->returned_deposit = $preparedData['returned_deposit'];
        $customer->tax_rates = $preparedData['tax_rates'];
        $customer->sumPriceIncl = $preparedData['sumPriceIncl'];
        $customer->sumPriceExcl = $preparedData['sumPriceExcl'];
        $customer->sumTax = $preparedData['sumTax'];
        $customer->new_invoice_necessary = $preparedData['new_invoice_necessary'];
        $customer->is_cancellation_invoice = false;

        return $customer;

    }

    public function prepareDataForCustomerInvoice($orderDetails, $returnedDeposits, $cancelledInvoice)
    {

        // sorting by manufacturer name as third level assocition is hard (or even not possible)
        foreach($orderDetails as $orderDetail) {
            $manufacturerName[] = StringComponent::slugify($orderDetail->product->manufacturer->name);
            $productName[] = StringComponent::slugify($orderDetail->product_name);
            $deliveryDay[] = $orderDetail->pickup_day;
        }

        if (!empty($orderDetails)) {
            array_multisort(
                $manufacturerName, SORT_ASC,
                $productName, SORT_ASC,
                $deliveryDay, SORT_ASC,
                $orderDetails,
            );
        }

        // prepare correct weight if price per unit was used
        foreach($orderDetails as $orderDetail) {
            if (!empty($orderDetail->order_detail_unit)) {
                // do not add unit a second time if cancellation invoice is rendered ($source == 'OrderDetails')
                if ($orderDetail->getSource() == 'ActiveOrderDetails') {
                    $orderDetail->product_name .= ', ' . Configure::read('app.numberHelper')->formatUnitAsDecimal($orderDetail->order_detail_unit->product_quantity_in_units) . $orderDetail->order_detail_unit->unit_name;
                }
            }
        }

        // prepare delivered deposit
        $orderDetailTable = FactoryLocator::get('Table')->get('OrderDetails');
        $orderedDeposit = $returnedDeposit = ['deposit_incl' => 0, 'deposit_excl' => 0, 'deposit_tax' => 0, 'deposit_amount' => 0, 'entities' => []];
        foreach($orderDetails as $orderDetail) {
            if ($orderDetail->deposit != 0) {
                $orderedDeposit['deposit_incl'] += $orderDetail->deposit;
                $orderedDeposit['deposit_excl'] += $orderDetailTable->getDepositNet($orderDetail->deposit, $orderDetail->product_amount);
                $orderedDeposit['deposit_tax'] += $orderDetailTable->getDepositTax($orderDetail->deposit, $orderDetail->product_amount);
                $orderedDeposit['deposit_amount'] += $orderDetail->product_amount;
            }
        }

        foreach($returnedDeposits as $deposit) {
            $returnedDeposit['deposit_incl'] += $deposit->amount * -1;
            $returnedDeposit['deposit_excl'] += $orderDetailTable->getDepositNet($deposit->amount, 1) * -1;
            $returnedDeposit['deposit_tax'] += $orderDetailTable->getDepositTax($deposit->amount, 1) * -1;
            $returnedDeposit['deposit_amount']++;
            $returnedDeposit['entities'][] = $deposit;
        }

        $defaultArray = [
            'sum_price_excl' => 0,
            'sum_tax' => 0,
            'sum_price_incl' => 0,
        ];
        $taxRates = $orderDetailTable->getTaxSums($orderDetails);

        $depositVatRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'));
        $depositVatRate = Configure::read('app.numberHelper')->formatTaxRate($depositVatRate);

        if (!isset($taxRates[$depositVatRate])) {
            $taxRates[$depositVatRate] = $defaultArray;
        }
        $taxRates[$depositVatRate]['sum_price_excl'] += $orderedDeposit['deposit_excl'] + $returnedDeposit['deposit_excl'];
        $taxRates[$depositVatRate]['sum_tax'] += $orderedDeposit['deposit_tax'] + $returnedDeposit['deposit_tax'];
        $taxRates[$depositVatRate]['sum_price_incl'] += $orderedDeposit['deposit_incl'] + $returnedDeposit['deposit_incl'];

        ksort($taxRates);

        $taxRates = $this->clearZeroArray($taxRates);

        // prepare sums
        $sumPriceIncl = 0;
        $sumPriceExcl = 0;
        $sumTax = 0;
        foreach ($orderDetails as $orderDetail) {
            $sumPriceIncl += $orderDetail->total_price_tax_incl;
            $sumPriceExcl += $orderDetail->total_price_tax_excl;
            $sumTax += $orderDetail->order_detail_tax->total_amount;
        }

        $sumPriceIncl += $orderedDeposit['deposit_incl'];
        $sumPriceExcl += $orderedDeposit['deposit_excl'];
        $sumTax += $orderedDeposit['deposit_tax'];

        $sumPriceIncl += $returnedDeposit['deposit_incl'];
        $sumPriceExcl += $returnedDeposit['deposit_excl'];
        $sumTax += $returnedDeposit['deposit_tax'];

        $preparedData = [
            'active_order_details' => $orderDetails,
            'ordered_deposit' => $orderedDeposit,
            'returned_deposit' => $returnedDeposit,
            'tax_rates' => $taxRates,
            'sumPriceIncl' => $sumPriceIncl,
            'sumPriceExcl' => $sumPriceExcl,
            'sumTax' => $sumTax,
            'cancelledInvoice' => $cancelledInvoice,
            'new_invoice_necessary' => !empty($orderDetails) || $orderedDeposit['deposit_amount'] < 0 || $returnedDeposit['deposit_amount'] > 0,
        ];

        return $preparedData;
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
