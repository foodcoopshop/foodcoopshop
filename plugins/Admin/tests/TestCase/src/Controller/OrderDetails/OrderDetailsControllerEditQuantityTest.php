<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\OrderDetailsControllerTestCase;
use Cake\Core\Configure;

class OrderDetailsControllerEditQuantityTest extends OrderDetailsControllerTestCase
{

    public function testEditOrderDetailQuantityNotValid()
    {
        $this->loginAsSuperadmin();
        $cart = $this->preparePricePerUnitOrder();
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, -1, 'reason');
        $this->assertEquals($this->httpClient->getJsonDecodedContent()->msg, 'Das gelieferte Gewicht ist nicht gültig.');
    }

    public function testEditOrderDetailQuantityAsSuperadminDifferentQuantity()
    {
        $this->loginAsSuperadmin();

        $cart = $this->preparePricePerUnitOrder();

        $newQuantity = 800.584;
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity, false);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);

        $this->assertEquals(12.01, $changedOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(10.91, $changedOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals($newQuantity, $changedOrderDetails[0]->order_detail_unit->product_quantity_in_units);
        $this->assertEquals(1, $changedOrderDetails[0]->order_detail_unit->mark_as_saved);

        $this->assertEquals(0.55, $changedOrderDetails[0]->order_detail_tax->unit_amount);
        $this->assertEquals(1.10, $changedOrderDetails[0]->order_detail_tax->total_amount);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [Configure::read('test.loginEmailMeatManufacturer')];
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[1], 'Gewicht angepasst für "Forelle : Stück": 800,584 g', ['800,584 g', 'Demo Superadmin', 'Der Basis-Preis beträgt 1,50 € / 100 g'], $expectedToEmails, $expectedCcEmails);
    }

    public function testEditOrderDetailQuantityAsSuperadminSameQuantity()
    {
        $this->loginAsSuperadmin();

        $cart = $this->preparePricePerUnitOrder();

        $newQuantity = 700;
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity, false);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);

        $this->assertEquals(10.50, $changedOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(9.54, $changedOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals($newQuantity, $changedOrderDetails[0]->order_detail_unit->product_quantity_in_units);
        $this->assertEquals(1, $changedOrderDetails[0]->order_detail_unit->mark_as_saved);

        $this->assertEquals(0.48, $changedOrderDetails[0]->order_detail_tax->unit_amount);
        $this->assertEquals(0.96, $changedOrderDetails[0]->order_detail_tax->total_amount);

        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs));
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


}