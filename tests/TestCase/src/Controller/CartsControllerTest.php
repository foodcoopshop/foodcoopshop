<?php
declare(strict_types=1);

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
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestEmailTransport;
use App\Services\DeliveryRhythmService;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\Date;
use App\Model\Entity\Customer;
use App\Model\Entity\Cart;
use App\Model\Entity\OrderDetail;

class CartsControllerTest extends AppCakeTestCase
{

    protected $ActionLog;
    protected $PickupDay;
    protected $ProductAttribute;
    protected $PurchasePriceProduct;
    protected $Unit;

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

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

    public function setUp(): void
    {
        parent::setUp();
        $this->Cart = $this->getTableLocator()->get('Carts');
        $this->Product = $this->getTableLocator()->get('Products');
        $this->StockAvailable = $this->getTableLocator()->get('StockAvailables');
    }

    public function testAddLoggedOut()
    {
        $response = $this->addProductToCart($this->productId1, 2);
        $this->assertRegExpWithUnquotedString('Zum Bestellen <a href="/anmelden">bitte zuerst anmelden oder neu registrieren</a>.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddAsManufacturer()
    {
        $this->loginAsVegetableManufacturer();
        $this->addProductToCart($this->productId1, 2);
        $this->assertRegExpWithUnquotedString('Herstellern steht diese Funktion leider nicht zur Verfügung.', $this->getJsonDecodedContent()->msg);
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
        $this->assertRegExpWithUnquotedString('Das Produkt mit der ID 0 ist nicht vorhanden.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddWrongAmount()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 251);
        $this->assertRegExpWithUnquotedString('Die gewünschte Menge <b>251</b> ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddAmountNotAvailableAnyMore()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 98);
        $this->assertRegExpWithUnquotedString('Die gewünschte Menge <b>98</b> des Produktes <b>Artischocke</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 97', $response->msg);
        $this->assertJsonError();
    }

    public function testAddProductWithoutCredit()
    {
        $this->resetCustomerCreditBalance();
        $this->changeConfiguration('FCS_MINIMAL_CREDIT_BALANCE', 0);
        $this->loginAsCustomer();
        // test product without attribute and deposit
        $response = $this->addProductToCart($this->productId1, 8);
        $errorMessage = 'Das Produkt um <b>15,06 €</b> kann nicht in den Warenkorb gelegt werden, bitte lade neues Guthaben auf.<br />Dein Guthaben abzüglich Warenwert und Pfand beträgt <b>0,00 €</b>, du kannst bis <b>0,00 €</b> bestellen.';
        $this->assertRegExpWithUnquotedString($errorMessage, $response->msg);
        // test product without attribute and NO deposit
        $response = $this->addProductToCart($this->productId3, 1);
        $errorMessage = 'Das Produkt um <b>0,64 €</b> kann nicht in den Warenkorb gelegt werden, bitte lade neues Guthaben auf.<br />Dein Guthaben abzüglich Warenwert und Pfand beträgt <b>0,00 €</b>, du kannst bis <b>0,00 €</b> bestellen.';
        $this->assertRegExpWithUnquotedString($errorMessage, $response->msg);
        // test product with attribute and deposit
        $errorMessage = 'Das Produkt um <b>9,18 €</b> kann nicht in den Warenkorb gelegt werden, bitte lade neues Guthaben auf.<br />Dein Guthaben abzüglich Warenwert und Pfand beträgt <b>0,00 €</b>, du kannst bis <b>0,00 €</b> bestellen.';
        $response = $this->addProductToCart($this->productId2, 14);
        $this->assertRegExpWithUnquotedString($errorMessage, $response->msg);
        $this->assertJsonError();
        // test product with attribute and NO deposit
        $errorMessage = 'Das Produkt um <b>10,00 €</b> kann nicht in den Warenkorb gelegt werden, bitte lade neues Guthaben auf.<br />Dein Guthaben abzüglich Warenwert und Pfand beträgt <b>0,00 €</b>, du kannst bis <b>0,00 €</b> bestellen.';
        $response = $this->addProductToCart('348-11', 1);
        $this->assertRegExpWithUnquotedString($errorMessage, $response->msg);
        $this->assertJsonError();
    }

    public function testAddProductWithPricePerUnitWithoutCredit()
    {
        $this->resetCustomerCreditBalance();
        $this->changeConfiguration('FCS_MINIMAL_CREDIT_BALANCE', 0);
        $this->loginAsCustomer();
        // test product without attribute
        $response = $this->addProductToCart(347, 1);
        $errorMessage = 'Das Produkt um <b>5,25 €</b> kann nicht in den Warenkorb gelegt werden, bitte lade neues Guthaben auf.<br />Dein Guthaben abzüglich Warenwert und Pfand beträgt <b>0,00 €</b>, du kannst bis <b>0,00 €</b> bestellen.';
        $this->assertRegExpWithUnquotedString($errorMessage, $response->msg);
        // test product with attribute
        $errorMessage = 'Das Produkt um <b>10,00 €</b> kann nicht in den Warenkorb gelegt werden, bitte lade neues Guthaben auf.<br />Dein Guthaben abzüglich Warenwert und Pfand beträgt <b>0,00 €</b>, du kannst bis <b>0,00 €</b> bestellen.';
        $response = $this->addProductToCart('348-11', 1);
        $this->assertRegExpWithUnquotedString($errorMessage, $response->msg);
        $this->assertJsonError();
    }

    public function testAddProductWithPricePerUnitWithoutCreditAndPurchasePriceCustomer()
    {
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->changeCustomer(Configure::read('test.customerId'), 'shopping_price', Customer::PURCHASE_PRICE);
        $this->resetCustomerCreditBalance();
        $this->changeConfiguration('FCS_MINIMAL_CREDIT_BALANCE', 0);
        $this->loginAsCustomer();
        // test product without attribute
        $this->addProductToCart(347, 1);
        $this->assertJsonOk();
    }

    public function testAddProductDeliveryRhythmIndividualOrderNotPossibleAnyMore()
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm((int) $this->productId1, '0-individual', '2018-12-14', '2018-07-12');
        $response = $this->addProductToCart($this->productId1, 1);
        $this->assertRegExpWithUnquotedString('Das Produkt <b>Artischocke</b> kann nicht mehr bestellt werden.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddProductDeliveryRhythmIndividualOrderPossible()
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm((int) $this->productId1, '0-individual', '2035-12-14', '2035-07-12');
        $this->addProductToCart($this->productId1, 1);
        $this->assertJsonOk();
    }

    public function testOrderAlwaysAvailableWithNotEnoughQuantityForProductAttribute()
    {
        $originalQuantity = 2;
        $this->doPrepareAlwaysAvailable($this->productId2, $originalQuantity);
        $product = $this->Product->find('all',
            conditions: [
                'Products.id_product' => $this->Product->getProductIdAndAttributeId($this->productId2)['productId'],
            ],
            contain: [
                'ProductAttributes.StockAvailables',
            ],
        )->first();
        // quantity must not have changed
        $this->assertEquals($originalQuantity, $product->product_attributes[0]->stock_available->quantity);
    }

    public function testOrderAlwaysAvailableWithNotEnoughQuantityForProduct()
    {
        $originalQuantity = 2;
        $this->doPrepareAlwaysAvailable($this->productId1, $originalQuantity);
        $product = $this->Product->find('all',
            conditions: [
                'Products.id_product' => $this->productId1,
            ],
            contain: [
                'StockAvailables',
            ],
        )->first();
        // quantity must not have changed
        $this->assertEquals($originalQuantity, $product->stock_available->quantity);
    }

    public function testOrderAlwaysAvailableWithNotEnoughQuantityForEnabledStockProduct()
    {
        $originalQuantity = 2;
        $this->changeManufacturer(5, 'stock_management_enabled', 1);
        $this->Product->changeIsStockProduct([[$this->productId1 => true]]);
        $this->Product->changeQuantity([[$this->productId1 => [
            'always_available' => 1,
            'quantity' => $originalQuantity,
        ]]]);
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 50);
        $this->assertRegExpWithUnquotedString('Die gewünschte Menge <b>50</b> des Produktes <b>Artischocke</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 2', $response->msg);
    }

    public function testOrderAlwaysAvailableWithNotEnoughQuantityForEnabledStockProductAttributes()
    {
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
        $this->assertRegExpWithUnquotedString('Die gewünschte Menge <b>50</b> der Variante <b>0,5l</b> des Produktes <b>Milch</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 2', $response->msg);
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
        $this->assertRegExpWithUnquotedString('Die gewünschte Menge <b>1</b> der Variante <b>0,5l</b> des Produktes <b>Milch</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 0', $response->msg);
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
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $this->assertTrue(is_int($cartId), 'cart not finished correctly');
    }

    public function testRemoveProduct()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 2);
        $this->assertJsonOk();
        $response = $this->removeProduct($this->productId1);
        $cart = $this->Cart->getCart($this, Cart::TYPE_WEEKLY_RHYTHM);
        $this->assertEquals([], $cart['CartProducts'], 'cart must be empty');
        $this->assertJsonOk();
        $response = $this->removeProduct($this->productId1);
        $this->assertRegExpWithUnquotedString('Produkt 346 war nicht in Warenkorb vorhanden.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddedProductWithoutAttributesInCartAndOnFinishProductHasAttributes()
    {
        $this->loginAsSuperadmin();
        $productId = 103;
        $this->addProductToCart($productId, 1);
        $this->assertJsonOk();
        $this->ProductAttribute= $this->getTableLocator()->get('ProductAttributes');
        $this->ProductAttribute->add($productId, 35);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExpWithUnquotedString('Dem Produkt wurden in der Zwischenzeit Varianten hinzugef', $this->_response->getBody()->__toString());
    }

    public function testRemoveProductIfProductAttributeWasDeletedAndOtherProductAttributesExistAfterAddingToCart()
    {
        $this->loginAsCustomer();
        $this->addProductToCart($this->productId2, 1);

        $productEntity = $this->Product->get(60);
        $productEntity->active = APP_OFF;
        $this->Product->save($productEntity);

        $cpEntity = $this->Cart->CartProducts->get(3);
        $cpEntity->id_product_attribute = 5000;
        $this->Cart->CartProducts->save($cpEntity);

        $this->removeProduct($this->productId2);
        $cart = $this->Cart->getCart($this, Cart::TYPE_WEEKLY_RHYTHM);
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
        $cart = $this->Cart->getCart($this, Cart::TYPE_WEEKLY_RHYTHM);
        $this->assertEquals($this->productId1, $cart['CartProducts'][0]['productId'], 'product id not found in cart');
        $this->assertEquals($amount1, $cart['CartProducts'][0]['amount'], 'amount not found in cart or amount wrong');
    }

    public function testAttributePlacedInCart()
    {
        $this->loginAsSuperadmin();
        $amount2 = 3;
        $this->addProductToCart($this->productId2, $amount2);
        $this->assertJsonOk();
        $cart = $this->Cart->getCart($this, Cart::TYPE_WEEKLY_RHYTHM);
        $this->assertEquals($this->productId2, $cart['CartProducts'][0]['productId'], 'product id not found in cart');
        $this->assertEquals($amount2, $cart['CartProducts'][0]['amount'], 'amount not found in cart or amount wrong');
    }

    public function testAddTooManyProducts()
    {
        $this->loginAsSuperadmin();
        $amount = 1;
        $this->addProductToCart($this->productId1, $amount);
        $this->addTooManyProducts($this->productId1, 250, $amount, 'Die gewünschte Menge <b>251</b> des Produktes <b>Artischocke</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 97', 0);
    }

    public function testAddTooManyAttributes()
    {
        $this->loginAsCustomer();
        $amount = 1;
        $this->addProductToCart($this->productId2, $amount);
        $this->addTooManyProducts($this->productId2, 48, 1, 'Die gewünschte Menge <b>49</b> der Variante <b>0,5l</b> des Produktes <b>Milch</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 19', 0);
    }

    public function testProductDeactivatedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->changeProductStatus($this->productId1, APP_OFF);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertMatchesRegularExpression('/Das Produkt (.*) ist leider nicht mehr aktiviert und somit nicht mehr bestellbar./', $this->_response->getBody()->__toString());
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
        $this->assertMatchesRegularExpression('/Der Hersteller des Produktes (.*) hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar./', $this->_response->getBody()->__toString());
        $this->changeManufacturerStatus($manufacturerId, APP_ON);
    }

    public function testManufacturerDeliveryBreakActivatedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $manufacturerId = 5;
        $this->changeManufacturerNoDeliveryDays($manufacturerId, (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb());
        $this->finishCart();
        $this->checkValidationError();
        $this->assertMatchesRegularExpression('/Der Hersteller des Produktes (.*) hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar./', $this->_response->getBody()->__toString());
    }

    public function testManufacturerDeliveryBreakActivatedWhileShoppingWithStockProduct()
    {
        $this->loginAsSuperadmin();
        $this->addProductToCart($this->productId3, 1);
        $this->checkCartStatus();

        $this->Product->save(
            $this->Product->patchEntity(
                $this->Product->get($this->productId3),
                [
                    'is_stock_product' => '1',
                ]
            )
        );

        $manufacturerId = 5;
        $this->changeManufacturerNoDeliveryDays($manufacturerId, (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb());
        $this->finishCart();

        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $this->checkCartStatusAfterFinish();
        $cart = $this->getCartById($cartId);
        $this->assertEquals($this->productId3, $cart->cart_products[0]->id_product);
    }

    public function testGlobalDeliveryBreakActivatedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb());
        $this->loginAsSuperadmin();
        $this->finishCart(0, 0);
        $this->checkValidationError();
        $this->assertMatchesRegularExpression('/(.*) hat die Lieferpause aktiviert und das Produkt (.*) ist nicht mehr bestellbar./', $this->_response->getBody()->__toString());
    }

    public function testProductStockAvailableDecreasedWhileShopping()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $this->changeStockAvailable($this->productId1, 1);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertMatchesRegularExpression('/Menge <b>2/', $this->_response->getBody()->__toString());
        $this->assertResponseContains('Menge: 1');
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
        $this->assertMatchesRegularExpression('/Menge \<b\>3/', $this->_response->getBody()->__toString());
        $this->assertResponseContains('Menge: 1');
        $this->changeStockAvailable($this->productId2, 20); // reset to old stock available
    }

