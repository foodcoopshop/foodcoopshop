<?php
namespace App\Test\TestCase;

use App\View\Helper\MyHtmlHelper;
use App\View\Helper\MyTimeHelper;
use App\View\Helper\PricePerUnitHelper;
use App\View\Helper\SlugHelper;
use App\Network\AppHttpClient;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Cake\ORM\TableRegistry;
use Cake\View\View;
use Network\View\Helper\NetworkHelper;

require_once ROOT . DS . 'tests' . DS . 'config' . DS . 'test.config.php';

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
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

    public $httpClient;

    /**
     * called before every test method
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->initHttpClient();

        $View = new View();
        $this->Slug = new SlugHelper($View);
        $this->Html = new MyHtmlHelper($View);
        $this->Time = new MyTimeHelper($View);
        $this->Network = new NetworkHelper($View);
        $this->PricePerUnit = new PricePerUnitHelper($View);
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');

        $this->resetTestDatabaseData();
        $this->resetLogs();
        $this->Configuration->loadConfigurations();
        
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
    
    public function tearDown(): void
    {
        parent::tearDown();
        $this->assertLogFilesForErrors();
    }
    
    protected function assertLogFilesForErrors()
    {
        $log = $this->getLogFile('debug')->read(true, 'r');
        $log .= $this->getLogFile('error')->read(true, 'r');
        $this->assertDoesNotMatchRegularExpression('/(Warning|Notice)/', $log);
    }
    
    protected function resetTestDatabaseData()
    {
        $this->dbConnection = ConnectionManager::get('test');
        $this->testDumpDir = TESTS . 'config' . DS . 'sql' . DS;
        $this->dbConnection->query(file_get_contents($this->testDumpDir . 'test-db-data.sql'));
    }

    public function initHttpClient()
    {
        $this->httpClient = new AppHttpClient([
            'headers' => [
                'x-unit-test-mode' => true
            ]
        ]);
        $this->httpClient->loginEmail = Configure::read('test.loginEmailSuperadmin');
        $this->httpClient->loginPassword = Configure::read('test.loginPassword');
    }

    protected function assertJsonError()
    {
        $response = $this->httpClient->getJsonDecodedContent();
        $this->assertEquals(0, $response->status, 'json status should be "0"');
    }

    protected function assert200OkHeader()
    {
        $this->assertEquals(200, $this->httpClient->getStatusCode());
    }

    protected function assert401UnauthorizedHeader()
    {
        $this->assertEquals(401, $this->httpClient->getStatusCode());
    }

    protected function assert403ForbiddenHeader()
    {
        $this->assertEquals(403, $this->httpClient->getStatusCode());
    }
    
    protected function assertAccessDeniedWithRedirectToLoginForm()
    {
        $this->assertRegExpWithUnquotedString('Zugriff verweigert, bitte melde dich an.', $this->httpClient->getContent());
    }

    protected function assert404NotFoundHeader()
    {
        $this->assertEquals(404, $this->httpClient->getStatusCode());
    }

    protected function assertRedirectToLoginPage()
    {
        $this->assertRegExpWithUnquotedString($this->httpClient->baseUrl . $this->Slug->getLogin(), $this->httpClient->getUrl(), 'redirect to login page failed');
    }

    protected function assertJsonAccessRestricted()
    {
        $response = $this->httpClient->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString('Du bist nicht angemeldet.', $response->msg, 'login check does not work');
    }

    protected function assertJsonOk()
    {
        $response = $this->httpClient->getJsonDecodedContent();
        $this->assertEquals(1, $response->status, 'json status should be "1", msg: ' . $response->msg);
    }
    
    /**
     * called with json request, Controlller::isAuthorized false redirects to home
     */
    protected function assertNotPerfectlyImplementedAccessRestricted()
    {
        $this->assertEquals(Configure::read('app.cakeServerName') . '/', $this->httpClient->getUrl());
    }

    /**
     * back tick allows using forward slash in $unquotedString
     * @param string $unquotedString
     * @param string $response
     * @param string $msg
     */
    protected function assertRegExpWithUnquotedString($unquotedString, $response, $msg = '')
    {
        $this->assertMatchesRegularExpression('`' . preg_quote($unquotedString) . '`', $response, $msg);
    }

    /**
     * back tick ` allows using forward slash in $unquotedString
     * @param string $unquotedString
     * @param string $response
     * @param string $msg
     */
    protected function assertDoesNotMatchRegularExpressionWithUnquotedString($unquotedString, $response, $msg = '')
    {
        $this->assertDoesNotMatchRegularExpression('`' . preg_quote($unquotedString) . '`', $response, $msg);
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
            $this->httpClient->get($url);
            $this->assertEquals(404, $this->httpClient->getStatusCode());
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
            $this->httpClient->get($url);
            $html = $this->httpClient->getContent();
            $this->assertDoesNotMatchRegularExpression('/class="cake-stack-trace"|class="cake-error"|\bFatal error\b|exception \'[^\']+\' with message|\<strong\>(Error|Exception)\s*:\s*\<\/strong\>|Parse error|Not Found|\/app\/views\/errors\/|error in your SQL syntax|ERROR!|^\<\/body\>/', $html);
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
        $this->assertEqualsCanonicalizing($preparedToAddresses, $expectedToEmails, 'email to_addresses wrong');
        
        $preparedCcAddresses = [];
        foreach ($ccAddress as $email) {
            $preparedCcAddresses[] = $email;
        }
        $this->assertEqualsCanonicalizing($preparedCcAddresses, $expectedCcEmails, 'email cc_addresses wrong');

        $preparedBccAddresses = [];
        foreach ($bccAddress as $email) {
            $preparedBccAddresses[] = $email;
        }
        $this->assertEqualsCanonicalizing($preparedBccAddresses, $expectedBccEmails, 'email bcc_addresses wrong');
    }

    protected function changeReadOnlyConfiguration($configKey, $value)
    {
        $query = 'UPDATE ' . $this->Configuration->getTable() . ' SET value = :value WHERE name = :configKey';
        $params = [
            'value' => $value,
            'configKey' => $configKey
        ];
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);
        $this->Configuration->loadConfigurations();
    }

    /**
     * needs to login as superadmin and logs user out automatically
     * eventually create a new httpClient instance for this method
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
     * @param int $productId
     * @param int $amount
     * @return string
     */
    protected function addProductToCart($productId, $amount)
    {
        $this->httpClient->ajaxPost('/warenkorb/ajaxAdd/', [
            'productId' => $productId,
            'amount' => $amount
        ]);
        return $this->httpClient->getJsonDecodedContent();
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
                'customer_id' => $this->httpClient->getLoggedUserId(),
                'pickup_day' => Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb(),
                'comment' => $comment
            ];
        }

        if ($timebaseCurrencyTimeSum !== null) {
            $data['Carts']['timebased_currency_seconds_sum_tmp'] = $timebaseCurrencyTimeSum;
        }

        $this->httpClient->post(
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
    protected function changeProductPrice($productId, $price, $pricePerUnitEnabled = false, $priceInclPerUnit = 0, $priceUnitName = '', $priceUnitAmount = 0, $priceQuantityInUnits = 0)
    {
        $this->httpClient->ajaxPost('/admin/products/editPrice', [
            'productId' => $productId,
            'price' => $price,
            'pricePerUnitEnabled' => $pricePerUnitEnabled,
            'priceInclPerUnit' => $priceInclPerUnit,
            'priceUnitName' => $priceUnitName,
            'priceUnitAmount' => $priceUnitAmount,
            'priceQuantityInUnits' => $priceQuantityInUnits
        ]);
        return $this->httpClient->getJsonDecodedContent();
    }
    
    protected function changeProductDeliveryRhythm($productId, $deliveryRhythmType, $deliveryRhythmFirstDeliveryDay = '', $deliveryRhythmOrderPossibleUntil = '', $deliveryRhythmSendOrderListWeekday = '', $deliveryRhythmSendOrderListDay = '')
    {
        $this->httpClient->ajaxPost('/admin/products/editDeliveryRhythm', [
            'productIds' => [$productId],
            'deliveryRhythmType' => $deliveryRhythmType,
            'deliveryRhythmFirstDeliveryDay' => $deliveryRhythmFirstDeliveryDay,
            'deliveryRhythmOrderPossibleUntil' => $deliveryRhythmOrderPossibleUntil,
            'deliveryRhythmSendOrderListWeekday' => $deliveryRhythmSendOrderListWeekday,
            'deliveryRhythmSendOrderListDay' => $deliveryRhythmSendOrderListDay,
        ]);
        return $this->httpClient->getJsonDecodedContent();
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
        $this->httpClient->doFoodCoopShopLogout();
    }

    protected function loginAsSuperadmin()
    {
        $this->httpClient->loginEmail = Configure::read('test.loginEmailSuperadmin');
        $this->httpClient->doFoodCoopShopLogin();
    }

    protected function loginAsAdmin()
    {
        $this->httpClient->loginEmail = Configure::read('test.loginEmailAdmin');
        $this->httpClient->doFoodCoopShopLogin();
    }

    protected function loginAsCustomer()
    {
        $this->httpClient->loginEmail = Configure::read('test.loginEmailCustomer');
        $this->httpClient->doFoodCoopShopLogin();
    }

    protected function loginAsMeatManufacturer()
    {
        $this->httpClient->loginEmail = Configure::read('test.loginEmailMeatManufacturer');
        $this->httpClient->doFoodCoopShopLogin();
    }

    protected function loginAsVegetableManufacturer()
    {
        $this->httpClient->loginEmail = Configure::read('test.loginEmailVegetableManufacturer');
        $this->httpClient->doFoodCoopShopLogin();
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
    
    protected function prepareSendingOrderLists()
    {
        $folder = new Folder();
        $folder->delete(Configure::read('app.folder_order_lists'));
        $file = new File(Configure::read('app.folder_order_lists') . DS . '.gitignore', true);
        $file->append('/*
!.gitignore');
    }

}
