<?php
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
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
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

    public function setUp(): void
    {
        parent::setUp();
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $this->Product = TableRegistry::getTableLocator()->get('Products');
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
        $response = $this->addProductToCart($this->productId1, 251);
        $this->assertRegExpWithUnquotedString('Die gewünschte Anzahl <b>251</b> ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddAmountNotAvailableAnyMore()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 98);
        $this->assertRegExpWithUnquotedString('Die gewünschte Anzahl <b>98</b> des Produktes <b>Artischocke</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 97', $response->msg);
        $this->assertJsonError();
    }
    
    public function testAddProductDeliveryRhythmIndividualOrderNotPossibleAnyMore()
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm($this->productId1, '0-individual', '2018-12-14', '2018-07-12');
        $response = $this->addProductToCart($this->productId1, 1);
        $this->assertRegExpWithUnquotedString('Das Produkt <b>Artischocke</b> kann nicht mehr bestellt werden.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testAddProductDeliveryRhythmIndividualOrderPossible()
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm($this->productId1, '0-individual', '2035-12-14', '2035-07-12');
        $this->addProductToCart($this->productId1, 1);
        $this->assertJsonOk();
    }
    
    public function testOrderAlwaysAvailableWithNotEnoughQuantityForProductAttribute()
    {
        $originalQuantity = 2;
        $this->doPrepareAlwaysAvailable($this->productId2, $originalQuantity);
        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $this->Product->getProductIdAndAttributeId($this->productId2)['productId'],
            ],
            'contain' => [
                'ProductAttributes.StockAvailables'
            ]
        ])->first();
        // quantity must not have changed
        $this->assertEquals($originalQuantity, $product->product_attributes[0]->stock_available->quantity);
    }
    
    public function testOrderAlwaysAvailableWithNotEnoughQuantityForProduct()
    {
        $originalQuantity = 2;
        $this->doPrepareAlwaysAvailable($this->productId1, $originalQuantity);
        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $this->productId1,
            ],
            'contain' => [
                'StockAvailables'
            ]
        ])->first();
        // quantity must not have changed
        $this->assertEquals($originalQuantity, $product->stock_available->quantity);
    }
    
    public function testOrderAlwaysAvailableWithNotEnoughQuantityForEnabledStockProduct() {
        $originalQuantity = 2;
        $this->changeManufacturer(5, 'stock_management_enabled', 1);
        $this->Product->changeIsStockProduct([[$this->productId1 => true]]);
        $this->Product->changeQuantity([[$this->productId1 => [
            'always_available' => 1,
            'quantity' => $originalQuantity,
        ]]]);
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 50);
        $this->assertRegExpWithUnquotedString('Die gewünschte Anzahl <b>50</b> des Produktes <b>Artischocke</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 2', $response->msg);
    }
    
    public function testOrderAlwaysAvailableWithNotEnoughQuantityForEnabledStockProductAttributes() {
        $originalQuantity = 2;
        $this->changeManufacturer(15, 'stock_management_enabled', 1);
        $productId = $this->Product->getProductIdAndAttributeId($this->productId2)['productId'];
        $this->Product->changeIsStockProduct([[$productId => true]]);
        $this->Product->changeQuantity([[$this->productId2 => [
            'always_available' => 1,
            'quantity' => $originalQuantity,
        ]]]);
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId2, 50);
        $this->assertRegExpWithUnquotedString('Die gewünschte Anzahl <b>50</b> der Variante <b>0,5l</b> des Produktes <b>Milch</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 2', $response->msg);
    }
    
    public function testStockManagementEnabledIsStockProductFalseAndQuantityLimitLessThanZero()
    {
        $this->changeManufacturer(15, 'stock_management_enabled', 1);
        $productId = $this->Product->getProductIdAndAttributeId($this->productId2)['productId'];
        $this->Product->changeIsStockProduct([[$productId => false]]);
        $this->Product->changeQuantity([[$this->productId2 => [
            'always_available' => 0,
            'quantity' => 0,
            'quantity_limit' => -5,
        ]]]);
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId2, 1);
        $this->assertRegExpWithUnquotedString('Die gewünschte Anzahl <b>1</b> der Variante <b>0,5l</b> des Produktes <b>Milch</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 0', $response->msg);
    }
    
    /**
     * very rarely product ids were mixed
     */
    public function testDecreaseQuantityIfProductWithAttributeWasInSameCart()
    {
        $this->changeManufacturer(5, 'stock_management_enabled', 0);
        $this->Product->changeIsStockProduct([[$this->productId1 => false]]);
        $this->Product->changeQuantity([[$this->productId1 => [
            'always_available' => 0,
            'quantity' => 6,
            'quantity_limit' => 0,
            'sold_out_limit' => 0,
        ]]]);
        $this->loginAsSuperadmin();
        $this->addProductToCart($this->productId1, 1);
        $this->addProductToCart('348-12', 5);
        $this->finishCart();
        $this->checkStockAvailable($this->productId1, 5);
    }
    
    private function doPrepareAlwaysAvailable($productId, $originalQuantity)
    {
        $this->Product->changeQuantity([[$productId => [
            'always_available' => 1,
            'quantity' => $originalQuantity,
        ]]]);
        $this->loginAsCustomer();
        $this->addProductToCart($productId, 50);
        $this->assertJsonOk();
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());
        $this->assertTrue(is_int($cartId), 'cart not finished correctly');
    }
    
    public function testRemoveProduct()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 2);
        $this->assertJsonOk();
        $response = $this->removeProduct($this->productId1);
        $cart = $this->Cart->getCart($this->httpClient->getLoggedUserId(), $this->Cart::CART_TYPE_WEEKLY_RHYTHM);
        $this->assertEquals([], $cart['CartProducts'], 'cart must be empty');
        $this->assertJsonOk();
        $response = $this->removeProduct($this->productId1);
        $this->assertRegExpWithUnquotedString('Produkt 346 war nicht in Warenkorb vorhanden.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testRemoveProductIfProductAttributeWasDeletedAndOtherProductAttributesExistAfterAddingToCart()
    {
        $this->loginAsCustomer();
        $this->addProductToCart($this->productId2, 1);
        $query = 'UPDATE ' . $this->Product->getTable().' SET active = 0 WHERE id_product = 60';
        $this->dbConnection->execute($query);
        $query = 'UPDATE ' . $this->Cart->CartProducts->getTable().' SET id_product_attribute = 5000 WHERE id_cart_product = 3';
        $this->dbConnection->execute($query);
        $this->removeProduct($this->productId2);
        $cart = $this->Cart->getCart($this->httpClient->getLoggedUserId(), $this->Cart::CART_TYPE_WEEKLY_RHYTHM);
        $this->assertEquals([], $cart['CartProducts'], 'cart must be empty');
        $this->assertJsonOk();
    }

    public function testProductPlacedInCart()
    {
        $this->loginAsSuperadmin();

        $amount1 = 2;
        $this->addProductToCart($this->productId1, $amount1);
        $this->assertJsonOk();

        // check if product was placed in cart
        $cart = $this->Cart->getCart($this->httpClient->getLoggedUserId(), $this->Cart::CART_TYPE_WEEKLY_RHYTHM);
        $this->assertEquals($this->productId1, $cart['CartProducts'][0]['productId'], 'product id not found in cart');
        $this->assertEquals($amount1, $cart['CartProducts'][0]['amount'], 'amount not found in cart or amount wrong');
    }

    public function testAttributePlacedInCart()
    {
        $this->loginAsSuperadmin();
        $amount2 = 3;
        $this->addProductToCart($this->productId2, $amount2);
        $this->assertJsonOk();
        $cart = $this->Cart->getCart($this->httpClient->getLoggedUserId(), $this->Cart::CART_TYPE_WEEKLY_RHYTHM);
        $this->assertEquals($this->productId2, $cart['CartProducts'][0]['productId'], 'product id not found in cart');
        $this->assertEquals($amount2, $cart['CartProducts'][0]['amount'], 'amount not found in cart or amount wrong');
    }

    public function testAddTooManyProducts()
    {
        $this->loginAsSuperadmin();
        $amount = 1;
        $this->addProductToCart($this->productId1, $amount);
        $this->addTooManyProducts($this->productId1, 250, $amount, 'Die gewünschte Anzahl <b>251</b> des Produktes <b>Artischocke</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 97', 0);
    }

    public function testAddTooManyAttributes()
    {
        $this->loginAsSuperadmin();
        $amount = 1;
        $this->addProductToCart($this->productId2, $amount);
        $this->addTooManyProducts($this->productId2, 48, 1, 'Die gewünschte Anzahl <b>49</b> der Variante <b>0,5l</b> des Produktes <b>Milch</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 19', 0);
    }

    public function testProductDeactivatedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->changeProductStatus($this->productId1, APP_OFF);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertMatchesRegularExpression('/Das Produkt (.*) ist leider nicht mehr aktiviert und somit nicht mehr bestellbar./', $this->httpClient->getContent());
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
        $this->assertMatchesRegularExpression('/Der Hersteller des Produktes (.*) hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar./', $this->httpClient->getContent());
        $this->changeManufacturerStatus($manufacturerId, APP_ON);
    }

    public function testManufacturerDeliveryBreakActivatedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $manufacturerId = 5;
        $this->changeManufacturerNoDeliveryDays($manufacturerId, Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb());
        $this->finishCart();
        $this->checkValidationError();
        $this->assertMatchesRegularExpression('/Der Hersteller des Produktes (.*) hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar./', $this->httpClient->getContent());
        $this->changeManufacturerNoDeliveryDays($manufacturerId);
    }

    public function testGlobalDeliveryBreakActivatedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb());
        $this->loginAsSuperadmin();
        $this->finishCart(0, 0);
        $this->checkValidationError();
        $this->assertMatchesRegularExpression('/(.*) hat die Lieferpause aktiviert und das Produkt (.*) ist nicht mehr bestellbar./', $this->httpClient->getContent());
    }
    
    public function testProductStockAvailableDecreasedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->changeStockAvailable($this->productId1, 1);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertMatchesRegularExpression('/Anzahl <b>2/', $this->httpClient->getContent());
        $this->assertRegExpWithUnquotedString('Menge: 1', $this->httpClient->getContent()); // ü needs to be escaped properly
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
        $this->assertMatchesRegularExpression('/Anzahl \<b\>3/', $this->httpClient->getContent());
        $this->assertRegExpWithUnquotedString('Menge: 1', $this->httpClient->getContent()); // ü needs to be escaped properly
        $this->changeStockAvailable($this->productId2, 20); // reset to old stock available
    }

    public function testFinishCartCheckboxesValidation()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->finishCart(0, 0);
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die AGB.', $this->httpClient->getContent(), 'checkbox validation general_terms_and_conditions_accepted did not work');
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.', $this->httpClient->getContent(), 'checkbox validation cancellation_terms_accepted did not work');
    }

    public function testFinishCartOrderCommentValidation()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->finishCart(1, 1, 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, adfasfd sa');
        $this->assertRegExpWithUnquotedString('Bitte gib maximal 500 Zeichen ein.', $this->httpClient->getContent(), 'order comment validation did not work');
    }

    public function testFinishOrderWithComment()
    {

        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $pickupDayComment = 'this is a valid pickup day comment';
        $this->finishCart(1, 1, $pickupDayComment);
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());
        $this->checkCartStatusAfterFinish();

        $cart = $this->getCartById($cartId);
        $pickupDay = Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb();
        
        // check order_details for product1 (index 2!)
        $this->checkOrderDetails($cart->cart_products[2]->order_detail, 'Artischocke : Stück', 2, 0, 1, 3.3, 3.64, 0.17, 0.34, 2, $pickupDay);

        // check order_details for product2 (index 0!)
        $this->checkOrderDetails($cart->cart_products[0]->order_detail, 'Milch : 0,5l', 3, 10, 1.5, 1.65, 1.86, 0.07, 0.21, 3, $pickupDay);

        // check order_details for product3 (index 1!)
        $this->checkOrderDetails($cart->cart_products[1]->order_detail, 'Knoblauch : 100 g', 1, 0, 0, 0.64, 0.64, 0.000000, 0.000000, 0, $pickupDay);

        $this->checkStockAvailable($this->productId1, 95);
        $this->checkStockAvailable($this->productId2, 16); // product is NOT always available!
        $this->checkStockAvailable($this->productId3, 77);

        // check new (empty) cart
        $cart = $this->Cart->getCart($this->httpClient->getLoggedUserId(), $this->Cart::CART_TYPE_WEEKLY_RHYTHM);
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

        $this->httpClient->doFoodCoopShopLogout();
    }
    
    public function testProductsWithAllowedNegativeStock() {
        $this->changeManufacturer(5, 'stock_management_enabled', true);
        $this->loginAsCustomer();
        $this->addProductToCart(349, 8);
        $this->assertJsonOk();
    }
    
    public function testProductsWithAllowedNegativeStockButTooHighAmount() {
        $this->changeManufacturer(5, 'stock_management_enabled', true);
        $this->loginAsCustomer();
        $response = $this->addProductToCart(349, 11);
        $this->assertRegExpWithUnquotedString('Die gewünschte Anzahl <b>11</b> des Produktes <b>Lagerprodukt</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 10', $response->msg);
        $this->assertJsonError();
    }
    
    private function placeOrderWithStockProducts() {
        $stockProductId = 349;
        $stockProductAttributeId = '350-13';
        $this->addProductToCart($stockProductId, 6);
        $this->addProductToCart($stockProductAttributeId, 5);
        $this->finishCart(1, 1);
    }
    
    public function testFinishOrderWithMultiplePickupDays() {
        
        $this->loginAsSuperadmin();
        $productIdA = 346;
        $productIdB = 347;
        $productIdC = '60-10';
        $this->changeProductDeliveryRhythm($productIdA, '0-individual', '28.09.2018');
        $this->addProductToCart($productIdA, 3);
        $this->addProductToCart($productIdB, 2);
        $this->addProductToCart($productIdC, 1);
        $this->finishCart(1, 1);
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs));
    }
    
    public function testFinishOrderStockNotificationsIsStockProductDisabled() {
        
        $this->loginAsSuperadmin();
        $this->httpClient->ajaxPost('/admin/products/editIsStockProduct', [
            'productId' => 350,
            'isStockProduct' => 0
        ]);
        $this->httpClient->ajaxPost('/admin/products/editIsStockProduct', [
            'productId' => 349,
            'isStockProduct' => 0
        ]);
        $this->placeOrderWithStockProducts();
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs));
    }
    
    public function testFinishOrderStockNotificationsStockManagementDisabled() {
        
        $this->loginAsSuperadmin();
        $this->placeOrderWithStockProducts();
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs));
    }
    
    public function testFinishOrderStockNotificationsDisabled() {
        
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_product_sold_out_limit_reached_for_manufacturer', 0);
        $this->changeManufacturer($manufacturerId, 'send_product_sold_out_limit_reached_for_contact_person', 0);
        
        $this->loginAsSuperadmin();
        $this->placeOrderWithStockProducts();
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs));
    }
    
    public function testFinishOrderStockNotificationsEnabled()
    {
        
        $this->loginAsSuperadmin();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'stock_management_enabled', true);
        
        $this->placeOrderWithStockProducts();
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        
        // check email to manufacturer
        $this->assertEmailLogs(
            $emailLogs[0],
            'Lagerstand für Produkt "Lagerprodukt mit Varianten : 0,5 kg": 0',
            [
                'Lagerstand: <b>0</b>',
                'Bestellungen möglich bis zu einem Lagerstand von: <b>-5</b>'
            ],
            [
                Configure::read('test.loginEmailVegetableManufacturer')
            ]
        );
        
        // check email to contact person
        $this->assertEmailLogs(
            $emailLogs[1],
            '',
            [],
            [
                Configure::read('test.loginEmailAdmin')
            ]
        );
        
        // check email to manufacturer
        $this->assertEmailLogs(
            $emailLogs[2],
            'Lagerstand für Produkt "Lagerprodukt": -1',
            [
                'Lagerstand: <b>-1</b>',
                'Bestellungen möglich bis zu einem Lagerstand von: <b>-5</b>'
            ],
            [
                Configure::read('test.loginEmailVegetableManufacturer')
            ]
        );
        
        // check email to contact person
        $this->assertEmailLogs(
            $emailLogs[3],
            '',
            [],
            [
                Configure::read('test.loginEmailAdmin')
            ]
        );
        
        $this->httpClient->doFoodCoopShopLogout();
    }

    public function testFinishOrderTimebasedCurrencyEnabledCustomerOverdraftReached()
    {
        $reducedMaxPercentage = 15;
        $this->prepareTimebasedCurrencyConfiguration($reducedMaxPercentage);
        
        $this->loginAsSuperadmin();
        $this->fillCart();
        
        // bratwürstel, manufacturerId 4
        $this->addProductToCart(103, 50); 
        $this->addProductToCart(103, 99);
        $this->addProductToCart(103, 99);
        
        $this->finishCart(1, 1, '', '38000');
        $this->assertRegExpWithUnquotedString('Dein Überziehungsrahmen von 10 h ist erreicht.', $this->httpClient->getContent());
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
        $this->assertMatchesRegularExpression('/Bitte gib eine Zahl zwischen 0 und (.*) an./', $this->httpClient->getContent());

        $this->finishCart(1, 1, '', '');
        $this->assertMatchesRegularExpression('/Bitte gib eine Zahl zwischen 0 und (.*) an./', $this->httpClient->getContent());

        $this->finishCart(1, 1, '', '1200');

        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());
        $this->checkCartStatusAfterFinish();
        
        $cart = $this->getCartById($cartId);
        $orderDetailA = $cart->cart_products[3]->order_detail;
        $orderDetailB = $cart->cart_products[2]->order_detail;
        $orderDetailC = $cart->cart_products[1]->order_detail;
        $orderDetailD = $cart->cart_products[0]->order_detail;
        
        // check table order_detail
        $this->assertEquals($orderDetailA->total_price_tax_incl, 2.700000, 'order_detail->total_price_tax_incl not correct');
        $this->assertEquals($orderDetailA->total_price_tax_excl, 2.450000, 'order_detail->total_price_tax_excl not correct');

        $this->assertEquals($orderDetailB->total_price_tax_incl, 15.240000, 'order_detail->total_price_tax_incl not correct');
        $this->assertEquals($orderDetailB->total_price_tax_excl,  13.850000, 'order_detail->total_price_tax_excl not correct');

        $this->assertEquals($orderDetailC->total_price_tax_incl, 0.480000, 'order_detail->total_price_tax_incl not correct');
        $this->assertEquals($orderDetailC->total_price_tax_excl, 0.480000, 'order_detail->total_price_tax_excl not correct');

        $this->assertEquals($orderDetailD->total_price_tax_incl, 1.860000, 'order_detail->total_price_tax_incl not correct');
        $this->assertEquals($orderDetailD->total_price_tax_excl, 1.650000, 'order_detail->total_price_tax_excl not correct');

        // check table timebased_currency_order_details
        $this->assertEquals($orderDetailA->timebased_currency_order_detail->money_excl, 0.85, 'order_detail timebased_currency_order_detail->money_excl not correct');
        $this->assertEquals($orderDetailA->timebased_currency_order_detail->money_incl, 0.94, 'order_detail timebased_currency_order_detail->money_incl not correct');
        $this->assertEquals($orderDetailA->timebased_currency_order_detail->seconds, 336, 'order_detail timebased_currency_order_detail->seconds not correct');
        $this->assertEquals($orderDetailA->timebased_currency_order_detail->max_percentage, $defaultMaxPercentage, 'order_detail timebased_currency_order_detail->max_percentage not correct');
        $this->assertEquals($orderDetailA->timebased_currency_order_detail->exchange_rate, Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE')), 'order_detail timebased_currency_order_detail->exchange_rate not correct');

        $this->assertEquals($orderDetailB->timebased_currency_order_detail->money_excl, 2.05, 'order_detail timebased_currency_order_detail->money_excl not correct');
        $this->assertEquals($orderDetailB->timebased_currency_order_detail->money_incl, 2.26, 'order_detail timebased_currency_order_detail->money_incl not correct');
        $this->assertEquals($orderDetailB->timebased_currency_order_detail->seconds, 805, 'order_detail timebased_currency_order_detail->seconds not correct');
        $this->assertEquals($orderDetailB->timebased_currency_order_detail->max_percentage, $reducedMaxPercentage, 'order_detail timebased_currency_order_detail->max_percentage not correct');
        $this->assertEquals($orderDetailB->timebased_currency_order_detail->exchange_rate, Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE')), 'order_detail timebased_currency_order_detail->exchange_rate not correct');

        $this->assertEquals($orderDetailC->timebased_currency_order_detail->money_excl, 0.160000, 'order_detail timebased_currency_order_detail->money_excl not correct');
        $this->assertEquals($orderDetailC->timebased_currency_order_detail->money_incl, 0.160000, 'order_detail timebased_currency_order_detail->money_incl not correct');
        $this->assertEquals($orderDetailC->timebased_currency_order_detail->seconds, 59, 'order_detail timebased_currency_order_detail->seconds not correct');
        $this->assertEquals($orderDetailC->timebased_currency_order_detail->max_percentage, $defaultMaxPercentage, 'order_detail timebased_currency_order_detail->max_percentage not correct');
        $this->assertEquals($orderDetailC->timebased_currency_order_detail->exchange_rate, Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE')), 'order_detail timebased_currency_order_detail->exchange_rate not correct');

        $this->assertEmpty($orderDetailD->timebased_currency_order_detail);

    }

    public function testFinishCartWithPricePerUnit()
    {
        $this->loginAsSuperadmin();

        $productIdA = 347; // forelle
        $productIdB = '348-11'; // rindfleisch, 0,5 kg

        $this->addProductToCart($productIdA, 2);
        $this->addProductToCart($productIdB, 3);

        $this->finishCart(1, 1);
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());

        $this->checkCartStatusAfterFinish();
        $cart = $this->getCartById($cartId);
        $pickupDay = Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb();
        
        // check order_details
        $this->checkOrderDetails($cart->cart_products[1]->order_detail, 'Forelle : Stück', 2, 0, 0, 9.54, 10.5, 0.48, 0.96, 2, $pickupDay);
        $this->checkOrderDetails($cart->cart_products[0]->order_detail, 'Rindfleisch', 3, 11, 0, 27.27, 30, 0.91, 2.73, 2, $pickupDay);

        // check order_details_units
        $orderDetailA = $cart->cart_products[1]->order_detail;
        $orderDetailB = $cart->cart_products[0]->order_detail;
        
        $this->assertEquals($orderDetailA->order_detail_unit->product_quantity_in_units, 700);
        $this->assertEquals($orderDetailA->order_detail_unit->price_incl_per_unit, 1.5);
        $this->assertEquals($orderDetailA->order_detail_unit->quantity_in_units, 350);
        $this->assertEquals($orderDetailA->order_detail_unit->unit_name, 'g');
        $this->assertEquals($orderDetailA->order_detail_unit->unit_amount, 100);

        $this->assertEquals($orderDetailB->order_detail_unit->product_quantity_in_units, 1.5);
        $this->assertEquals($orderDetailB->order_detail_unit->price_incl_per_unit, 20);
        $this->assertEquals($orderDetailB->order_detail_unit->quantity_in_units, 0.5);
        $this->assertEquals($orderDetailB->order_detail_unit->unit_name, 'kg');
        $this->assertEquals($orderDetailB->order_detail_unit->unit_amount, 1);

        // check order_detail_taxes
        $this->assertEquals($orderDetailA->order_detail_tax->unit_amount, 0.48);
        $this->assertEquals($orderDetailA->order_detail_tax->total_amount, 0.96);

        $this->assertEquals($orderDetailB->order_detail_tax->unit_amount, 0.91);
        $this->assertEquals($orderDetailB->order_detail_tax->total_amount, 2.73);

    }

    public function testInstantOrder()
    {
        
        // add a product to the "normal" cart (CART_TYPE_WEEKLY_RHYTHM)
        $this->loginAsCustomer();
        $this->addProductToCart($this->productId1, 5);
        $this->logout();
        
        $this->loginAsSuperadmin();
        $testCustomer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => Configure::read('test.customerId')
            ]
        ])->first();
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getOrderDetailsList().'/initInstantOrder/' . Configure::read('test.customerId'));
        $this->assertRegExpWithUnquotedString('Diese Bestellung wird für <b>' . $testCustomer->name . '</b> getätigt.', $this->httpClient->getContent());
        
        $this->addProductToCart($this->productId2, 3); // attribute
        $this->addProductToCart(349, 1); // stock product - no notification!
        
        $this->finishCart(1, 1);
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());
        $cart = $this->getCartById($cartId);
        
        // product that was added as CART_TYPE_WEEKLY_RHYTHM must not be included in CART_TYPE_INSTANT_ORDER cart
        $this->assertEquals(2, count($cart->cart_products));
        
        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->id_customer, $testCustomer->id_customer, 'order_detail id_customer not correct');
            $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), Configure::read('app.timeHelper')->getCurrentDateForDatabase(), 'order_detail pickup_day not correct');
        }
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(2, count($emailLogs));
        
        $this->assertEmailLogs(
            $emailLogs[0],
            'Benachrichtigung über Sofort-Bestellung',
            [
                'Milch : 0,5l',
                'Hallo Demo,',
                '1,86'
            ],
            [
                Configure::read('test.loginEmailMilkManufacturer')
            ]
        );
        
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all', [])->toArray();
        $this->assertEquals('carts', $actionLogs[0]->object_type);
        $this->assertEquals($cart->id_cart, $actionLogs[0]->object_id);
        $this->assertEquals(Configure::read('test.superadminId'), $actionLogs[0]->customer_id);
    }
    
    public function testInstantOrderWithDeliveryBreak()
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb());
        $this->loginAsSuperadmin();
        $this->httpClient->get($this->Slug->getOrderDetailsList().'/initInstantOrder/' . Configure::read('test.customerId'));
        $this->addProductToCart($this->productId1, 1);
        $this->finishCart(1, 1);
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all', [])->toArray();
        $this->assertRegExpWithUnquotedString('Die Sofort-Bestellung (1,82 €) für <b>Demo Mitglied</b> wurde erfolgreich getätigt.', $actionLogs[0]->text);
    }
    
    public function testInstantOrderWithExpiredBulkOrder()
    {
        $this->Product->save(
            $this->Product->patchEntity(
                $this->Product->get($this->productId1),
                [
                    'delivery_rhythm_type' => 'individual',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-03')
                ]
            )
        );
        
        $this->loginAsSuperadmin();
        $this->httpClient->get($this->Slug->getOrderDetailsList().'/initInstantOrder/' . Configure::read('test.customerId'));
        $this->addProductToCart($this->productId1, 1);
        $this->finishCart(1, 1);
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all', [])->toArray();
        $this->assertRegExpWithUnquotedString('Die Sofort-Bestellung (1,82 €) für <b>Demo Mitglied</b> wurde erfolgreich getätigt.', $actionLogs[0]->text);
    }
    
    public function testFinishEmptyCart()
    {
        $this->loginAsCustomer();
        $this->addProductToCart($this->productId1, 1);
        $this->removeProduct($this->productId1);
        $this->httpClient->followOneRedirectForNextRequest();
        $this->finishCart();
        $this->assertMatchesRegularExpression('/Dein Warenkorb war leer/', $this->httpClient->getContent());
    }

    /**
     * cart products should never have the amount 0
     * with a bit of hacking it would be possible
     * check here that if that happens, finishing the cart does not break the order
     */
    public function testOrderIfAmountOfOneProductIsNull()
    {
        $this->loginAsCustomer();
        $this->addProductToCart($this->productId1, 1);
        $this->addProductToCart($this->productId1, -1);
        $this->addProductToCart($this->productId2, 1);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());
        $this->assertTrue(is_int($cartId), 'cart not finished correctly');

        $this->checkCartStatusAfterFinish();
        $cart = $this->getCartById($cartId);
        $this->assertEquals(1, count($cart->cart_products));
        $this->assertEquals(1, $cart->cart_products[0]->order_detail->product_amount);
        
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
        $cart = $this->Cart->getCart($this->httpClient->getLoggedUserId(), $this->Cart::CART_TYPE_WEEKLY_RHYTHM);
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
        $this->assertEquals($cart->status, 0, 'cake cart status wrong');
    }

    private function addTooManyProducts($productId, $amount, $expectedAmount, $expectedErrorMessage, $productIndex)
    {
        $this->addProductToCart($productId, $amount);
        $response = $this->httpClient->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertEquals($productId, $response->productId);
        $this->assertJsonError();
        $cart = $this->Cart->getCart($this->httpClient->getLoggedUserId(), $this->Cart::CART_TYPE_WEEKLY_RHYTHM);
        $this->assertEquals($expectedAmount, $cart['CartProducts'][$productIndex]['amount'], 'amount not found in cart or wrong');
    }

    private function checkValidationError()
    {
        $this->assertMatchesRegularExpression('/initCartErrors()/', $this->httpClient->getContent());
    }

    private function changeStockAvailable($productId, $amount)
    {
        $this->Product->changeQuantity([[$productId => ['quantity' => $amount]]]);
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

    private function checkOrderDetails($orderDetail, $name, $amount, $productAttributeId, $deposit, $totalPriceTaxExcl, $totalPriceTaxIncl, $taxUnitAmount, $taxTotalAmount, $taxId, $pickupDay)
    {

        // check order_details
        $this->assertEquals($orderDetail->product_name, $name, '%s order_detail product name was not correct');
        $this->assertEquals($orderDetail->product_amount, $amount, 'order_detail amount was not correct');
        $this->assertEquals($orderDetail->product_attribute_id, $productAttributeId, 'order_detail product_attribute_id was not correct');
        $this->assertEquals($orderDetail->deposit, $deposit, 'order_detail deposit was not correct');
        $this->assertEquals($orderDetail->total_price_tax_excl, $totalPriceTaxExcl, 'order_detail total_price_tax_excl not correct');
        $this->assertEquals($orderDetail->total_price_tax_incl, $totalPriceTaxIncl, 'order_detail total_price_tax_incl not correct');
        $this->assertEquals($orderDetail->id_tax, $taxId, 'order_detail id_tax not correct');
        $this->assertEquals($orderDetail->id_customer, $this->httpClient->getLoggedUserId(), 'order_detail id_customer not correct');
        $this->assertEquals($orderDetail->order_state, ORDER_STATE_ORDER_PLACED, 'order_detail order_state not correct');
        $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), $pickupDay, 'order_detail pickup_day not correct');
        
        // check order_details_tax
        $this->assertEquals($orderDetail->order_detail_tax->unit_amount, $taxUnitAmount, 'order_detail tax unit amount not correct');
        $this->assertEquals($orderDetail->order_detail_tax->total_amount, $taxTotalAmount, 'order_detail tax total amount not correct');
    }

    /**
     * @param int $productId
     * @param int $amount
     * @return string
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
     * @return string
     */
    private function removeProduct($productId)
    {
        $this->httpClient->ajaxPost('/warenkorb/ajaxRemove', [
            'productId' => $productId
        ]);
        return $this->httpClient->getJsonDecodedContent();
    }
}
