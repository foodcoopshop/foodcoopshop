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

class OrderDetailsControllerEditPriceTest extends OrderDetailsControllerTestCase
{

    public $newPrice = '3,53';
    public $editPriceReason = 'Product was smaller than expected.';

    public function testEditOrderDetailPriceNotValid()
    {
        $this->loginAsVegetableManufacturer();
        $this->editOrderDetailPrice($this->orderDetailIdA, -1, $this->editPriceReason);
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Der Preis ist nicht gültig.');
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

    public function testEditOrderDetailPriceAsSuperadminEmailDisabledWithConfig()
    {
        Configure::write('app.sendEmailWhenOrderDetailQuantityOrPriceChanged', false);
        $this->loginAsSuperadmin();
        $this->editOrderDetailPrice($this->orderDetailIdA, $this->newPrice, $this->editPriceReason);
        $this->assertMailCount(0);
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

    private function assertOrderDetailProductPriceChangedEmails($emailIndex, $expectedToEmails, $expectedCcEmails)
    {
        $this->assertMailSubjectContainsAt($emailIndex, 'Preis angepasst: Artischocke : Stück');
        $this->assertMailContainsHtmlAt($emailIndex, 'Der Preis des Produktes <b>Artischocke : Stück</b> wurde erfolgreich angepasst.');
        $this->assertMailContainsHtmlAt($emailIndex, $this->editPriceReason);
        $this->assertMailContainsHtmlAt($emailIndex, $this->newPrice);
        $this->assertMailContainsHtmlAt($emailIndex, 'Demo Gemüse-Hersteller');

        foreach($expectedToEmails as $expectedToEmail) {
            $this->assertMailSentToAt($emailIndex, $expectedToEmail);
        }
        foreach($expectedCcEmails as $expectedCcEmail) {
            $this->assertMailSentWithAt($emailIndex, $expectedCcEmail, 'cc');
        }
    }

    private function editOrderDetailPrice($orderDetailId, $productPrice, $editPriceReason)
    {
        $this->post(
            '/admin/order-details/editProductPrice/',
            [
                'orderDetailId' => $orderDetailId,
                'productPrice' => $productPrice,
                'editPriceReason' => $editPriceReason
            ]
        );
    }

}