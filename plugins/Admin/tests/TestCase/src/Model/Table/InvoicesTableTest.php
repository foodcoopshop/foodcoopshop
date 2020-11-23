<?php

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\PrepareInvoiceDataTrait;
use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class InvoicesTableTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;
    use PrepareInvoiceDataTrait;

    public $Invoice;

    public function setUp(): void
    {
        parent::setUp();
        $this->Invoice = $this->getTableLocator()->get('Invoices');
    }

    public function testGetNextInvoiceNumberForCustomerInvoicesDoNotExist()
    {
        $result = $this->Invoice->getNextInvoiceNumberForCustomer('2020', []);
        $this->assertEquals($result, '2020-000001');
    }

    public function testGetNextInvoiceNumberForCustomerDifferentYearAndInvoiceAlreadyExists()
    {
        $invoice = $this->Invoice->newEmptyEntity();
        $invoice->invoice_number = '2020-000001';
        $result = $this->Invoice->getNextInvoiceNumberForCustomer('2021', $invoice);
        $this->assertEquals($result, '2021-000001');
    }

    public function testGetNextInvoiceNumberForCustomerSameYearAndInvoiceAlreadyExists()
    {
        $invoice = $this->Invoice->newEmptyEntity();
        $invoice->invoice_number = '2020-000001';
        $result = $this->Invoice->getNextInvoiceNumberForCustomer('2020', $invoice);
        $this->assertEquals($result, '2020-000002');
    }

    public function testGetNextInvoiceNumberForManufacturerInvoicesDoNotExist()
    {
        $result = $this->Invoice->getNextInvoiceNumberForManufacturer([]);
        $this->assertEquals($result, '0001');
    }

    public function testGetNextInvoiceNumberForManufacturerInvoicesAlreadyExist()
    {
        $invoice = $this->Invoice->newEmptyEntity();
        $invoice->invoice_number = '0001';
        $result = $this->Invoice->getNextInvoiceNumberForManufacturer([$invoice]);
        $this->assertEquals($result, '0002');
    }

    public function testGetPreparedTaxRatesForSumTable()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;
        $this->generateInvoice($customerId, $paidInCash);

        $invoices = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id_customer' => $customerId,
            ],
            'contain' => [
                'InvoiceTaxes',
            ]
        ])->toArray();

        $result = $this->Invoice->getPreparedTaxRatesForSumTable($invoices);

        $expected = [
            'taxRates' => [
                'cashless' => [],
                'cash' => [
                    '0' => [
                        'sum_price_excl' => 4.54,
                        'sum_tax' => 0,
                        'sum_price_incl' => 4.54,
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
                    'sum_price_excl' => 7.58,
                    'sum_tax' => 0.4,
                    'sum_price_incl' => 7.98,
                ],
                'total' => [
                    'sum_price_excl' => 7.58,
                    'sum_tax' => 0.4,
                    'sum_price_incl' => 7.98,
                ],
            ],
        ];

        $this->assertEquals($result, $expected);

    }

}
