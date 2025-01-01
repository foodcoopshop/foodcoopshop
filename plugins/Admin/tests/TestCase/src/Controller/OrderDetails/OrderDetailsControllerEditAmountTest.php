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

class OrderDetailsControllerEditAmountTest extends OrderDetailsControllerTestCase
{

    public int $newAmount = 1;
    public string $editAmountReason = 'One product was not delivered.';

    public function testEditOrderDetailAmountNotValid(): void
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(1, 2);
        $this->editOrderDetailAmount($this->mockCart->cart_products[1]->order_detail->id_order_detail, -1, $this->editAmountReason);
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Die Menge ist nicht gültig.');
    }

    public function testEditOrderDetailAmountAsManufacturer(): void
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(5, 2);
        $this->logout();
        $this->loginAsVegetableManufacturer();
        $this->editOrderDetailAmount($this->mockCart->cart_products[0]->order_detail->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockCartFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->cart_products[0]->order_detail->product_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[0]->order_detail->tax_unit_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[0]->order_detail->tax_total_amount);
        $this->assertEquals(10, $changedOrder->cart_products[0]->order_detail->tax_rate);

        $expectedToEmail = Configure::read('test.loginEmailSuperadmin');
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmail);

        $this->assertChangedStockAvailable($this->productIdA, 96);
    }

    public function testEditOrderDetailAmountAsSuperadminWithEnabledNotificationPurchasePrice(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();

        $this->addProductToCart(346, 3);
        $this->addProductToCart('348-12', 5);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);

        $this->editOrderDetailAmount($cart->cart_products[1]->order_detail->id_order_detail, 1, $this->editAmountReason);
        $this->editOrderDetailAmount($cart->cart_products[0]->order_detail->id_order_detail, 2, $this->editAmountReason);

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $changedOrderDetails = $orderDetailsTable->find('all',
            conditions: [
                'OrderDetails.id_order_detail IN' => [
                    $cart->cart_products[0]->order_detail->id_order_detail,
                    $cart->cart_products[1]->order_detail->id_order_detail,
                ],
            ],
            contain: [
                'OrderDetailUnits',
                'OrderDetailPurchasePrices',
            ]
        )->toArray();

        $this->assertEquals(8.4, $changedOrderDetails[0]->order_detail_purchase_price->total_price_tax_incl);
        $this->assertEquals(7.43, $changedOrderDetails[0]->order_detail_purchase_price->total_price_tax_excl);
        $this->assertEquals(0.97, $changedOrderDetails[0]->order_detail_purchase_price->tax_unit_amount);
        $this->assertEquals(0.97, $changedOrderDetails[0]->order_detail_purchase_price->tax_total_amount);

        $this->assertEquals(2.88, $changedOrderDetails[1]->order_detail_purchase_price->total_price_tax_incl);
        $this->assertEquals(2.4, $changedOrderDetails[1]->order_detail_purchase_price->total_price_tax_excl);
        $this->assertEquals(0.24, $changedOrderDetails[1]->order_detail_purchase_price->tax_unit_amount);
        $this->assertEquals(0.48, $changedOrderDetails[1]->order_detail_purchase_price->tax_total_amount);

    }

    public function testEditOrderDetailAmountAsSuperadminWithEnabledNotification(): void
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(1, 2);

        $this->editOrderDetailAmount($this->mockCart->cart_products[0]->order_detail->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockCartFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->cart_products[0]->order_detail->product_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[0]->order_detail->tax_unit_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[0]->order_detail->tax_total_amount);
        $this->assertEquals(10, $changedOrder->cart_products[0]->order_detail->tax_rate);

        $expectedToEmail = Configure::read('test.loginEmailSuperadmin');
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmail);

        $this->assertChangedStockAvailable($this->productIdA, 96);
    }

    public function testEditOrderDetailAmountAsSuperadminWithEnabledNotificationAfterOrderListsWereSent(): void
    {
        $this->changeManufacturer(5, 'anonymize_customers', 1);
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(1, 2);
        $orderDetailId = $this->mockCart->cart_products[0]->order_detail->id_order_detail;
        $this->simulateSendOrderListsCronjob($orderDetailId);

        $this->editOrderDetailAmount($orderDetailId, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockCartFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->cart_products[0]->order_detail->product_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[0]->order_detail->tax_unit_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[0]->order_detail->tax_total_amount);
        $this->assertEquals(10, $changedOrder->cart_products[0]->order_detail->tax_rate);

        $expectedToEmail = Configure::read('test.loginEmailSuperadmin');
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmail);

        $expectedToEmail = Configure::read('test.loginEmailVegetableManufacturer');
        $this->assertOrderDetailProductAmountChangedEmails(2, $expectedToEmail);
        $this->assertMailContainsHtmlAt(2, 'Hallo Demo Gemüse-Hersteller');
        $this->assertMailContainsHtmlAt(2, 'D.S. - ID 92');

        $this->assertChangedStockAvailable($this->productIdA, 96);
    }

    public function testEditOrderDetailAmountAsSuperadminWithDisabledNotification(): void
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(1, 2);
        $customersTable = $this->getTableLocator()->get('Customers');
        $manufacturerId = $customersTable->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_amount_changed_notification', 0);

        $this->editOrderDetailAmount($this->mockCart->cart_products[0]->order_detail->id_order_detail, $this->newAmount, $this->editAmountReason);

        $changedOrder = $this->getChangedMockCartFromDatabase();
        $this->assertEquals($this->newAmount, $changedOrder->cart_products[0]->order_detail->product_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[0]->order_detail->tax_unit_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[0]->order_detail->tax_total_amount);
        $this->assertEquals(10, $changedOrder->cart_products[0]->order_detail->tax_rate);

        $expectedToEmail = Configure::read('test.loginEmailSuperadmin');
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmail);

        $this->assertChangedStockAvailable($this->productIdA, 96);
    }

    private function assertOrderDetailProductAmountChangedEmails($emailIndex, $expectedToEmail): void
    {
        $this->runAndAssertQueue();
        $this->assertMailSubjectContainsAt($emailIndex, 'Bestellte Menge angepasst: Artischocke : Stück');
        $this->assertMailContainsHtmlAt($emailIndex, 'Die Menge des Produktes <b>Artischocke : Stück</b> wurde angepasst');
        $this->assertMailContainsHtmlAt($emailIndex, $this->editAmountReason);
        $this->assertMailContainsHtmlAt($emailIndex, 'Neue Menge: <b>' . $this->newAmount . '</b>');
        $this->assertMailContainsHtmlAt($emailIndex, 'Demo Gemüse-Hersteller');
        $this->assertMailSentToAt($emailIndex, $expectedToEmail);
    }

    private function editOrderDetailAmount($orderDetailId, $productAmount, $editAmountReason): void
    {
        $this->ajaxPost(
            '/admin/order-details/editProductAmount/',
            [
                'orderDetailId' => $orderDetailId,
                'productAmount' => $productAmount,
                'editAmountReason' => $editAmountReason
            ]
        );
    }
}