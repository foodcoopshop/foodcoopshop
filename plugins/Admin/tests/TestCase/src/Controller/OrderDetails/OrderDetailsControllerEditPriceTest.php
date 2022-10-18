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
use Cake\TestSuite\TestEmailTransport;

class OrderDetailsControllerEditPriceTest extends OrderDetailsControllerTestCase
{

    public $newPrice = '3,53';
    public $editPriceReason = 'Product was smaller than expected.';

    public function testEditOrderDetailPriceNotValid()
    {
        $this->loginAsVegetableManufacturer();
        $this->editOrderDetailPrice($this->orderDetailIdA, 'not-valid-price', $this->editPriceReason, true);
        $this->assertEquals($this->getJsonDecodedContent()->msg, 'Der Preis ist nicht gültig.');
    }

    public function testEditOrderDetaiWithNegativePrice()
    {
        $this->loginAsVegetableManufacturer();
        $this->newPrice = '-10,50';
        $this->editOrderDetailPrice($this->orderDetailIdA, $this->newPrice, $this->editPriceReason, true);
        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$this->orderDetailIdA]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl));
    }

    public function testEditOrderDetailPriceAsManufacturer()
    {
        $this->loginAsVegetableManufacturer();
        $this->editOrderDetailPrice($this->orderDetailIdA, $this->newPrice, $this->editPriceReason, true);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$this->orderDetailIdA]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl));

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails);
    }

    public function testEditOrderDetailPriceAsSuperadminWithDisabledNotification()
    {
        $this->loginAsSuperadmin();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'send_ordered_product_price_changed_notification', 0);

        $this->editOrderDetailPrice($this->orderDetailIdA, $this->newPrice, $this->editPriceReason, true);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$this->orderDetailIdA]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl));

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails);
    }

    public function testEditOrderDetailPriceAsSuperadminWithEnabledNotification()
    {
        $this->loginAsSuperadmin();

        $this->editOrderDetailPrice($this->orderDetailIdA, $this->newPrice, $this->editPriceReason, true);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$this->orderDetailIdA]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl));

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailProductPriceChangedEmails(0, $expectedToEmails);

        $this->assertMailSentToAt(1, Configure::read('test.loginEmailVegetableManufacturer'));

    }

    public function testEditOrderDetailPriceIfPriceWasZero()
    {
        $this->loginAsSuperadmin();
        $this->changeProductPrice($this->productIdA, 0);
        $this->mockCart = $this->generateAndGetCart();

        $mockOrderDetailId = $this->mockCart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailPrice($mockOrderDetailId, $this->newPrice, $this->editPriceReason, true);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$mockOrderDetailId]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl));

        $expectedToEmails = [Configure::read('test.loginEmailSuperadmin')];
        $this->assertOrderDetailProductPriceChangedEmails(1, $expectedToEmails);
    }

    public function testEditOrderDetailPriceNoEditPriceReason()
    {
        $this->loginAsSuperadmin();
        $this->changeProductPrice($this->productIdA, 0);
        $this->mockCart = $this->generateAndGetCart();

        $mockOrderDetailId = $this->mockCart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailPrice($mockOrderDetailId, $this->newPrice, '', true);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$mockOrderDetailId]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl));

        $this->runAndAssertQueue();
        $email = TestEmailTransport::getMessages()[1];
        $this->assertDoesNotMatchRegularExpressionWithUnquotedString('Warum wurde der Preis angepasst?', $email->getBodyHtml());
    }

    public function testEditOrderDetailPriceNoEmailToCustomer()
    {
        $this->loginAsSuperadmin();
        $this->mockCart = $this->generateAndGetCart();

        $mockOrderDetailId = $this->mockCart->cart_products[0]->order_detail->id_order_detail;
        $this->editOrderDetailPrice($mockOrderDetailId, $this->newPrice, $this->editPriceReason, false);

        $changedOrderDetails = $this->getOrderDetailsFromDatabase([$mockOrderDetailId]);
        $this->assertEquals($this->newPrice, Configure::read('app.numberHelper')->formatAsDecimal($changedOrderDetails[0]->total_price_tax_incl));

        $this->assertOrderDetailProductPriceChangedEmails(1, []);
    }

    private function assertOrderDetailProductPriceChangedEmails($emailIndex, $expectedToEmails)
    {
        $this->runAndAssertQueue();
        $this->assertMailSubjectContainsAt($emailIndex, 'Preis angepasst: Artischocke : Stück');
        $this->assertMailContainsHtmlAt($emailIndex, 'Der Preis des Produktes <b>Artischocke : Stück</b> wurde erfolgreich angepasst.');
        $this->assertMailContainsHtmlAt($emailIndex, $this->editPriceReason);
        $this->assertMailContainsHtmlAt($emailIndex, $this->newPrice);
        $this->assertMailContainsHtmlAt($emailIndex, 'Demo Gemüse-Hersteller');

        foreach($expectedToEmails as $expectedToEmail) {
            $this->assertMailSentToAt($emailIndex, $expectedToEmail);
        }
    }

    private function editOrderDetailPrice($orderDetailId, $productPrice, $editPriceReason, $sendEmailToCustomer)
    {
        $this->post(
            '/admin/order-details/editProductPrice/',
            [
                'orderDetailId' => $orderDetailId,
                'productPrice' => $productPrice,
                'editPriceReason' => $editPriceReason,
                'sendEmailToCustomer' => $sendEmailToCustomer,
            ]
        );
    }

}