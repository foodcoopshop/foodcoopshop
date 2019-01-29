<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class OrderDetailsControllerTest extends AppCakeTestCase
{

    public $Manufacturer;

    public $EmailLog;

    public $productIdA = 346;
    public $productIdB = 340;
    public $productIdC = '60-10';
    
    public $orderDetailIdA = 1;
    public $orderDetailIdB = 2;
    public $orderDetailIdC = 3;
    
    public $cancellationReason = 'Product was not fresh any more.';

    public $newPrice = '3,53';
    public $editPriceReason = 'Product was smaller than expected.';

    public $newAmount = 1;
    public $editAmountReason = 'One product was not delivered.';


    public function setUp()
    {
        parent::setUp();
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
    }
    
    public function testEditPickupDayAsSuperadminWrongWeekday()
    {
        $this->loginAsSuperadmin();
        $response = $this->editPickupDayOfOrderDetails([$this->orderDetailIdA, $this->orderDetailIdB], '2018-01-01', 'bla');
        $this->assertRegExpWithUnquotedString('Der Abholtag muss ein Freitag sein.', $response->msg);
        $this->assertJsonError();    
    }

    public function testEditPickupDayAsSuperadminEmptyReason()
    {
        $this->loginAsSuperadmin();
        $response = $this->editPickupDayOfOrderDetails([$this->orderDetailIdA, $this->orderDetailIdB], '2018-01-01', '');
        $this->assertRegExpWithUnquotedString('Bitte gib an, warum der Abholtag geändert wird.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditPickupDayAsSuperadminNoOrderDetailIds()
    {
        $this->loginAsSuperadmin();
        $response = $this->editPickupDayOfOrderDetails([], '2018-01-01', 'asdf');
        $this->assertRegExpWithUnquotedString('error - no order detail id passed', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditPickupDayAsSuperadminWrongOrderDetailIds()
    {
        $this->loginAsSuperadmin();
        $response = $this->editPickupDayOfOrderDetails([200,40], '2018-01-01', 'asdf');
        $this->assertRegExpWithUnquotedString('error - order details wrong', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditPickupDayAsSuperadminOk()
    {
        $this->loginAsSuperadmin();
        $reason = 'this is the reason';
        $this->editPickupDayOfOrderDetails([$this->orderDetailIdA, $this->orderDetailIdB], '2018-09-07', $reason);
        $this->assertJsonOk();
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs(
            $emailLogs[0],
            'Der Abholtag deiner Bestellung wurde geändert auf: Freitag, 07.09.2018',
            [
                $reason,
                'Neuer Abholtag : <b>Freitag, 07.09.2018</b>',
                'Alter Abholtag: Freitag, 02.02.2018',
            ],
            [Configure::read('test.loginEmailSuperadmin')]
        );
    }
    
    public function testCancellationAsManufacturer()
    {
        $this->loginAsVegetableManufacturer();
        
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);
        
        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);
        
        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedStockAvailable($this->productIdA, 98);
    }
    
    public function testCancellationAsSuperadminWithEnabledNotificationAfterOrderListsWereSent()
    {
        $this->loginAsSuperadmin();
        $this->simulateSendOrderListsCronjob($this->orderDetailIdA);
        
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [
            Configure::read('test.loginEmailVegetableManufacturer')
        ];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->simulateSendOrderListsCronjob($this->orderDetailIdA);
        
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_deleted_notification', 0);

        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);
        
        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationAsSuperadminWithEnabledBulkOrders()
    {
        $this->loginAsSuperadmin();
        $this->simulateSendOrderListsCronjob($this->orderDetailIdA);
        
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'bulk_orders_allowed', 1);

        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdA]);
        
        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailDeletedEmails(0, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedStockAvailable($this->productIdA, 98);
    }

    public function testCancellationProductAttributeStockAvailableAsSuperadmin()
    {
        $this->loginAsSuperadmin();
        $this->deleteAndAssertRemoveFromDatabase([$this->orderDetailIdC]);
        $this->assertChangedStockAvailable($this->productIdC, 20);
    }

    public function testCancellationWithTimebasedCurrency()
    {
        $cart = $this->prepareTimebasedCurrencyCart();
        $orderDetailId = $cart->cart_products[1]->order_detail->id_order_detail;
        $this->deleteAndAssertRemoveFromDatabase([$orderDetailId]);
        
        // assert if record TimebasedCurrencyOrderDetail was removed
        $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
        $timebasedCurrencyOrderDetail = $this->TimebasedCurrencyOrderDetail->find('all', [
            'conditions' => [
                'TimebasedCurrencyOrderDetails.id_order_detail' => $orderDetailId
            ]
        ]);
        $this->assertEquals(0, $timebasedCurrencyOrderDetail->count());
    }

    public function testEditOrderDetailPriceAsManufacturer()
    {
        $this->loginAsVegetableManufacturer();
        $this->editOrderDetailPrice($this->orderDetailIdA, $this->newPrice, $this->editPriceReason);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$this->orderDetailIdA]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailQuantityAsSuperadmin()
    {
        $this->loginAsSuperadmin();

        $cart = $this->preparePricePerUnitOrder();

        $newQuantity = 800.584;
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity, false);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);
        
        $this->assertEquals(12.00876, $changedOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(10.90876, $changedOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals($newQuantity, $changedOrderDetails[0]->order_detail_unit->product_quantity_in_units);

        $this->assertEquals(0.55, $changedOrderDetails[0]->order_detail_tax->unit_amount);
        $this->assertEquals(1.10, $changedOrderDetails[0]->order_detail_tax->total_amount);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [Configure::read('test.loginEmailMeatManufacturer')];
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[1], 'Gewicht angepasst: Forelle : Stück', [$newQuantity, 'Demo Superadmin', 'Der Basis-Preis beträgt 1,50 € / 100 g'], $expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailQuantityAsSuperadminDoNotChangePrice()
    {
        $this->loginAsSuperadmin();

        $cart = $this->preparePricePerUnitOrder();

        $newQuantity = 800.854;
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity, true);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);
        
        $this->assertEquals($changedOrderDetails[0]->total_price_tax_incl, $changedOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals($changedOrderDetails[0]->total_price_tax_excl, $changedOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals($newQuantity, $changedOrderDetails[0]->order_detail_unit->product_quantity_in_units);

        $this->assertEquals($changedOrderDetails[0]->order_detail_tax->unit_amount, $changedOrderDetails[0]->order_detail_tax->unit_amount);
        $this->assertEquals($changedOrderDetails[0]->order_detail_tax->total_amount, $changedOrderDetails[0]->order_detail_tax->total_amount);

        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs));

    }

    public function testEditOrderDetailPriceWithTimebasedCurrency()
    {
        $cart = $this->prepareTimebasedCurrencyCart();
        $orderDetailId = $cart->cart_products[1]->order_detail->id_order_detail;
        $this->editOrderDetailPrice($orderDetailId, $this->newPrice, $this->editPriceReason);
        
        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $this->assertTimebasedCurrencyOrderDetail($changedOrderDetails[0], 1.38, 1.52, 544);
    }

    public function testEditOrderDetailPriceAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();

        $this->editOrderDetailPrice($this->orderDetailIdA, $this->newPrice, $this->editPriceReason);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$this->orderDetailIdA]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl), 'order detail price was not changed properly');
        
        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [Configure::read('test.loginEmailVegetableManufacturer')];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailPriceIfPriceWasZero()
    {
        $this->loginAsSuperadmin();
        $this->changeProductPrice($this->productIdA, 0);
        $this->mockCart = $this->generateAndGetCart();
        
        $mockOrderDetailId = $this->mockCart->cart_products[1]->order_detail->id_order_detail;
        $this->editOrderDetailPrice($mockOrderDetailId, $this->newPrice, $this->editPriceReason);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$mockOrderDetailId]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails(1, $expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailPriceAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_price_changed_notification', 0);

        $this->editOrderDetailPrice($this->orderDetailIdA, $this->newPrice, $this->editPriceReason);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$this->orderDetailIdA]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailPriceAsSuperadminWithEnabledBulkOrders()
    {
        $this->loginAsSuperadmin();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'bulk_orders_allowed', 1);

        $this->editOrderDetailPrice($this->orderDetailIdA, $this->newPrice, $this->editPriceReason);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$this->orderDetailIdA]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl), 'order detail price was not changed properly');
        
        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailAmountAsManufacturer()
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(5, 2);
        $this->logout();
        $this->loginAsVegetableManufacturer();
        $this->editOrderDetailAmount($this->mockCart->cart_products[1]->order_detail->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockCartFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->cart_products[1]->order_detail->product_amount, 'order detail amount was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedStockAvailable($this->productIdA, 96);
    }

    public function testEditOrderDetailAmountWithTimebasedCurrency()
    {

        $cart = $this->prepareTimebasedCurrencyCart();
        $orderDetailId = $cart->cart_products[1]->order_detail->id_order_detail;
        $this->editOrderDetailAmount($orderDetailId, $this->newAmount, $this->editAmountReason);
        
        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);
        
        $this->assertEquals($this->newAmount, $changedOrderDetails[0]->product_amount, 'order detail amount was not changed properly');
        $this->assertEquals('1,40', Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl), 'order detail price was not changed properly');

        $this->assertTimebasedCurrencyOrderDetail($changedOrderDetails[0], 0.55, 0.6, 216);
    }

    public function testEditOrderDetailAmountAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(1, 2);

        $this->editOrderDetailAmount($this->mockCart->cart_products[1]->order_detail->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockCartFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->cart_products[1]->order_detail->product_amount, 'order detail amount was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedStockAvailable($this->productIdA, 96);
    }

    public function testEditOrderDetailAmountAsSuperadminWithEnabledNotificationAfterOrderListsWereSent()
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(1, 2);
        $orderDetailId = $this->mockCart->cart_products[1]->order_detail->id_order_detail;
        $this->simulateSendOrderListsCronjob($orderDetailId);
        
        $this->editOrderDetailAmount($orderDetailId, $this->newAmount, $this->editAmountReason);
        
        $changedOrder = $this->getChangedMockCartFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->cart_products[1]->order_detail->product_amount, 'order detail amount was not changed properly');
        
        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [
            Configure::read('test.loginEmailVegetableManufacturer')
        ];
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmails, $expectedCcEmails);
        
        $this->assertChangedStockAvailable($this->productIdA, 96);
    }
    
    public function testEditOrderDetailAmountAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(1, 2);
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_amount_changed_notification', 0);

        $this->editOrderDetailAmount($this->mockCart->cart_products[1]->order_detail->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockCartFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->cart_products[1]->order_detail->product_amount, 'order detail amount was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedStockAvailable($this->productIdA, 96);
    }

    public function testEditOrderDetailAmountAsSuperadminWithEnabledBulkOrders()
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(1, 2);
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'bulk_orders_allowed', 1);

        $this->editOrderDetailAmount($this->mockCart->cart_products[1]->order_detail->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockCartFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->cart_products[1]->order_detail->product_amount, 'order detail amount was not changed properly');

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedStockAvailable($this->productIdA, 96);
    }

    private function preparePricePerUnitOrder()
    {
        $productIdA = 347; // forelle

        $this->addProductToCart($productIdA, 1);
        $this->addProductToCart($productIdA, 1); // addProductToCart needs to be called twice!

        $this->finishCart(1, 1);
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());
        $cart = $this->getCartById($cartId);
        return $cart;
    }

    private function prepareTimebasedCurrencyCart()
    {
        $reducedMaxPercentage = 15;
        $this->prepareTimebasedCurrencyConfiguration($reducedMaxPercentage);
        $this->loginAsSuperadmin();
        $this->addProductToCart(344, 1); // addProductToCart needs to be called twice!
        $this->addProductToCart(346, 2);
        $this->finishCart(1, 1, '', '352');
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());
        $cart = $this->getCartById($cartId);
        return $cart;
    }

    private function assertTimebasedCurrencyOrderDetail($changedOrderDetail, $moneyExcl, $moneyIncl, $seconds)
    {
        $this->assertEquals($changedOrderDetail->timebased_currency_order_detail->money_excl, $moneyExcl);
        $this->assertEquals($changedOrderDetail->timebased_currency_order_detail->money_incl, $moneyIncl);
        $this->assertEquals($changedOrderDetail->timebased_currency_order_detail->seconds, $seconds);
    }

    private function getChangedMockCartFromDatabase()
    {
        if (!$this->mockCart) {
            return false;
        }
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $this->mockCart->id_cart
            ],
            'contain' => [
                'CartProducts.OrderDetails.OrderDetailUnits',
            ]
        ])->first();
        return $cart;
    }
    
    private function getOrderDetailsFromDatabase($orderDetailIds) {
        $orderDetails = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail IN' => $orderDetailIds
            ],
            'contain' => [
                'OrderDetailTaxes',
                'OrderDetailUnits',
                'TimebasedCurrencyOrderDetails'
            ]
        ])->toArray();
        return $orderDetails;
    }

    private function deleteAndAssertRemoveFromDatabase($orderDetailIds)
    {
        $this->deleteOrderDetail($orderDetailIds, $this->cancellationReason);
        $orderDetails = $this->getOrderDetailsFromDatabase($orderDetailIds);
        $this->assertEmpty($orderDetails, 'order detail was not deleted properly');
    }

    /**
     * @return array $order
     */
    private function generateAndGetCart($productAAmount = 1, $productBAmount = 1)
    {

        //TODO calling the method addProductToCart only once leads to order error - needs debugging
        $this->addProductToCart($this->productIdA, $productAAmount);
        $this->addProductToCart($this->productIdB, $productBAmount);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->httpClient->getUrl());
        $cart = $this->getCartById($cartId);
        return $cart;
    }

    private function assertOrderDetailDeletedEmails($emailLogIndex, $expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[$emailLogIndex], 'Produkt storniert: Artischocke : Stück', [$this->cancellationReason, '1,82', 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function assertOrderDetailProductPriceChangedEmails($emailLogIndex, $expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[$emailLogIndex], 'Preis angepasst: Artischocke', [$this->editPriceReason, $this->newPrice, 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function assertOrderDetailProductAmountChangedEmails($emailLogIndex, $expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[$emailLogIndex], 'Bestellte Anzahl angepasst: Artischocke : Stück', ['Die Anzahl des Produktes <b>Artischocke : Stück</b> wurde angepasst', $this->editAmountReason, 'Neue Anzahl: <b>' . $this->newAmount . '</b>', 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function assertChangedStockAvailable($productIds, $expectedAmount)
    {
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $ids = $this->Product->getProductIdAndAttributeId($productIds);
        $this->StockAvailable = TableRegistry::getTableLocator()->get('StockAvailables');
        $changedStockAvailable = $this->StockAvailable->find('all', [
            'conditions' => [
                'StockAvailables.id_product' => $ids['productId'],
                'StockAvailables.id_product_attribute' => $ids['attributeId'],
            ]
        ])->first();
        $quantity = $changedStockAvailable->quantity;
        $this->assertEquals($expectedAmount, $quantity, 'amount was not corrected properly');
    }
    
    private function simulateSendOrderListsCronjob($orderDetailId)
    {
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get($orderDetailId),
                [
                    'order_state' => ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER
                ]
            )
        );
        
    }

    private function editPickupDayOfOrderDetails($orderDetailIds, $pickupDay, $reason)
    {
        $this->httpClient->ajaxPost(
            '/admin/order-details/editPickupDay/',
            [
                'orderDetailIds' => $orderDetailIds,
                'pickupDay' => $pickupDay,
                'changePickupDayReason' => $reason
            ]
        );
        return $this->httpClient->getJsonDecodedContent();
    }
    
    private function deleteOrderDetail($orderDetailIds, $cancellationReason)
    {
        $this->httpClient->post(
            '/admin/order-details/delete/',
            [
                'orderDetailIds' => $orderDetailIds,
                'cancellationReason' => $cancellationReason
            ]
        );
    }

    private function editOrderDetailPrice($orderDetailId, $productPrice, $editPriceReason)
    {
        $this->httpClient->post(
            '/admin/order-details/editProductPrice/',
            [
                'orderDetailId' => $orderDetailId,
                'productPrice' => $productPrice,
                'editPriceReason' => $editPriceReason
            ]
        );
    }

    private function editOrderDetailQuantity($orderDetailId, $productQuantity, $doNotChangePrice)
    {
        $this->httpClient->post(
            '/admin/order-details/editProductQuantity/',
            [
                'orderDetailId' => $orderDetailId,
                'productQuantity' => $productQuantity,
                'doNotChangePrice' => $doNotChangePrice
            ]
        );
    }

    private function editOrderDetailAmount($orderDetailId, $productAmount, $editAmountReason)
    {
        $this->httpClient->post(
            '/admin/order-details/editProductAmount/',
            [
                'orderDetailId' => $orderDetailId,
                'productAmount' => $productAmount,
                'editAmountReason' => $editAmountReason
            ]
        );
    }
}
