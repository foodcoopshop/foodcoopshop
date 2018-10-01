<?php
namespace App\Test\TestCase;

use App\Lib\SimpleBrowser\AppSimpleBrowser;
use App\View\Helper\MyHtmlHelper;
use App\View\Helper\MyTimeHelper;
use App\View\Helper\SlugHelper;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\ORM\TableRegistry;
use Cake\View\View;

require_once ROOT . DS . 'tests' . DS . 'config' . DS . 'test.config.php';

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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
abstract class AppCakeTestCase extends \PHPUnit\Framework\TestCase
{

    protected $dbConnection;

    protected $testDumpDir;

    protected $appDumpDir;

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

        $View = new View();
        $this->Slug = new SlugHelper($View);
        $this->Html = new MyHtmlHelper($View);
        $this->Time = new MyTimeHelper($View);
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');

        $this->resetTestDatabaseData();
        $this->resetLogs();
        
    }
    
    private function getLogFile($name)
    {
        return new File(ROOT . DS . 'logs' . DS . $name . '.log');
    }

    protected function resetLogs()
    {
        $this->getLogFile('debug')->write('');
        $this->getLogFile('error')->write('');
    }
    
    public function tearDown()
    {
        parent::tearDown();
        $this->assertLogFilesForErrors();
    }
    
    protected function assertLogFilesForErrors()
    {
        $log = $this->getLogFile('debug')->read(true, 'r');
        $log .= $this->getLogFile('error')->read(true, 'r');
        $this->assertNotRegExp('/(Warning|Notice)/', $log);
    }
    
    protected function resetTestDatabaseData()
    {

        $this->dbConnection = ConnectionManager::get('test');
        $this->testDumpDir = ROOT . DS .  'tests' . DS . 'config' . DS . 'sql' . DS;
        $this->importDump($this->testDumpDir . 'test-db-data.sql');

        // regenerate password hashes
        $ph = new DefaultPasswordHasher();
        $query = 'UPDATE fcs_customer SET passwd = :passwd;';
        $params = [
            'passwd' => $ph->hash(Configure::read('test.loginPassword'))
        ];
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);

    }

    public function initSimpleBrowser()
    {
        $this->browser = new AppSimpleBrowser();
        $this->browser->addHeader('x-unit-test-mode: true');
        $this->browser->loginEmail = Configure::read('test.loginEmailSuperadmin');
        $this->browser->loginPassword = Configure::read('test.loginPassword');
    }

    protected function importDump($file)
    {
        $this->dbConnection->query(file_get_contents($file));
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

    /**
     * since cakephp v3 the redirect page contains a get param "redirect"
     */
    protected function assertRedirectToLoginPage()
    {
        $this->assertRegExpWithUnquotedString($this->browser->baseUrl . $this->Slug->getLogin(), $this->browser->getUrl(), 'redirect to login page failed');
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
        $fromAddress = json_decode($emailLog->from_address);
        $toAddress = json_decode($emailLog->to_address);
        $ccAddress = json_decode($emailLog->cc_address);
        $bccAddress = json_decode($emailLog->bcc_address);

        $this->assertNotEmpty($fromAddress, 'email from_address must not be empty');

        if ($expectedSubjectPattern != '') {
            $this->assertRegExpWithUnquotedString($expectedSubjectPattern, $emailLog->subject, 'email subject wrong');
        }
        if (!empty($expectedMessagePatterns)) {
            foreach ($expectedMessagePatterns as $expectedMessagePattern) {
                $this->assertRegExpWithUnquotedString($expectedMessagePattern, $emailLog->message, 'email message wrong');
            }
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

    protected function changeReadOnlyConfiguration($configKey, $value)
    {
        $query = 'UPDATE ' . $this->Configuration->getTable() . ' SET value = :value WHERE name = :configKey';
        $params = [
            'value' => $value,
            'configKey' => $configKey
        ];
        $statement = $this->dbConnection->prepare($query);
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
        $query = 'UPDATE fcs_configuration SET value = :newValue WHERE name = :configKey;';
        $params = [
            'newValue' => $newValue,
            'configKey' => $configKey
        ];
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);
        $this->Configuration->loadConfigurations();
        $this->logout();
    }

    protected function debug($content)
    {
        pr($content);
        ob_flush();
    }

    protected function changeManufacturerNoDeliveryDays($manufacturerId, $noDeliveryDays = '')
    {
        $query = 'UPDATE fcs_manufacturer SET no_delivery_days = :noDeliveryDays WHERE id_manufacturer = :manufacturerId;';
        $params = [
            'manufacturerId' => $manufacturerId,
            'noDeliveryDays' => $noDeliveryDays
        ];
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);
    }

    /**
     *
     * @param int $productId
     * @param int $amount
     * @return string
     */
    protected function addProductToCart($productId, $amount)
    {
        $this->browser->ajaxPost('/warenkorb/ajaxAdd', [
            'productId' => $productId,
            'amount' => $amount
        ]);
        return $this->browser->getJsonDecodedContent();
    }


    protected function finishCart($general_terms_and_conditions_accepted = 1, $cancellation_terms_accepted = 1, $comment = '', $timebaseCurrencyTimeSum = null)
    {
        $data = [
            'Carts' => [
                'general_terms_and_conditions_accepted' => $general_terms_and_conditions_accepted,
                'cancellation_terms_accepted' => $cancellation_terms_accepted
            ],
        ];

        if ($comment != '') {
            $data['Carts']['pickup_day_entities'][0] = [
                'customer_id' => $this->browser->getLoggedUserId(),
                'pickup_day' => Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb(),
                'comment' => $comment
            ];
        }

        if ($timebaseCurrencyTimeSum !== null) {
            $data['Carts']['timebased_currency_seconds_sum_tmp'] = $timebaseCurrencyTimeSum;
        }

        $this->browser->post(
            $this->Slug->getCartFinish(), $data
        );
    }
    
    
    protected function getCartById($cartId)
    {
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $cartId
            ],
            'contain' => [
                'CartProducts.OrderDetails.OrderDetailTaxes',
                'CartProducts.OrderDetails.OrderDetailUnits',
                'CartProducts.OrderDetails.TimebasedCurrencyOrderDetails'
            ]
        ])->first();
        return $cart;
    }
    

    /**
     * @param string $productId
     * @param double $price
     * @param boolean $pricePerUnitEnabled
     * @param number $priceInclPerUnit
     * @param string $priceUnitName
     * @param number $priceUnitAmount
     * @param number $priceQuantityInUnits
     * @return mixed
     */
    protected function changeProductPrice($productId, $price, $usePricePerUnit = false, $pricePerUnitEnabled = false, $priceInclPerUnit = 0, $priceUnitName = '', $priceUnitAmount = 0, $priceQuantityInUnits = 0)
    {
        $this->browser->ajaxPost('/admin/products/editPrice', [
            'productId' => $productId,
            'price' => $price,
            'pricePerUnitEnabled' => $pricePerUnitEnabled,
            'priceInclPerUnit' => $priceInclPerUnit,
            'priceUnitName' => $priceUnitName,
            'priceUnitAmount' => $priceUnitAmount,
            'priceQuantityInUnits' => $priceQuantityInUnits
        ]);
        return $this->browser->getJsonDecodedContent();
    }
    
    protected function changeProductDeliveryRhythm($productId, $deliveryRhythmType, $deliveryRhythmFirstDeliveryDay = '', $deliveryRhythmOrderPossibleUntil = '')
    {
        $this->browser->ajaxPost('/admin/products/editDeliveryRhythm', [
            'productId' => $productId,
            'deliveryRhythmType' => $deliveryRhythmType,
            'deliveryRhythmFirstDeliveryDay' => $deliveryRhythmFirstDeliveryDay,
            'deliveryRhythmOrderPossibleUntil' => $deliveryRhythmOrderPossibleUntil
        ]);
        return $this->browser->getJsonDecodedContent();
    }

    protected function changeManufacturer($manufacturerId, $field, $value)
    {
        $query = 'UPDATE ' . $this->Manufacturer->getTable().' SET '.$field.' = :value WHERE id_manufacturer = :manufacturerId';
        $params = [
            'value' => $value,
            'manufacturerId' => $manufacturerId
        ];
        $statement = $this->dbConnection->prepare($query);
        return $statement->execute($params);
    }

    protected function changeCustomer($customerId, $field, $value)
    {
        $query = 'UPDATE ' . $this->Customer->getTable().' SET '.$field.' = :value WHERE id_customer = :customerId';
        $params = [
            'value' => $value,
            'customerId' => $customerId
        ];
        $statement = $this->dbConnection->prepare($query);
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

    protected function prepareTimebasedCurrencyConfiguration($reducedMaxPercentage)
    {
        $this->changeConfiguration('FCS_TIMEBASED_CURRENCY_ENABLED', 1);
        $this->changeConfiguration('FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE', '10,50');
        $this->changeCustomer(Configure::read('test.superadminId'), 'timebased_currency_enabled', 1);
        $this->changeCustomer(Configure::read('test.customerId'), 'timebased_currency_enabled', 1);
        $this->changeManufacturer(5, 'timebased_currency_enabled', 1);
        $this->changeManufacturer(4, 'timebased_currency_enabled', 1);
        $this->changeManufacturer(4, 'timebased_currency_max_percentage', $reducedMaxPercentage);
    }

}
