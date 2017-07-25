<?php

App::uses('AppCakeTestCase', 'Test');
App::uses('CakeCart', 'Model');
App::uses('Product', 'Model');
App::uses('Order', 'Model');
App::uses('StockAvailable', 'Model');
App::uses('EmailLog', 'Model');

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
class CartsControllerTest extends AppCakeTestCase
{

    // artischocke, 0,5 € deposit
    public $productId1 = '346';
    // milk with attribute 0,5, 0,5 € deposit
    public $productId2 = '60-10';
    // knoblauch, 0% tax
    public $productId3 = '344';
    public $Cart;

    public $Product;

    public $Order;

    public $StockAvailable;

    public $EmailLog;

    public function setUp()
    {
        parent::setUp();
        $this->CakeCart = new CakeCart();
        $this->Product = new Product();
        $this->Order = new Order();
        $this->StockAvailable = new StockAvailable();
        $this->EmailLog = new EmailLog();
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
        $this->assertRegExpWithUnquotedString('Die gewünschte Anzahl "100" ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }

    public function testRemoveProduct()
    {
        $this->loginAsCustomer();
        $response = $this->addProductToCart($this->productId1, 2);
        $this->assertJsonOk();
        $response = $this->removeProduct($this->productId1);
        $cakeCart = $this->CakeCart->getCakeCart($this->browser->getLoggedUserId());
        $this->assertEquals(array(), $cakeCart['CakeCartProducts'], 'cart must be empty');
        $this->assertJsonOk();
        $response = $this->removeProduct($this->productId1);
        $this->assertRegExpWithUnquotedString('Produkt 346 war nicht in Warenkorb vorhanden.', $response->msg);
        $this->assertJsonError();
    }

    public function testCartLoggedIn()
    {
        // manufacturer status needs to be changed as well, therefore use a superadmin account for both shopping and changing manufacturer data
        $this->loginAsSuperadmin();

        /**
         * START add product
         */
        $amount1 = 2;
        $this->addProductToCart($this->productId1, $amount1);
        $this->assertJsonOk();

        // check if product was placed in cart
        $cakeCart = $this->CakeCart->getCakeCart($this->browser->getLoggedUserId());
        $this->assertEquals($this->productId1, $cakeCart['CakeCartProducts'][0]['productId'], 'product id not found in cart');
        $this->assertEquals($amount1, $cakeCart['CakeCartProducts'][0]['amount'], 'amount not found in cart or amount wrong');

        // try to add an amount that is not available any more
        $this->addTooManyProducts($this->productId1, 99, $amount1, 'Die gewünschte Anzahl (101) des Produktes "Artischocke" ist leider nicht mehr verfügbar. Verfügbare Menge: 98', 0);

        /**
         * START add product with attribute
         */
        $amount2 = 3;
        $this->addProductToCart($this->productId2, $amount2);
        $this->assertJsonOk();

        // check if product was placed in cart
        $cakeCart = $this->CakeCart->getCakeCart($this->browser->getLoggedUserId());
        $this->assertEquals($this->productId2, $cakeCart['CakeCartProducts'][1]['productId'], 'product id not found in cart');
        $this->assertEquals($amount2, $cakeCart['CakeCartProducts'][1]['amount'], 'amount not found in cart or amount wrong');

        // try to add an amount that is not available any more
        $this->addTooManyProducts($this->productId2, 48, $amount2, 'Die gewünschte Anzahl (51) der Variante "0,5l" des Produktes "Milch" ist leider nicht mehr verfügbar. Verfügbare Menge: 20', 1);

        /**
         * START add product with zero tax
         */
        $amount3 = 1;
        $this->addProductToCart($this->productId3, $amount3);
        $this->assertJsonOk();

        // cake cart status check BEFORE finish
        $cakeCart = $this->CakeCart->getCakeCart($this->browser->getLoggedUserId());
        $this->assertEquals($cakeCart['CakeCart']['status'], 1, 'cake cart status wrong');

        $this->assertEquals($cakeCart['CakeCart']['id_cart'], 1, 'cake cart id wrong');

        /**
         * START finish cart
         */
        // START test if PRODUCT that was deactivated during shopping process
        $this->changeProductStatus($this->productId1, APP_OFF);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Das Produkt (.*) ist leider nicht mehr aktiviert und somit nicht mehr bestellbar./', $this->browser->getContent());
        $this->changeProductStatus($this->productId1, APP_ON);

        // START test if MANUFACTURER was deactivated during shopping process
        $manufacturerId = 5;
        $this->changeManufacturerStatus($manufacturerId, APP_OFF);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Der Hersteller des Produkts (.*) ist entweder im Urlaub oder nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar./', $this->browser->getContent());
        $this->changeManufacturerStatus($manufacturerId, APP_ON);

        // START test if MANUFACTURER's holiday mode was activated during shopping process
        $manufacturerId = 5;
        $this->changeManufacturerHolidayMode($manufacturerId, date('Y-m-d'));
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Der Hersteller des Produkts (.*) ist entweder im Urlaub oder nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar./', $this->browser->getContent());
        $this->changeManufacturerHolidayMode($manufacturerId, null);

        // START test if stock available for PRODUCT has gone down (eg. by another order)
        $this->changeStockAvailable($this->productId1, 1);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Anzahl \(2\) des Produktes (.*) ist leider nicht mehr (.*) Menge: 1/', $this->browser->getContent()); // ü needs to be escaped properly
        $this->changeStockAvailable($this->productId1, 98); // reset to old stock available

        // START test if stock available for ATTRIBUTE has gone down (eg. by another order)
        $this->changeStockAvailable($this->productId2, 1);
        $this->finishCart();
        $this->checkValidationError();
        $this->assertRegExp('/Anzahl \(3\) der Variante (.*) des Produktes (.*) ist leider nicht mehr (.*) Menge: 1/', $this->browser->getContent()); // ü needs to be escaped properly
        $this->changeStockAvailable($this->productId2, 20); // reset to old stock available

        // FINALLY order can be finished

        // 1) do not check legal checkboxes
        $this->finishCart(false, false);
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die AGB.', $this->browser->getContent(), 'checkbox validation general_terms_and_conditions_accepted did not work');
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.', $this->browser->getContent(), 'checkbox validation cancellation_terms_accepted did not work');

        // 2) add order comment
        $this->finishCart(true, true, 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, adfasfd sa');
        $this->assertRegExpWithUnquotedString('Bitte gib maximal 500 Zeichen ein.', $this->browser->getContent(), 'order comment validation did not work');

        // 3) check the checkboxes and add a valid order comment
        $orderComment = 'this is a valid order comment';
        $this->finishCart(true, true, $orderComment);
        $orderId = Configure::read('htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());

        $this->checkCartStatusAfterFinish();

        /**
         * START check order
         */
        $this->Order->recursive = 2;
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'Order.id_order' => $orderId
            )
        ));
        $this->assertNotEquals(array(), $order, 'order not correct');
        $this->assertEquals($order['Order']['id_order'], $orderId, 'order id not correct');
        $this->assertEquals($order['Order']['id_customer'], $this->browser->getLoggedUserId(), 'order customer_id not correct');
        $this->assertEquals($order['Order']['id_cake_cart'], 1, 'order cake_id not correct');
        $this->assertEquals($order['Order']['current_state'], 3, 'order current_state not correct');
        $this->assertEquals($order['Order']['total_deposit'], 2.5, 'order total_deposit not correct');
        $this->assertEquals($order['Order']['total_paid_tax_excl'], 5.578515, 'order total_paid_tax_excl not correct');
        $this->assertEquals($order['Order']['total_paid_tax_incl'], 6.136364, 'order total_paid_tax_incl not correct');
        $this->assertEquals($order['Order']['general_terms_and_conditions_accepted'], 1, 'order general_terms_and_conditions_accepted not correct');
        $this->assertEquals($order['Order']['cancellation_terms_accepted'], 1, 'order cancellation_terms_accepted not correct');
        $this->assertEquals($order['Order']['comment'], $orderComment, 'order comment not correct');

        // check order_details for product1
        $this->checkOrderDetails($order['OrderDetails'][0], 'Artischocke : Stück', 2, 0, 1, 3.305786, 3.305786, 3.64, 0.17, 0.34, 2);

        // check order_details for product2 (third! index)
        $this->checkOrderDetails($order['OrderDetails'][2], 'Milch : 0,5l', 3, 10, 1.5, 1.636365, 1.636365, 1.86, 0.07, 0.21, 3);

        // check order_details for product3 (second! index)
        $this->checkOrderDetails($order['OrderDetails'][1], 'Knoblauch : 100 g', 1, 0, 0, 0.636364, 0.636364, 0.636364, 0.000000, 0.000000, 0);

        $this->checkStockAvailable($this->productId1, 96);
        $this->checkStockAvailable($this->productId2, 17);
        $this->checkStockAvailable($this->productId3, 77);

        // check new (empty) cart
        $cakeCart = $this->CakeCart->getCakeCart($this->browser->getLoggedUserId());
        $this->assertEquals($cakeCart['CakeCart']['id_cart'], 2, 'cake cart id wrong');
        $this->assertEquals(array(), $cakeCart['CakeCartProducts'], 'cake cart products not empty');

        // check email to customer
        $emailLogs = $this->EmailLog->find('all');
        $this->assertEmailLogs($emailLogs[0], 'Bestellbestätigung', array('Artischocke : Stück', 'Hallo Demo Superadmin,'), array(Configure::read('test.loginEmailSuperadmin')));

        $this->browser->doFoodCoopShopLogout();
    }

    public function testShopOrder()
    {
        $this->loginAsSuperadmin();
        $responseHtml = $this->browser->get('/admin/orders/initShopOrder/' . Configure::read('test.shopOrderTestUser')['email']);
        $this->assertRegExp('/Diese Bestellung wird für \<b\>' . Configure::read('test.shopOrderTestUser')['name'] . '\<\/b\> getätigt./', $responseHtml);
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
        $orderId = Configure::read('htmlHelper')->getOrderIdFromCartFinishedUrl($this->browser->getUrl());
        $this->assertTrue(is_int($orderId), 'order not finished correctly');

        $this->checkCartStatusAfterFinish();

        // only one order with the cake cart id should have been created
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'Order.id_cake_cart' => 1
            )
        ));
        $this->assertEquals(1, count($orders), 'more than one order inserted');

        foreach ($orders[0]['OrderDetails'] as $orderDetail) {
            $this->assertFalse($orderDetail['product_quantity'] == 0, 'product quantity must not be 0!');
        }
    }

    /**
     * cake cart status check AFTER finish
     * as cart is finished, a new cart is already existing
     */
    private function checkCartStatusAfterFinish()
    {
        $cakeCart = $this->CakeCart->find('first', array(
            'conditions' => array(
                'CakeCart.id_cart' => 1
            )
        ));
        $this->assertEquals($cakeCart['CakeCart']['status'], 0, 'cake cart status wrong');
    }

    private function addTooManyProducts($productId, $amount, $expectedAmount, $expectedErrorMessage, $productIndex)
    {
        $this->addProductToCart($productId, $amount);
        $response = $this->browser->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertEquals($productId, $response->productId);
        $this->assertJsonError();
        $cakeCart = $this->CakeCart->getCakeCart($this->browser->getLoggedUserId());
        $this->assertEquals($expectedAmount, $cakeCart['CakeCartProducts'][$productIndex]['amount'], 'amount not found in cart or wrong');
    }

    private function checkValidationError()
    {
        $this->assertRegExp('/initCartErrors()/', $this->browser->getContent());
    }

    private function changeStockAvailable($productId, $amount)
    {
        $this->browser->post('/admin/products/editQuantity/', array(
            'data' => array(
                'productId' => $productId,
                'quantity' => $amount
            )
        ));
        return $this->browser->getJsonDecodedContent();
    }

    private function checkStockAvailable($productId, $result)
    {
        $ids = $this->Product->getProductIdAndAttributeId($productId);

        // get changed product
        $stockAvailable = $this->StockAvailable->find('first', array(
            'conditions' => array(
                'StockAvailable.id_product' => $ids['productId'],
                'StockAvailable.id_product_attribute' => $ids['attributeId']
            )
        ));

        // stock available check of changed product
        $this->assertEquals($stockAvailable['StockAvailable']['quantity'], $result, 'stockavailable quantity wrong');
    }

    private function checkOrderDetails($orderDetail, $name, $quantity, $productAttributeId, $deposit, $productPrice, $totalPriceTaxExcl, $totalPriceTaxIncl, $taxUnitAmount, $taxTotalAmount, $taxId)
    {

        // check order_details
        $this->assertEquals($orderDetail['product_name'], $name, '%s order_detail product name was not correct');
        $this->assertEquals($orderDetail['product_quantity'], $quantity, 'order_detail quantity was not correct');
        $this->assertEquals($orderDetail['product_attribute_id'], $productAttributeId, 'order_detail product_attribute_id was not correct');
        $this->assertEquals($orderDetail['deposit'], $deposit, 'order_detail deposit was not correct');
        $this->assertEquals($orderDetail['product_price'], $productPrice, 'order_detail product_price was not correct');
        $this->assertEquals($orderDetail['total_price_tax_excl'], $totalPriceTaxExcl, 'order_detail total_price_tax_excl not correct');
        $this->assertEquals($orderDetail['total_price_tax_incl'], $totalPriceTaxIncl, 'order_detail total_price_tax_incl not correct');
        $this->assertEquals($orderDetail['id_tax'], $taxId, 'order_detail id_tax not correct');

        // check order_details_tax
        $this->assertEquals($orderDetail['OrderDetailTax']['unit_amount'], $taxUnitAmount, 'order_detail tax unit amount not correct');
        $this->assertEquals($orderDetail['OrderDetailTax']['total_amount'], $taxTotalAmount, 'order_detail tax total amount not correct');
    }

    /**
     *
     * @param int $productId
     * @param int $amount
     * @return json string
     */
    private function changeProductStatus($productId, $status)
    {
        $this->browser->get('/admin/products/changeStatus/' . $productId . '/' . $status);
        return $this->browser->getJsonDecodedContent();
    }

    private function changeManufacturerStatus($manufacturerId, $status)
    {
        $this->browser->get('/admin/manufacturers/changeStatus/' . $manufacturerId . '/' . $status);
        return $this->browser->getJsonDecodedContent();
    }

    /**
     *
     * @param int $productId
     * @return json string
     */
    private function removeProduct($productId)
    {
        $this->browser->ajaxPost('/warenkorb/ajaxRemove', array(
            'data' => array(
                'productId' => $productId
            )
        ));
        return $this->browser->getJsonDecodedContent();
    }
}
