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
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Das gelieferte Gewicht ist nicht gültig.');
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

        $this->assertMailSentWithAt(1, 'Gewicht angepasst für "Forelle : Stück": 800,584 g', 'originalSubject');
        $this->assertMailContainsHtmlAt(1, '800,584 g');
        $this->assertMailContainsHtmlAt(1, 'Demo Superadmin');
        $this->assertMailContainsHtmlAt(1, 'Der Basis-Preis beträgt 1,50 € / 100 g');

        $this->assertMailSentToAt(1, Configure::read('test.loginEmailSuperadmin'));
        $this->assertMailSentWithAt(1, Configure::read('test.loginEmailMeatManufacturer'), 'cc');
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

        $this->assertMailCount(1);
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

        $this->assertMailCount(1);
    }

    public function testEditOrderDetailQuantityAsSuperadminEmailDisabledWithConfig()
    {
        Configure::write('app.sendEmailWhenOrderDetailQuantityOrPriceChanged', false);
        $this->loginAsSuperadmin();
        $cart = $this->preparePricePerUnitOrder();
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, 800.854, false);
        $this->assertMailCount(1);
    }

    private function preparePricePerUnitOrder()
    {
        $productIdA = 347; // forelle
        $this->addProductToCart($productIdA, 2);
        $this->finishCart(1, 1);
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);
        return $cart;
    }

    private function editOrderDetailQuantity($orderDetailId, $productQuantity, $doNotChangePrice)
    {
        $this->ajaxPost(
            '/admin/order-details/editProductQuantity/',
            [
                'orderDetailId' => $orderDetailId,
                'productQuantity' => $productQuantity,
                'doNotChangePrice' => $doNotChangePrice
            ]
        );
    }


}