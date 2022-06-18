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
use App\Lib\HelloCash\HelloCash;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\PrepareAndTestInvoiceDataTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\Utility\Hash;

class HelloCashTest extends AppCakeTestCase
{
    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;
    use PrepareAndTestInvoiceDataTrait;

    protected $HelloCash;
    protected $Invoice;

    public function setUp(): void
    {
        if (
            Configure::read('app.helloCashAtCredentials.username') == ''
            || Configure::read('app.helloCashAtCredentials.password') == ''
            || Configure::read('app.helloCashAtCredentials.cashier_id') == ''
            ) {
                $this->markTestSkipped('The credentials for HelloCash are missing.');
            }
        parent::setUp();
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeConfiguration('FCS_HELLO_CASH_API_ENABLED', 1);
        $this->HelloCash = new HelloCash();
        $this->Invoice = $this->getTableLocator()->get('Invoices');
    }

    public function testGenerateReceipt()
    {
        $this->changeCustomer(Configure::read('test.superadminId'), 'invoices_per_email_enabled', 0);
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;
        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);
        $this->assertSessionHasKey('invoiceRouteForAutoPrint');

        $invoice = $this->Invoice->find('all', [])->first();

        $receiptHtml = $this->HelloCash->getReceipt($invoice->id, false);

        $this->assertRegExpWithUnquotedString('Beleg Nr.: ' . $invoice->invoice_number, $receiptHtml);
        $this->assertRegExpWithUnquotedString('Zahlungsart: Bar<br/>Bezahlt: 38,03 €', $receiptHtml);
        $this->assertRegExpWithUnquotedString('<td class="posTd1">Rindfleisch, 1,5kg</td>', $receiptHtml);
        $this->assertRegExpWithUnquotedString('<td class="posTd2">-5,20</td>', $receiptHtml);

