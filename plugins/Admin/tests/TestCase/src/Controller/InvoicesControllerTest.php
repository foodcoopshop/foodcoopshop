<?php

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use App\Test\TestCase\Traits\PrepareInvoiceDataTrait;

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
    use PrepareInvoiceDataTrait;

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
        ])->first();

        $this->ajaxPost(
            '/admin/invoices/cancel/',
            [
                'invoiceId' => $invoice->id,
            ]
        );
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

        $invoice = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id_customer' => $customerId,
            ],
        ])->first();

        $this->assertEquals(2, $invoice->cancellation_invoice_id);

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_invoice' => $invoice->id,
            ],
        ])->toArray();
        $this->assertEquals(0, count($orderDetails));

        $this->Payment = $this->getTableLocator()->get('Payments');
        $payments = $this->Payment->find('all', [
            'conditions' => [
                'Payments.invoice_id' => $invoice->id,
            ],
        ])->toArray();
        $this->assertEquals(0, count($payments));

        $currentDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        $formattedCurrentDay = Configure::read('app.timeHelper')->formatToDateShort($currentDay);
        $this->assertMailSentWithAt(1, 'Rechnung Nr. 2020-000001, ' . $formattedCurrentDay, 'originalSubject');
        $this->assertMailSentWithAt(2, 'Storno-Rechnung Nr. 2020-000002, ' . $formattedCurrentDay, 'originalSubject');

    }

}
