<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\Console\CommandRunner;
use Cake\TestSuite\EmailTrait;

class SendInvoicesShellTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public $EmailLog;
    public $Order;
    public $commandRunner;

    public function setUp(): void
    {
        parent::setUp();
        $this->EmailLog = $this->getTableLocator()->get('EmailLogs');
        $this->Cart = $this->getTableLocator()->get('Carts');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testContentOfInvoice()
    {
        $this->loginAsSuperadmin();
        $this->get('/admin/manufacturers/getInvoice.pdf?manufacturerId=4&dateFrom=01.02.2018&dateTo=28.02.2018&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'invoice.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);
    }

    public function testSendInvoicesWithVariableMemberFee()
    {

        $this->prepareSendInvoices();

        $this->changeConfiguration('FCS_USE_VARIABLE_MEMBER_FEE', 1);
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.meatManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'variable_member_fee', 10);

        $this->commandRunner->run(['cake', 'send_invoices', '2018-03-11 10:20:30']);

        $orderDetails = $this->OrderDetail->find('all')->toArray();
        foreach($orderDetails as $orderDetail) {
            $expectedOrderState = ORDER_STATE_BILLED_CASHLESS;
            if ($orderDetail->id_order_detail == 4) {
                $expectedOrderState = ORDER_STATE_ORDER_PLACED;
            }
            $this->assertEquals($orderDetail->order_state, $expectedOrderState);
        }

        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(5, count($emailLogs), 'amount of sent emails wrong');
        $this->assertEmailLogs(
            $emailLogs[1],
            'Rechnung Nr. 0001',
            [
                'Demo-Fleisch-Hersteller_4_Rechnung_0001_FoodCoop-Test.pdf',
                'Content-Type: application/pdf',
            ],
            [
                Configure::read('test.loginEmailMeatManufacturer')
            ]
        );

        $this->loginAsSuperadmin(); //should still be logged in as superadmin but is not...
        $this->get($this->Slug->getActionLogsList() . '?dateFrom=11.03.2018&dateTo=11.03.2018');
        $this->assertResponseContains('4,09 €</b> (10%)');
        $this->assertResponseContains('0,62 €</b>');
        $this->assertResponseContains('11.03.2018 10:20:30');

        $this->get('/admin/manufacturers/getInvoice.pdf?manufacturerId=4&dateFrom=01.02.2018&dateTo=28.02.2018&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'invoiceWithVariableMemberFee.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);

    }

    public function testSendInvoicesNoInvoicesSentIfCalledMultipleTimes()
    {

        $this->prepareSendInvoices();
        $this->commandRunner->run(['cake', 'send_invoices', '2018-03-11 10:20:30']);
        $this->commandRunner->run(['cake', 'send_invoices', '2018-03-11 10:20:30']); // sic! run again

        $emailLogs = $this->EmailLog->find('all')->toArray();

        // no additional (would be 8) emails should be sent if called twice on same day
        $this->assertEquals(6, count($emailLogs), 'amount of sent emails wrong');

        $this->assertEmailLogs(
            $emailLogs[4],
            'wurden verschickt',
            [
                'dateFrom=11.03.2018'
            ],
            [
                Configure::read('test.loginEmailSuperadmin')
            ]
        );

    }

    private function prepareSendInvoices()
    {
        $this->loginAsSuperadmin();
        // add new orders
        $this->addProductToCart(346, 1);
        $this->addProductToCart(346, 1);
        $this->finishCart();
    }

}
