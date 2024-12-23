<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\OrderDetailsControllerTestCase;
use Cake\Core\Configure;

class OrderDetailsControllerEditPurchasePriceTest extends OrderDetailsControllerTestCase
{

    public function testEditOrderDetailPurchasePricePriceExclValidPurchasePrice()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $orderDetailId = 1;
        $newPriceExcl = 20;
        $newTaxRate = 13;
        $this->editOrderDetailPurchasePrice($orderDetailId, $newPriceExcl, $newTaxRate);
        $this->assertFlashMessage('Der Einkaufspreis wurde erfolgreich gespeichert.');

        $orderDetailPurchasePricesTable = $this->getTableLocator()->get('OrderDetailPurchasePrices');
        $odpp = $orderDetailPurchasePricesTable->find('all', conditions: [
            'OrderDetailPurchasePrices.id_order_detail' => $orderDetailId,
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
        $newPriceExcl = 'invalid-price';
        $newTaxRate = 13;
        $this->editOrderDetailPurchasePrice($orderDetailId, $newPriceExcl, $newTaxRate);
        $this->assertResponseContains('Bitte gib eine korrekte Zahl ein.');
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