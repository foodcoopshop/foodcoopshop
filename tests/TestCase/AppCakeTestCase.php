<?php
declare(strict_types=1);

namespace App\Test\TestCase;

use App\Services\DeliveryRhythmService;
use App\Services\FolderService;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\QueueTrait;
use App\View\Helper\MyHtmlHelper;
use App\View\Helper\MyTimeHelper;
use App\View\Helper\PricePerUnitHelper;
use App\View\Helper\SlugHelper;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\View\View;
use Cake\TestSuite\TestCase;
use Cake\TestSuite\TestEmailTransport;
use Network\View\Helper\NetworkHelper;
use Cake\Routing\Router;
use Cake\Http\ServerRequest;
use App\Test\Fixture\AppFixture;

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

    use AppIntegrationTestTrait;
    use ConsoleIntegrationTestTrait;
    use LoginTrait;
    use QueueTrait;

    protected $dbConnection;
    protected $testDumpDir;
    protected $appDumpDir;
    public $Slug;
    public $Html;
    public $Time;
    public $Cart;
    public $Configuration;
    public $Manufacturer;
    public $Network;
    public $Payment;
    public $PricePerUnit;

    public function setUp(): void
    {

        $this->fixtures = AppFixture::IMPLEMENTED_FIXTURES;
        parent::setUp();

        $this->dbConnection = ConnectionManager::get('test');
        $this->resetLogs();
        $this->Configuration = $this->getTableLocator()->get('Configurations');
        $this->Configuration->loadConfigurations();

        $View = new View();
        $this->Slug = new SlugHelper($View);
        $this->Html = new MyHtmlHelper($View);
        $this->Time = new MyTimeHelper($View);
        $this->Network = new NetworkHelper($View);
        $this->PricePerUnit = new PricePerUnitHelper($View);
        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');

        // enable tokens only for IntegrationTests
        if (method_exists($this, 'enableSecurityToken')) {
            $this->enableSecurityToken();
            $this->enableCsrfToken();
        }

        // sometimes tests were interfering with each other
        TestEmailTransport::clearMessages();
    }

    protected function setDummyRequest()
    {
        Router::setRequest(new ServerRequest());
    }

    private function getLogFile(string $name): string
    {
        return ROOT . DS . 'logs' . DS . $name . '.log';
    }

    protected function resetLogs(): void
    {
        file_put_contents($this->getLogFile('debug'), '');
        file_put_contents($this->getLogFile('error'), '');
        file_put_contents($this->getLogFile('cli-debug'), '');
        file_put_contents($this->getLogFile('cli-error'), '');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->assertLogFilesForErrors();
    }

    protected function assertLogFilesForErrors()
    {
        $log = file_get_contents($this->getLogFile('debug'));
        $log .= file_get_contents($this->getLogFile('error'));
        $log .= file_get_contents($this->getLogFile('cli-debug'));
        $log .= file_get_contents($this->getLogFile('cli-error'));
        $this->assertDoesNotMatchRegularExpression('/(Warning|Notice)/', $log);
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
        $this->assertRegExpWithUnquotedString($this->Slug->getLogin(), $this->_response->getHeaderLine('Location'));
    }

    protected function assertJsonOk()
    {
        $response = $this->getJsonDecodedContent();
        $this->assertEquals(1, $response->status);
    }

    protected function assertNotPerfectlyImplementedAccessRestricted()
    {
        $this->assertEquals($this->Slug->getLogin() , $this->_response->getHeaderLine('Location'));
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

    /**
     * automatically logout of user
     */
    protected function changeConfiguration(string $configKey, $value)
    {
        $configurationEntity = $this->Configuration->get($configKey);
        $configurationEntity->value = $value;
        $this->Configuration->save($configurationEntity);
        $this->Configuration->loadConfigurations();
        $this->logout();
    }

    protected function changeManufacturerNoDeliveryDays(int $manufacturerId, string $noDeliveryDays = ''): void
    {
        $this->changeManufacturer($manufacturerId, 'no_delivery_days', $noDeliveryDays);
    }

    /**
     * @param int $productId
     * @param int $amount
     */
    protected function addProductToCart($productId, $amount)
    {
        $this->ajaxPost('/warenkorb/ajaxAdd/', [
            'productId' => $productId,
            'amount' => $amount,
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
                'customer_id' => $this->getId(),
                'pickup_day' => !is_null($pickupDay) ? $pickupDay : (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb(),
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

        $cart = $this->Cart->find('all',
            conditions: [
                'Carts.id_cart' => $cartId,
            ],
            contain: $contain,
        )->first();

        return $cart;
    }

    protected function changeProductPrice($productId, $price, $pricePerUnitEnabled = false, $priceInclPerUnit = 0, $priceUnitName = '', $priceUnitAmount = 0, $priceQuantityInUnits = 0, $changeOpenOrderDetails = false)
    {
        $this->ajaxPost('/admin/products/editPrice', [
            'productId' => $productId,
            'price' => $price,
            'pricePerUnitEnabled' => $pricePerUnitEnabled,
            'priceInclPerUnit' => $priceInclPerUnit,
            'priceUnitName' => $priceUnitName,
            'priceUnitAmount' => $priceUnitAmount,
            'priceQuantityInUnits' => $priceQuantityInUnits,
            'priceChangeOpenOrderDetails' => $changeOpenOrderDetails,
        ]);
        return $this->getJsonDecodedContent();
    }

    protected function changeProductDeliveryRhythm(
        int $productId,
        string $deliveryRhythmType,
        string $deliveryRhythmFirstDeliveryDay = '',
        string $deliveryRhythmOrderPossibleUntil = '',
        string $deliveryRhythmSendOrderListWeekday = '',
        string $deliveryRhythmSendOrderListDay = ''
        )
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

    protected function changeManufacturer(int $manufacturerId, string $field, $value)
    {
        $newManufacturer = $this->Manufacturer->get($manufacturerId);
        $newManufacturer->{$field} = $value;
        $this->Manufacturer->save($newManufacturer);
    }

    protected function changeCustomer(int $customerId, string $field, $value)
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $newCustomer = $customersTable->get($customerId);
        $newCustomer->{$field} = $value;
        $customersTable->save($newCustomer);
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
        $paymentsTable = $this->getTableLocator()->get('Payments');
        $paymentsTable->delete($paymentsTable->get(2));
    }

    private function prepareSendingOrderListsOrInvoices($contentFolder)
    {
        FolderService::rrmdir($contentFolder);
        mkdir($contentFolder, 0755, true);
        $file = fopen($contentFolder . DS . '.gitignore', 'w');
        fwrite($file, '/*
!.gitignore');
        fclose($file);
    }

}
