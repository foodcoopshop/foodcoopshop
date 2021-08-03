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
use App\Test\TestCase\Traits\PrepareInvoiceDataTrait;
use Cake\Core\Configure;

class HelloCashTest extends AppCakeTestCase
{
    use AppIntegrationTestTrait;
    use LoginTrait;
    use PrepareInvoiceDataTrait;

    protected $HelloCash;
    protected $Invoice;

    public function setUp(): void
    {
        parent::setUp();

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeConfiguration('FCS_HELLO_CASH_API_ENABLED', 1);
        $this->HelloCash = new HelloCash();
        $this->Invoice = $this->getTableLocator()->get('Invoices');
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

    }

}
