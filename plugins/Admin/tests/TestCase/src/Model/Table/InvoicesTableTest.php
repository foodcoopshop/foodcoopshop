<?php

use App\Test\TestCase\AppCakeTestCase;

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
        $result = $this->Invoice->getNextInvoiceNumberForCustomer('2021', [$invoice]);
        $this->assertEquals($result, '2021-000001');
    }

    public function testGetNextInvoiceNumberForCustomerSameYearAndInvoiceAlreadyExists()
    {
        $invoice = $this->Invoice->newEmptyEntity();
        $invoice->invoice_number = '2020-000001';
        $result = $this->Invoice->getNextInvoiceNumberForCustomer('2020', [$invoice]);
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

}
