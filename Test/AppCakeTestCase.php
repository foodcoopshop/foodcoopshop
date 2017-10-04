<?php

require_once('test_files/Config/test.config.php');

App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('AppSimpleBrowser', 'Lib/SimpleBrowser');
App::uses('SlugHelper', 'View/Helper');
App::uses('MyHtmlHelper', 'View/Helper');
App::uses('MyTimeHelper', 'View/Helper');
App::uses('ConnectionManager', 'Model');
App::uses('Configuration', 'Model');
App::uses('Manufacturer', 'Model');

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

    public $Time;

    public $Customer;

    public $Manufacturer;

    public $browser;

    /**
     * called before every test method
     *
     * {@inheritDoc}
     * @see CakeTestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();

        $this->initSimpleBrowser();

        self::resetTestDatabaseData();

        $Controller = new Controller();
        $View = new View($Controller);
        $this->Slug = new SlugHelper($View);
        $this->Html = new MyHtmlHelper($View);
        $this->Time = new MyTimeHelper($View);
        $this->Configuration = new Configuration();
        $this->Customer = new Customer();
        $this->Manufacturer = new Manufacturer();
        $this->generatePasswordHashes();
    }

    protected static function resetTestDatabaseData()
    {
        self::$dbConnection = ConnectionManager::getDataSource('test');
        self::$testDumpDir = APP . 'Test' . DS . 'test_files' . DS . 'Config' . DS . 'sql' . DS;
        self::importDump(self::$testDumpDir . 'test-db-data.sql');
    }

    public function initSimpleBrowser()
    {
        $this->browser = new AppSimpleBrowser();
        $this->browser->addHeader('x-unit-test-mode: true');
        $this->browser->loginEmail = Configure::read('test.loginEmailSuperadmin');
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

    protected function assert200OkHeader()
    {
        $this->assertRegExp('/HTTP\/1.1 200 OK/', $this->browser->getHeaders(), 'header 200 ok not found');
    }

    protected function assert401UnauthorizedHeader()
    {
        $this->assertRegExp('/HTTP\/1.1 401 Unauthorized/', $this->browser->getHeaders(), 'header 401 unauthorized not found');
    }

    protected function assert403ForbiddenHeader()
    {
        $this->assertRegExp('/HTTP\/1.1 403 Forbidden/', $this->browser->getHeaders(), 'header 403 forbidden not found');
    }

    protected function assert404NotFoundHeader()
    {
        $this->assertRegExp('/HTTP\/1.1 404 Not Found/', $this->browser->getHeaders(), 'header 404 not found not found');
    }

    protected function assertRedirectToLoginPage()
    {
        $this->assertUrl($this->browser->baseUrl . $this->Slug->getLogin(), $this->browser->getUrl(), 'redirect to login page failed');
    }

    protected function assertJsonAccessRestricted()
    {
        $response = $this->browser->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString('Du bist nicht angemeldet.', $response->msg, 'login check does not work');
    }

    protected function assertJsonOk()
    {
        $response = $this->browser->getJsonDecodedContent();
        $this->assertEquals(1, $response->status, 'json status should be "1", msg: ' . $response->msg);
    }

    /**
     * back tick ` allows using forward slash in $unquotedString
     * @param string $unquotedString
     * @param string $response
     * @param string $msg
     */
    protected function assertRegExpWithUnquotedString($unquotedString, $response, $msg = '')
    {
        $this->assertRegExp('`' . preg_quote($unquotedString) . '`', $response, $msg);
    }

    /**
     * back tick ` allows using forward slash in $unquotedString
     * @param string $unquotedString
     * @param string $response
     * @param string $msg
     */
    protected function assertNotRegExpWithUnquotedString($unquotedString, $response, $msg = '')
    {
        $this->assertNotRegExp('`' . preg_quote($unquotedString) . '`', $response, $msg);
    }

    protected function assertUrl($url, $expectedUrl, $msg = '')
    {
        $this->assertEquals($url, $expectedUrl, $msg);
    }

    /**
     * array $testPages
     * @return void
     */
    protected function assertPagesFor404($testPages)
    {
        foreach ($testPages as $url) {
            $this->browser->get($url);
            $html = $this->browser->getContent();
            $this->assertRegExp('/wurde leider nicht gefunden./', $html);
            $headers = $this->browser->getHeaders();
            $this->assertRegExp("/404 Not Found/", $headers);
        }
    }

    /**
     * array $testPages
     * asserts html for errors or missing elements that need to occur
     * @return void
     */
    protected function assertPagesForErrors($testPages)
    {
        foreach ($testPages as $url) {
            $this->browser->get($url);
            $html = $this->browser->getContent();
            $this->assertNotRegExp('/class="cake-stack-trace"|class="cake-error"|\bFatal error\b|undefined|exception \'[^\']+\' with message|\<strong\>(Error|Exception)\s*:\s*\<\/strong\>|Parse error|Not Found|\/app\/views\/errors\/|error in your SQL syntax|ERROR!|^\<\/body\>/', $html);
        }
    }

    /**
     *
     * @param array $emailLog
     * @param string $expectedSubjectPattern
     * @param array $expectedMessagePatterns
     * @param array $expectedToEmails
     * @param array $expectedCcEmails
     * @param array $expectedBccEmails
     */
    protected function assertEmailLogs($emailLog, $expectedSubjectPattern = '', $expectedMessagePatterns = array(), $expectedToEmails = array(), $expectedCcEmails = array(), $expectedBccEmails = array())
    {

        $fromAddress = json_decode($emailLog['EmailLog']['from_address']);
        $toAddress = json_decode($emailLog['EmailLog']['to_address']);
        $ccAddress = json_decode($emailLog['EmailLog']['cc_address']);
        $bccAddress = json_decode($emailLog['EmailLog']['bcc_address']);

        $this->assertNotEmpty($fromAddress, 'email from_address must not be empty');

        if ($expectedSubjectPattern != '') {
            $this->assertRegExpWithUnquotedString($expectedSubjectPattern, $emailLog['EmailLog']['subject'], 'email subject wrong');
        }
        foreach ($expectedMessagePatterns as $expectedMessagePattern) {
            $this->assertRegExpWithUnquotedString($expectedMessagePattern, $emailLog['EmailLog']['message'], 'email message wrong');
        }

        $preparedToAddresses = array();
        foreach ($toAddress as $email) {
            $preparedToAddresses[] = $email;
        }
        $this->assertEquals($preparedToAddresses, $expectedToEmails, 'email to_addresses wrong', 0, 0, true);

        $preparedCcAddresses = array();
        foreach ($ccAddress as $email) {
            $preparedCcAddresses[] = $email;
        }
        $this->assertEquals($preparedCcAddresses, $expectedCcEmails, 'email cc_addresses wrong', 0, 0, true);

        $preparedBccAddresses = array();
        foreach ($bccAddress as $email) {
            $preparedBccAddresses[] = $email;
        }
        $this->assertEquals($preparedBccAddresses, $expectedBccEmails, 'email bcc_addresses wrong', 0, 0, true);
    }

    /**
     * due to different app.cookieKeys, logins would not work with a defined hash
     */
    protected function generatePasswordHashes()
    {
        App::uses('AppPasswordHasher', 'Controller/Component/Auth');
        $ph = new AppPasswordHasher();
        $sql = 'UPDATE '.$this->Customer->tablePrefix.'customer SET passwd = :passwd;';
        $params = array(
            'passwd' => $ph->hash(Configure::read('test.loginPassword'))
        );
        $this->Customer->getDataSource()->fetchAll($sql, $params);
    }

    protected function changeReadOnlyConfiguration($configKey, $value)
    {
        $query = 'UPDATE ' . $this->Configuration->tablePrefix . $this->Configuration->useTable.' SET value = :value WHERE name = :configKey';
        $params = array(
            'value' => $value,
            'configKey' => $configKey
        );
        return $this->Configuration->getDataSource()->fetchAll($query, $params);
    }

    /**
     * needs to login as superadmin and logs user out automatically
     * eventually create a new browser instance for this method
     *
     * @param string $configKey
     * @param string $newValue
     */
    protected function changeConfiguration($configKey, $newValue)
    {
        $this->loginAsSuperadmin();
        $configuration = $this->Configuration->find('first', array(
            'conditions' => array(
                'Configuration.active' => APP_ON,
                'Configuration.name' => $configKey
            )
        ));
        $this->browser->post('/admin/configurations/edit/'.$configuration['Configuration']['id_configuration'], array(
            'Configuration' => array(
                'value' => $newValue
            ),
            'referer' => ''
        ));
        $this->assertRegExpWithUnquotedString('Die Einstellung wurde erfolgreich geändert.', $this->browser->getContent(), 'configuration edit failed');
        $this->Configuration->loadConfigurations();
        $this->logout();
    }

    protected function debug($content)
    {
        pr($content);
        ob_flush();
    }

    protected function changeManufacturerHolidayMode($manufacturerId, $dateFrom = null, $dateTo = null)
    {
        $sql = 'UPDATE fcs_manufacturer SET holiday_from = :dateFrom, holiday_to = :dateTo WHERE id_manufacturer = :manufacturerId;';
        $params = array(
            'manufacturerId' => $manufacturerId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        );
        $this->Customer->getDataSource()->fetchAll($sql, $params);
    }

    /**
     * @param String $cakeShell
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockShell($cakeShell)
    {
        App::uses('ConsoleOutput', 'Console');
        App::uses('ConsoleInput', 'Console');
        App::uses('Shell', 'Console');
        App::uses('AppShell', 'Console/Command');

        $out = $this->getMock('ConsoleOutput', array(), array(), '', false);
        $in = $this->getMock('ConsoleInput', array(), array(), '', false);

        return $this->getMock(
            $cakeShell,
            array('in', 'err', 'createFile', '_stop', 'clear'),
            array($out, $out, $in)
        );
    }

    /**
     *
     * @param int $productId
     * @param int $amount
     * @return json string
     */
    protected function addProductToCart($productId, $amount)
    {
        $this->browser->ajaxPost('/warenkorb/ajaxAdd', array(
            'data' => array(
                'productId' => $productId,
                'amount' => $amount
            )
        ));
        return $this->browser->getJsonDecodedContent();
    }


    protected function finishCart($general_terms_and_conditions_accepted = true, $cancellation_terms_accepted = true, $comment = '')
    {
        $this->browser->post(
            $this->Slug->getCartFinish(),
            array(
                'data' => array(
                    'Order' => array(
                        'general_terms_and_conditions_accepted' => $general_terms_and_conditions_accepted,
                        'cancellation_terms_accepted' => $cancellation_terms_accepted,
                        'comment' => $comment
                    )
                )
            )
        );
    }

    /**
     *
     * @param int $productId
     * @param double $price
     * @return json string
     */
    protected function changeProductPrice($productId, $price)
    {
        $this->browser->ajaxPost('/admin/products/editPrice', array(
            'data' => array(
                'productId' => $productId,
                'price' => $price
            )
        ));
        return $this->browser->getJsonDecodedContent();
    }

    protected function changeManufacturerOption($manufacturerId, $option, $value)
    {
        $query = 'UPDATE ' . $this->Manufacturer->tablePrefix . $this->Manufacturer->useTable.' SET '.$option.' = :value WHERE id_manufacturer = :manufacturerId';
        $params = array(
            'value' => $value,
            'manufacturerId' => $manufacturerId
        );
        return $this->Manufacturer->getDataSource()->fetchAll($query, $params);
    }

    protected function logout()
    {
        $this->browser->doFoodCoopShopLogout();
    }

    protected function loginAsSuperadmin()
    {
        $this->browser->loginEmail = Configure::read('test.loginEmailSuperadmin');
        $this->browser->doFoodCoopShopLogin();
    }

    protected function loginAsAdmin()
    {
        $this->browser->loginEmail = Configure::read('test.loginEmailAdmin');
        $this->browser->doFoodCoopShopLogin();
    }

    protected function loginAsCustomer()
    {
        $this->browser->loginEmail = Configure::read('test.loginEmailCustomer');
        $this->browser->doFoodCoopShopLogin();
    }

    protected function loginAsMeatManufacturer()
    {
        $this->browser->loginEmail = Configure::read('test.loginEmailMeatManufacturer');
        $this->browser->doFoodCoopShopLogin();
    }

    protected function loginAsVegetableManufacturer()
    {
        $this->browser->loginEmail = Configure::read('test.loginEmailVegetableManufacturer');
        $this->browser->doFoodCoopShopLogin();
    }
}
