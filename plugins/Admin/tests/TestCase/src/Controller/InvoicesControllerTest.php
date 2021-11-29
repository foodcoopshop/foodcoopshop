<?php

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\Utility\Hash;
use App\Test\TestCase\Traits\PrepareAndTestInvoiceDataTrait;
use App\Test\TestCase\Traits\QueueTrait;

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
class InvoicesControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;
    use PrepareAndTestInvoiceDataTrait;
    use QueueTrait;

    public $commandRunner;

    public function testGeneratePaidInCashSavedCorrectly()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;

        $this->generateInvoice($customerId, $paidInCash);

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoice = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id_customer' => $customerId,
            ],
        ])->first();

        $this->assertEquals($invoice->paid_in_cash, $paidInCash);

        // assert that payment was automatically added
        $this->Customer = $this->getTableLocator()->get('Customers');
        $credit = $this->Customer->getCreditBalance($customerId);
        $this->assertEquals(100, $credit);

    }

    public function testCancel()
    {

        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.superadminId');
        $paidInCash = 1;

        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoice = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id_customer' => $customerId,
            ],
            'contain' => [
                'OrderDetails',
                'InvoiceTaxes',
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

        $response = $this->ajaxPost(
            '/admin/invoices/cancel/',
            [
                'invoiceId' => $invoice->id,
            ]
        );
        $response = json_decode($this->_response);
        $this->runAndAssertQueue();

        $invoices = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id_customer' => $customerId,
            ],
            'contain' =>
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
        $this->assertMailContainsHtmlAt(1, 'Guthaben beträgt <b>61,97 €</b>');
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailSuperadmin'));
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailSuperadmin'));

        $invoice = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id' => (int) $response->invoiceId,
            ],
        ])->first();
        $this->assertNotNull($invoice->email_status);

    }

}
