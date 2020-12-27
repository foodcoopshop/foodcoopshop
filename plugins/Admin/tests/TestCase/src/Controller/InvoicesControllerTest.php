<?php

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\Utility\Hash;
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

        $this->ajaxPost(
            '/admin/invoices/cancel/',
            [
                'invoiceId' => $invoice->id,
            ]
        );
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

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

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_invoice' => $invoices[0]->id,
            ],
        ])->toArray();
        $this->assertEquals(0, count($orderDetails));

        $orderDetails = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail IN' => $orderDetailIds,
            ],
            'contain' => [
                'OrderDetailTaxes',
            ],
        ])->toArray();
        $this->assertEquals(5, count($orderDetails));

        foreach($orderDetails as $orderDetail) {
            $this->assertTrue($orderDetail->total_price_tax_excl >= 0);
            $this->assertTrue($orderDetail->total_price_tax_incl >= 0);
            if (!empty($orderDetail->order_detail_tax)) {
                $this->assertTrue($orderDetail->order_detail_tax->unit_amount >= 0);
                $this->assertTrue($orderDetail->order_detail_tax->total_amount >= 0);
            }
        }

        $payments = $this->Payment->find('all', [
            'conditions' => [
                'Payments.invoice_id' => $invoices[0]->id,
            ],
        ])->toArray();
        $this->assertEquals(0, count($payments));

        $payments = $this->Payment->find('all', [
            'conditions' => [
                'Payments.id IN' => $paymentIds,
            ],
        ])->toArray();
        $this->assertEquals(2, count($payments));

        foreach($payments as $payment) {
            $this->assertTrue($payment->amount >= 0);
        }

        $currentDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        $formattedCurrentDay = Configure::read('app.timeHelper')->formatToDateShort($currentDay);
        $this->assertMailSubjectContainsAt(1, 'Rechnung Nr. 2020-000001, ' . $formattedCurrentDay);
        $this->assertMailSubjectContainsAt(2, 'Storno-Rechnung Nr. 2020-000002, ' . $formattedCurrentDay);

    }

}
