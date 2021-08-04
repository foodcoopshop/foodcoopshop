<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Application;
use App\Lib\HelloCash\HelloCash;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\PrepareInvoiceDataTrait;
use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;

class HelloCashTest extends AppCakeTestCase
{
    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;
    use PrepareInvoiceDataTrait;

    protected $HelloCash;
    protected $Invoice;
    protected $commandRunner;

    public function setUp(): void
    {
        parent::setUp();
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeConfiguration('FCS_HELLO_CASH_API_ENABLED', 1);
        $this->HelloCash = new HelloCash();
        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testGenerateReceipt()
    {
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;
        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $invoice = $this->Invoice->find('all', [])->first();

        $receiptHtml = $this->HelloCash->getReceipt($invoice->id, false);

        $this->assertRegExpWithUnquotedString('Beleg Nr.: ' . $invoice->invoice_number, $receiptHtml);
        $this->assertRegExpWithUnquotedString('Zahlungsart: Bar<br/>Bezahlt: 38,03 €', $receiptHtml);
        $this->assertRegExpWithUnquotedString('<td class="posTd1">Rindfleisch, 1,5kg</td>', $receiptHtml);
        $this->assertRegExpWithUnquotedString('<td class="posTd2">-5,20</td>', $receiptHtml);

        $this->commandRunner->run(['cake', 'queue', 'run', '-q']);

        $this->assertMailCount(1);

    }

    public function testGenerateInvoice()
    {
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 0;
        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $invoice = $this->Invoice->find('all', [])->first();

        $this->HelloCash->getInvoice($invoice->id, false);

        $this->commandRunner->run(['cake', 'queue', 'run', '-q']);

        $this->assertMailCount(2);
        $this->assertMailContainsAttachment('Rechnung_' . $invoice->invoice_number . '.pdf');

    }

    public function testCancelInvoice()
    {
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 0;
        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $invoice = $this->Invoice->find('all', [])->first();

        $this->HelloCash->getInvoice($invoice->id, false);
        $this->ajaxPost(
            '/admin/invoices/cancel/',
            [
                'invoiceId' => $invoice->id,
            ]
        );
        $response = json_decode($this->_response);

        $this->commandRunner->run(['cake', 'queue', 'run', '-q']);

        $invoice = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id' => $response->invoiceId,
            ],
            'contain' => [
                'CancelledInvoices',
            ]
        ])->first();
        $this->assertNotNull($invoice->email_status);

        $this->assertMailCount(3);
        $this->assertMailContainsAttachment('Rechnung_' . $invoice->cancelled_invoice->invoice_number . '.pdf');
        $this->assertMailContainsAttachment('Storno-Rechnung_' . $invoice->invoice_number . '.pdf');
        $this->assertMailContainsHtmlAt(1, 'Dein Kontostand: <b>61,97 €</b>');

    }

}
