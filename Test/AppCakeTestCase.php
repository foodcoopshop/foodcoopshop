<?php

require_once ('test_files/Config/test.config.php');

App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('AppSimpleBrowser', 'Lib/SimpleBrowser');
App::uses('SlugHelper', 'View/Helper');
App::uses('MyHtmlHelper', 'View/Helper');
App::uses('ConnectionManager', 'Model');

/**
 * AppCakeTestCase
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppCakeTestCase extends CakeTestCase
{

    protected static $dbConnection;

    protected static $testDumpDir;

    protected static $appDumpDir;

    public $Slug;

    public $Html;

    public $Customer;

    public $browser;

    public function setUp()
    {
        parent::setUp();
        
        $this->initSimpleBrowser();
        
        $Controller = new Controller();
        $View = new View($Controller);
        $this->Slug = new SlugHelper($View);
        $this->Html = new MyHtmlHelper($View);
        $this->Customer = new Customer();
    }

    protected static function initTestDatabase()
    {
        self::$dbConnection = ConnectionManager::getDataSource('test');
        self::$testDumpDir = APP . 'Test' . DS . 'test_files' . DS . 'Config' . DS . 'sql' . DS;
        self::$appDumpDir = APP . 'Config' . DS . 'sql' . DS . '_helper' . DS;
        self::importDump(self::$appDumpDir . 'init-orders.sql');
        self::importDump(self::$testDumpDir . '02-init-stock-available.sql');
        self::importDump(self::$testDumpDir . '03-init-manufacturers.sql');
    }

    public function initSimpleBrowser()
    {
        $this->browser = new AppSimpleBrowser();
        $this->browser->loginEmail = Configure::read('test.loginEmail');
        $this->browser->loginPassword = Configure::read('test.loginPassword');
    }

    protected static function importDump($file)
    {
        self::$dbConnection->query(file_get_contents($file));
    }

    protected function assertJsonError()
    {
        $response = $this->browser->getJsonDecodedContent();
        $this->assertEquals(0, $response->status, 'json status should be "0"');
    }

    protected function assertJsonAccessRestricted()
    {
        $response = $this->browser->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString('Du bist nicht angemeldet.', $response->msg, 'login check does not work');
    }

    protected function assertJsonOk()
    {
        $response = $this->browser->getJsonDecodedContent();
        $this->assertEquals(1, $response->status, 'json status should be "1"');
    }
    
    protected function assertRegExpWithUnquotedString($unquotetString, $response, $msg='')
    {
        $this->assertRegExp('/' . preg_quote($unquotetString) . '/', $response, $msg);
    }
    
    protected function assertUrl($url, $expectedUrl, $msg='') {
        $this->assertEquals($this->browser->baseUrl . $expectedUrl, $url, $msg);
    }
    
}

?>