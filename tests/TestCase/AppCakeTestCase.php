<?php
namespace App\Test\TestCase;

//require_once('test_files/config/test.config.php');

use App\Auth\AppPasswordHasher;
use App\Lib\SimpleBrowser\AppSimpleBrowser;
use App\View\Helper\MyHtmlHelper;
use App\View\Helper\MyTimeHelper;
use App\View\Helper\SlugHelper;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\View\View;

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
abstract class AppCakeTestCase extends \PHPUnit\Framework\TestCase
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
     */
    public function setUp()
    {
        parent::setUp();

        $this->initSimpleBrowser();

        self::resetTestDatabaseData();

        $View = new View();
        $this->Slug = new SlugHelper($View);
        $this->Html = new MyHtmlHelper($View);
        $this->Time = new MyTimeHelper($View);
        $this->Configuration = TableRegistry::get('Configurations');
        $this->Customer = TableRegistry::get('Customers');
        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $this->generatePasswordHashes();
    }

    protected static function resetTestDatabaseData()
    {
        self::$dbConnection = ConnectionManager::get('test');
        self::$testDumpDir = ROOT . DS .  'tests' . DS . 'test_files' . DS . 'config' . DS . 'sql' . DS;
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

    protected function assertAccessDeniedWithRedirectToLoginForm()
    {
        $this->assertRegExpWithUnquotedString('Zugriff verweigert, bitte melde dich an.', $this->browser->getContent());
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
    protected function assertEmailLogs($emailLog, $expectedSubjectPattern = '', $expectedMessagePatterns = [], $expectedToEmails = [], $expectedCcEmails = [], $expectedBccEmails = [])
    {

        $fromAddress = json_decode($emailLog['EmailLogs']['from_address']);
        $toAddress = json_decode($emailLog['EmailLogs']['to_address']);
        $ccAddress = json_decode($emailLog['EmailLogs']['cc_address']);
        $bccAddress = json_decode($emailLog['EmailLogs']['bcc_address']);

        $this->assertNotEmpty($fromAddress, 'email from_address must not be empty');

        if ($expectedSubjectPattern != '') {
            $this->assertRegExpWithUnquotedString($expectedSubjectPattern, $emailLog['EmailLogs']['subject'], 'email subject wrong');
        }
        foreach ($expectedMessagePatterns as $expectedMessagePattern) {
            $this->assertRegExpWithUnquotedString($expectedMessagePattern, $emailLog['EmailLogs']['message'], 'email message wrong');
        }

        $preparedToAddresses = [];
        foreach ($toAddress as $email) {
            $preparedToAddresses[] = $email;
        }
        $this->assertEquals($preparedToAddresses, $expectedToEmails, 'email to_addresses wrong', 0, 0, true);

        $preparedCcAddresses = [];
        foreach ($ccAddress as $email) {
            $preparedCcAddresses[] = $email;
        }
        $this->assertEquals($preparedCcAddresses, $expectedCcEmails, 'email cc_addresses wrong', 0, 0, true);

        $preparedBccAddresses = [];
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
        $ph = new AppPasswordHasher();
        $query = 'UPDATE '.$this->Customer->tablePrefix.'customer SET passwd = :passwd;';
        $params = [
            'passwd' => $ph->hash(Configure::read('test.loginPassword'))
        ];
        $statement = self::$dbConnection->prepare($query);
        $statement->execute($params);
    }

    protected function changeReadOnlyConfiguration($configKey, $value)
    {
        $query = 'UPDATE ' . $this->Configuration->tablePrefix . $this->Configuration->useTable.' SET value = :value WHERE name = :configKey';
        $params = [
            'value' => $value,
            'configKey' => $configKey
        ];
        $statement = self::$dbConnection->prepare($query);
        return $statement->execute($params);
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
        $configuration = $this->Configuration->find('all', [
            'conditions' => [
                'Configuration.active' => APP_ON,
                'Configuration.name' => $configKey
            ]
        ])->first();
        $this->browser->post('/admin/configurations/edit/'.$configuration['Configurations']['id_configuration'], [
            'Configurations' => [
                'value' => $newValue
            ],
            'referer' => ''
        ]);
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
        $query = 'UPDATE fcs_manufacturer SET holiday_from = :dateFrom, holiday_to = :dateTo WHERE id_manufacturer = :manufacturerId;';
        $params = [
            'manufacturerId' => $manufacturerId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];
        $statement = self::$dbConnection->prepare($query);
        $statement->execute($params);
    }

    /**
     * @param String $cakeShell
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockShell($cakeShell)
    {

        $out = $this->createMock('ConsoleOutput', [], [], '', false);
        $in = $this->createMock('ConsoleInput', [], [], '', false);

        return $this->createMock(
            $cakeShell,
            ['in', 'err', 'createFile', '_stop', 'clear'],
            [$out, $out, $in]
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
        $this->browser->ajaxPost('/warenkorb/ajaxAdd', [
            'data' => [
                'productId' => $productId,
                'amount' => $amount
            ]
        ]);
        return $this->browser->getJsonDecodedContent();
    }


    protected function finishCart($general_terms_and_conditions_accepted = true, $cancellation_terms_accepted = true, $comment = '')
    {
        $this->browser->post(
            $this->Slug->getCartFinish(),
            [
                'data' => [
                    'Orders' => [
                        'general_terms_and_conditions_accepted' => $general_terms_and_conditions_accepted,
                        'cancellation_terms_accepted' => $cancellation_terms_accepted,
                        'comment' => $comment
                    ]
                ]
            ]
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
        $this->browser->ajaxPost('/admin/products/editPrice', [
            'data' => [
                'productId' => $productId,
                'price' => $price
            ]
        ]);
        return $this->browser->getJsonDecodedContent();
    }

    protected function changeManufacturer($manufacturerId, $option, $value)
    {
        $query = 'UPDATE ' . $this->Manufacturer->tablePrefix . $this->Manufacturer->useTable.' SET '.$option.' = :value WHERE id_manufacturer = :manufacturerId';
        $params = [
            'value' => $value,
            'manufacturerId' => $manufacturerId
        ];
        $statement = self::$dbConnection->prepare($query);
        return $statement->execute($params);
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
