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

use App\Test\TestCase\OrderDetailsControllerTestCase;
use Cake\Core\Configure;
use App\Test\TestCase\Traits\SelfServiceCartTrait;
use Cake\ORM\TableRegistry;
use App\Model\Entity\Cart;

class OrderDetailsControllerEditQuantityTest extends OrderDetailsControllerTestCase
{

    use SelfServiceCartTrait;

    public function testEditOrderDetailQuantityNotValid(): void
    {
        $this->loginAsSuperadmin();
        $cart = $this->preparePricePerUnitOrder();
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, -1);
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Das gelieferte Gewicht ist nicht gültig.');
    }

    public function testEditOrderDetailQuantityAsSuperadminStockProduct(): void
    {

        $this->changeManufacturer(4, 'anonymize_customers', 1);
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $stockAvailableTable = TableRegistry::getTableLocator()->get('StockAvailables');

        $unitsTable = $this->getTableLocator()->get('Units');
        $unitEntityA = $unitsTable->get(8);
        $unitEntityA->use_weight_as_amount = 1;
        $unitsTable->save($unitEntityA);

        $productId = 351;
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart($productId, 1, '0,51');
        $this->finishSelfServiceCart(1, 1);

        $cartsTable = $this->getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all', order: [
            'Carts.id_cart' => 'DESC'
        ])->first();

        $cart = $this->getCartById($cart->id_cart);

        $stockAvailable = $stockAvailableTable->find('all')->where([
            'id_product' => $productId,
            'id_product_attribute' => 0,
        ])->first();
        $this->assertEquals(998.49, $stockAvailable->quantity);

        $newQuantity = 0.55;
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity);

        $stockAvailable = $stockAvailableTable->find('all')->where([
            'id_product' => $productId,
            'id_product_attribute' => 0,
        ])->first();
        $this->assertEquals(998.45, $stockAvailable->quantity);

    }

    public function testEditOrderDetailQuantityAsSuperadminDifferentQuantity(): void
    {

        $this->changeManufacturer(4, 'anonymize_customers', 1);
        $this->loginAsSuperadmin();

        $cart = $this->preparePricePerUnitOrder();

        $newQuantity = 800.584;
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);

        $this->assertEquals(12.01, $changedOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(10.91, $changedOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals($newQuantity, $changedOrderDetails[0]->order_detail_unit->product_quantity_in_units);
        $this->assertEquals(1, $changedOrderDetails[0]->order_detail_unit->mark_as_saved);

        $this->assertEquals(0.55, $changedOrderDetails[0]->tax_unit_amount);
        $this->assertEquals(1.10, $changedOrderDetails[0]->tax_total_amount);

        $this->assertMailSubjectContainsAt(1, 'Gewicht angepasst für "Forelle : Stück": 800,584 g');
        $this->assertMailContainsHtmlAt(1, '800,584 g');
        $this->assertMailContainsHtmlAt(1, 'Hallo Demo Superadmin');
        $this->assertMailContainsHtmlAt(1, 'Der Grundpreis beträgt 1,50 € / 100 g');

        $this->assertMailSentToAt(1, Configure::read('test.loginEmailSuperadmin'));
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailMeatManufacturer'));
        $this->assertMailContainsHtmlAt(2, 'Hallo Demo Fleisch-Hersteller');
        $this->assertMailContainsHtmlAt(2, 'D.S. - ID 92');
    }

    public function testEditOrderDetailQuantityAsSuperadminDifferentQuantityPurchasePriceAvailable(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();

        $cart = $this->preparePricePerUnitOrder();

        $newQuantity = 800.584;
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity);

        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $changedOrderDetails = $orderDetailsTable->find('all',
            conditions: [
                'OrderDetails.id_order_detail IN' => [$orderDetailId],
            ],
            contain: [
                'OrderDetailUnits',
                'OrderDetailPurchasePrices',
            ]
        )->toArray();

        $this->assertEquals(7.85, $changedOrderDetails[0]->order_detail_purchase_price->total_price_tax_incl);
        $this->assertEquals(6.95, $changedOrderDetails[0]->order_detail_purchase_price->total_price_tax_excl);
        $this->assertEquals(0.45, $changedOrderDetails[0]->order_detail_purchase_price->tax_unit_amount);
        $this->assertEquals(0.90, $changedOrderDetails[0]->order_detail_purchase_price->tax_total_amount);
    }

    public function testEditOrderDetailQuantityAsSuperadminSameQuantity(): void
    {
        $this->loginAsSuperadmin();

        $cart = $this->preparePricePerUnitOrder();

        $newQuantity = 700;
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);

        $this->assertEquals(10.50, $changedOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(9.54, $changedOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals($newQuantity, $changedOrderDetails[0]->order_detail_unit->product_quantity_in_units);
        $this->assertEquals(1, $changedOrderDetails[0]->order_detail_unit->mark_as_saved);

        $this->assertEquals(0.48, $changedOrderDetails[0]->tax_unit_amount);
        $this->assertEquals(0.96, $changedOrderDetails[0]->tax_total_amount);

        $this->assertMailCount(1);
    }

    public function testEditOrderDetailQuantityAsSuperadminEmailDisabledWithConfig(): void
    {
        Configure::write('app.sendEmailWhenOrderDetailQuantityChanged', false);
        $this->loginAsSuperadmin();
        $cart = $this->preparePricePerUnitOrder();
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, 800.854);
        $this->assertMailCount(1);
    }

    public function testEditOrderDetailQuantityAsSuperadminUserUsedWrongUnit(): void
    {
        $this->loginAsSuperadmin();
        $cart = $this->preparePricePerUnitOrder();
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailQuantity($orderDetailId, 0.7);
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Der neue Preis wäre <b>0,01 €</b> für <b>0,7 g</b>. Bitte überprüfe die Einheit.');
        $this->editOrderDetailQuantity($orderDetailId, 800000);
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Der neue Preis wäre <b>12.000,00 €</b> für <b>800.000 g</b>. Bitte überprüfe die Einheit.');
    }

    public function testEditOrderDetailQuantityAsSuperadminQuantityPriceUnchangedWhenQuantityNotChanged(): void
    {
        $this->loginAsSuperadmin();
        $cart = $this->preparePricePerUnitOrder();
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;

        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $newPrice = 11.5;
        $cart->cart_products[0]->order_detail->total_price_tax_incl = $newPrice;
        $orderDetailsTable->save($cart->cart_products[0]->order_detail);
        $this->editOrderDetailQuantity($orderDetailId, 700);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);
        $this->assertEquals($newPrice, $changedOrderDetails[0]->total_price_tax_incl);
    }


    /*'
     * https://github.com/foodcoopshop/foodcoopshop/issues/836
     * fix is not yet implemented
     */
    /*
    public function testEditOrderDetailQuantityAsSuperadminWithHugeQuantity(): void
    {
        $this->loginAsSuperadmin();
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetailsTable->deleteAll([]);
        $this->changeConfiguration('FCS_MINIMAL_CREDIT_BALANCE', -1000);
        $productId = '348-11';
        $this->changeProductPrice($productId, 0, true, '25,2', 'g', 1000, 80);
        $this->addProductToCart($productId, 99);
        $this->finishCart(1, 1, '', null);
        $orderDetailId = 4;
        $newQuantity = 8000;
        $this->editOrderDetailQuantity($orderDetailId, $newQuantity);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId]);

        $this->assertEquals(201.56, $changedOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(183.24, $changedOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals($newQuantity, $changedOrderDetails[0]->order_detail_unit->product_quantity_in_units);
        $this->assertEquals(1, $changedOrderDetails[0]->order_detail_unit->mark_as_saved);

        $this->assertEquals(18.32, $changedOrderDetails[0]->tax_unit_amount);
        $this->assertEquals(18.32, $changedOrderDetails[0]->tax_total_amount);

    }
    */

    private function preparePricePerUnitOrder(): Cart
    {
        $productIdA = 347; // forelle
        $this->addProductToCart($productIdA, 2);
        $this->finishCart(1, 1);
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);
        return $cart;
    }

    private function editOrderDetailQuantity($orderDetailId, $productQuantity): void
    {
        $this->ajaxPost(
            '/admin/order-details/editProductQuantity/',
            [
                'orderDetailId' => $orderDetailId,
                'productQuantity' => $productQuantity,
            ]
        );
        $this->runAndAssertQueue();
    }

}