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

class OrderDetailsControllerEditAmountTest extends OrderDetailsControllerTestCase
{

    public $newAmount = 1;
    public $editAmountReason = 'One product was not delivered.';


    public function testEditOrderDetailAmountNotValid()
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart(1, 2);
        $this->editOrderDetailAmount($this->mockCart->cart_products[1]->order_detail->id_order_detail, -1, $this->editAmountReason);
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Die Anzahl ist nicht gültig.');
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
        $this->assertEquals(0.17, $changedOrder->cart_products[1]->order_detail->order_detail_tax->unit_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[1]->order_detail->order_detail_tax->total_amount);

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
        $this->assertEquals(0.17, $changedOrder->cart_products[1]->order_detail->order_detail_tax->unit_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[1]->order_detail->order_detail_tax->total_amount);

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
        $this->assertEquals(0.17, $changedOrder->cart_products[1]->order_detail->order_detail_tax->unit_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[1]->order_detail->order_detail_tax->total_amount);

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
        $this->assertEquals(0.17, $changedOrder->cart_products[1]->order_detail->order_detail_tax->unit_amount);
        $this->assertEquals(0.17, $changedOrder->cart_products[1]->order_detail->order_detail_tax->total_amount);

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $expectedCcEmails = [];
        $this->assertOrderDetailProductAmountChangedEmails(1, $expectedToEmails, $expectedCcEmails);

        $this->assertChangedStockAvailable($this->productIdA, 96);
    }

    private function assertOrderDetailProductAmountChangedEmails($emailLogIndex, $expectedToEmails, $expectedCcEmails)
    {
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[$emailLogIndex], 'Bestellte Anzahl angepasst: Artischocke : Stück', ['Die Anzahl des Produktes <b>Artischocke : Stück</b> wurde angepasst', $this->editAmountReason, 'Neue Anzahl: <b>' . $this->newAmount . '</b>', 'Demo Gemüse-Hersteller'], $expectedToEmails, $expectedCcEmails);
    }

    private function editOrderDetailAmount($orderDetailId, $productAmount, $editAmountReason)
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