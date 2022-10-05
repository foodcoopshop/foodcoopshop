<?php
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

        $this->assertEquals(0.55, $changedOrderDetails[0]->tax_unit_amount);
        $this->assertEquals(1.10, $changedOrderDetails[0]->tax_total_amount);

        $this->assertMailSubjectContainsAt(1, 'Gewicht angepasst für "Forelle : Stück": 800,584 g');
        $this->assertMailContainsHtmlAt(1, '800,584 g');
        $this->assertMailContainsHtmlAt(1, 'Demo Superadmin');
        $this->assertMailContainsHtmlAt(1, 'Der Grundpreis beträgt 1,50 € / 100 g');

        $this->assertMailSentToAt(1, Configure::read('test.loginEmailSuperadmin'));
        $this->assertMailSentWithAt(1, Configure::read('test.loginEmailMeatManufacturer'), 'cc');
    }

    public function testEditOrderDetailQuantityAsSuperadminDifferentQuantityPurchasePriceAvailable()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();

        $cart = $this->preparePricePerUnitOrder();

        $newQuantity = 800.584;
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity, false);

        $changedOrderDetails = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail IN' => [$orderDetailId],
            ],
            'contain' => [
                'OrderDetailUnits',
                'OrderDetailPurchasePrices',
            ]
        ])->toArray();

        $this->assertEquals(7.85, $changedOrderDetails[0]->order_detail_purchase_price->total_price_tax_incl);
        $this->assertEquals(6.95, $changedOrderDetails[0]->order_detail_purchase_price->total_price_tax_excl);
        $this->assertEquals(0.45, $changedOrderDetails[0]->order_detail_purchase_price->tax_unit_amount);
        $this->assertEquals(0.90, $changedOrderDetails[0]->order_detail_purchase_price->tax_total_amount);
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

        $this->assertEquals(0.48, $changedOrderDetails[0]->tax_unit_amount);
        $this->assertEquals(0.96, $changedOrderDetails[0]->tax_total_amount);

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

        $this->assertEquals($changedOrderDetails[0]->tax_unit_amount, $changedOrderDetails[0]->tax_unit_amount);
        $this->assertEquals($changedOrderDetails[0]->tax_total_amount, $changedOrderDetails[0]->tax_total_amount);

        $this->assertMailCount(1);
    }

    public function testEditOrderDetailQuantityAsSuperadminEmailDisabledWithConfig()
    {
        Configure::write('app.sendEmailWhenOrderDetailQuantityChanged', false);
        $this->loginAsSuperadmin();
        $cart = $this->preparePricePerUnitOrder();
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, 800.854, false);
        $this->assertMailCount(1);
    }

    public function testEditOrderDetailQuantityAsSuperadminUserUsedWrongUnit()
    {
        $this->loginAsSuperadmin();
        $cart = $this->preparePricePerUnitOrder();
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, 0.7, false);
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Der neue Preis wäre <b>0,01 €</b> für <b>0,7 g</b>. Bitte überprüfe die Einheit.');
        $this->editOrderDetailQuantity($orderDetailId, 800000, false);
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Der neue Preis wäre <b>12.000,00 €</b> für <b>800.000 g</b>. Bitte überprüfe die Einheit.');
    }

    /*'
     * https://github.com/foodcoopshop/foodcoopshop/issues/836
     * fix is not yet implemented
     */
    /*
    public function testEditOrderDetailQuantityAsSuperadminWithHugeQuantity()
    {
        $this->loginAsSuperadmin();
        $this->OrderDetail->deleteAll([]);
        $this->changeConfiguration('FCS_MINIMAL_CREDIT_BALANCE', -1000);
        $productId = '348-11';
        $this->changeProductPrice($productId, 0, true, '25,2', 'g', 1000, 80);
        $this->addProductToCart($productId, 99);
        $this->finishCart(1, 1, '', null);
        $orderDetailId = 4;
        $newQuantity = 8000;
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity, false);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);

        $this->assertEquals(201.56, $changedOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(183.24, $changedOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals($newQuantity, $changedOrderDetails[0]->order_detail_unit->product_quantity_in_units);
        $this->assertEquals(1, $changedOrderDetails[0]->order_detail_unit->mark_as_saved);

        $this->assertEquals(18.32, $changedOrderDetails[0]->tax_unit_amount);
        $this->assertEquals(18.32, $changedOrderDetails[0]->tax_total_amount);

    }
    */

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
        $this->runAndAssertQueue();
    }


}