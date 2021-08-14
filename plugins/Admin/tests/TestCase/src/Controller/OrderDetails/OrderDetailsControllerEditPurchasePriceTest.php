<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\OrderDetailsControllerTestCase;
use Cake\Core\Configure;

class OrderDetailsControllerEditPurchasePriceTest extends OrderDetailsControllerTestCase
{

    public $newPriceExcl = 1;

    public function testEditOrderDetailPurchasePricePriceExclValidPurchasePrice()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $orderDetailId = 1;
        $newPriceExcl = 20;
        $newTaxRate = 13;
        $this->editOrderDetailPurchasePrice($orderDetailId, $newPriceExcl, $newTaxRate);
        $this->assertFlashMessage('Der Einkaufspreis wurde erfolgreich gespeichert.');

        $this->OrderDetailPurchasePrice = $this->getTableLocator()->get('OrderDetailPurchasePrices');
        $odpp = $this->OrderDetailPurchasePrice->find('all', [
            'conditions' => [
                'OrderDetailPurchasePrices.id_order_detail' => $orderDetailId,
            ],
        ])->first();
        $this->assertEquals($odpp->tax_rate, $newTaxRate);
        $this->assertEquals($odpp->total_price_tax_excl, $newPriceExcl);
        $this->assertEquals($odpp->total_price_tax_incl, 22.6);
        $this->assertEquals($odpp->tax_unit_amount, 2.6);
        $this->assertEquals($odpp->tax_total_amount, 2.6);
    }

    public function testEditOrderDetailPurchasePricePriceExclInvalidPurchasePrice()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $orderDetailId = 1;
        $newPriceExcl = -3;
        $newTaxRate = 13;
        $this->editOrderDetailPurchasePrice($orderDetailId, $newPriceExcl, $newTaxRate);
        $this->assertResponseContains('Der Betrag muss größer als 0 sein.');
    }

    protected function editOrderDetailPurchasePrice($orderDetailId, $purchasePriceExcl, $taxRate)
    {
        $this->post(
            Configure::read('app.slugHelper')->getOrderDetailPurchasePriceEdit($orderDetailId),
            [
                'referer' => '/',
                'OrderDetails' => [
                    'order_detail_purchase_price' => [
                        'tax_rate' => $taxRate,
                        'total_price_tax_excl' => $purchasePriceExcl,
                    ],
                ]
            ]
        );
    }
}