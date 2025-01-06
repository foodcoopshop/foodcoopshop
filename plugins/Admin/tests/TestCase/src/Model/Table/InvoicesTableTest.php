<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\GenerateOrderWithDecimalsInTaxRateTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\PrepareAndTestInvoiceDataTrait;
use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class InvoicesTableTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;
    use PrepareAndTestInvoiceDataTrait;
    use GenerateOrderWithDecimalsInTaxRateTrait;

    public function testGetNextInvoiceNumberForCustomerInvoicesDoNotExistWithPrefix(): void
    {
        $invoicePrefix = 'ABC-';
        $this->changeConfiguration('FCS_INVOICE_NUMBER_PREFIX', $invoicePrefix);
        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $result = $invoicesTable->getNextInvoiceNumberForCustomer('2020', []);
        $this->assertEquals($result, $invoicePrefix . '2020-000001');
    }

    public function testGetNextInvoiceNumberForCustomerInvoicesDoNotExist(): void
    {
        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $result = $invoicesTable->getNextInvoiceNumberForCustomer('2020', []);
        $this->assertEquals($result, '2020-000001');
    }

    public function testGetNextInvoiceNumberForCustomerDifferentYearAndInvoiceAlreadyExistsWithPrefix(): void
    {
        $invoicePrefix = 'ABC-';
        $this->changeConfiguration('FCS_INVOICE_NUMBER_PREFIX', $invoicePrefix);
        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoice = $invoicesTable->newEmptyEntity();
        $invoice->invoice_number = $invoicePrefix . '2020-000001';
        $result = $invoicesTable->getNextInvoiceNumberForCustomer('2021', $invoice);
        $this->assertEquals($result, $invoicePrefix . '2021-000001');
    }

    public function testGetNextInvoiceNumberForCustomerDifferentYearAndInvoiceAlreadyExists(): void
    {
        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoice = $invoicesTable->newEmptyEntity();
        $invoice->invoice_number = '2020-000001';
        $result = $invoicesTable->getNextInvoiceNumberForCustomer('2021', $invoice);
        $this->assertEquals($result, '2021-000001');
    }

    public function testGetNextInvoiceNumberForCustomerSameYearAndInvoiceAlreadyExists(): void
    {
        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoice = $invoicesTable->newEmptyEntity();
        $invoice->invoice_number = '2020-000001';
        $result = $invoicesTable->getNextInvoiceNumberForCustomer('2020', $invoice);
        $this->assertEquals($result, '2020-000002');
    }

    public function testGetNextInvoiceNumberForManufacturerInvoicesDoNotExist(): void
    {
        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $result = $invoicesTable->getNextInvoiceNumberForManufacturer([]);
        $this->assertEquals($result, '0001');
    }

    public function testGetNextInvoiceNumberForManufacturerInvoicesAlreadyExist(): void
    {
        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoice = $invoicesTable->newEmptyEntity();
        $invoice->invoice_number = '0001';
        $result = $invoicesTable->getNextInvoiceNumberForManufacturer([$invoice]);
        $this->assertEquals($result, '0002');
    }

    public function testGetPreparedTaxRatesForSumTableWithDecimalsInTaxRate(): void
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $this->generateOrderWithDecimalsInTaxRate($customerId);

        $paidInCash = 1;
        $this->generateInvoice($customerId, $paidInCash);

        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoices = $invoicesTable->find('all',
            conditions: [
                'Invoices.id_customer' => $customerId,
            ],
            contain: [
                'InvoiceTaxes',
            ]
        )->toArray();

        $result = $invoicesTable->getPreparedTaxRatesForSumTable($invoices);

        $expected = [
            'taxRates' => [
                'cashless' => [],
                'cash' => [
                    '0' => [
                        'sum_price_excl' => 4.54,
                        'sum_tax' => 0,
                        'sum_price_incl' => 4.54,
                    ],
                    '8,4' => [
                        'sum_price_excl' => 13.65,
                        'sum_tax' => 1.14,
                        'sum_price_incl' => 14.79,
                    ],
                    '10' => [
                        'sum_price_excl' => 1.65,
                        'sum_tax' => 0.17,
                        'sum_price_incl' => 1.82,
                    ],
                    '13' => [
                        'sum_price_excl' => 0.55,
                        'sum_tax' => 0.07,
                        'sum_price_incl' => 0.62,
                    ],
                    '20' => [
                        'sum_price_excl' => 0.84,
                        'sum_tax' => 0.16,
                        'sum_price_incl' => 1,
                    ],
                ],
                'total' => [
                    '0' => [
                        'sum_price_excl' => 4.54,
                        'sum_tax' => 0,
                        'sum_price_incl' => 4.54,
                    ],
                    '8,4' => [
                        'sum_price_excl' => 13.65,
                        'sum_tax' => 1.14,
                        'sum_price_incl' => 14.79,
                    ],
                    '10' => [
                        'sum_price_excl' => 1.65,
                        'sum_tax' => 0.17,
                        'sum_price_incl' => 1.82,
                    ],
                    '13' => [
                        'sum_price_excl' => 0.55,
                        'sum_tax' => 0.07,
                        'sum_price_incl' => 0.62,
                    ],
                    '20' => [
                        'sum_price_excl' => 0.84,
                        'sum_tax' => 0.16,
                        'sum_price_incl' => 1,
                    ],
                ]
            ],
            'taxRatesSums' => [
                'cash' => [
                    'sum_price_excl' => 21.23,
                    'sum_tax' => 1.54,
                    'sum_price_incl' => 22.77,
                ],
                'total' => [
                    'sum_price_excl' => 21.23,
                    'sum_tax' => 1.54,
                    'sum_price_incl' => 22.77,
                ],
            ],
        ];

        $this->assertEquals($result, $expected);

    }

    public function testGetPreparedTaxRatesForSumTableWithTaxBasedOnNetInvoiceSum(): void
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeConfiguration('FCS_DEPOSIT_TAX_RATE', 10);
        $this->changeConfiguration('FCS_TAX_BASED_ON_NET_INVOICE_SUM', 1);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');

        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->updateAll(['id_tax' => 2], ['active' => APP_ON]);
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->updateAll(['tax_rate' => 10], ['id_customer' => $customerId]);

        $this->prepareOrdersAndPaymentsForInvoice($customerId);

        $paidInCash = 0;
        $this->generateInvoice($customerId, $paidInCash);

        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoices = $invoicesTable->find('all',
            conditions: [
                'Invoices.id_customer' => $customerId,
            ],
            contain: [
                'InvoiceTaxes',
            ]
        )->toArray();

        $result = $invoicesTable->getPreparedTaxRatesForSumTable($invoices);

        $expected = [
            'taxRates' => [
                'cash' => [],
                'cashless' => [
                    '10' => [
                        'sum_price_excl' => 34.95,
                        'sum_tax' => 3.5,
                        'sum_price_incl' => 38.45,
                    ],
                ],
                'total' => [
                    '10' => [
                        'sum_price_excl' => 34.95,
                        'sum_tax' => 3.5,
                        'sum_price_incl' => 38.45,
                    ],
                ]
            ],
            'taxRatesSums' => [
                'cashless' => [
                    'sum_price_excl' => 34.95,
                    'sum_tax' => 3.5,
                    'sum_price_incl' => 38.45,
                ],
                'total' => [
                    'sum_price_excl' => 34.95,
                    'sum_tax' => 3.5,
                    'sum_price_incl' => 38.45,
                ],
            ],
        ];

        $this->assertEquals($result, $expected);

    }

}
