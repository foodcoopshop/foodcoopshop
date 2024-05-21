<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Query;
use Cake\I18n\DateTime;

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

    public function getLatestInvoicesForCustomer($customerId)
    {

        $invoices = $this->find('all',
        conditions: [
            'Invoices.id_customer' => $customerId,
        ],
        order: [
            'Invoices.created' => 'DESC'
        ],
        contain: [
            'InvoiceTaxes',
        ],
        limit: 5)->toArray();

        foreach($invoices as &$invoice) {

            foreach($invoice->invoice_taxes as $invoiceTax) {
                $invoice->total_sum_price_excl += $invoiceTax->total_price_tax_excl;
                $invoice->total_sum_tax += $invoiceTax->total_price_tax;
                $invoice->total_sum_price_incl += $invoiceTax->total_price_tax_incl;
            }

            if (is_null($invoice->total_sum_price_excl)) {
                $invoice->total_sum_price_excl = 0;
            }
            if (is_null($invoice->total_sum_tax)) {
                $invoice->total_sum_tax = 0;
            }
            if (is_null($invoice->total_sum_price_incl)) {
                $invoice->total_sum_price_incl = 0;
            }

        }

        return $invoices;
    }

    public function clearZeroArray($array)
    {
        foreach($array as $key => $value) {
            if (array_sum($value) == 0) {
                unset($array[$key]);
            }
        }
        return $array;
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

                if (isset($taxRate) && isset($taxRates[$trt][$taxRate])) {
                    $taxRates[$trt][$taxRate]['sum_price_excl'] = round($taxRates[$trt][$taxRate]['sum_price_excl'], 2);
                    $taxRates[$trt][$taxRate]['sum_tax'] = round($taxRates[$trt][$taxRate]['sum_tax'], 2);
                    $taxRates[$trt][$taxRate]['sum_price_incl'] = round($taxRates[$trt][$taxRate]['sum_price_incl'], 2);
                    $taxRatesSums[$trt]['sum_price_excl'] = round($taxRatesSums[$trt]['sum_price_excl'], 2);
                    $taxRatesSums[$trt]['sum_tax'] = round($taxRatesSums[$trt]['sum_tax'], 2);
                    $taxRatesSums[$trt]['sum_price_incl'] = round($taxRatesSums[$trt]['sum_price_incl'], 2);
                }
            }
        }

        $taxRates['cashless'] = $this->clearZeroArray($taxRates['cashless']);
        $taxRates['cash'] = $this->clearZeroArray($taxRates['cash']);
        $taxRates['total'] = $this->clearZeroArray($taxRates['total']);

        ksort($taxRates['cashless'], SORT_NUMERIC);
        ksort($taxRates['cash'], SORT_NUMERIC);
        ksort($taxRates['total'], SORT_NUMERIC);

        $result = [
            'taxRates' => $taxRates,
            'taxRatesSums' => $this->clearZeroArray($taxRatesSums),
        ];

        return $result;

    }

    public function getDataForCustomerInvoice($customerId, $currentDay)
    {

        $customersTable = FactoryLocator::get('Table')->get('Customers');
        $customer = $customersTable->find('all',
            conditions: [
                'Customers.id_customer' => $customerId,
            ],
            contain: [
                'AddressCustomers',
                'ActiveOrderDetails' => function (Query $q) use ($currentDay) {
                    $q->where(function (QueryExpression $exp) use ($currentDay) {
                        return $exp->lte('DATE_FORMAT(ActiveOrderDetails.pickup_day, \'%Y-%m-%d\')', $currentDay);
                    });
                    return $q;
                },
                'ActiveOrderDetails.OrderDetailUnits',
                'ActiveOrderDetails.Products.Manufacturers',
            ]
        )->first();

        // fetch returned deposit
        $paymentsTable = FactoryLocator::get('Table')->get('Payments');
        $deposits = $paymentsTable->getCustomerDepositNotBilled($customerId);

        // create empty dummy data for deleted customer
        if (is_null($customer)) {
            $customer = $customersTable->newEmptyEntity();
            $customer->active_order_details = [];
        }

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
        $manufacturerName = [];
        $productName = [];
        $deliveryDay = [];

        foreach($orderDetails as $orderDetail) {
            $manufacturerName[] = mb_strtolower(StringComponent::slugify($orderDetail->product->manufacturer->name));
            $productName[] = mb_strtolower(StringComponent::slugify($orderDetail->product_name));
            $deliveryDay[] = $orderDetail->pickup_day;
        }

        if (!empty($orderDetails)) {
            if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
                array_multisort(
                    $productName, SORT_ASC,
                    $orderDetails,
                );
            } else {
                array_multisort(
                    $manufacturerName, SORT_ASC,
                    $productName, SORT_ASC,
                    $deliveryDay, SORT_ASC,
                    $orderDetails,
                );
            }
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
        $depositTaxRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'));
        $orderDetailTable = FactoryLocator::get('Table')->get('OrderDetails');
        $orderedDeposit = $returnedDeposit = ['deposit_incl' => 0, 'deposit_excl' => 0, 'deposit_tax' => 0, 'deposit_amount' => 0, 'entities' => []];
        foreach($orderDetails as $orderDetail) {
            if ($orderDetail->deposit != 0) {
                $orderedDeposit['deposit_incl'] += $orderDetail->deposit;
                $orderedDeposit['deposit_excl'] += $orderDetailTable->getDepositNet($orderDetail->deposit, $orderDetail->product_amount, $depositTaxRate);
                $orderedDeposit['deposit_tax'] += $orderDetailTable->getDepositTax($orderDetail->deposit, $orderDetail->product_amount, $depositTaxRate);
                $orderedDeposit['deposit_amount'] += $orderDetail->product_amount;
            }
        }

        foreach($returnedDeposits as $deposit) {
            $returnedDeposit['deposit_incl'] += $deposit->amount * -1;
            $returnedDeposit['deposit_excl'] += $orderDetailTable->getDepositNet($deposit->amount, 1, $depositTaxRate) * -1;
            $returnedDeposit['deposit_tax'] += $orderDetailTable->getDepositTax($deposit->amount, 1, $depositTaxRate) * -1;
            $returnedDeposit['deposit_amount']++;
            $returnedDeposit['entities'][] = $deposit;
        }

        $taxRates = [];
        $depositVatRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'));
        $depositVatRate = Configure::read('app.numberHelper')->formatTaxRate($depositVatRate);

        if (!Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {

            $defaultArray = [
                'sum_price_excl' => 0,
                'sum_tax' => 0,
                'sum_price_incl' => 0,
            ];
            $taxRates = $orderDetailTable->getTaxSums($orderDetails);

            if (!Configure::read('appDb.FCS_TAX_BASED_ON_NET_INVOICE_SUM')) {
                if (!isset($taxRates[$depositVatRate])) {
                    $taxRates[$depositVatRate] = $defaultArray;
                }
                $taxRates[$depositVatRate]['sum_price_excl'] += $orderedDeposit['deposit_excl'] + $returnedDeposit['deposit_excl'];
                $taxRates[$depositVatRate]['sum_tax'] += $orderedDeposit['deposit_tax'] + $returnedDeposit['deposit_tax'];
                $taxRates[$depositVatRate]['sum_price_incl'] += $orderedDeposit['deposit_incl'] + $returnedDeposit['deposit_incl'];
                ksort($taxRates, SORT_NUMERIC);
                $taxRates = $this->clearZeroArray($taxRates);
            }

        }

        if (!Configure::read('appDb.FCS_TAX_BASED_ON_NET_INVOICE_SUM')) {
            $sums = $this->getSums($orderDetails, $orderedDeposit, $returnedDeposit);
        } else {
            $sums = $this->getSumsTaxBasedOnNetInvoiceSum($orderDetails, $orderedDeposit, $returnedDeposit);
            $taxRates[$depositVatRate] = [
                'sum_price_excl' => $sums['priceExcl'],
                'sum_tax' => $sums['tax'],
                'sum_price_incl' => $sums['priceIncl'],
            ];
        }

        $preparedData = [
            'active_order_details' => $orderDetails,
            'ordered_deposit' => $orderedDeposit,
            'returned_deposit' => $returnedDeposit,
            'tax_rates' => $taxRates,
            'sumPriceIncl' => $sums['priceIncl'],
            'sumPriceExcl' => $sums['priceExcl'],
            'sumTax' => $sums['tax'],
            'cancelledInvoice' => $cancelledInvoice,
            'new_invoice_necessary' => !empty($orderDetails) || $orderedDeposit['deposit_amount'] < 0 || $returnedDeposit['deposit_amount'] > 0,
        ];

        return $preparedData;
    }

    private function getSumsTaxBasedOnNetInvoiceSum($orderDetails, $orderedDeposit, $returnedDeposit)
    {

        $result = [
            'priceIncl' => 0,
            'priceExcl' => 0,
            'tax' => 0,
        ];

        foreach ($orderDetails as $orderDetail) {
            $result['priceExcl'] += $orderDetail->total_price_tax_excl;
        }

        $result['priceExcl'] += $orderedDeposit['deposit_excl'];
        $result['priceExcl'] += $returnedDeposit['deposit_excl'];

        // q&d: taxRate for priceIncl (also contains orderDetails) is taken from FCS_DEPOSIT_TAX_RATE
        $depositVatRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'));
        $result['priceIncl'] = round($result['priceExcl'] * (1 + $depositVatRate / 100), 2);
        $result['tax'] = round($result['priceExcl'] * ($depositVatRate / 100), 2);

        return $result;

    }

    private function getSums($orderDetails, $orderedDeposit, $returnedDeposit)
    {

        $result = [
            'priceIncl' => 0,
            'priceExcl' => 0,
            'tax' => 0,
        ];
        foreach ($orderDetails as $orderDetail) {
            $result['priceIncl'] += $orderDetail->total_price_tax_incl;
            $result['priceExcl'] += $orderDetail->total_price_tax_excl;
            $result['tax'] += $orderDetail->tax_total_amount;
        }

        $result['priceIncl'] += $orderedDeposit['deposit_incl'];
        $result['priceExcl'] += $orderedDeposit['deposit_excl'];
        $result['tax'] += $orderedDeposit['deposit_tax'];

        $result['priceIncl'] += $returnedDeposit['deposit_incl'];
        $result['priceExcl'] += $returnedDeposit['deposit_excl'];
        $result['tax'] += $returnedDeposit['deposit_tax'];

        return $result;

    }

    public function getLastInvoiceForCustomer()
    {
        $lastInvoice = $this->find('all',
        conditions: [
            'id_customer > 0',
        ],
        order: [
            'id' => 'DESC'
        ])->first();
        return $lastInvoice;
    }

    public function saveInvoice($invoiceId, $customerId, $taxRates, $invoiceNumber, $invoicePdfFile, $currentDay, $paidInCash, $invoicesPerEmailEnabled)
    {

        $invoiceData = [
            'id_customer' => $customerId,
            'invoice_number' => $invoiceNumber,
            'filename' => $invoicePdfFile,
            'created' => new DateTime($currentDay),
            'paid_in_cash' => $paidInCash,
            'invoice_taxes' => [],
            'email_status' => $invoicesPerEmailEnabled ? null : __('deactivated'),
        ];

        if (!empty($invoiceId)) {
            $invoiceData['id'] = $invoiceId;
        }

        foreach($taxRates as $taxRate => $values) {
            $invoiceData['invoice_taxes'][] = [
                'tax_rate' => Configure::read('app.numberHelper')->parseFloatRespectingLocale($taxRate),
                'total_price_tax_excl' => $values['sum_price_excl'],
                'total_price_tax_incl' => $values['sum_price_incl'],
                'total_price_tax' => $values['sum_tax'],
            ];
        }
        $invoiceEntity = $this->newEntity($invoiceData);

        $newInvoice = $this->save($invoiceEntity, [
            'associated' => [
                'InvoiceTaxes',
            ],
        ]);

        return $newInvoice;

    }

    public function getNextInvoiceNumberForCustomer($currentYear, $lastInvoice)
    {

        $increasingNumberOfLastInvoice = 1;

        $invoicePrefix = Configure::read('appDb.FCS_INVOICE_NUMBER_PREFIX');

        if (! empty($lastInvoice)) {

            $lastInvoiceNumberWithoutPrefix = $lastInvoice->invoice_number;
            if ($invoicePrefix != '') {
                $lastInvoiceNumberWithoutPrefix = preg_replace('/^' . $invoicePrefix . '/', '', $lastInvoice->invoice_number);
            }

            $explodedInvoiceNumber = explode('-', $lastInvoiceNumberWithoutPrefix);
            $yearOfLastInvoice = $explodedInvoiceNumber[0];
            if ($currentYear == $yearOfLastInvoice) {
                $increasingNumberOfLastInvoice = (int) $explodedInvoiceNumber[1] + 1;
            }
        }

        $newIncreasingInvoiceNumber = $this->formatInvoiceNumberWithLeadingZeros((string) $increasingNumberOfLastInvoice, 6);

        $newInvoiceNumber = $invoicePrefix . $currentYear . '-' . $newIncreasingInvoiceNumber;
        return $newInvoiceNumber;

    }

    public function getNextInvoiceNumberForManufacturer($invoices)
    {
        $invoiceNumber = 1;
        if (! empty($invoices)) {
            $invoiceNumber = (int) $invoices[0]->invoice_number + 1;
        }
        $newInvoiceNumber = $this->formatInvoiceNumberWithLeadingZeros((string) $invoiceNumber, 4);
        return $newInvoiceNumber;
    }

    /**
     * turns eg 24 into 0024
     */
    private function formatInvoiceNumberWithLeadingZeros(string $invoiceNumber, int $zeroCount): string
    {
        return str_pad($invoiceNumber, $zeroCount, '0', STR_PAD_LEFT);
    }

}