    public function testAddAndOrderProductCustomerCanSelectPickupDayWithGlobalDeliveryBreakSameDay()
    {
        $this->changeConfiguration('FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY', 1);
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        $this->loginAsSuperadmin();
        $this->addProductToCart($this->productId1, 1);
        $this->assertJsonOk();
        $this->finishCart(1, 1, '', null, Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        $this->assertResponseNotContains('hat die Lieferpause aktiviert');
    }

    public function testCustomerCanSelectPickupDayFinishWithPickupDayValidation()
    {
        $this->changeConfiguration('FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY', 1);
        $tomorrow = $this->Time->getTomorrowForDatabase();
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', $tomorrow);
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();
        $this->finishCart(1, 1, '');
        $this->assertResponseContains('Bitte wähle einen Abholtag aus.');
        $this->finishCart(1, 1, '', null, '');
        $this->assertResponseContains('Bitte wähle einen Abholtag aus.');
        $this->finishCart(1, 1, '', null, '2020-01-01'); // do not allow past pickup days
        $this->assertResponseContains('Der Abholtag ist nicht gültig.');
        $this->finishCart(1, 1, '', null, $tomorrow); // pickup day has value of FCS_NO_DELIVERY_DAYS_GLOBAL
        $this->assertResponseContains('Der Abholtag ist nicht gültig.');
    }

    public function testCustomerCanSelectPickupDayFinishWithCorrectPickupDayAndComment()
    {

        $pickupDay = $this->Time->getTomorrowForDatabase();
        $comment = 'this is the comment';

        $this->changeConfiguration('FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY', 1);
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();
        $this->finishCart(1, 1, $comment, null, $pickupDay);

        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $this->checkCartStatusAfterFinish();

        $cart = $this->getCartById($cartId);
        $this->checkOrderDetails($cart->cart_products[0]->order_detail, 'Artischocke : Stück', 2, 0, 1, 3.3, 3.64, 0.17, 0.34, 10, $pickupDay);

        $this->PickupDay = $this->getTableLocator()->get('PickupDays');
        $pickupDayEntity = $this->PickupDay->find('all')->toArray();
        $this->assertEquals(1, count($pickupDayEntity));
        $this->assertEquals($pickupDay, $pickupDayEntity[0]->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')));

        $this->assertMailSubjectContainsAt(0, 'Bestellbestätigung');
        $this->assertMailContainsHtmlAt(0, 'Abholtag: <b> ' . $this->Time->getDateFormattedWithWeekday(strtotime($pickupDay)) . '</b>');
        $this->assertMailContainsHtmlAt(0, 'Kommentar: "<b>' . $comment . '</b>"');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));
    }

