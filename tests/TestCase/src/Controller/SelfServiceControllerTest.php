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
use App\Model\Entity\OrderDetail;
use App\Test\TestCase\Traits\SelfServiceCartTrait;
use App\Model\Entity\Cart;
use Cake\ORM\TableRegistry;

class SelfServiceControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use AssertPagesForErrorsTrait;
    use LoginTrait;
    use EmailTrait;
    use SelfServiceCartTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
    }

    public function testPageSelfService(): void
    {
        $this->loginAsSuperadmin();
        $testUrls = [
            $this->Slug->getSelfService()
        ];
        $this->assertPagesForErrors($testUrls);
    }

    public function testBarCodeLoginAsSuperadminValid(): void
    {
        $this->doBarCodeLogin();
        $this->assertEquals($_SESSION['Auth']->id_customer, Configure::read('test.superadminId'));
    }

    public function testSelfServiceAddProductPricePerUnitWrong(): void
    {
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(351, 1);
        $response = $this->getJsonDecodedContent();
        $expectedErrorMessage = 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.';
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertJsonError();
    }

    public function testSelfServiceAddProductPricePerUnitNotAvailable(): void
    {
        $unitsTable = $this->getTableLocator()->get('Units');
        $unitEntity = $unitsTable->get(8);
        $unitEntity->use_weight_as_amount = 1;
        $unitsTable->save($unitEntity);

        $this->loginAsSuperadmin();
        $productId = 351;
        $stockAvailablesTable = TableRegistry::getTableLocator()->get('StockAvailables');
        $stockAvailableObject = $stockAvailablesTable->find('all')->where([
            'id_product' => $productId,
            'id_product_attribute' => 0,
        ])->first();
        $patchedEntity = $stockAvailablesTable->patchEntity(
            $stockAvailableObject,
            [
                'quantity' => 1,
            ],
        );
        $stockAvailablesTable->save($patchedEntity);

        $this->addProductToSelfServiceCart($productId, 1, '1,2');
        $response = $this->getJsonDecodedContent();
        $expectedErrorMessage = 'Die gewünschte Menge <b>1,2 kg</b> des Produktes <b>Lagerprodukt 2</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 1 kg';
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertJsonError();
    }

    public function testSelfServiceAddAttributePricePerUnitWrong(): void
    {
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart('350-15', 1, 'bla bla');
        $response = $this->getJsonDecodedContent();
        $expectedErrorMessage = 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.';
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertJsonError();
    }

    public function testSelfServiceAddAttributePricePerUnitNotAvailable(): void
    {
        $unitsTable = $this->getTableLocator()->get('Units');
        $unitEntity = $unitsTable->get(7);
        $unitEntity->use_weight_as_amount = 1;
        $unitsTable->save($unitEntity);

        $this->loginAsSuperadmin();
        $productId = 350;
        $attributeId = 15;
        $stockAvailablesTable = TableRegistry::getTableLocator()->get('StockAvailables');
        $stockAvailableObject = $stockAvailablesTable->find('all')->where([
            'id_product' => $productId,
            'id_product_attribute' => $attributeId,
        ])->first();
        $patchedEntity = $stockAvailablesTable->patchEntity(
            $stockAvailableObject,
            [
                'quantity' => 1.1,
            ],
        );
        $stockAvailablesTable->save($patchedEntity);

        $this->addProductToSelfServiceCart('350-15', 1, '1,3');
        $response = $this->getJsonDecodedContent();
        $expectedErrorMessage = 'Die gewünschte Menge <b>1,3 kg</b> der Variante <b>0,5 kg</b> des Produktes <b>Lagerprodukt mit Varianten</b> ist leider nicht mehr verfügbar. Verfügbare Menge: 1,1 kg';
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertJsonError();
    }

    public function testSelfServiceOrderWithoutCheckboxes(): void
    {
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(349, 1);
        $this->finishSelfServiceCart(0, 0);
        $this->assertResponseContains('Bitte akzeptiere die AGB.');
        $this->assertResponseContains('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.');
    }

    public function testSelfServiceWithActiveShowConfirmDialogOnSubmitConfig(): void
    {
        Configure::write('app.selfServiceEasyModeEnabled', true);
        $this->loginAsCustomer();
        $this->addProductToSelfServiceCart(344, 1);
        $this->finishSelfServiceCart(0, 0);
        $this->assertResponseNotContains('Bitte akzeptiere die AGB.');
        $this->assertResponseNotContains('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.');
        $cartsTable = $this->getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all', 
        conditions: [
            'Carts.id_customer' => Configure::read('test.customerId'),
            'Carts.cart_type' => Cart::TYPE_SELF_SERVICE,
        ],
        order: [
            'Carts.id_cart' => 'DESC',
        ])->first();
        $this->assertEquals(0, $cart->status);
    }

    public function testSelfServiceWithEasyModeAndPaymentTypesConfig(): void
    {
        Configure::write('app.selfServiceEasyModeEnabled', true);
        Configure::write('app.selfServicePaymentTypes', [
            [
                'id' => 1,
                'payment_type' => 'Bar',
                'payment_text' => 'Bitte Einkauf in Bar bezahlen.',
            ],
            [
                'id' => 1,
                'payment_type' => 'Bankomatkarte',
                'payment_text' => 'Bitte Einkauf mit Bankomatkarte bezahlen.',
            ],
        ]);
        $this->loginAsCustomer();
        $this->addProductToSelfServiceCart(344, 1);
        $this->finishSelfServiceCart(0, 0);
        $this->assertResponseNotContains('Bitte akzeptiere die AGB.');
        $this->assertResponseNotContains('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.');
        $cartsTable = $this->getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all', 
        conditions: [
            'Carts.id_customer' => Configure::read('test.customerId'),
            'Carts.cart_type' => Cart::TYPE_SELF_SERVICE,
        ],
        order: [
            'Carts.id_cart' => 'DESC',
        ])->first();
        $this->assertEquals(0, $cart->status);
    }

    public function testSelfServiceRemoveProductWithPricePerUnit(): void
    {
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(351, 1, '0,5');
        $this->removeProductFromSelfServiceCart(351);
        $this->assertJsonOk();
        $cartProductUnitsTable = $this->getTableLocator()->get('CartProductUnits');
        $cartProductUnits = $cartProductUnitsTable->find('all')->toArray();
        $this->assertEmpty($cartProductUnits);
    }

    public function testSelfServiceOrderWithoutPricePerUnit(): void
    {
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(346, 1, 0);
        $this->finishSelfServiceCart(1, 1);

        $cartsTable = $this->getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all', order: [
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

    public function testSelfServiceOrderWithPricePerUnit(): void
    {
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart('350-15', 1, '1,5');
        $this->addProductToSelfServiceCart(351, 1, '0,5');
        $this->finishSelfServiceCart(1, 1);

        $cartsTable = $this->getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all', order: [
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

    public function testSelfServiceOrderWithPricePerUnitAndUseWeightAsAmount(): void
    {
        $unitsTable = $this->getTableLocator()->get('Units');
        $unitEntityA = $unitsTable->get(7);
        $unitEntityA->use_weight_as_amount = 1;
        $unitEntityB = $unitsTable->get(8);
        $unitEntityB->use_weight_as_amount = 1;
        $unitsTable->saveMany([$unitEntityA, $unitEntityB]);

        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart('350-15', 1, '1,999');
        $this->addProductToSelfServiceCart(351, 1, '0,51');
        $this->finishSelfServiceCart(1, 1);

        $cartsTable = $this->getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all', order: [
            'Carts.id_cart' => 'DESC'
        ])->first();

        $cart = $this->getCartById($cart->id_cart);

        $this->assertEquals(2, count($cart->cart_products));

        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->order_detail_unit->mark_as_saved, 1);
            $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        }

        $stockAvailableTable = TableRegistry::getTableLocator()->get('StockAvailables');
        $stockAvailable = $stockAvailableTable->find('all')->where([
            'id_product' => 350,
            'id_product_attribute' => 15,
        ])->first();
        $this->assertEquals(997.001, $stockAvailable->quantity);

        $stockAvailableTable = TableRegistry::getTableLocator()->get('StockAvailables');
        $stockAvailable = $stockAvailableTable->find('all')->where([
            'id_product' => 351,
            'id_product_attribute' => 0,
        ])->first();
        $this->assertEquals(998.49, $stockAvailable->quantity);

        $this->assertMailCount(1);
        $this->assertMailSubjectContainsAt(0, 'Dein Einkauf');
        $this->assertMailContainsHtmlAt(0, 'Lagerprodukt mit Varianten : 1,999 kg');
        $this->assertMailContainsHtmlAt(0, 'Lagerprodukt 2 : 0,51 kg');
        $this->assertMailContainsHtmlAt(0, '19,99 €');
        $this->assertMailContainsHtmlAt(0, '7,65 €');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));

    }

    public function testSelfServiceOrderWithPricePerUnitPurchasePriceEnabled(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(347, 1, '500');
        $this->addProductToSelfServiceCart('348-12', 1, '250');
        $this->finishSelfServiceCart(1, 1);

        $cartsTable = $this->getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all', order: [
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

    public function testSelfServiceOrderWithDeliveryBreak(): void
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', (new DeliveryRhythmService())->getDeliveryDateByCurrentDayForDb());
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart('350-15', 1, '1,5');
        $this->finishSelfServiceCart(1, 1);
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogs = $actionLogsTable->find('all')->toArray();
        $this->assertRegExpWithUnquotedString('Demo Superadmin hat eine neue Bestellung getätigt (15,00 €).', $actionLogs[0]->text);
    }

    public function testSearchByCustomProductBarcode(): void
    {
        $this->loginAsSuperadmin();
        $barcodeForProduct = '1234567890123';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
        $this->assertRegExpWithUnquotedString('Das Produkt <b>Lagerprodukt</b> wurde in deine Einkaufstasche gelegt.', $_SESSION['Flash']['flash'][0]['message']);
        $this->assertRedirect($this->Slug->getSelfService());
    }

    public function testSearchByCustomProductAttributeBarcode(): void
    {
        $this->loginAsSuperadmin();
        $barcodeForProduct = '2145678901234';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
        $this->assertRegExpWithUnquotedString('Das Produkt <b>Lagerprodukt mit Varianten</b> wurde in deine Einkaufstasche gelegt.', $_SESSION['Flash']['flash'][0]['message']);
        $this->assertRedirect($this->Slug->getSelfService());
    }

    public function testSearchByCustomProductBarcodeWithWeight(): void
    {
        $this->loginAsSuperadmin();
        $barcodeForProduct = '2712345000235';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
        $this->assertRegExpWithUnquotedString('Das Produkt <b>Lagerprodukt mit Gewichtsbarcode</b> wurde in deine Einkaufstasche gelegt.', $_SESSION['Flash']['flash'][0]['message']);
        $this->assertRedirect($this->Slug->getSelfService());

        $cartProductUnitsTable = $this->getTableLocator()->get('CartProductUnits');
        $cartProductUnits = $cartProductUnitsTable->find('all')->first();
        $this->assertEquals(0.023, $cartProductUnits->ordered_quantity_in_units);
    }

    public function testSearchByCustomProductAttributeBarcodeWithWeight(): void
    {
        $this->loginAsSuperadmin();
        $barcodeForProduct = '2112345001234';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
        $this->assertRegExpWithUnquotedString('Das Produkt <b>Lagerprodukt mit Varianten</b> wurde in deine Einkaufstasche gelegt.', $_SESSION['Flash']['flash'][0]['message']);

        $cartProductUnitsTable = $this->getTableLocator()->get('CartProductUnits');
        $cartProductUnits = $cartProductUnitsTable->find('all')->first();
        $this->assertEquals(0.123, $cartProductUnits->ordered_quantity_in_units);

    }

    public function testSearchBySystemProductBarcodeWithMissingWeight(): void
    {
        $this->loginAsSuperadmin();
        $barcodeForProduct = 'b5320000';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
        $this->assertFlashMessageAt(0, 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.');
        $this->assertRedirect($this->Slug->getSelfService('', $barcodeForProduct));
    }

    public function testSearchBySystemProductAttributeBarcodeWithMissingWeight(): void
    {
        $this->loginAsSuperadmin();
        $barcodeForProduct = 'e05f0015';
        $this->get($this->Slug->getSelfService($barcodeForProduct));
        $this->assertFlashMessageAt(0, 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.');
        $this->assertRedirect($this->Slug->getSelfService('', $barcodeForProduct));
    }

    public function testSelfServiceOrderWithRetailModeAndSelfServiceCustomerWithAutoGenerateInvoiceDisabled(): void
    {

        Configure::write('app.selfServiceModeAutoGenerateInvoice', false);
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeCustomer(Configure::read('test.selfServiceCustomerId'), 'invoices_per_email_enabled', 0);
        $this->loginAsSuperadmin();
        $this->get('/admin/customers/changeStatus/' . Configure::read('test.selfServiceCustomerId'). '/1/0');
        $this->loginAsSelfServiceCustomer();
        $this->addProductToSelfServiceCart(346, 1, 0);
        $this->addProductToSelfServiceCart(351, 1, '0,5');

        $cartsTable = $this->getTableLocator()->get('Carts');
        $this->finishSelfServiceCart(1, 1);
        $this->runAndAssertQueue();
        $this->assertSessionNotHasKey('invoiceRouteForAutoPrint');

        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoices = $invoicesTable->find('all');
        $this->assertEquals($invoices->count(), 0);
    }

    public function testSelfServiceOrderWithRetailModeAndSelfServiceCustomer(): void
    {
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeCustomer(Configure::read('test.selfServiceCustomerId'), 'invoices_per_email_enabled', 0);
        $this->loginAsSuperadmin();
        $this->get('/admin/customers/changeStatus/' . Configure::read('test.selfServiceCustomerId'). '/1/0');
        $this->loginAsSelfServiceCustomer();
        $this->addProductToSelfServiceCart(346, 1, 0);
        $this->addProductToSelfServiceCart(351, 1, '0,5');

        $cartsTable = $this->getTableLocator()->get('Carts');
        $this->finishSelfServiceCart(1, 1);
        $this->runAndAssertQueue();
        $this->assertSessionHasKey('invoiceRouteForAutoPrint');

        $cart = $cartsTable->find('all',
            order: [
                'Carts.id_cart' => 'DESC'
            ],
        )->first();
        $cart = $this->getCartById($cart->id_cart);

        $this->assertEquals(2, count($cart->cart_products));

        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->order_state, OrderDetail::STATE_BILLED_CASHLESS);
        }

        $this->assertMailCount(1);

        $this->assertMailSubjectContainsAt(0, 'Dein Einkauf');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSelfServiceCustomer'));

        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoices = $invoicesTable->find('all');
        $this->assertEquals($invoices->count(), 1);
        $this->assertEquals($invoices->toArray()[0]->paid_in_cash, 1);

    }

    public function testSelfServiceOrderForDifferentCustomer(): void
    {
        // add a product to the "normal" cart (Cart::TYPE_WEEKLY_RHYTHM)
        $this->loginAsCustomer();
        $this->addProductToCart(346, 5);
        $this->logout();

        $this->loginAsSuperadmin();
        $customersTable = $this->getTableLocator()->get('Customers');
        $testCustomer = $customersTable->find('all',
            conditions: [
                $customersTable->aliasField('id_customer') => Configure::read('test.customerId'),
            ]
        )->first();
        $this->get($this->Slug->getOrderDetailsList().'/initSelfServiceOrder/' . Configure::read('test.customerId'));
        $this->loginAsSuperadminAddOrderCustomerToSession($_SESSION);
        $this->get($this->_response->getHeaderLine('Location'));
        $this->assertResponseContains('Diese Bestellung wird für <b>' . $testCustomer->name . '</b> getätigt.');

        $this->addProductToSelfServiceCart(349, 1);
        $this->addProductToSelfServiceCart('350-13', 2, 1);

        $cartsTable = $this->getTableLocator()->get('Carts');
        $this->finishSelfServiceCart(1, 1);

        $carts = $cartsTable->find('all',
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

        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoiceCount = $invoicesTable->find('all')->count();
        $this->assertEquals($invoiceCount, 0);

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogs = $actionLogsTable->find('all')->toArray();
        $this->assertEquals('carts', $actionLogs[0]->object_type);
        $this->assertEquals($carts[0]->id_cart, $actionLogs[0]->object_id);
        $this->assertEquals($actionLogs[0]->text, 'Demo Superadmin hat eine neue Bestellung für <b>Demo Mitglied</b> getätigt (9,00 €).');
        $this->assertEquals(Configure::read('test.superadminId'), $actionLogs[0]->customer_id);
    }

    public function testProductDetailHtmlProductCatalogSelfServiceOrder(): void
    {
        $this->loginAsSuperadmin();
        $this->isSelfServiceModeByUrl = true;
        $productId = 349;
        $this->get($this->Slug->getSelfService($productId));
        $nextDeliveryDay = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
        $pickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($nextDeliveryDay));
        $this->assertResponseContains('<span class="pickup-day">'.$pickupDay.'</span>');
    }

    public function testAutoLoginAsSelfServiceCustomerOk(): void
    {
        $selfServiceCustomerId = 93;
        $this->changeCustomer($selfServiceCustomerId, 'active', 1);
        Configure::write('app.selfServiceLoginCustomers', [
            [
                'id' => 1,
                'label' => 'SB-Kunde',
                'customerId' => $selfServiceCustomerId,
            ],
        ]);
        $this->get($this->Slug->getAutoLoginAsSelfServiceCustomer(1));
        $this->assertSession($selfServiceCustomerId, 'Auth.id_customer');
        $this->assertRedirect($this->Slug->getSelfService());
    }

    public function testAutoLoginAsSelfServiceCustomerNotOk(): void
    {
        $selfServiceCustomerId = 93;
        Configure::write('app.selfServiceLoginCustomers', [
            [
                'id' => 1,
                'label' => 'SB-Kunde',
                'customerId' => $selfServiceCustomerId,
            ],
        ]);
        $this->get($this->Slug->getAutoLoginAsSelfServiceCustomer(1));
        $this->assertFlashMessage('Anmelden ist fehlgeschlagen.');
        $this->assertRedirect($this->Slug->getHome());
    }

    private function doBarCodeLogin(): void
    {
        $this->post($this->Slug->getLogin(), [
            'barcode' => Configure::read('test.superadminBarCode')
        ]);
    }

}
