<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\PrepareAndTestInvoiceDataTrait;
use Cake\Core\Configure;
use App\Model\Entity\Customer;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ListsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;
    use PrepareAndTestInvoiceDataTrait;

    protected $Invoice;

    public function setUp(): void
    {
        parent::setUp();
        $this->prepareSendingOrderLists();
        $this->prepareSendingInvoices();
    }

    public function testAccessDownloadableInvoice()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);

        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.customerId');
        $paidInCash = 1;

        $this->prepareOrdersAndPaymentsForInvoice($customerId);
        $this->generateInvoice($customerId, $paidInCash);

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoice = $this->Invoice->find('all', conditions: [
            'Invoices.id_customer' => $customerId,
        ])->first();

        $this->loginAsCustomer();
        $downloadUrl = Configure::read('app.slugHelper')->getInvoiceDownloadRoute($invoice->filename);
        $this->get($downloadUrl);
        $this->assertResponseOk();
        $this->assertContentType('pdf');

        $this->loginAsSuperadmin();
        $this->get($downloadUrl);
        $this->assertResponseOk();
        $this->assertContentType('pdf');

        // change admin to customer to test access from different customer
        $customerEntity = $this->Customer->get(Configure::read('test.adminId'));
        $customerEntity->id_default_group = Customer::GROUP_MEMBER;
        $this->Customer->save($customerEntity);

        $this->loginAsAdmin();
        $this->get($downloadUrl);
        $this->assertResponseCode(401);

    }

    /**
     * this method is not split up into separated test methods because
     * generating the pdfs for the test needs a lot of time
     */
    public function testAccessOrderListPageAndDownloadableFile()
    {
        $this->changeManufacturer(4, 'anonymize_customers', 1);
        $this->exec('send_order_lists 2018-01-31');
        $this->runAndAssertQueue();

        $listPageUrl = $this->Slug->getOrderLists().'?dateFrom=02.02.2018';

        $path = realpath(Configure::read('app.folder_order_lists').DS.'2018'.DS.'02');
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

        $files = [];
        foreach ($objects as $name => $object) {
            if (!preg_match('/\.pdf$/', $name)) {
                continue;
            }
            $files[] = str_replace(Configure::read('app.folder_order_lists'), '', $object->getPathName());
        }
        sort($files);

        $orderListDownloadUrlClearText = Configure::read('app.slugHelper')->getOrderListDownloadRoute($files[0]);
        $orderListDownloadUrlAnonymized = Configure::read('app.slugHelper')->getOrderListDownloadRoute($files[6]);

        // check list page as manufacturer
        $this->loginAsMeatManufacturer();
        $this->get($listPageUrl);
        $this->assertResponseContains('<b>1</b> Datensatz');
        $this->assertResponseContains('<td>Demo Fleisch-Hersteller</td>');
        $this->assertResponseNotContains('<td>Demo Gemüse-Hersteller</td>');
        $this->assertResponseNotContains('<td>Demo Milch-Hersteller</td>');

        // check downloadable file as correct manufacturer
        $this->get($orderListDownloadUrlAnonymized);
        $this->assertResponseOk();
        $this->assertContentType('pdf');

        // check if clear text file is not downloadable with anonymized configuration
        $this->get($orderListDownloadUrlClearText);
        $this->assertResponseCode(401);

        // check if anonymized file is not downloadable with clear text configuration
        $this->changeManufacturer(4, 'anonymize_customers', 0);
        $this->get($orderListDownloadUrlAnonymized);
        $this->assertResponseCode(401);

        // check downloadable file as wrong manufacturer
        $this->loginAsVegetableManufacturer();
        $this->get($orderListDownloadUrlClearText);
        $this->assertResponseCode(401);

        // check downloadable file as admin
        $this->loginAsAdmin();
        $this->get($orderListDownloadUrlClearText);
        $this->assertResponseOk();
        $this->assertContentType('pdf');

        $this->get($orderListDownloadUrlAnonymized);
        $this->assertResponseOk();
        $this->assertContentType('pdf');

        // check list page as admin
        $this->get($listPageUrl);
        $this->assertResponseContains('<b>4</b> Datensätze');
        $this->assertResponseContains('<td>Demo Fleisch-Hersteller</td>');
        $this->assertResponseContains('<td>Demo Gemüse-Hersteller</td>');
        $this->assertResponseContains('<td>Demo Milch-Hersteller</td>');

    }

    /**
     * this method is not split up into separated test methods because
     * generating the pdfs for the test needs a lot of time
     */
    public function testAccessManufacturerInvoicesPageAndDownloadableFile()
    {
        $this->loginAsSuperadmin();
        $this->addProductToCart(346, 1);
        $this->addProductToCart(346, 1);
        $this->finishCart();
        $this->logout();

        $this->exec('send_invoices_to_manufacturers 2018-03-11 10:20:30');
        $this->runAndAssertQueue();

        $listPageUrl = $this->Slug->getManufacturerInvoices();

        $path = realpath(Configure::read('app.folder_invoices'));
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

        $files = [];
        foreach ($objects as $name => $object) {
            if (!preg_match('/\.pdf$/', $name)) {
                continue;
            }
            $files[] = str_replace(Configure::read('app.folder_invoices'), '', $object->getPathName());
        }
        sort($files);

        $invoiceDownloadUrlMeatManufacturer = Configure::read('app.slugHelper')->getInvoiceDownloadRoute($files[0]);
        $invoiceDownloadUrlMilkManufacturer = Configure::read('app.slugHelper')->getInvoiceDownloadRoute($files[2]);

        // check list page as manufacturer
        $this->loginAsMeatManufacturer();
        $this->get($listPageUrl);
        $this->assertResponseContains('<b>1</b> Datensatz');
        $this->assertResponseContains('<td>Demo Fleisch-Hersteller</td>');
        $this->assertResponseNotContains('<td>Demo Gemüse-Hersteller</td>');
        $this->assertResponseNotContains('<td>Demo Milch-Hersteller</td>');

        // check downloadable file as correct manufacturer
        $this->get($invoiceDownloadUrlMeatManufacturer);
        $this->assertResponseOk();
        $this->assertContentType('pdf');

        // check downloadable file as wrong manufacturer
        $this->get($invoiceDownloadUrlMilkManufacturer);
        $this->assertResponseCode(401);

        // check downloadable file as admin
        $this->loginAsAdmin();
        $this->get($invoiceDownloadUrlMeatManufacturer);
        $this->assertResponseOk();
        $this->assertContentType('pdf');

    }

}