    public function testFinishCartCheckboxesValidation()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();
        $this->finishCart(0, 0);
        $this->assertResponseContains('Bitte akzeptiere die AGB.');
        $this->assertResponseContains('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.');
    }

    public function testFinishCartOrderCommentValidation()
    {
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();
        $this->finishCart(1, 1, 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, adfasfd sa');
        $this->assertResponseContains('Bitte gib maximal 500 Zeichen ein.');
    }

    public function testFinishCartWithMinimalCreditBalanceCheck()
    {
        $this->loginAsAdmin();
        $this->fillCart();
        $this->changeConfiguration('FCS_MINIMAL_CREDIT_BALANCE', 0);
        $this->finishCart(1,1);
        $this->assertResponseContains('Bitte lade neues Guthaben auf.');
        $this->assertResponseContains('-8,64');
        $this->assertMailCount(0);
    }

    public function testFinishWithPurchasePriceIncludingProductsWithoutPurchasePrice()
    {
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsAdmin();

        $this->addProductToCart('350-14', 1); // add lagerprodukt mit variante
        $this->addProductToCart(340, 1); // add beuschl
        $this->Unit = $this->getTableLocator()->get('Units');
        $this->Unit->saveUnits(346, 12, false, 1, 'kg', 1, 0.4, 0); // artischocke
        $this->Unit->saveUnits(347, 0, false, 1, 'kg', 1, 0.4, 0); // forelle

        $this->addAllDifferentProductTypesToCart();
        $this->finishCart(1,1);
        // product and missing pp per piece
        $this->assertMatchesRegularExpression('/Das Produkt (.*)Beuschl(.*) kann aufgrund von fehlenden Produktdaten zur Zeit leider nicht bestellt werden./', $this->_response->getBody()->__toString());
        // product and missing pp per unit
        $this->assertMatchesRegularExpression('/Das Produkt (.*)Forelle(.*) kann aufgrund von fehlenden Produktdaten zur Zeit leider nicht bestellt werden./', $this->_response->getBody()->__toString());
        // attribute and missing pp per piece
        $this->assertMatchesRegularExpression('/Die Variante (.*)1 kg(.*) des Produktes (.*)Lagerprodukt mit Varianten(.*) kann aufgrund von fehlenden Produktdaten zur Zeit leider nicht bestellt werden./', $this->_response->getBody()->__toString());
        // attribute and missing pp per unit
        $this->assertMatchesRegularExpression('/Die Variante (.*)1 kg(.*) des Produktes (.*)Rindfleisch(.*) kann aufgrund von fehlenden Produktdaten zur Zeit leider nicht bestellt werden./', $this->_response->getBody()->__toString());
    }

    public function testFinishWithPickupDayCommentNotification()
    {
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);

        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $pickupDayComment = 'this is a valid pickup day comment';
        $this->finishCart(1, 1, $pickupDayComment);

        $this->assertMailCount(2);
        $this->assertMailSubjectContainsAt(0, 'Neuer Bestell-Kommentar von Demo Superadmin');
        $this->assertMailContainsAt(0, $pickupDayComment);

    }

    public function testFinishWithPurchasePriceOk()
    {
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsAdmin();
        $this->addAllDifferentProductTypesToCart();

        $productId = 340;
        $this->PurchasePriceProduct = $this->getTableLocator()->get('PurchasePriceProducts');
        $entity = $this->PurchasePriceProduct->newEntity(
            [
                'product_id' => $productId,
                'tax_id' => 2,
                'price' => 1.072727,
            ],
        );
        $this->PurchasePriceProduct->save($entity);
        $this->addProductToCart($productId, 2);
        $this->addProductToCart(163, 1); //mangold
        $this->finishCart(1,1);

        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $this->checkCartStatusAfterFinish();
        $cart = $this->getCartById($cartId);

        $objectA = $cart->cart_products[0]->order_detail; // Artischocke
        $objectB = $cart->cart_products[1]->order_detail; // Forelle
        $objectC = $cart->cart_products[2]->order_detail; // Rindfleisch
        $objectD = $cart->cart_products[3]->order_detail; // Milch
        $objectE = $cart->cart_products[4]->order_detail; // Beuschl
        $objectF = $cart->cart_products[5]->order_detail; // Mangold

        $this->assertEmpty($objectA->order_detail_unit);
        $this->assertEquals($objectB->order_detail_unit->purchase_price_incl_per_unit, 0.98);
        $this->assertEquals($objectC->order_detail_unit->purchase_price_incl_per_unit, 14);
        $this->assertEmpty($objectD->order_detail_unit);
        $this->assertEmpty($objectE->order_detail_unit);
        $this->assertEmpty($objectF->order_detail_unit);

        $this->assertEquals($objectA->order_detail_purchase_price->tax_rate, 20);
        $this->assertEquals($objectB->order_detail_purchase_price->tax_rate, 13);
        $this->assertEquals($objectC->order_detail_purchase_price->tax_rate, 13);
        $this->assertEquals($objectD->order_detail_purchase_price->tax_rate, 10);
        $this->assertEquals($objectE->order_detail_purchase_price->tax_rate, 10);
        $this->assertEquals($objectF->order_detail_purchase_price->tax_rate, 0);

        $this->assertEquals($objectA->order_detail_purchase_price->total_price_tax_incl, 2.88);
        $this->assertEquals($objectB->order_detail_purchase_price->total_price_tax_incl, 10.29);
        $this->assertEquals($objectC->order_detail_purchase_price->total_price_tax_incl, 25.2);
        $this->assertEquals($objectD->order_detail_purchase_price->total_price_tax_incl, 0.28);
        $this->assertEquals($objectE->order_detail_purchase_price->total_price_tax_incl, 2.36);
        $this->assertEquals($objectF->order_detail_purchase_price->total_price_tax_incl, 1.07);

        $this->assertEquals($objectA->order_detail_purchase_price->total_price_tax_excl, 2.40);
        $this->assertEquals($objectB->order_detail_purchase_price->total_price_tax_excl, 9.11);
        $this->assertEquals($objectC->order_detail_purchase_price->total_price_tax_excl, 22.30);
        $this->assertEquals($objectD->order_detail_purchase_price->total_price_tax_excl, 0.25);
        $this->assertEquals($objectE->order_detail_purchase_price->total_price_tax_excl, 2.14);
        $this->assertEquals($objectF->order_detail_purchase_price->total_price_tax_excl, 1.07);

        $this->assertEquals($objectA->order_detail_purchase_price->tax_unit_amount, 0.24);
        $this->assertEquals($objectB->order_detail_purchase_price->tax_unit_amount, 0.39);
        $this->assertEquals($objectC->order_detail_purchase_price->tax_unit_amount, 0.97);
        $this->assertEquals($objectD->order_detail_purchase_price->tax_unit_amount, 0.03);
        $this->assertEquals($objectE->order_detail_purchase_price->tax_unit_amount, 0.11);
        $this->assertEquals($objectF->order_detail_purchase_price->tax_unit_amount, 0);

        $this->assertEquals($objectA->order_detail_purchase_price->tax_total_amount, 0.48);
        $this->assertEquals($objectB->order_detail_purchase_price->tax_total_amount, 1.17);
        $this->assertEquals($objectC->order_detail_purchase_price->tax_total_amount, 2.91);
        $this->assertEquals($objectD->order_detail_purchase_price->tax_total_amount, 0.03);
        $this->assertEquals($objectE->order_detail_purchase_price->tax_total_amount, 0.22);
        $this->assertEquals($objectF->order_detail_purchase_price->tax_total_amount, 0);

    }

    public function testDecreaseStockAvailableForMultipleAttributesOfOneProduct()
    {
        $productA = '350-13';
        $this->loginAsSuperadmin();
        $this->addProductToCart($productA, 1);
        $this->finishCart(1, 1);
        $this->checkStockAvailable($productA, 4);
        $this->checkStockAvailable('350-14', 999); // must be same as before fnishing order
    }

    public function testIsSubscribeNewsletterLinkAddedToMail()
    {
        $this->changeConfiguration('FCS_NEWSLETTER_ENABLED', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'newsletter_enabled', 0);
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->finishCart(1, 1);
        $this->assertMailContainsAt(0, 'Du kannst unseren Newsletter <a href="' . Configure::read('App.fullBaseUrl') . '/admin/customers/profile">im Admin-Bereich unter "Meine Daten"</a> abonnieren.');
    }

    public function testIsSubscribeNewsletterLinkNotAddedToMail()
    {
        $this->changeConfiguration('FCS_NEWSLETTER_ENABLED', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'newsletter_enabled', 1);
        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->finishCart(1, 1);
        // assertMailNotContainsAt not available!
        $this->assertDoesNotMatchRegularExpressionWithUnquotedString('Du kannst unseren Newsletter', TestEmailTransport::getMessages()[0]->getBodyHtml());
    }

    public function testFinishOrderWithComment()
    {

        $this->loginAsSuperadmin();
        $this->fillCart();
        $this->checkCartStatus();

        $pickupDayComment = 'this is a valid pickup day comment';
        $this->finishCart(1, 1, $pickupDayComment);

        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $this->checkCartStatusAfterFinish();

        $cart = $this->getCartById($cartId);
        $pickupDay = (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb();

        // check order_details for product1 (index 2!)
        $this->checkOrderDetails($cart->cart_products[0]->order_detail, 'Artischocke : Stück', 2, 0, 1, 3.3, 3.64, 0.17, 0.34, 10, $pickupDay);

        // check order_details for product2 (index 0!)
        $this->checkOrderDetails($cart->cart_products[1]->order_detail, 'Milch : 0,5l', 3, 10, 1.5, 1.65, 1.86, 0.07, 0.21, 13, $pickupDay);

        // check order_details for product3 (index 1!)
        $this->checkOrderDetails($cart->cart_products[2]->order_detail, 'Knoblauch : 100 g', 1, 0, 0, 0.64, 0.64, 0.000000, 0.000000, 0, $pickupDay);

        $this->checkStockAvailable($this->productId1, 95);
        $this->checkStockAvailable($this->productId2, 16); // product is NOT always available!
        $this->checkStockAvailable($this->productId3, 77);

        // check new (empty) cart
        $cart = $this->Cart->getCart($this, Cart::TYPE_WEEKLY_RHYTHM);
        $this->assertEquals($cart['Cart']['id_cart'], 3, 'cake cart id wrong');
        $this->assertEquals([], $cart['CartProducts'], 'cake cart products not empty');

        $this->assertMailSubjectContainsAt(0, 'Bestellbestätigung');
        $this->assertMailContainsHtmlAt(0, 'Hallo Demo Superadmin,');
        $this->assertMailContainsHtmlAt(0, 'Kommentar: "<b>' . $pickupDayComment . '</b>"');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));
        $this->assertMailContainsAttachment('Informationen-ueber-Ruecktrittsrecht-und-Ruecktrittsformular.pdf');
        $this->assertMailContainsAttachment('Bestelluebersicht.pdf');
        $this->assertMailContainsAttachment('Allgemeine-Geschaeftsbedingungen.pdf');

        $this->assertMailContainsHtmlAt(0, 'Artischocke : Stück');
        $this->assertMailContainsHtmlAt(0, 'Knoblauch : 100 g');
        $this->assertMailContainsHtmlAt(0, 'Milch : 0,5l');

        $this->logout();
    }

    public function testProductsWithAllowedNegativeStock()
    {
        $this->changeManufacturer(5, 'stock_management_enabled', 1);
        $this->loginAsCustomer();
        $this->addProductToCart(349, 8);
        $this->assertJsonOk();
    }

    public function testProductsWithAllowedNegativeStockButTooHighAmount()
    {
        $this->changeManufacturer(5, 'stock_management_enabled', 1);
        $this->loginAsCustomer();
        $response = $this->addProductToCart(349, 11);
        $this->assertRegExpWithUnquotedString('Die gewünschte Menge <b>11</b> des Produktes <b>Lagerprodukt</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 10', $response->msg);
        $this->assertJsonError();
    }

    private function placeOrderWithStockProducts()
    {
        $stockProductId = 349;
        $stockProductAttributeId = '350-13';
        $this->addProductToCart($stockProductId, 6);
        $this->addProductToCart($stockProductAttributeId, 5);
        $this->finishCart(1, 1);
    }

    public function testFinishOrderWithMultiplePickupDays()
    {

        $this->loginAsSuperadmin();
        $productIdA = 346;
        $productIdB = 347;
        $productIdC = '60-10';
        $this->changeProductDeliveryRhythm($productIdA, '0-individual', '28.09.2018');
        $this->addProductToCart($productIdA, 3);
        $this->addProductToCart($productIdB, 2);
        $this->addProductToCart($productIdC, 1);
        $this->finishCart(1, 1);

        $this->assertMailCount(1);
    }

    public function testFinishOrderStockNotificationsIsStockProductDisabled()
    {

        $this->loginAsSuperadmin();
        $this->ajaxPost('/admin/products/editIsStockProduct', [
            'productId' => 350,
            'isStockProduct' => 0
        ]);
        $this->ajaxPost('/admin/products/editIsStockProduct', [
            'productId' => 349,
            'isStockProduct' => 0
        ]);
        $this->placeOrderWithStockProducts();

        $this->assertMailCount(1);
    }

    public function testFinishOrderStockNotificationsStockManagementDisabled()
    {
        $this->loginAsSuperadmin();
        $this->changeManufacturer(5, 'stock_management_enabled', 0);
        $this->placeOrderWithStockProducts();
        $this->assertMailCount(1);
    }

    public function testFinishOrderStockNotificationsDisabled()
    {
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_product_sold_out_limit_reached_for_manufacturer', 0);
        $this->changeManufacturer($manufacturerId, 'send_product_sold_out_limit_reached_for_contact_person', 0);
        $this->loginAsSuperadmin();
        $this->placeOrderWithStockProducts();
        $this->assertMailCount(1);
    }

    public function testFinishOrderStockNotificationsEnabled()
    {

        $this->loginAsCustomer();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'stock_management_enabled', 1);

        $this->placeOrderWithStockProducts();

        // check email to manufacturer
        $this->assertMailSubjectContainsAt(2, 'Lagerstand für Produkt "Lagerprodukt mit Varianten : 0,5 kg": 0');
        $this->assertMailContainsHtmlAt(2, 'Lagerstand: <b>0</b>');
        $this->assertMailContainsHtmlAt(2, 'Bestellungen möglich bis zu einem Lagerstand von: <b>-5</b>');
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailVegetableManufacturer'));

        // check email to contact person
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailAdmin'));

        // check email to manufacturer
        $this->assertMailSubjectContainsAt(0, 'Lagerstand für Produkt "Lagerprodukt": -1');
        $this->assertMailContainsHtmlAt(0, 'Lagerstand: <b>-1</b>');
        $this->assertMailContainsHtmlAt(0, 'Bestellungen möglich bis zu einem Lagerstand von: <b>-5</b>');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailVegetableManufacturer'));

        // check email to contact person
        $this->assertMailSentToAt(3, Configure::read('test.loginEmailAdmin'));

        $this->logout();
    }

    public function testFinishCartWithPricePerUnit()
    {
        $this->loginAsSuperadmin();

        $productIdA = 347; // forelle
        $productIdB = '348-11'; // rindfleisch, 0,5 kg

        $this->addProductToCart($productIdA, 2);
        $this->addProductToCart($productIdB, 3);

        $this->finishCart(1, 1);
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));

        $this->checkCartStatusAfterFinish();
        $cart = $this->getCartById($cartId);
        $pickupDay = (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb();

        // check order_details
        $this->checkOrderDetails($cart->cart_products[0]->order_detail, 'Forelle : Stück', 2, 0, 0, 9.54, 10.5, 0.48, 0.96, 10, $pickupDay);
        $this->checkOrderDetails($cart->cart_products[1]->order_detail, 'Rindfleisch', 3, 11, 0, 27.27, 30, 0.91, 2.73, 10, $pickupDay);

        // check order_details_units
        $orderDetailA = $cart->cart_products[0]->order_detail;
        $orderDetailB = $cart->cart_products[1]->order_detail;

        $this->assertEquals($orderDetailA->order_detail_unit->product_quantity_in_units, 700);
        $this->assertEquals($orderDetailA->order_detail_unit->price_incl_per_unit, 1.5);
        $this->assertEquals($orderDetailA->order_detail_unit->quantity_in_units, 350);
        $this->assertEquals($orderDetailA->order_detail_unit->unit_name, 'g');
        $this->assertEquals($orderDetailA->order_detail_unit->unit_amount, 100);
        $this->assertEquals($orderDetailA->order_detail_unit->mark_as_saved, 0);

        $this->assertEquals($orderDetailB->order_detail_unit->product_quantity_in_units, 1.5);
        $this->assertEquals($orderDetailB->order_detail_unit->price_incl_per_unit, 20);
        $this->assertEquals($orderDetailB->order_detail_unit->quantity_in_units, 0.5);
        $this->assertEquals($orderDetailB->order_detail_unit->unit_name, 'kg');
        $this->assertEquals($orderDetailB->order_detail_unit->unit_amount, 1);
        $this->assertEquals($orderDetailB->order_detail_unit->mark_as_saved, 0);

        $this->assertEquals($orderDetailA->tax_rate, 10);
        $this->assertEquals($orderDetailA->tax_unit_amount, 0.48);
        $this->assertEquals($orderDetailA->tax_total_amount, 0.96);

        $this->assertEquals($orderDetailB->tax_rate, 10);
        $this->assertEquals($orderDetailB->tax_unit_amount, 0.91);
        $this->assertEquals($orderDetailB->tax_total_amount, 2.73);

        $this->assertMailContainsHtmlAt(0, 'Forelle : Stück, je ca. 350 g');
        $this->assertMailContainsHtmlAt(0, 'Rindfleisch : je ca. 0,5 kg');

    }

    public function testFinishCartWithPricePerUnitAndUseWeightAsAmount()
    {
        $this->loginAsSuperadmin();

        $productIdA = 347; // forelle
        $productIdB = '348-11'; // rindfleisch, 0,5 kg
        $productIdC = 351; // stock product

        $unitsTable = $this->getTableLocator()->get('Units');
        $unitEntityA = $unitsTable->get(1);
        $unitEntityA->use_weight_as_amount = 1;
        $unitEntityB = $unitsTable->get(2);
        $unitEntityB->use_weight_as_amount = 1;
        $unitEntityC = $unitsTable->get(8);
        $unitEntityC->use_weight_as_amount = 1;
        $unitsTable->saveMany([$unitEntityA, $unitEntityB, $unitEntityC]);

        $this->addProductToCart($productIdA, 2);
        $this->addProductToCart($productIdB, 3);
        $this->addProductToCart($productIdC, 3);

        $this->finishCart(1, 1);
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));

        $this->checkCartStatusAfterFinish();
        $cart = $this->getCartById($cartId);
        $pickupDay = (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb();

        // check order_details
        $this->checkOrderDetails($cart->cart_products[0]->order_detail, 'Forelle : Stück', 2, 0, 0, 9.54, 10.5, 0.48, 0.96, 10, $pickupDay);
        $this->checkOrderDetails($cart->cart_products[1]->order_detail, 'Rindfleisch', 3, 11, 0, 27.27, 30, 0.91, 2.73, 10, $pickupDay);
        $this->checkOrderDetails($cart->cart_products[2]->order_detail, 'Lagerprodukt 2', 3, 0, 0, 22.5, 45, 7.5, 22.5, 20, $pickupDay);

        $this->checkStockAvailable($productIdC, 996);

        // check order_details_units
        $orderDetailA = $cart->cart_products[0]->order_detail;
        $orderDetailB = $cart->cart_products[1]->order_detail;
        $orderDetailC = $cart->cart_products[2]->order_detail;

        $this->assertEquals($orderDetailA->order_detail_unit->product_quantity_in_units, 700);
        $this->assertEquals($orderDetailA->order_detail_unit->price_incl_per_unit, 1.5);
        $this->assertEquals($orderDetailA->order_detail_unit->quantity_in_units, 350);
        $this->assertEquals($orderDetailA->order_detail_unit->unit_name, 'g');
        $this->assertEquals($orderDetailA->order_detail_unit->unit_amount, 100);
        $this->assertEquals($orderDetailA->order_detail_unit->mark_as_saved, 0);

        $this->assertEquals($orderDetailB->order_detail_unit->product_quantity_in_units, 1.5);
        $this->assertEquals($orderDetailB->order_detail_unit->price_incl_per_unit, 20);
        $this->assertEquals($orderDetailB->order_detail_unit->quantity_in_units, 0.5);
        $this->assertEquals($orderDetailB->order_detail_unit->unit_name, 'kg');
        $this->assertEquals($orderDetailB->order_detail_unit->unit_amount, 1);
        $this->assertEquals($orderDetailB->order_detail_unit->mark_as_saved, 0);

        $this->assertEquals($orderDetailC->order_detail_unit->product_quantity_in_units, 3);
        $this->assertEquals($orderDetailC->order_detail_unit->price_incl_per_unit, 15);
        $this->assertEquals($orderDetailC->order_detail_unit->quantity_in_units, 1);
        $this->assertEquals($orderDetailC->order_detail_unit->unit_name, 'kg');
        $this->assertEquals($orderDetailC->order_detail_unit->unit_amount, 1);
        $this->assertEquals($orderDetailC->order_detail_unit->mark_as_saved, 0);

        $this->assertEquals($orderDetailA->tax_rate, 10);
        $this->assertEquals($orderDetailA->tax_unit_amount, 0.48);
        $this->assertEquals($orderDetailA->tax_total_amount, 0.96);

        $this->assertEquals($orderDetailB->tax_rate, 10);
        $this->assertEquals($orderDetailB->tax_unit_amount, 0.91);
        $this->assertEquals($orderDetailB->tax_total_amount, 2.73);

        $this->assertEquals($orderDetailC->tax_rate, 20);
        $this->assertEquals($orderDetailC->tax_unit_amount, 7.5);
        $this->assertEquals($orderDetailC->tax_total_amount, 22.5);

        $this->assertMailContainsHtmlAt(0, 'Forelle : Stück, je ca. 350 g');
        $this->assertMailContainsHtmlAt(0, 'Rindfleisch : je ca. 0,5 kg');
        $this->assertMailContainsHtmlAt(0, 'Lagerprodukt 2 : je ca. 1 kg');

    }

    public function testFinishCartWithShoppingPricesAreZeroPrices()
    {
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'shopping_price', Customer::ZERO_PRICE);
        $this->loginAsSuperadmin();
        $this->addAllDifferentProductTypesToCart();
        $this->finishCart(1,1);

        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $this->checkCartStatusAfterFinish();
        $cart = $this->getCartById($cartId);

        $objectA = $cart->cart_products[1]->order_detail;
        $objectB = $cart->cart_products[2]->order_detail;
        $objectC = $cart->cart_products[0]->order_detail;
        $objectD = $cart->cart_products[3]->order_detail;

        $this->assertEquals($objectA->total_price_tax_incl, 0);
        $this->assertEquals($objectA->total_price_tax_excl, 0);
        $this->assertEquals($objectA->tax_unit_amount, 0);
        $this->assertEquals($objectA->tax_total_amount, 0);
        $this->assertEquals($objectA->deposit, 0);
        $this->assertEquals($objectA->order_detail_unit->price_incl_per_unit, 0);
        $this->assertNull($objectA->order_detail_unit->purchase_price_incl_per_unit);
        $this->assertEmpty($objectA->order_detail_purchase_price);

        $this->assertEquals($objectB->total_price_tax_incl, 0);
        $this->assertEquals($objectB->total_price_tax_excl, 0);
        $this->assertEquals($objectB->tax_unit_amount, 0);
        $this->assertEquals($objectB->tax_total_amount, 0);
        $this->assertEquals($objectB->deposit, 0);
        $this->assertEquals($objectB->order_detail_unit->price_incl_per_unit, 0);
        $this->assertNull($objectB->order_detail_unit->purchase_price_incl_per_unit);
        $this->assertEmpty($objectB->order_detail_purchase_price);

        $this->assertEquals($objectC->total_price_tax_incl, 0);
        $this->assertEquals($objectC->total_price_tax_excl, 0);
        $this->assertEquals($objectC->tax_unit_amount, 0);
        $this->assertEquals($objectC->tax_total_amount, 0);
        $this->assertEquals($objectC->deposit, 0);
        $this->assertEmpty($objectC->order_detail_unit);
        $this->assertEmpty($objectC->order_detail_purchase_price);

        $this->assertEquals($objectD->total_price_tax_incl, 0);
        $this->assertEquals($objectD->total_price_tax_excl, 0);
        $this->assertEquals($objectD->tax_unit_amount, 0);
        $this->assertEquals($objectD->tax_total_amount, 0);
        $this->assertEquals($objectD->deposit, 0);
        $this->assertEmpty($objectD->order_detail_unit);
        $this->assertEmpty($objectD->order_detail_purchase_price);

    }

    public function testFinishCartWithShoppingPricesArePurchasePrices()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'shopping_price', Customer::PURCHASE_PRICE);
        $this->loginAsSuperadmin();
        $this->addAllDifferentProductTypesToCart();
        return;
        $this->finishCart(1,1);

        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $this->checkCartStatusAfterFinish();
        $cart = $this->getCartById($cartId);

        $objectA = $cart->cart_products[1]->order_detail;
        $objectB = $cart->cart_products[0]->order_detail;
        $objectC = $cart->cart_products[2]->order_detail;
        $objectD = $cart->cart_products[3]->order_detail;

        $this->assertEquals($objectA->total_price_tax_incl, 9.99);
        $this->assertEquals($objectA->total_price_tax_excl, 9.09);
        $this->assertEquals($objectA->tax_unit_amount, 0.30);
        $this->assertEquals($objectA->tax_total_amount, 0.90);
        $this->assertEquals($objectA->deposit, 0.00);
        $this->assertEquals($objectA->order_detail_unit->price_incl_per_unit, 0.95);

        $this->assertEquals($objectB->total_price_tax_incl, 24.54);
        $this->assertEquals($objectB->total_price_tax_excl, 22.32);
        $this->assertEquals($objectB->tax_unit_amount, 0.74);
        $this->assertEquals($objectB->tax_total_amount, 2.22);
        $this->assertEquals($objectB->deposit, 0.00);
        $this->assertEquals($objectB->order_detail_unit->price_incl_per_unit, 13.63);

        $this->assertEquals($objectC->total_price_tax_incl, 0.28);
        $this->assertEquals($objectC->total_price_tax_excl, 0.25);
        $this->assertEquals($objectC->tax_unit_amount, 0.03);
        $this->assertEquals($objectC->tax_total_amount, 0.03);
        $this->assertEquals($objectC->deposit, 0.50);
        $this->assertEmpty($objectC->order_detail_unit);

        $this->assertEquals($objectD->total_price_tax_incl, 2.64);
        $this->assertEquals($objectD->total_price_tax_excl, 2.40);
        $this->assertEquals($objectD->tax_unit_amount, 0.12);
        $this->assertEquals($objectD->tax_total_amount, 0.24);
        $this->assertEquals($objectD->deposit, 1);
        $this->assertEmpty($objectD->order_detail_unit);

    }

    public function testInstantOrderOk()
    {
        // add a product to the "normal" cart (Cart::TYPE_WEEKLY_RHYTHM)
        $this->loginAsCustomer();
        $this->addProductToCart($this->productId1, 5);
        $this->logout();

        $this->loginAsSuperadmin();
        $testCustomer = $this->Customer->find('all',
            conditions: [
                'Customers.id_customer' => Configure::read('test.customerId'),
            ],
        )->first();
        $this->get($this->Slug->getOrderDetailsList().'/initInstantOrder/' . Configure::read('test.customerId'));
        $this->loginAsSuperadminAddOrderCustomerToSession($_SESSION);
        $this->get($this->_response->getHeaderLine('Location'));
        $this->assertResponseContains('Diese Bestellung wird für <b>' . $testCustomer->name . '</b> getätigt.');

        $this->addProductToCart($this->productId2, 3); // attribute
        $this->addProductToCart(349, 1); // stock product - no notification!

        $cartTable = FactoryLocator::get('Table')->get('Carts');
        $cart = $cartTable->find()->where(
            [
                'Carts.id_customer' => Configure::read('test.customerId'),
                'Carts.cart_type' => Cart::TYPE_INSTANT_ORDER,
            ]
        )->contain(['CartProducts'])->first();
        $this->assertCount(2, $cart->cart_products);

        $this->finishCart(1, 1);
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);

        // product that was added as Cart::TYPE_WEEKLY_RHYTHM must not be included in Cart::TYPE_INSTANT_ORDER cart
        $this->assertEquals(2, count($cart->cart_products));

        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->id_customer, $testCustomer->id_customer, 'order_detail id_customer not correct');
            $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), Configure::read('app.timeHelper')->getCurrentDateForDatabase(), 'order_detail pickup_day not correct');
        }

        $this->assertMailCount(2);
        $this->assertMailSubjectContainsAt(0, 'Benachrichtigung über Sofort-Bestellung');
        $this->assertMailContainsHtmlAt(0, 'Milch : 0,5l');
        $this->assertMailContainsHtmlAt(0, 'Hallo Demo,');
        $this->assertMailContainsHtmlAt(0, '1,86');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailMilkManufacturer'));

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all')->toArray();
        $this->assertEquals('carts', $actionLogs[0]->object_type);
        $this->assertEquals($cart->id_cart, $actionLogs[0]->object_id);
        $this->assertEquals(Configure::read('test.superadminId'), $actionLogs[0]->customer_id);
    }

    public function testInstantOrderWithDeliveryBreak()
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb());
        $this->loginAsSuperadmin();
        $this->get($this->Slug->getOrderDetailsList().'/initInstantOrder/' . Configure::read('test.customerId'));
        $this->loginAsSuperadminAddOrderCustomerToSession($_SESSION);
        $this->get($this->_response->getHeaderLine('Location'));
        $this->addProductToCart($this->productId1, 1);
        $this->finishCart(1, 1);
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all')->toArray();
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
                    'delivery_rhythm_first_delivery_day' => new Date('2018-08-03')
                ]
            )
        );

        $this->loginAsSuperadmin();
        $this->get($this->Slug->getOrderDetailsList().'/initInstantOrder/' . Configure::read('test.customerId'));
        $this->loginAsSuperadminAddOrderCustomerToSession($_SESSION);
        $this->get($this->_response->getHeaderLine('Location'));
        $this->addProductToCart($this->productId1, 1);
        $this->finishCart(1, 1);
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all')->toArray();
        $this->assertRegExpWithUnquotedString('Die Sofort-Bestellung (1,82 €) für <b>Demo Mitglied</b> wurde erfolgreich getätigt.', $actionLogs[0]->text);
    }

    public function testFinishCartWithDeletedProduct()
    {
        $this->loginAsCustomer();
        $this->addProductToCart($this->productId1, 1);

        // delete product that was already placed in cart
        $productsTable = FactoryLocator::get('Table')->get('Products');
        $product = $productsTable->get($this->productId1);
        $product->active = APP_DEL;
        $productsTable->save($product);

        $this->finishCart();
        $this->assertResponseContains('ist leider nicht mehr aktiviert und somit nicht mehr bestellbar');
    }

    public function testFinishEmptyCart()
    {
        $this->loginAsCustomer();
        $this->addProductToCart($this->productId1, 1);
        $this->removeProduct($this->productId1);
        $this->finishCart();
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
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $this->assertTrue(is_int($cartId), 'cart not finished correctly');

        $this->checkCartStatusAfterFinish();
        $cart = $this->getCartById($cartId);
        $this->assertEquals(1, count($cart->cart_products));
        $this->assertEquals(1, $cart->cart_products[0]->order_detail->product_amount);

    }

    public function testLastOrders()
     {
        $this->loginAsSuperadmin();
        $productId = '346'; // artischocke

        $this->addProductToCart($productId, 3);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);

        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $orderDetailsTable = FactoryLocator::get('Table')->get('OrderDetails');
        $orderDetail = $orderDetailsTable->get($orderDetailId);

        $orderDetail->created = $orderDetail->created->subDays(14);
        $orderDetailsTable->save($orderDetail);

        $lastOrders = $orderDetailsTable->getLastOrderDetailsForDropdown(Configure::read('test.superadminId'));
        $this->assertEquals(1, count($lastOrders));

        $this->post('/warenkorb/addOrderToCart?deliveryDate=' . $orderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')));
        $this->assertFlashMessage('Dein Warenkorb wurde geleert und deine vergangene Bestellung in den Warenkorb geladen.<br />Du kannst jetzt weitere Produkte hinzufügen.');

        $this->post('/warenkorb/emptyCart');
        $this->assertFlashMessage('Dein Warenkorb wurde geleert, du kannst jetzt neue Produkte hinzufügen.');

     }

    protected function addAllDifferentProductTypesToCart()
    {
        $this->addProductToCart(346, 2);      // Artischocke: main product with normal price
        $this->addProductToCart(347, 3);      // Forelle: main product with price per unit
        $this->addProductToCart('348-12', 3); // Rindfleisch: attribute with price per unit
        $this->addProductToCart('60-10', 1);  // Milch: attribute with normal price
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
        $cart = $this->Cart->getCart($this, Cart::TYPE_WEEKLY_RHYTHM);
        $this->assertEquals($cart['Cart']['status'], 1, 'cake cart status wrong');
        $this->assertEquals($cart['Cart']['id_cart'], 2, 'cake cart id wrong');
    }

    /**
     * cake cart status check AFTER finish
     * as cart is finished, a new cart is already existing
     */
    private function checkCartStatusAfterFinish()
    {
        $cart = $this->Cart->find('all',
            conditions: [
                'Carts.id_cart' => 1,
            ],
        )->first();
        $this->assertEquals($cart->status, 0, 'cake cart status wrong');
    }

    private function addTooManyProducts($productId, $amount, $expectedAmount, $expectedErrorMessage, $productIndex)
    {
        $this->addProductToCart($productId, $amount);
        $response = $this->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertEquals($productId, $response->productId);
        $this->assertJsonError();
        $cart = $this->Cart->getCart($this, Cart::TYPE_WEEKLY_RHYTHM);
        $this->assertEquals($expectedAmount, $cart['CartProducts'][$productIndex]['amount'], 'amount not found in cart or wrong');
    }

    private function checkValidationError()
    {
        $this->assertMatchesRegularExpression('/initCartErrors()/', $this->_response->getBody()->__toString());
    }

    private function changeStockAvailable($productId, $amount)
    {
        $this->Product->changeQuantity([[$productId => ['quantity' => $amount]]]);
    }

    private function checkStockAvailable($productId, $result)
    {
        $ids = $this->Product->getProductIdAndAttributeId($productId);

        // get changed product
        $stockAvailable = $this->StockAvailable->find('all',
            conditions: [
                'StockAvailables.id_product' => $ids['productId'],
                'StockAvailables.id_product_attribute' => $ids['attributeId'],
            ],
        )->first();

        // stock available check of changed product
        $this->assertEquals($stockAvailable->quantity, $result, 'stockavailable quantity wrong');
    }

    private function checkOrderDetails($orderDetail, $name, $amount, $productAttributeId, $deposit, $totalPriceTaxExcl, $totalPriceTaxIncl, $taxUnitAmount, $taxTotalAmount, $taxRate, $pickupDay)
    {
        $this->assertEquals($orderDetail->product_name, $name);
        $this->assertEquals($orderDetail->product_amount, $amount);
        $this->assertEquals($orderDetail->product_attribute_id, $productAttributeId);
        $this->assertEquals($orderDetail->deposit, $deposit);
        $this->assertEquals($orderDetail->total_price_tax_excl, $totalPriceTaxExcl);
        $this->assertEquals($orderDetail->total_price_tax_incl, $totalPriceTaxIncl);
        $this->assertEquals($orderDetail->id_customer, $this->getId());
        $this->assertEquals($orderDetail->order_state, OrderDetail::STATE_OPEN);
        $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), $pickupDay);
        $this->assertEquals($orderDetail->tax_unit_amount, $taxUnitAmount);
        $this->assertEquals($orderDetail->tax_total_amount, $taxTotalAmount);
        $this->assertEquals($orderDetail->tax_rate, $taxRate);
    }

    private function changeProductStatus($productId, $status): void
    {
        $this->Product->changeStatus([[$productId => $status]]);
    }

    private function changeManufacturerStatus($manufacturerId, $status): void
    {
        $this->changeManufacturer($manufacturerId, 'active', $status);
    }

    private function removeProduct($productId)
    {
        $this->ajaxPost('/warenkorb/ajaxRemove', [
            'productId' => $productId
        ]);
        return $this->getJsonDecodedContent();
    }
}
