<?php

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Console\CommandRunner;
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

    public $commandRunner;

    public function setUp(): void
    {
        parent::setUp();
        $this->prepareSendingOrderLists();
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    /**
     * this method is not split up into separated test methods because
     * generating the pdfs (commandRunner) for the test needs a lot of time
     */
    public function testAccessOrderListPageAndDownloadableFile()
    {
        $this->commandRunner->run(['cake', 'send_order_lists', '2018-01-31']);
        $this->commandRunner->run(['cake', 'queue', 'runworker', '-q']);

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
        Configure::write('Error.log', false);
        $this->get($orderListDownloadUrl);
        Configure::write('Error.log', true);
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
