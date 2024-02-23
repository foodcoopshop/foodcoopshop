<?php
declare(strict_types=1);

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

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\PrepareAndTestInvoiceDataTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use App\Test\TestCase\Traits\GenerateOrderWithDecimalsInTaxRateTrait;

class SendInvoicesToCustomersCommandTest extends AppCakeTestCase
{

    protected $Invoice;
    protected $OrderDetail;
    protected $Product;

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;
    use PrepareAndTestInvoiceDataTrait;
    use GenerateOrderWithDecimalsInTaxRateTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->prepareSendingInvoices();
    }

    public function testContentOfInvoiceForPerson()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->loginAsSuperadmin();

        $customerId = Configure::read('test.superadminId');
        $this->prepareOrdersAndPaymentsForInvoice($customerId);

        $this->get('/admin/invoices/preview.pdf?customerId='.$customerId.'&paidInCash=1&currentDay=2018-02-02&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'customerInvoiceForPerson.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);

    }

    public function testContentOfInvoiceForPersonWithZeroTaxDepositRate()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeConfiguration('FCS_DEPOSIT_TAX_RATE', 0);
        $this->loginAsSuperadmin();

        $customerId = Configure::read('test.superadminId');
        $this->prepareOrdersAndPaymentsForInvoice($customerId);

        $this->get('/admin/invoices/preview.pdf?customerId='.$customerId.'&paidInCash=1&currentDay=2018-02-02&outputType=html');
        $this->assertResponseContains('<td align="left" width="142">Pfand geliefert</td><td align="left" width="81"></td><td align="right" width="58">1,00 €</td><td align="right" width="58">0,00 € (0%)</td><td align="right" width="58">1,00 €</td>');

    }

    public function testContentOfInvoiceForCompany()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'is_company', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'firstname', 'Company Name');
        $this->changeCustomer(Configure::read('test.superadminId'), 'lastname', 'Contact Name');
        $this->loginAsSuperadmin();

        $customerId = Configure::read('test.superadminId');
        $this->prepareOrdersAndPaymentsForInvoice($customerId);

        $this->get('/admin/invoices/preview.pdf?customerId='.$customerId.'&paidInCash=1&currentDay=2018-02-02&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'customerInvoiceForCompany.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);

    }

    public function testContentOfInvoiceWithDecimalsInTaxRate()
    {
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $this->generateOrderWithDecimalsInTaxRate($customerId);
        $this->prepareOrdersAndPaymentsForInvoice($customerId);

        $this->get('/admin/invoices/preview.pdf?customerId='.$customerId.'&paidInCash=1&currentDay=2018-02-02&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'customerWithDecimalsInTaxRate.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);
    }

    public function testContentOfInvoiceWithTaxBasedOnNetInvoiceSum()
    {

        $customerId = Configure::read('test.superadminId');

        $this->Product = $this->getTableLocator()->get('Products');
        $this->Product->updateAll(['id_tax' => 2], ['active' => APP_ON]);
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->OrderDetail->updateAll(['tax_rate' => 10], ['id_customer' => $customerId]);

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeConfiguration('FCS_DEPOSIT_TAX_RATE', 10);
        $this->changeConfiguration('FCS_TAX_BASED_ON_NET_INVOICE_SUM', 1);
        $this->loginAsSuperadmin();

        $this->prepareOrdersAndPaymentsForInvoice($customerId);

        $this->get('/admin/invoices/preview.pdf?customerId='.$customerId.'&paidInCash=1&currentDay=2018-02-02&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'customerInvoiceWithTaxBasedOnInvoiceSum.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);
    }

    public function testSendInvoicesWithExcludedFutureOrder()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->loginAsSuperadmin();
        Configure::write('app.paypalMeUsername', 'username');

        $customerId = Configure::read('test.superadminId');
        $this->prepareOrdersAndPaymentsForInvoice($customerId);

        $cronjobRunDay = '2018-02-02 10:20:30';

        // move one order detail in future - must be excluded from invoice
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetailEntity = $this->OrderDetail->get(1);
        $orderDetailEntity->pickup_day = '2018-02-09';
        $this->OrderDetail->save($orderDetailEntity);

        $this->Invoice = $this->getTableLocator()->get('Invoices');

        // never create invoices for zero price users
        $this->changeCustomer(Configure::read('test.superadminId'), 'shopping_price', 'ZP');
        $this->exec('send_invoices_to_customers "' . $cronjobRunDay . '"');
        $this->runAndAssertQueue();
        $this->assertEquals(0, count($this->Invoice->find('all')->toArray()));

        $this->changeCustomer(Configure::read('test.superadminId'), 'shopping_price', 'SP');
        $this->exec('send_invoices_to_customers "' . $cronjobRunDay . '"');
        $this->runAndAssertQueue();

        $pdfFilenameWithoutPath = '2018-02-02_Demo-Superadmin_92_Rechnung_2018-000001_FoodCoop-Test.pdf';
        $pdfFilenameWithPath = DS . '2018' . DS . '02' . DS . $pdfFilenameWithoutPath;
        $this->assertFileExists(Configure::read('app.folder_invoices') . $pdfFilenameWithPath);

        $invoice = $this->Invoice->find('all',
            conditions: [
                'Invoices.id_customer' => $customerId,
            ],
            contain: [
                'InvoiceTaxes',
            ],
        )->first();

        $this->assertEquals($invoice->id, 1);
        $this->assertEquals($invoice->id_manufacturer, 0);
        $this->assertEquals($invoice->created, new DateTime($cronjobRunDay));
        $this->assertEquals($invoice->invoice_number, '2018-000001');
        $this->assertEquals($invoice->filename, str_replace('\\', '/', $pdfFilenameWithPath));

        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[0], 0, 4.54, 0, 4.54);
        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[1], 10, 32.04, 3.21, 35.25);
        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[2], 13, 0.55, 0.07, 0.62);
        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[3], 20, -3.92, -0.78, -4.70);

        $this->assertMailCount(2);
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailSuperadmin'));
        $this->assertMailSubjectContainsAt(1, 'Rechnung Nr. 2018-000001, 02.02.2018');
        $this->assertMailContainsHtmlAt(1, 'Guthaben beträgt <b>61,97 €</b>');
        $this->assertMailContainsHtmlAt(1, 'https://paypal.me/username/35.71EUR');
        $this->assertMailContainsAttachment($pdfFilenameWithoutPath);

        $this->getAndAssertOrderDetailsAfterInvoiceGeneration($invoice->id, 4);
        $this->getAndAssertPaymentsAfterInvoiceGeneration($customerId);

        // call again
        $this->exec('send_invoices_to_customers ' . $cronjobRunDay);
        $this->runAndAssertQueue();

        $this->assertEquals(1, count($this->Invoice->find('all')->toArray()));

    }

}
