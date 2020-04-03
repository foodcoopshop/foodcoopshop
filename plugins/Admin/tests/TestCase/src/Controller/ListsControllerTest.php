<?php

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
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
        $listPageUrl = $this->Slug->getOrderLists().'?dateFrom=02.02.2018';
        
        $folder = new Folder(Configure::read('app.folder_order_lists').DS.'2018'.DS.'02');
        $objects = $folder->read();
        $orderListDownloadUrl = '/admin/lists/getOrderList?file=2018/02/'.$objects[1][0];
        
        // check list page as manufacturer
        $this->loginAsMeatManufacturer();
        $this->httpClient->get($listPageUrl);
        $this->assertRegExpWithUnquotedString('<b>1</b> Datensatz', $this->httpClient->getContent());
        $this->assertRegExpWithUnquotedString('<td>Demo Fleisch-Hersteller</td>', $this->httpClient->getContent());
        $this->assertDoesNotMatchRegularExpressionWithUnquotedString('<td>Demo Gemüse-Hersteller</td>', $this->httpClient->getContent());
        $this->assertDoesNotMatchRegularExpressionWithUnquotedString('<td>Demo Milch-Hersteller</td>', $this->httpClient->getContent());
        
        // check downloadable file as correct manufacturer
        $this->httpClient->get($orderListDownloadUrl);
        $this->assert200OkHeader();
    
        // check downloadable file as wrong manufacturer
        $this->loginAsVegetableManufacturer();
        $this->httpClient->get($orderListDownloadUrl);
        $this->assert401UnauthorizedHeader();
        
        // check downloadable file as admin
        $this->loginAsAdmin();
        $this->httpClient->get($orderListDownloadUrl);
        $this->assert200OkHeader();
        
        // check list page as admin
        $this->httpClient->get($listPageUrl);
        $this->assertRegExpWithUnquotedString('<b>3</b> Datensätze', $this->httpClient->getContent());
        $this->assertRegExpWithUnquotedString('<td>Demo Fleisch-Hersteller</td>', $this->httpClient->getContent());
        $this->assertRegExpWithUnquotedString('<td>Demo Gemüse-Hersteller</td>', $this->httpClient->getContent());
        $this->assertRegExpWithUnquotedString('<td>Demo Milch-Hersteller</td>', $this->httpClient->getContent());
        
    }

    
}
