<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Services\DeliveryRhythmService;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\AssertPagesForErrorsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;

class SelfServiceControllerTest extends AppCakeTestCase
{

    protected $ActionLog;
    public $Cart;
    protected $CartProductUnit;
    protected $Invoice;

    use AppIntegrationTestTrait;
    use AssertPagesForErrorsTrait;
    use LoginTrait;
    use EmailTrait;

    public function testPageSelfService()
    {
        $this->loginAsSuperadmin();
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $testUrls = [
            $this->Slug->getSelfService()
        ];
        $this->assertPagesForErrors($testUrls);
    }

    public function testBarCodeLoginAsSuperadminValid()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->doBarCodeLogin();
        $this->assertEquals($_SESSION['Auth']->id_customer, Configure::read('test.superadminId'));
    }

    public function testSelfServiceAddProductPricePerUnitWrong()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(351, 1);
        $response = $this->getJsonDecodedContent();
        $expectedErrorMessage = 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.';
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertJsonError();
    }

    public function testSelfServiceAddAttributePricePerUnitWrong()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart('350-15', 1, 'bla bla');
        $response = $this->getJsonDecodedContent();
        $expectedErrorMessage = 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.';
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertJsonError();
    }

    public function testSelfServiceOrderWithoutCheckboxes() {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(349, 1);
        $this->finishSelfServiceCart(0, 0);
        $this->assertResponseContains('Bitte akzeptiere die AGB.');
        $this->assertResponseContains('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.');
    }

    public function testSelfServiceRemoveProductWithPricePerUnit()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(351, 1, '0,5');
        $this->removeProductFromSelfServiceCart(351);
        $this->assertJsonOk();
        $this->CartProductUnit = $this->getTableLocator()->get('CartProductUnits');
        $cartProductUnits = $this->CartProductUnit->find('all')->toArray();
        $this->assertEmpty($cartProductUnits);
    }

    public function testSelfServiceOrderWithoutPricePerUnit()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(346, 1, 0);
        $this->finishSelfServiceCart(1, 1);

        $this->Cart = $this->getTableLocator()->get('Carts');
        $cart = $this->Cart->find('all', order: [
            'Carts.id_cart' => 'DESC'
        ])->first();

        $cart = $this->getCartById($cart->id_cart);

        $this->assertEquals(1, count($cart->cart_products));

        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        }

        $this->assertMailCount(1);

        $this->assertMailSubjectContainsAt(0, 'Dein Einkauf');
        $this->assertMailContainsHtmlAt(0, 'Artischocke');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));

    }

    public function testSelfServiceOrderWithPricePerUnit()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart('350-15', 1, '1,5');
        $this->addProductToSelfServiceCart(351, 1, '0,5');
        $this->finishSelfServiceCart(1, 1);

        $this->Cart = $this->getTableLocator()->get('Carts');
        $cart = $this->Cart->find('all', order: [
            'Carts.id_cart' => 'DESC'
        ])->first();

        $cart = $this->getCartById($cart->id_cart);

        $this->assertEquals(2, count($cart->cart_products));

        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->order_detail_unit->mark_as_saved, 1);
            $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        }

        $this->assertMailCount(1);
        $this->assertMailSubjectContainsAt(0, 'Dein Einkauf');
        $this->assertMailContainsHtmlAt(0, 'Lagerprodukt mit Varianten : 1,5 kg');
        $this->assertMailContainsHtmlAt(0, 'Lagerprodukt 2 : 0,5 kg');
        $this->assertMailContainsHtmlAt(0, '15,00 €');
        $this->assertMailContainsHtmlAt(0, '5,00 €');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));

    }

    public function testSelfServiceOrderWithPricePerUnitPurchasePriceEnabled()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(347, 1, '500');
        $this->addProductToSelfServiceCart('348-12', 1, '250');
        $this->finishSelfServiceCart(1, 1);

        $this->Cart = $this->getTableLocator()->get('Carts');
        $cart = $this->Cart->find('all', order: [
            'Carts.id_cart' => 'DESC'
        ])->first();
        $cart = $this->getCartById($cart->id_cart);

        $this->assertEquals(4.9, $cart->cart_products[0]->order_detail->order_detail_purchase_price->total_price_tax_incl);
        $this->assertEquals(4.34, $cart->cart_products[0]->order_detail->order_detail_purchase_price->total_price_tax_excl);
        $this->assertEquals(0.56, $cart->cart_products[0]->order_detail->order_detail_purchase_price->tax_unit_amount);
        $this->assertEquals(0.56, $cart->cart_products[0]->order_detail->order_detail_purchase_price->tax_total_amount);

        $this->assertEquals(7, $cart->cart_products[1]->order_detail->order_detail_purchase_price->total_price_tax_incl);
        $this->assertEquals(6.19, $cart->cart_products[1]->order_detail->order_detail_purchase_price->total_price_tax_excl);
        $this->assertEquals(0.81, $cart->cart_products[1]->order_detail->order_detail_purchase_price->tax_unit_amount);
        $this->assertEquals(0.81, $cart->cart_products[1]->order_detail->order_detail_purchase_price->tax_total_amount);
    }

    public function testSelfServiceOrderWithDeliveryBreak()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb());
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart('350-15', 1, '1,5');
        $this->finishSelfServiceCart(1, 1);
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all')->toArray();
        $this->assertRegExpWithUnquotedString('Demo Superadmin hat eine neue Bestellung getätigt (15,00 €).', $actionLogs[0]->text);
    }

    public function testSearchByCustomProductBarcode()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $barcodeForProduct = '1234567890123';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
        $this->assertRegExpWithUnquotedString('Das Produkt <b>Lagerprodukt</b> wurde in deine Einkaufstasche gelegt.', $_SESSION['Flash']['flash'][0]['message']);
        $this->assertRedirect($this->Slug->getSelfService());
    }

    public function testSearchByCustomProductAttributeBarcode()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $barcodeForProduct = '2345678901234';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
        $this->assertFlashMessageAt(0, 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.');
        $this->assertRedirect($this->Slug->getSelfService('', $barcodeForProduct));
    }

    public function testSearchByCustomProductBarcodeWithWeight()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $barcodeForProduct = '2712345600235';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
    } 

    public function testSearchBySystemProductBarcodeWithMissingWeight()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $barcodeForProduct = 'b5320000';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
        $this->assertFlashMessageAt(0, 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.');
        $this->assertRedirect($this->Slug->getSelfService('', $barcodeForProduct));
    }

    public function testSearchBySystemProductAttributeBarcodeWithMissingWeight()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $barcodeForProduct = 'e05f0015';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
        $this->assertFlashMessageAt(0, 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.');
        $this->assertRedirect($this->Slug->getSelfService('', $barcodeForProduct));
    }

    public function testSelfServiceOrderWithRetailModeAndSelfServiceCustomerWithAutoGenerateInvoiceDisabled()
    {

        Configure::write('app.selfServiceModeAutoGenerateInvoice', false);
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeCustomer(Configure::read('test.selfServiceCustomerId'), 'invoices_per_email_enabled', 0);
        $this->loginAsSuperadmin();
        $this->get('/admin/customers/changeStatus/' . Configure::read('test.selfServiceCustomerId'). '/1/0');
        $this->loginAsSelfServiceCustomer();
        $this->addProductToSelfServiceCart(346, 1, 0);
        $this->addProductToSelfServiceCart(351, 1, '0,5');

        $this->Cart = $this->getTableLocator()->get('Carts');
        $this->finishSelfServiceCart(1, 1);
        $this->runAndAssertQueue();
        $this->assertSessionNotHasKey('invoiceRouteForAutoPrint');

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoices = $this->Invoice->find('all');
        $this->assertEquals($invoices->count(), 0);
    }

    public function testSelfServiceOrderWithRetailModeAndSelfServiceCustomer()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeCustomer(Configure::read('test.selfServiceCustomerId'), 'invoices_per_email_enabled', 0);
        $this->loginAsSuperadmin();
        $this->get('/admin/customers/changeStatus/' . Configure::read('test.selfServiceCustomerId'). '/1/0');
        $this->loginAsSelfServiceCustomer();
        $this->addProductToSelfServiceCart(346, 1, 0);
        $this->addProductToSelfServiceCart(351, 1, '0,5');

        $this->Cart = $this->getTableLocator()->get('Carts');
        $this->finishSelfServiceCart(1, 1);
        $this->runAndAssertQueue();
        $this->assertSessionHasKey('invoiceRouteForAutoPrint');

        $cart = $this->Cart->find('all',
            order: [
                'Carts.id_cart' => 'DESC'
            ],
        )->first();
        $cart = $this->getCartById($cart->id_cart);

        $this->assertEquals(2, count($cart->cart_products));

        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->order_state, ORDER_STATE_BILLED_CASHLESS);
        }

        $this->assertMailCount(1);

        $this->assertMailSubjectContainsAt(0, 'Dein Einkauf');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSelfServiceCustomer'));

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoices = $this->Invoice->find('all');
        $this->assertEquals($invoices->count(), 1);
        $this->assertEquals($invoices->toArray()[0]->paid_in_cash, 1);

    }

    public function testSelfServiceOrderForDifferentCustomer()
    {

        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);

        // add a product to the "normal" cart (CART_TYPE_WEEKLY_RHYTHM)
        $this->loginAsCustomer();
        $this->addProductToCart(346, 5);
        $this->logout();

        $this->loginAsSuperadmin();
        $testCustomer = $this->Customer->find('all',
            conditions: [
                'Customers.id_customer' => Configure::read('test.customerId'),
            ]
        )->first();
        $this->get($this->Slug->getOrderDetailsList().'/initSelfServiceOrder/' . Configure::read('test.customerId'));
        $this->loginAsSuperadminAddOrderCustomerToSession($_SESSION);
        $this->get($this->_response->getHeaderLine('Location'));
        $this->assertResponseContains('Diese Bestellung wird für <b>' . $testCustomer->name . '</b> getätigt.');

        $this->addProductToSelfServiceCart(349, 1);
        $this->addProductToSelfServiceCart('350-13', 2, 1);

        $this->Cart = $this->getTableLocator()->get('Carts');
        $this->finishSelfServiceCart(1, 1);

        $carts = $this->Cart->find('all',
            conditions: [
                'Carts.id_customer' => Configure::read('test.customerId'),
            ],
            order: [
                'Carts.id_cart' => 'DESC'
            ],
            contain: [
                'CartProducts.OrderDetails',
            ],
        )->toArray();

        $this->assertEquals(2, count($carts[0]->cart_products));
        $this->assertEquals(1, count($carts[1]->cart_products));

        foreach($carts[0]->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->id_customer, $testCustomer->id_customer);
            $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        }

        $this->assertMailCount(0);

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoiceCount = $this->Invoice->find('all')->count();
        $this->assertEquals($invoiceCount, 0);

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all')->toArray();
        $this->assertEquals('carts', $actionLogs[0]->object_type);
        $this->assertEquals($carts[0]->id_cart, $actionLogs[0]->object_id);
        $this->assertEquals($actionLogs[0]->text, 'Demo Superadmin hat eine neue Bestellung für <b>Demo Mitglied</b> getätigt (9,00 €).');
        $this->assertEquals(Configure::read('test.superadminId'), $actionLogs[0]->customer_id);
    }

    public function testProductDetailHtmlProductCatalogSelfServiceOrder()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->isSelfServiceModeByUrl = true;
        $productId = 349;
        $this->get($this->Slug->getSelfService($productId));
        $nextDeliveryDay = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
        $pickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($nextDeliveryDay));
        $this->assertResponseContains('<span class="pickup-day">'.$pickupDay.'</span>');
    }

    private function addProductToSelfServiceCart($productId, $amount, $orderedQuantityInUnits = -1)
    {
        $this->getSelfServicePostOptions();
        $this->post(
            '/warenkorb/ajaxAdd/',
            [
                'productId' => $productId,
                'amount' => $amount,
                'orderedQuantityInUnits' => $orderedQuantityInUnits
            ],
        );
        return $this->getJsonDecodedContent();
    }

    private function removeProductFromSelfServiceCart($productId)
    {
        $this->getSelfServicePostOptions();
        $this->post(
            '/warenkorb/ajaxRemove/',
            [
                'productId' => $productId
            ],
        );
        return $this->getJsonDecodedContent();
    }

    private function getSelfServicePostOptions()
    {
        $this->configRequest([
            'headers' => [
                'X_REQUESTED_WITH' => 'XMLHttpRequest',
                'ACCEPT' => 'application/json',
                'REFERER' => Configure::read('App.fullBaseUrl') . '/' . __('route_self_service'),
            ],
        ]);
    }

    private function finishSelfServiceCart($generalTermsAndConditionsAccepted, $cancellationTermsAccepted)
    {
        $data = [
            'Carts' => [
                'general_terms_and_conditions_accepted' => $generalTermsAndConditionsAccepted,
                'cancellation_terms_accepted' => $cancellationTermsAccepted,
            ],
        ];
        $this->configRequest([
            'headers' => [
                'REFERER' => Configure::read('App.fullBaseUrl') . '/' . __('route_self_service'),
            ],
        ]);
        $this->post(
            $this->Slug->getSelfService(),
            $data,
        );
        $this->runAndAssertQueue();
    }

    private function doBarCodeLogin()
    {
        $this->post($this->Slug->getLogin(), [
            'barcode' => Configure::read('test.superadminBarCode')
        ]);
    }

}
