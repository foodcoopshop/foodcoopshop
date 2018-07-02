<?php

/**
 * CartsControllerTest
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
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class CartsControllerTest extends AppCakeTestCase
{

    // artischocke, 0,5 deposit, manufacturerId 5
    public $productId1 = '346';
    // milk with attribute 0,5 l, 0,5 deposit, manufacturerId 15
    public $productId2 = '60-10';
    // knoblauch, 0% tax, , manufacturerId 5
    public $productId3 = '344';

    public $Cart;

    public $Product;

    public $Order;

    public $StockAvailable;

    public $EmailLog;

    public function setUp()
    {
        parent::setUp();
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->Order = TableRegistry::getTableLocator()->get('Orders');
        $this->StockAvailable = TableRegistry::getTableLocator()->get('StockAvailables');
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
    }

    public function testAddLoggedOut()
    {
        $this->addProductToCart($this->productId1, 2);
        $this->assertJsonAccessRestricted();
        $this->assertJsonError();
    }

    public function testAddWrongProductId1()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart(8787, 2);
        $this->assertRegExpWithUnquotedString('Das Produkt mit der ID 8787 ist nicht vorhanden.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddWrongProductId2()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart('test', 2);
        $this->assertRegExpWithUnquotedString('Das Produkt mit der ID test ist nicht vorhanden.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddWrongAmount()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 100);
        $this->assertRegExpWithUnquotedString('Die gewünschte Anzahl <b>100</b> ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }

    public function testRemoveProduct()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 2);
        $this->assertJsonOk();
        $response = $this->removeProduct($this->productId1);
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals([], $cart['CartProducts'], 'cart must be empty');
        $this->assertJsonOk();
        $response = $this->removeProduct($this->productId1);
        $this->assertRegExpWithUnquotedString('Produkt 346 war nicht in Warenkorb vorhanden.', $response->msg);
        $this->assertJsonError();
    }

    public function testProductPlacedInCart()
    {
        $this->loginAsSuperadmin();

        $amount1 = 2;
        $this->addProductToCart($this->productId1, $amount1);
        $this->assertJsonOk();

        // check if product was placed in cart
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals($this->productId1, $cart['CartProducts'][0]['productId'], 'product id not found in cart');
        $this->assertEquals($amount1, $cart['CartProducts'][0]['amount'], 'amount not found in cart or amount wrong');
    }

    public function testAttributePlacedInCart()
    {
        $this->loginAsSuperadmin();
        $amount2 = 3;
        $this->addProductToCart($this->productId2, $amount2);
        $this->assertJsonOk();

        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals($this->productId2, $cart['CartProducts'][0]['productId'], 'product id not found in cart');
        $this->assertEquals($amount2, $cart['CartProducts'][0]['amount'], 'amount not found in cart or amount wrong');

    }

    public function testAddTooManyProducts()
    {
        $this->loginAsSuperadmin();
        $amount = 1;
        $this->addProductToCart($this->productId1, $amount);
        $this->addTooManyProducts($this->productId1, 99, $amount, 'Die gewünschte Anzahl <b>100</b> des Produktes <b>Artischocke</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 97', 0);
    }

    public function testAddTooManyAttributes()
    {
        $this->loginAsSuperadmin();
        $amount = 1;
        $this->addProductToCart($this->productId2, $amount);
        $this->addTooManyProducts($this->productId2, 48, 1, 'Die gewünschte Anzahl <b>49</b> der Variante <b>0,5l</b> des Produktes <b>Milch</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 20', 0);
    }

    public function testProductDeactivatedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->changeProductStatus($this->productId1, APP_OFF);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Das Produkt (.*) ist leider nicht mehr aktiviert und somit nicht mehr bestellbar./', $this->browser->getContent());
        $this->changeProductStatus($this->productId1, APP_ON);
    }

    public function testManufacturerDeactivatedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $manufacturerId = 5;
        $this->changeManufacturerStatus($manufacturerId, APP_OFF);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Der Hersteller des Produktes (.*) hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar./', $this->browser->getContent());
        $this->changeManufacturerStatus($manufacturerId, APP_ON);
    }

    public function testManufacturerHolidayModeActivatedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $manufacturerId = 5;
        $this->changeManufacturerHolidayMode($manufacturerId, date('Y-m-d'));
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Der Hersteller des Produktes (.*) hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar./', $this->browser->getContent());
        $this->changeManufacturerHolidayMode($manufacturerId, null);
    }

    public function testProductStockAvailableDecreasedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->changeStockAvailable($this->productId1, 1);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Anzahl <b>2/', $this->browser->getContent());
        $this->assertRegExpWithUnquotedString('Menge: 1', $this->browser->getContent()); // ü needs to be escaped properly
        $this->changeStockAvailable($this->productId1, 98); // reset to old stock available
    }

    public function testAttributeStockAvailableDecreasedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->changeStockAvailable($this->productId2, 1);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Anzahl \<b\>3/', $this->browser->getContent());
        $this->assertRegExpWithUnquotedString('Menge: 1', $this->browser->getContent()); // ü needs to be escaped properly
        $this->changeStockAvailable($this->productId2, 20); // reset to old stock available
    }

    public function testFinishCartCheckboxesValidation()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->finishCart(0, 0);
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die AGB.', $this->browser->getContent(), 'checkbox validation general_terms_and_conditions_accepted did not work');
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.', $this->browser->getContent(), 'checkbox validation cancellation_terms_accepted did not work');
    }

    public function testFinishCartOrderCommentValidation()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->finishCart(1, 1, 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, adfasfd sa');
        $this->assertRegExpWithUnquotedString('Bitte gib maximal 500 Zeichen ein.', $this->browser->getContent(), 'order comment validation did not work');
    }

    public function testFinishOrderWithComment()
    {

        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $orderComment = 'this is a valid order comment';
        $this->finishCart(1, 1, $orderComment);
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());

        $this->checkCartStatusAfterFinish();

        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
            'contain' => [
                'OrderDetails.OrderDetailTaxes'
            ]
        ])->first();

        $this->checkOrder($order, $orderId, $orderComment);

        // check order_details for product1
        $this->checkOrderDetails($order->order_details[0], 'Artischocke : Stück', 2, 0, 1, 3.305786, 3.64, 0.17, 0.34, 2);

        // check order_details for product2 (third! index)
        $this->checkOrderDetails($order->order_details[2], 'Milch : 0,5l', 3, 10, 1.5, 1.636365, 1.86, 0.07, 0.21, 3);

        // check order_details for product3 (second! index)
        $this->checkOrderDetails($order->order_details[1], 'Knoblauch : 100 g', 1, 0, 0, 0.636364, 0.636364, 0.000000, 0.000000, 0);

        $this->checkStockAvailable($this->productId1, 95);
        $this->checkStockAvailable($this->productId2, 17);
        $this->checkStockAvailable($this->productId3, 77);

        // check new (empty) cart
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals($cart['Cart']['id_cart'], 3, 'cake cart id wrong');
        $this->assertEquals([], $cart['CartProducts'], 'cake cart products not empty');

        // check email to customer
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs(
            $emailLogs[0],
            'Bestellbestätigung',
            [
                'Artischocke : Stück',
                'Hallo Demo Superadmin,',
                'Content-Disposition: attachment; filename="Informationen-ueber-Ruecktrittsrecht-und-Ruecktrittsformular.pdf"',
                'Content-Disposition: attachment; filename="Bestelluebersicht.pdf"',
                'Content-Disposition: attachment; filename="Allgemeine-Geschaeftsbedingungen.pdf"'
            ],
            [
                Configure::read('test.loginEmailSuperadmin')
            ]
        );

        $this->browser->doFoodCoopShopLogout();
    }

    public function testFinishOrderTimebasedCurrencyEnabled()
    {
        $reducedMaxPercentage = 15;
        $defaultMaxPercentage = 30;
        $this->prepareTimebasedCurrencyConfiguration($reducedMaxPercentage);

        $this->loginAsSuperadmin();
        $this->fillCart();

        $this->addProductToCart(103, 5); // bratwürstel, manufacturerId 4

        $this->checkCartStatus();

        $this->finishCart(1, 1, '', '1700');
        $this->assertRegExp('/Bitte gib eine Zahl zwischen 0 und (.*) an./', $this->browser->getContent());

        $this->finishCart(1, 1, '', '');
        $this->assertRegExp('/Bitte gib eine Zahl zwischen 0 und (.*) an./', $this->browser->getContent());

        $this->finishCart(1, 1, '', '1200');
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());

        $this->checkCartStatusAfterFinish();

        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
            'contain' => [
                'TimebasedCurrencyOrders',
                'OrderDetails.TimebasedCurrencyOrderDetails',
                'OrderDetails.OrderDetailTaxes'
            ]
        ])->first();

        // check table order
        $this->assertEquals($order->total_paid, 18.416364, 'order->total_paid not correct');
        $this->assertEquals($order->total_paid_tax_incl, 18.416364, 'order->total_paid_tax_incl not correct');
        $this->assertEquals($order->total_paid_tax_excl, 16.791245, 'order->total_paid_tax_excl not correct');

        // check table order_detail
        $this->assertEquals($order->order_details[0]->total_price_tax_incl, 2.700000, 'order_detail->total_price_tax_incl not correct');
        $this->assertEquals($order->order_details[0]->total_price_tax_excl, 2.455786, 'order_detail->total_price_tax_excl not correct');

        $this->assertEquals($order->order_details[1]->total_price_tax_incl, 15.240000, 'order_detail->total_price_tax_incl not correct');
        $this->assertEquals($order->order_details[1]->total_price_tax_excl,  13.859095, 'order_detail->total_price_tax_excl not correct');

        $this->assertEquals($order->order_details[2]->total_price_tax_incl, 0.476364, 'order_detail->total_price_tax_incl not correct');
        $this->assertEquals($order->order_details[2]->total_price_tax_excl, 0.476364, 'order_detail->total_price_tax_excl not correct');

        $this->assertEquals($order->order_details[3]->total_price_tax_incl, 1.860000, 'order_detail->total_price_tax_incl not correct');
        $this->assertEquals($order->order_details[3]->total_price_tax_excl, 1.636365, 'order_detail->total_price_tax_excl not correct');

        // check table timebased_currency_order
        $this->assertEquals($order->timebased_currency_order->money_excl_sum, 3.590000, 'timebased_currency_order->money_excl_sum not correct');
        $this->assertEquals($order->timebased_currency_order->money_incl_sum, 3.940000, 'timebased_currency_order->money_incl_sum not correct');
        $this->assertEquals($order->timebased_currency_order->seconds_sum, 1200, 'timebased_currency_order->seconds_sum not correct');

        // check table timebased_currency_order_details
        $this->assertEquals($order->order_details[0]->timebased_currency_order_detail->money_excl, 0.85, 'order_detail timebased_currency_order_detail->money_excl not correct');
        $this->assertEquals($order->order_details[0]->timebased_currency_order_detail->money_incl, 0.94, 'order_detail timebased_currency_order_detail->money_incl not correct');
        $this->assertEquals($order->order_details[0]->timebased_currency_order_detail->seconds, 336, 'order_detail timebased_currency_order_detail->seconds not correct');
        $this->assertEquals($order->order_details[0]->timebased_currency_order_detail->max_percentage, $defaultMaxPercentage, 'order_detail timebased_currency_order_detail->max_percentage not correct');
        $this->assertEquals($order->order_details[0]->timebased_currency_order_detail->exchange_rate, Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE')), 'order_detail timebased_currency_order_detail->exchange_rate not correct');

        $this->assertEquals($order->order_details[1]->timebased_currency_order_detail->money_excl, 2.05, 'order_detail timebased_currency_order_detail->money_excl not correct');
        $this->assertEquals($order->order_details[1]->timebased_currency_order_detail->money_incl, 2.26, 'order_detail timebased_currency_order_detail->money_incl not correct');
        $this->assertEquals($order->order_details[1]->timebased_currency_order_detail->seconds, 805, 'order_detail timebased_currency_order_detail->seconds not correct');
        $this->assertEquals($order->order_details[1]->timebased_currency_order_detail->max_percentage, $reducedMaxPercentage, 'order_detail timebased_currency_order_detail->max_percentage not correct');
        $this->assertEquals($order->order_details[1]->timebased_currency_order_detail->exchange_rate, Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE')), 'order_detail timebased_currency_order_detail->exchange_rate not correct');

        $this->assertEquals($order->order_details[2]->timebased_currency_order_detail->money_excl, 0.160000, 'order_detail timebased_currency_order_detail->money_excl not correct');
        $this->assertEquals($order->order_details[2]->timebased_currency_order_detail->money_incl, 0.160000, 'order_detail timebased_currency_order_detail->money_incl not correct');
        $this->assertEquals($order->order_details[2]->timebased_currency_order_detail->seconds, 59, 'order_detail timebased_currency_order_detail->seconds not correct');
        $this->assertEquals($order->order_details[2]->timebased_currency_order_detail->max_percentage, $defaultMaxPercentage, 'order_detail timebased_currency_order_detail->max_percentage not correct');
        $this->assertEquals($order->order_details[2]->timebased_currency_order_detail->exchange_rate, Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE')), 'order_detail timebased_currency_order_detail->exchange_rate not correct');

        $this->assertEmpty($order->order_details[3]->timebased_currency_order_detail);

    }

    public function testFinishCartWithPricePerUnit()
    {
        $this->loginAsSuperadmin();

        $productIdA = 347; // forelle
        $productIdB = '348-11'; // rindfleisch, 0,5 kg

        $this->addProductToCart($productIdA, 2);
        $this->addProductToCart($productIdB, 3);

        $this->finishCart(1, 1);
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());

        $this->checkCartStatusAfterFinish();

        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
            'contain' => [
                'OrderDetails.OrderDetailTaxes',
                'OrderDetails.OrderDetailUnits'
            ]
        ])->first();

        // check order
        $this->assertEquals($order->total_paid_tax_excl, 36.81, 'order total_paid_tax_excl not correct');
        $this->assertEquals($order->total_paid_tax_incl, 40.500000, 'order total_paid_tax_incl not correct');

        // check order_details
        $this->checkOrderDetails($order->order_details[0], 'Forelle : Stück', 2, 0, 0, 9.54, 10.5, 0.48, 0.96, 2);
        $this->checkOrderDetails($order->order_details[1], 'Rindfleisch', 3, 11, 0, 27.27, 30, 0.91, 2.73, 2);

        // check order_details_units
        $this->assertEquals($order->order_details[0]->order_detail_unit->product_quantity_in_units, 700);
        $this->assertEquals($order->order_details[0]->order_detail_unit->price_incl_per_unit, 1.5);
        $this->assertEquals($order->order_details[0]->order_detail_unit->quantity_in_units, 350);
        $this->assertEquals($order->order_details[0]->order_detail_unit->unit_name, 'g');
        $this->assertEquals($order->order_details[0]->order_detail_unit->unit_amount, 100);

        $this->assertEquals($order->order_details[1]->order_detail_unit->product_quantity_in_units, 1.5);
        $this->assertEquals($order->order_details[1]->order_detail_unit->price_incl_per_unit, 20);
        $this->assertEquals($order->order_details[1]->order_detail_unit->quantity_in_units, 0.5);
        $this->assertEquals($order->order_details[1]->order_detail_unit->unit_name, 'kg');
        $this->assertEquals($order->order_details[1]->order_detail_unit->unit_amount, 1);

        // check order_detail_taxes
        $this->assertEquals($order->order_details[0]->order_detail_tax->unit_amount, 0.48);
        $this->assertEquals($order->order_details[0]->order_detail_tax->total_amount, 0.96);

        $this->assertEquals($order->order_details[1]->order_detail_tax->unit_amount, 0.91);
        $this->assertEquals($order->order_details[1]->order_detail_tax->total_amount, 2.73);

    }

    public function testInstantOrder()
    {
        $this->loginAsSuperadmin();
        $testCustomer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => Configure::read('test.customerId')
            ]
        ])->first();
        $responseHtml = $this->browser->get($this->Slug->getOrdersList().'/initInstantOrder/' . Configure::read('test.customerId'));
        $this->assertRegExp('/Diese Bestellung wird für \<b\>' . $testCustomer->name . '\<\/b\> getätigt./', $responseHtml);
        $this->assertUrl($this->browser->getUrl(), $this->browser->baseUrl . '/', 'redirect did not work');
    }

    /**
     * cart products should never have the amount 0
     * with a bit of hacking it would be possible, check here that if that happens,
     * finishing the cart does not break the order
     */
    public function testOrderIfAmountOfOneProductIsNull()
    {
        $this->loginAsCustomer();
        $this->addProductToCart($this->productId1, 1);
        $this->addProductToCart($this->productId1, - 1);
        $this->addProductToCart($this->productId2, 1);
        $this->finishCart();
        $orderId = Configure::read('app.htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());
        $this->assertTrue(is_int($orderId), 'order not finished correctly');

        $this->checkCartStatusAfterFinish();

        // only one order with the cake cart id should have been created
        $orders = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_cart' => 1
            ],
            'contain' => [
                'OrderDetails'
            ]
        ]);
        $this->assertEquals(1, $orders->count(), 'more than one order inserted');

        foreach ($orders as $order) {
            foreach ($order->order_details as $orderDetail) {
                $this->assertFalse($orderDetail->product_amount == 0, 'product amount must not be 0!');
            }
        }
    }

    private function fillCart()
    {
        $this->addProductToCart($this->productId1, 2); // product
        $this->addProductToCart($this->productId2, 3); // attribute
        $this->addProductToCart($this->productId3, 1); // product with zero tax
    }

    /**
     * before finishing cart!
     */
    private function checkCartStatus()
    {
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals($cart['Cart']['status'], 1, 'cake cart status wrong');
        $this->assertEquals($cart['Cart']['id_cart'], 2, 'cake cart id wrong');
    }

    /**
     * cake cart status check AFTER finish
     * as cart is finished, a new cart is already existing
     */
    private function checkCartStatusAfterFinish()
    {
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => 1
            ]
        ])->first();
        $this->assertEquals($cart['Cart']['status'], 0, 'cake cart status wrong');
    }

    private function addTooManyProducts($productId, $amount, $expectedAmount, $expectedErrorMessage, $productIndex)
    {
        $this->addProductToCart($productId, $amount);
        $response = $this->browser->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertEquals($productId, $response->productId);
        $this->assertJsonError();
        $cart = $this->Cart->getCart($this->browser->getLoggedUserId());
        $this->assertEquals($expectedAmount, $cart['CartProducts'][$productIndex]['amount'], 'amount not found in cart or wrong');
    }

    private function checkValidationError()
    {
        $this->assertRegExp('/initCartErrors()/', $this->browser->getContent());
    }

    private function changeStockAvailable($productId, $amount)
    {
        $this->Product->changeQuantity([[$productId => $amount]]);
    }

    private function checkStockAvailable($productId, $result)
    {
        $ids = $this->Product->getProductIdAndAttributeId($productId);

        // get changed product
        $stockAvailable = $this->StockAvailable->find('all', [
            'conditions' => [
                'StockAvailables.id_product' => $ids['productId'],
                'StockAvailables.id_product_attribute' => $ids['attributeId']
            ]
        ])->first();

        // stock available check of changed product
        $this->assertEquals($stockAvailable->quantity, $result, 'stockavailable quantity wrong');
    }

    private function checkOrder($order, $orderId, $orderComment)
    {
        $this->assertNotEquals([], $order, 'order not correct');
        $this->assertEquals($order->id_order, $orderId, 'order id not correct');
        $this->assertEquals($order->id_customer, $this->browser->getLoggedUserId(), 'order customer_id not correct');
        $this->assertEquals($order->id_cart, 2, 'order cart_id not correct');
        $this->assertEquals($order->current_state, 3, 'order current_state not correct');
        $this->assertEquals($order->total_deposit, 2.5, 'order total_deposit not correct');
        $this->assertEquals($order->total_paid_tax_excl, 5.578515, 'order total_paid_tax_excl not correct');
        $this->assertEquals($order->total_paid_tax_incl, 6.136364, 'order total_paid_tax_incl not correct');
        $this->assertEquals($order->general_terms_and_conditions_accepted, 1, 'order general_terms_and_conditions_accepted not correct');
        $this->assertEquals($order->cancellation_terms_accepted, 1, 'order cancellation_terms_accepted not correct');
        $this->assertEquals($order->comment, $orderComment, 'order comment not correct');
    }

    private function checkOrderDetails($orderDetail, $name, $amount, $productAttributeId, $deposit, $totalPriceTaxExcl, $totalPriceTaxIncl, $taxUnitAmount, $taxTotalAmount, $taxId)
    {

        // check order_details
        $this->assertEquals($orderDetail->product_name, $name, '%s order_detail product name was not correct');
        $this->assertEquals($orderDetail->product_amount, $amount, 'order_detail amount was not correct');
        $this->assertEquals($orderDetail->product_attribute_id, $productAttributeId, 'order_detail product_attribute_id was not correct');
        $this->assertEquals($orderDetail->deposit, $deposit, 'order_detail deposit was not correct');
        $this->assertEquals($orderDetail->total_price_tax_excl, $totalPriceTaxExcl, 'order_detail total_price_tax_excl not correct');
        $this->assertEquals($orderDetail->total_price_tax_incl, $totalPriceTaxIncl, 'order_detail total_price_tax_incl not correct');
        $this->assertEquals($orderDetail->id_tax, $taxId, 'order_detail id_tax not correct');

        // check order_details_tax
        $this->assertEquals($orderDetail->order_detail_tax->unit_amount, $taxUnitAmount, 'order_detail tax unit amount not correct');
        $this->assertEquals($orderDetail->order_detail_tax->total_amount, $taxTotalAmount, 'order_detail tax total amount not correct');
    }


    /**
     * @param int $productId
     * @param int $amount
     * @return json string
     */
    private function changeProductStatus($productId, $status)
    {
        $this->Product->changeStatus([[$productId => $status]]);
    }

    private function changeManufacturerStatus($manufacturerId, $status)
    {
        $this->changeManufacturer($manufacturerId, 'active', $status);
    }

    /**
     * @param int $productId
     * @return json string
     */
    private function removeProduct($productId)
    {
        $this->browser->ajaxPost('/warenkorb/ajaxRemove', [
            'productId' => $productId
        ]);
        return $this->browser->getJsonDecodedContent();
    }
}
