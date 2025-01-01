<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use App\Model\Entity\OrderDetail;

class SendInvoicesToManufacturersCommandTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->prepareSendingInvoices();
    }

    public function testContentOfInvoice(): void
    {
        $this->loginAsSuperadmin();
        $this->get('/admin/manufacturers/getInvoice.pdf?manufacturerId=4&dateFrom=01.02.2018&dateTo=28.02.2018&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'manufacturerInvoice.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);
    }

    public function testSendInvoicesWithVariableMemberFee(): void
    {

        $this->prepareSendInvoices();

        $this->changeConfiguration('FCS_USE_VARIABLE_MEMBER_FEE', 1);
        $customersTable = $this->getTableLocator()->get('Customers');
        $meatManufacturerId = $customersTable->getManufacturerIdByCustomerId(Configure::read('test.meatManufacturerId'));
        $this->changeManufacturer($meatManufacturerId, 'variable_member_fee', 10);
        $milkManufacturerId = $customersTable->getManufacturerIdByCustomerId(Configure::read('test.milkManufacturerId'));
        $this->changeManufacturer($milkManufacturerId, 'send_invoice', 0);

        $this->exec('send_invoices_to_manufacturers "2018-03-11 10:20:30"');
        $this->runAndAssertQueue();

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $orderDetailsTable->find('all')->toArray();
        foreach($orderDetails as $orderDetail) {
            $expectedOrderState = OrderDetail::STATE_BILLED_CASHLESS;
            if ($orderDetail->id_order_detail == 4) {
                $expectedOrderState = OrderDetail::STATE_OPEN;
            }
            $this->assertEquals($orderDetail->order_state, $expectedOrderState);
        }

        $this->assertMailCount(4);
        $this->assertMailSubjectContainsAt(2, 'Rechnung Nr. 0001, ' . Configure::read('app.timeHelper')->getLastMonthNameAndYear());
        $this->assertMailContainsAttachment('2018-03-11_Demo-Gemuese-Hersteller_5_Rechnung_0001_FoodCoop-Test.pdf');
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailMeatManufacturer'));

        $this->loginAsSuperadmin(); //should still be logged in as superadmin but is not...
        $this->get($this->Slug->getActionLogsList() . '?dateFrom=11.03.2018&dateTo=11.03.2018');
        $this->assertResponseContains('4,09 €</b> (10%)');
        $this->assertResponseContains('0,62 €</b>');
        $this->assertResponseContains('11.03.2018 10:20:30');
        $this->assertResponseContains('<td>0001</td><td></td>');

        $this->get('/admin/manufacturers/getInvoice.pdf?manufacturerId='.$meatManufacturerId.'&dateFrom=01.02.2018&dateTo=28.02.2018&outputType=html');
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'manufacturerInvoiceWithVariableMemberFee.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);

        $this->assertResponseContains($expectedResult);

    }

    public function testSendInvoicesNoInvoicesSentIfCalledMultipleTimes(): void
    {

        $this->prepareSendInvoices();
        $this->exec('send_invoices_to_manufacturers 2018-03-11 10:20:30');
        $this->runAndAssertQueue();
        $this->exec('send_invoices_to_manufacturers 2018-03-11 10:20:30'); // sic! run again
        $this->runAndAssertQueue();

        // no additional (would be 8) emails should be sent if called twice on same day
        $this->assertMailCount(6);
        $this->assertMailSubjectContainsAt(1, 'Rechnungen für '.$this->Time->getLastMonthNameAndYear().' wurden verschickt');
        $this->assertMailContainsAt(1, 'dateFrom=11.03.2018');
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailSuperadmin'));

    }

    private function prepareSendInvoices(): void
    {
        $this->loginAsSuperadmin();
        // add new orders
        $this->addProductToCart(346, 1);
        $this->addProductToCart(346, 1);
        $this->finishCart();
    }

}
