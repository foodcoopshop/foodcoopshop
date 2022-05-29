<?php

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\PrepareAndTestInvoiceDataTrait;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ListsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;
    use PrepareAndTestInvoiceDataTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->prepareSendingOrderLists();
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
        $invoice = $this->Invoice->find('all', [
            'conditions' => [
                'Invoices.id_customer' => $customerId,
            ],
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
        $customerEntity->id_default_group = CUSTOMER_GROUP_MEMBER;
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
        $this->exec('send_order_lists 2018-01-31');
        $this->runAndAssertQueue();

        $listPageUrl = $this->Slug->getOrderLists().'?dateFrom=02.02.2018';

        $folder = new Folder(Configure::read('app.folder_order_lists').DS.'2018'.DS.'02');
        $objects = $folder->read();
        $downloadFileName = $objects[1][0];
        $orderListDownloadUrl = '/admin/lists/getOrderList?file=2018/02/'.$downloadFileName;

        // check list page as manufacturer
        $this->loginAsMeatManufacturer();
        $this->get($listPageUrl);
        $this->assertResponseContains('<b>1</b> Datensatz');
        $this->assertResponseContains('<td>Demo Fleisch-Hersteller</td>');
        $this->assertResponseNotContains('<td>Demo Gemüse-Hersteller</td>');
        $this->assertResponseNotContains('<td>Demo Milch-Hersteller</td>');

        // check downloadable file as correct manufacturer
        $this->get($orderListDownloadUrl);
        $this->assertResponseOk();
        $this->assertContentType('pdf');

        // check downloadable file as wrong manufacturer
        $this->loginAsVegetableManufacturer();
        $this->get($orderListDownloadUrl);
        $this->assertResponseCode(401);

        // check downloadable file as admin
        $this->loginAsAdmin();
        $this->get($orderListDownloadUrl);
        $this->assertResponseOk();
        $this->assertContentType('pdf');

        // check list page as admin
        $this->get($listPageUrl);
        $this->assertResponseContains('<b>3</b> Datensätze');
        $this->assertResponseContains('<td>Demo Fleisch-Hersteller</td>');
        $this->assertResponseContains('<td>Demo Gemüse-Hersteller</td>');
        $this->assertResponseContains('<td>Demo Milch-Hersteller</td>');

    }

}
