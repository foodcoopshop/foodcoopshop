<?php
namespace App\Test\TestCase;

use App\Lib\DeliveryRhythm\DeliveryRhythm;
use App\Test\TestCase\Traits\QueueTrait;
use App\View\Helper\MyHtmlHelper;
use App\View\Helper\MyTimeHelper;
use App\View\Helper\PricePerUnitHelper;
use App\View\Helper\SlugHelper;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Cake\View\View;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\TestSuite\TestEmailTransport;
use Migrations\Migrations;
use Network\View\Helper\NetworkHelper;

require_once ROOT . DS . 'tests' . DS . 'config' . DS . 'test.config.php';

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
abstract class AppCakeTestCase extends TestCase
{

    use ConsoleIntegrationTestTrait;
    use QueueTrait;

    protected $dbConnection;

    protected $testDumpDir;

    protected $appDumpDir;

    public $Slug;

    public $Html;

    public $Time;

    public $Customer;

    public $Manufacturer;


    /**
     * called before every test method
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = ConnectionManager::get('test');
        $this->seedTestDatabase();
        $this->resetLogs();
        $this->Configuration = $this->getTableLocator()->get('Configurations');
        $this->Configuration->loadConfigurations();

        $View = new View();
        $this->Slug = new SlugHelper($View);
        $this->Html = new MyHtmlHelper($View);
        $this->Time = new MyTimeHelper($View);
        $this->Network = new NetworkHelper($View);
        $this->PricePerUnit = new PricePerUnitHelper($View);
        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');


        // enable security token only for IntegrationTests
        if (method_exists($this, 'enableSecurityToken')) {
            $this->enableSecurityToken();
        }

        $this->useCommandRunner();

        // sometimes tests were interfering with each other
        TestEmailTransport::clearMessages();
    }

    private function getLogFile($name)
    {
        return new File(ROOT . DS . 'logs' . DS . $name . '.log');
    }

    protected function resetLogs()
    {
        $this->getLogFile('debug')->write('');
        $this->getLogFile('error')->write('');
        $this->getLogFile('cli-debug')->write('');
        $this->getLogFile('cli-error')->write('');
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
        $log .= $this->getLogFile('cli-debug')->read(true, 'r');
        $log .= $this->getLogFile('cli-error')->read(true, 'r');
        $this->assertDoesNotMatchRegularExpression('/(Warning|Notice)/', $log);
    }

    protected function seedTestDatabase()
    {
        $migrations = new Migrations();
        $migrations->seed([
            'connection' => 'test',
            'source' => 'Seeds' . DS . 'tests', // needs to be a subfolder of config
        ]);
    }

    protected function getJsonDecodedContent()
    {
        return json_decode($this->_getBodyAsString());
    }

    protected function assertJsonError()
    {
        $response = $this->getJsonDecodedContent();
        $this->assertEquals(0, $response->status);
    }

    protected function assertAccessDeniedFlashMessage() {
        $this->assertFlashMessage('Zugriff verweigert, bitte melde dich an.');
    }

    protected function assertRedirectToLoginPage()
    {
        $this->assertRegExpWithUnquotedString(Configure::read('App.fullBaseUrl') .  $this->Slug->getLogin(), $this->_response->getHeaderLine('Location'));
    }

    protected function assertJsonOk()
    {
        $response = $this->getJsonDecodedContent();
        $this->assertEquals(1, $response->status);
    }

    /**
     * called with json request, Controller::isAuthorized false redirects to home
     */
    protected function assertNotPerfectlyImplementedAccessRestricted()
    {
        $this->assertEquals(Configure::read('App.fullBaseUrl') . '/' , $this->_response->getHeaderLine('Location'));
    }

    /**
     * back tick allows using forward slash in $unquotedString
     * @param string $unquotedString
     * @param string $response
     * @param string $msg
     */
    protected function assertRegExpWithUnquotedString($unquotedString, $response, $msg = '')
    {
        if (is_null($response)) return;
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
        $this->ajaxPost('/warenkorb/ajaxAdd/', [
            'productId' => $productId,
            'amount' => $amount
        ]);
        return $this->getJsonDecodedContent();
    }

