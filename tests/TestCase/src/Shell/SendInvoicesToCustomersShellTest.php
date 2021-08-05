<?php
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

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\PrepareAndTestInvoiceDataTrait;
use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\EmailTrait;

class SendInvoicesToCustomersShellTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;
    use PrepareAndTestInvoiceDataTrait;

    public $commandRunner;

    public function setUp(): void
    {
        parent::setUp();
        $this->prepareSendingInvoices();
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testContentOfInvoice()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->loginAsSuperadmin();

        $customerId = Configure::read('test.superadminId');
        $this->prepareOrdersAndPaymentsForInvoice($customerId);

        $this->get('/admin/invoices/preview.pdf?customerId='.$customerId.'&paidInCash=1&currentDay=2018-02-02&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'customerInvoice.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);
    }

    public function testSendInvoicesWithExcludedFutureOrder()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->loginAsSuperadmin();

        $customerId = Configure::read('test.superadminId');
        $this->prepareOrdersAndPaymentsForInvoice($customerId);

        $cronjobRunDay = '2018-02-02 10:20:30';

        // move one order detail in future - must be excluded from invoice
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $query = 'UPDATE ' . $this->OrderDetail->getTable().' SET pickup_day = :pickupDay WHERE id_order_detail IN(1);';
        $params = [
            'pickupDay' => '2018-02-09',
        ];
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);

        $this->commandRunner->run(['cake', 'send_invoices_to_customers', $cronjobRunDay]);
        $this->commandRunner->run(['cake', 'queue', 'run', '-q']);

        $pdfFilenameWithoutPath = '2018-02-02_Demo-Superadmin_92_Rechnung_2018-000001_FoodCoop-Test.pdf';
        $pdfFilenameWithPath = DS . '2018' . DS . '02' . DS . $pdfFilenameWithoutPath;
        $this->assertFileExists(Configure::read('app.folder_invoices') . $pdfFilenameWithPath);

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoice = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id_customer' => $customerId,
            ],
            'contain' => [
                'InvoiceTaxes',
            ],
        ])->first();

        $this->assertEquals($invoice->id, 1);
        $this->assertEquals($invoice->id_manufacturer, 0);
        $this->assertEquals($invoice->created, new FrozenTime($cronjobRunDay));
        $this->assertEquals($invoice->invoice_number, '2018-000001');
        $this->assertEquals($invoice->filename, str_replace('\\', '/', $pdfFilenameWithPath));

        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[0], 0, 4.54, 0, 4.54);
        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[1], 10, 32.04, 3.21, 35.25);
        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[2], 13, 0.55, 0.07, 0.62);
        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[3], 20, -3.92, -0.78, -4.70);

        $this->assertMailCount(2);
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailSuperadmin'));
        $this->assertMailSubjectContainsAt(1, 'Rechnung Nr. 2018-000001, 02.02.2018');
        $this->assertMailContainsAttachment($pdfFilenameWithoutPath);

        $this->getAndAssertOrderDetailsAfterInvoiceGeneration($invoice->id, 4);
        $this->getAndAssertPaymentsAfterInvoiceGeneration($customerId);

        // call again
        $this->commandRunner->run(['cake', 'send_invoices_to_customers', $cronjobRunDay]);
        $this->commandRunner->run(['cake', 'queue', 'run', '-q']);

        $this->assertEquals(1, count($this->Invoice->find('all')->toArray()));

    }

}
