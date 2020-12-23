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

class OrderDetailsControllerEditCustomerTest extends OrderDetailsControllerTestCase
{

    public $newCustomerId = 88;
    public $editCustomerReason = 'The member forgot his product and I took it.';
    public $editCustomerAmount = 1;

    public function testEditOrderDetailCustomerAsManufacturer() {
        $this->loginAsVegetableManufacturer();
        $this->editOrderDetailCustomer($this->orderDetailIdA, $this->newCustomerId, $this->editCustomerReason, $this->editCustomerAmount);
        $this->assertNotPerfectlyImplementedAccessRestricted();
    }

    public function testEditOrderDetailCustomerAsSuperadminNotParted() {
        $this->loginAsSuperadmin();
        $this->editOrderDetailCustomer($this->orderDetailIdA, $this->newCustomerId, $this->editCustomerReason, $this->editCustomerAmount);
        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$this->orderDetailIdA]);
        $this->assertEquals($this->newCustomerId, $changedOrderDetails[0]->id_customer);
        $this->assertEquals($this->editCustomerAmount, $changedOrderDetails[0]->product_amount);
        $recipients = [
            Configure::read('test.loginEmailAdmin'),
            Configure::read('test.loginEmailSuperadmin')
        ];
        $i = 0;
        foreach($recipients as $recipient) {
            $this->assertMailContainsHtmlAt($i, 'Das bestellte Produkt <b>Artischocke : Stück</b> wurde erfolgreich von Demo Superadmin auf das Mitglied <b>Demo Admin</b> umgebucht.');
            $this->assertMailContainsHtmlAt($i, $this->editCustomerReason);
            $this->assertMailSentToAt($i, $recipient);
            $this->assertMailSubjectContainsAt($i, 'Auf ein anderes Mitglied umgebucht: Artischocke : Stück');
            $i++;
        }
    }

    public function testEditOrderDetailCustomerAsSuperadminPartedIn2And5WithUnits()
    {
        $this->changeConfiguration('FCS_MINIMAL_CREDIT_BALANCE', -200);
        $this->loginAsSuperadmin();
        $productId = '347'; // forelle
        $amount = 7;
        $this->editCustomerAmount = 2;
        $this->addProductToCart($productId, $amount);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;

        $this->editOrderDetailCustomer($orderDetailId, $this->newCustomerId, $this->editCustomerReason, $this->editCustomerAmount);
        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId, 5]);

        $this->assertEquals(Configure::read('test.superadminId'), $changedOrderDetails[0]->id_customer);
        $this->assertEquals($this->newCustomerId, $changedOrderDetails[1]->id_customer);

        $this->assertEquals($changedOrderDetails[0]->id_tax, $changedOrderDetails[1]->id_tax);

        $this->assertEquals(5, $changedOrderDetails[0]->product_amount);
        $this->assertEquals(2, $changedOrderDetails[1]->product_amount);

        $this->assertEquals(26.25, $changedOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(10.5, $changedOrderDetails[1]->total_price_tax_incl);

        $this->assertEquals(23.85, $changedOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals(9.54, $changedOrderDetails[1]->total_price_tax_excl);

        $this->assertEquals(1750, $changedOrderDetails[0]->order_detail_unit->product_quantity_in_units);
        $this->assertEquals(500, $changedOrderDetails[1]->order_detail_unit->product_quantity_in_units);

    }

    public function testEditOrderDetailCustomerAsSuperadminPartedIn2And5()
    {
        $this->loginAsSuperadmin();
        $productId = '346'; // artischocke
        $amount = 7;
        $this->editCustomerAmount = 2;
        $this->addProductToCart($productId, $amount);
        $this->finishCart();
        $cartId = Configure::read('app.htmlHelper')->getCartIdFromCartFinishedUrl($this->_response->getHeaderLine('Location'));
        $cart = $this->getCartById($cartId);
        $orderDetailId = $cart->cart_products[0]->order_detail->id_order_detail;

        $this->editOrderDetailCustomer($orderDetailId, $this->newCustomerId, $this->editCustomerReason, $this->editCustomerAmount);
        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$orderDetailId, 5]);

        $this->assertEquals(Configure::read('test.superadminId'), $changedOrderDetails[0]->id_customer);
        $this->assertEquals($this->newCustomerId, $changedOrderDetails[1]->id_customer);

        $this->assertEquals($changedOrderDetails[0]->id_tax, $changedOrderDetails[1]->id_tax);

        $this->assertEquals(5, $changedOrderDetails[0]->product_amount);
        $this->assertEquals(2, $changedOrderDetails[1]->product_amount);

        $this->assertEquals(9.1, $changedOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(3.64, $changedOrderDetails[1]->total_price_tax_incl);

        $this->assertEquals(8.25, $changedOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals(3.30, $changedOrderDetails[1]->total_price_tax_excl);

        $this->assertEquals(0.85, $changedOrderDetails[0]->order_detail_tax->total_amount);
        $this->assertEquals(0.34, $changedOrderDetails[1]->order_detail_tax->total_amount);

    }

    private function editOrderDetailCustomer($orderDetailId, $customerId, $editCustomerReason, $amount)
    {
        $this->post(
            '/admin/order-details/editCustomer/',
            [
                'orderDetailId' => $orderDetailId,
                'customerId' => $customerId,
                'editCustomerReason' => $editCustomerReason,
                'amount' => $amount
            ]
        );
    }

}