    protected function finishCart($general_terms_and_conditions_accepted = 1, $cancellation_terms_accepted = 1, $comment = '', $timebaseCurrencyTimeSum = null, $pickupDay = null)
    {
        $data = [
            'Carts' => [
                'general_terms_and_conditions_accepted' => $general_terms_and_conditions_accepted,
                'cancellation_terms_accepted' => $cancellation_terms_accepted,
            ],
        ];

        if ($comment != '') {
            $data['Carts']['pickup_day_entities'][0] = [
                'customer_id' => $this->getUserId(),
                'pickup_day' => !is_null($pickupDay) ? $pickupDay : DeliveryRhythm::getDeliveryDateByCurrentDayForDb(),
                'comment' => $comment,
            ];
        }

        if ($pickupDay !== null) {
            $data['Carts']['pickup_day'] = $pickupDay;
        }

        $this->post(
            $this->Slug->getCartFinish(),
            $data,
        );

        $this->runAndAssertQueue();
    }

    protected function getCartById($cartId)
    {
        $contain = [
            'CartProducts.OrderDetails.OrderDetailUnits',
        ];

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $contain[] = 'CartProducts.OrderDetails.OrderDetailPurchasePrices';
        }

        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $cartId,
            ],
            'contain' => $contain,
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
        $this->ajaxPost('/admin/products/editPrice', [
            'productId' => $productId,
            'price' => $price,
            'pricePerUnitEnabled' => $pricePerUnitEnabled,
            'priceInclPerUnit' => $priceInclPerUnit,
            'priceUnitName' => $priceUnitName,
            'priceUnitAmount' => $priceUnitAmount,
            'priceQuantityInUnits' => $priceQuantityInUnits
        ]);
        return $this->getJsonDecodedContent();
    }

    protected function changeProductDeliveryRhythm($productId, $deliveryRhythmType, $deliveryRhythmFirstDeliveryDay = '', $deliveryRhythmOrderPossibleUntil = '', $deliveryRhythmSendOrderListWeekday = '', $deliveryRhythmSendOrderListDay = '')
    {
        $this->ajaxPost('/admin/products/editDeliveryRhythm', [
            'productIds' => [$productId],
            'deliveryRhythmType' => $deliveryRhythmType,
            'deliveryRhythmFirstDeliveryDay' => $deliveryRhythmFirstDeliveryDay,
            'deliveryRhythmOrderPossibleUntil' => $deliveryRhythmOrderPossibleUntil,
            'deliveryRhythmSendOrderListWeekday' => $deliveryRhythmSendOrderListWeekday,
            'deliveryRhythmSendOrderListDay' => $deliveryRhythmSendOrderListDay,
        ]);
        return $this->getJsonDecodedContent();
    }

    /**
     * @param int $customerId
     * @param int $amount - strange behavior: posting a string '64,32' leads to '64.32' in controller
     * @param string $type
     * @param int $manufacturerId optional
     * @param string $text optional
     * @param date $dateAdd optional
     * @return string
     */
    protected function addPayment($customerId, $amount, $type, $manufacturerId = 0, $text = '', $dateAdd = 0)
    {
        $this->ajaxPost('/admin/payments/add', [
            'customerId' => $customerId,
            'amount' => $amount,
            'type' => $type,
            'manufacturerId' => $manufacturerId,
            'text' => $text,
            'dateAdd' => $dateAdd,
        ]);
        return $this->getJsonDecodedContent();
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

    protected function getCorrectedLogoPathInHtmlForPdfs($html)
    {
        return preg_replace('/\{\{logoPath\}\}/', ROOT . DS . 'webroot' . DS . 'files' . DS . 'images' . DS . 'logo-pdf.jpg', $html);
    }

    protected function prepareSendingOrderLists()
    {
        $this->prepareSendingOrderListsOrInvoices(Configure::read('app.folder_order_lists'));
    }

    protected function prepareSendingInvoices()
    {
        $this->prepareSendingOrderListsOrInvoices(Configure::read('app.folder_invoices'));
    }

    protected function resetCustomerCreditBalance() {
        $this->Payment = $this->getTableLocator()->get('Payments');
        $this->dbConnection->execute('DELETE FROM ' . $this->Payment->getTable().' WHERE id = 2');
    }

    private function prepareSendingOrderListsOrInvoices($contentFolder)
    {
        $folder = new Folder();
        $folder->delete($contentFolder);
        $file = new File($contentFolder . DS . '.gitignore', true);
        $file->append('/*
!.gitignore');
    }

}
