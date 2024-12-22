<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\Utility\Hash;
use App\Test\TestCase\Traits\PrepareAndTestInvoiceDataTrait;

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
class InvoicesControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;
    use PrepareAndTestInvoiceDataTrait;

    protected $Invoice;

    public function testGeneratePaidInCashSavedCorrectly()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'invoices_per_email_enabled', 0);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;

        $this->generateInvoice($customerId, $paidInCash);
        $this->assertSessionHasKey('invoiceRouteForAutoPrint');

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoice = $this->Invoice->find('all', conditions: [
            'Invoices.id_customer' => $customerId,
        ])->first();

        $this->assertEquals($invoice->paid_in_cash, $paidInCash);

        // assert that payment was automatically added
        $customersTable = $this->getTableLocator()->get('Customers');
        $credit = $customersTable->getCreditBalance($customerId);
        $this->assertEquals(100, $credit);

    }

    public function testGenerateInvoiceSendPerEmailDeactivated()
    {

        $this->changeCustomer(Configure::read('test.superadminId'), 'invoices_per_email_enabled', 0);
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 0;

        $this->generateInvoice($customerId, $paidInCash);

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoice = $this->Invoice->find('all', conditions: [
            'Invoices.id_customer' => $customerId,
        ])->first();

        $this->exec('send_invoices_to_customers');
        $this->runAndAssertQueue();

        $this->assertEquals($invoice->email_status, 'deaktiviert');
        $this->assertMailCount(0);

    }

    public function testCancel()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;

        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoice = $this->Invoice->find('all',
        conditions: [
            'Invoices.id_customer' => $customerId,
        ],
        contain: [
            'OrderDetails',
            'InvoiceTaxes',
        ])->first();
        $orderDetailIds = Hash::extract($invoice, 'order_details.{n}.id_order_detail');

        $this->Payment = $this->getTableLocator()->get('Payments');
        $payments = $this->Payment->find('all', conditions: [
            'Payments.invoice_id' => $invoice->id,
        ])->toArray();
        $paymentIds = Hash::extract($payments, '{n}.id');

        $this->ajaxPost(
            '/admin/invoices/cancel/',
            [
                'invoiceId' => $invoice->id,
            ]
        );
        $response = json_decode($this->_response->getBody()->__toString());
        $this->runAndAssertQueue();

        $invoices = $this->Invoice->find('all',
            conditions: [
                'Invoices.id_customer' => $customerId,
            ],
            contain: [
                'InvoiceTaxes',
            ],
        )->toArray();
        $this->assertEquals(2, $invoices[0]->cancellation_invoice_id);

        $this->assertEquals($invoices[0]->sum_price_incl * -1, $invoices[1]->sum_price_incl);
        $this->assertEquals($invoices[0]->sum_price_excl * -1, $invoices[1]->sum_price_excl);
        $this->assertEquals($invoices[0]->sum_tax * -1, $invoices[1]->sum_tax);

        $this->getAndAssertOrderDetailsAfterCancellation($orderDetailIds);
        $this->getAndAssertPaymentsAfterCancellation($paymentIds);

        $currentDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        $formattedCurrentDay = Configure::read('app.timeHelper')->formatToDateShort($currentDay);
        $currentYear = date('Y', strtotime($currentDay));
        $this->assertMailSubjectContainsAt(1, 'Rechnung Nr. ' . $currentYear . '-000001, ' . $formattedCurrentDay);
        $this->assertMailSubjectContainsAt(2, 'Storno-Rechnung Nr. ' . $currentYear . '-000002, ' . $formattedCurrentDay);
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailSuperadmin'));
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailSuperadmin'));

        $invoice = $this->Invoice->find('all',
            conditions: [
                'Invoices.id' => (int) $response->invoiceId,
            ],
        )->first();
        $this->assertNotNull($invoice->email_status);

        // assert that automatically added payment was removed
        $customersTable = $this->getTableLocator()->get('Customers');
        $credit = $customersTable->getCreditBalance($customerId);
        $this->assertEquals(61.97, $credit);

    }

    public function testCancelInvoiceEmailDisabled()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'invoices_per_email_enabled', 0);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;

        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $this->assertSessionHasKey('invoiceRouteForAutoPrint');

        $this->assertMailCount(1);

    }

    public function testDownloadAsZipFile() {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'invoices_per_email_enabled', 0);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;

        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $this->get('/admin/invoices/downloadAsZipFile?dateFrom=' . date('Y-m-d') . '&dateTo=' . date('Y-m-d'));

        $this->assertResponseOk();
        $this->assertContentType('application/zip');
        $this->assertHeaderContains('Content-Disposition', 'inline; filename="Rechnungen.zip"');

    }

    public function testIndexAndMyIndex() {
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;

        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $this->get(Configure::read('app.slugHelper')->getInvoices());
        $this->assertResponseOk();
        $this->assertResponseContains('2024-000001');
        $this->assertResponseContains('38,03');

        $this->get(Configure::read('app.slugHelper')->getMyInvoices());
        $this->assertResponseOk();
        $this->assertResponseContains('2024-000001');
        $this->assertResponseContains('38,03');

        $this->loginAsAdmin();
        $this->get(Configure::read('app.slugHelper')->getMyInvoices());
        $this->assertResponseOk();
        $this->assertResponseNotContains('2024-000001');
        $this->assertResponseNotContains('38,03');

    }

}