        $this->runAndAssertQueue();
        $this->assertMailCount(1);
    }

    public function testGenerateReceiptForCompany()
    {
        $this->changeCustomer(Configure::read('test.superadminId'), 'invoices_per_email_enabled', 0);
        $this->changeCustomer(Configure::read('test.superadminId'), 'is_company', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'firstname', 'Company Name');
        $this->changeCustomer(Configure::read('test.superadminId'), 'lastname', 'Contact Name');

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;
        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);
        $this->assertSessionHasKey('invoiceRouteForAutoPrint');

        $invoice = $this->Invoice->find('all', [])->first();

        $receiptHtml = $this->HelloCash->getReceipt($invoice->id, false);

        $this->assertRegExpWithUnquotedString('Beleg Nr.: ' . $invoice->invoice_number, $receiptHtml);
        $this->assertRegExpWithUnquotedString('Company Name', $receiptHtml);
        $this->assertRegExpWithUnquotedString('Contact Name', $receiptHtml);
        $this->assertRegExpWithUnquotedString('Zahlungsart: Bar<br/>Bezahlt: 38,03 €', $receiptHtml);
        $this->assertRegExpWithUnquotedString('<td class="posTd1">Rindfleisch, 1,5kg</td>', $receiptHtml);
        $this->assertRegExpWithUnquotedString('<td class="posTd2">-5,20</td>', $receiptHtml);

        $this->runAndAssertQueue();
        $this->assertMailCount(1);
    }


    public function testGenerateInvoiceSendPerEmailActivated()
    {
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 0;
        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $invoice = $this->Invoice->find('all', [])->first();
        $this->HelloCash->getInvoice($invoice->id, false);
        $this->runAndAssertQueue();

        $this->assertMailCount(2);
        $this->assertMailContainsAttachment('Rechnung_' . $invoice->invoice_number . '.pdf');
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailSuperadmin'));
        $this->assertMailContainsHtmlAt(1, 'Guthaben beträgt <b>61,97 €</b>');

        $invoice = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id' => $invoice->id,
            ],
            'contain' => [
                'InvoiceTaxes',
            ]
        ])->first();
        $this->assertGreaterThan(1, $invoice->id);

        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[0], 10, 33.69, 3.38, 37.07);
        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[1], 0, 4.54, 0, 4.54);
        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[2], 13, 0.55, 0.07, 0.62);
        $this->doAssertInvoiceTaxes($invoice->invoice_taxes[3], 20, -3.5, -0.7, -4.2);

        $this->getAndAssertOrderDetailsAfterInvoiceGeneration($invoice->id, 5);
        $this->getAndAssertPaymentsAfterInvoiceGeneration($customerId);

    }

    public function testGenerateInvoiceSendPerEmailDeactivated()
    {
        $this->changeCustomer(Configure::read('test.superadminId'), 'invoices_per_email_enabled', 0);
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 0;
        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $invoice = $this->Invoice->find('all', [])->first();
        $this->HelloCash->getInvoice($invoice->id, false);
        $this->runAndAssertQueue();

        $this->assertMailCount(1);

        $invoice = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id' => $invoice->id,
            ],
            'contain' => [
                'InvoiceTaxes',
            ]
        ])->first();
        $this->assertEquals($invoice->email_status, 'deaktiviert');
    }

    public function testCancelInvoice()
    {
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 0;
        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $invoice = $this->Invoice->find('all', [
            'contain' => [
                'InvoiceTaxes',
                'OrderDetails',
            ],
        ])->first();
        $orderDetailIds = Hash::extract($invoice, 'order_details.{n}.id_order_detail');

        $this->Payment = $this->getTableLocator()->get('Payments');
        $payments = $this->Payment->find('all', [
            'conditions' => [
                'Payments.invoice_id' => $invoice->id,
            ],
        ])->toArray();
        $paymentIds = Hash::extract($payments, '{n}.id');

        $this->HelloCash->getInvoice($invoice->id, false);

        $this->ajaxPost(
            '/admin/invoices/cancel/',
            [
                'invoiceId' => $invoice->id,
            ]
        );
        $response = json_decode($this->_response);
        $this->runAndAssertQueue();

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
        $this->assertMailContainsHtmlAt(1, 'Guthaben beträgt <b>61,97 €</b>');
        $this->assertMailContainsHtmlAt(2, 'Guthaben beträgt <b>61,97 €</b>');

        $this->getAndAssertOrderDetailsAfterCancellation($orderDetailIds);
        $this->getAndAssertPaymentsAfterCancellation($paymentIds);

    }

    public function testUpdatingExistingUserOnGeneratingReceipt()
    {
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;
        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $invoiceA = $this->Invoice->find('all', [
            'contain' => [
                'Customers',
            ],
            'order' => ['Invoices.created' => 'DESC'],
        ])->first();

        $receiptHtml = $this->HelloCash->getReceipt($invoiceA->id, false);

        $this->assertGreaterThan(0, $invoiceA->customer->user_id_registrierkasse);

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->get($customerId);
        $customer->firstname = 'Superadmin Firstname Changed';
        $customer->lastname = 'Superadmin Lastname Changed';
        $this->Customer->save($customer);

        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $invoiceB = $this->Invoice->find('all', [
            'contain' => [
                'Customers',
            ],
            'order' => ['Invoices.created' => 'DESC'],
        ])->first();
        $receiptHtml = $this->HelloCash->getReceipt($invoiceB->id, false);

        $this->assertEquals($invoiceA->customer->user_id_registrierkasse, $invoiceB->customer->user_id_registrierkasse);
        $this->assertRegExpWithUnquotedString($customer->firstname, $receiptHtml);
        $this->assertRegExpWithUnquotedString($customer->lastname, $receiptHtml);

    }

    public function testCreatingUserThatDoesNotExistOnGeneratingReceipt()
    {
        $this->changeCustomer(Configure::read('test.superadminId'), 'invoices_per_email_enabled', 0);
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;
        $this->prepareOrdersAndPaymentsForInvoice($customerId);

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->get($customerId);
        $customer->user_id_registrierkasse = 1234567890;
        $this->Customer->save($customer);

        $this->generateInvoice($customerId, $paidInCash);
        $this->assertSessionHasKey('invoiceRouteForAutoPrint');

        $invoiceA = $this->Invoice->find('all', [
            'contain' => [
                'Customers',
            ],
        ])->first();

        $this->HelloCash->getReceipt($invoiceA->id, false);
        $this->assertNotEquals($customer->user_id_registrierkasse, $invoiceA->customer->user_id_registrierkasse);

    }

}
