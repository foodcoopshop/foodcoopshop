<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PurchasePriceProductsTableTest extends AppCakeTestCase
{

    public function testGetSellingPricesWithSurcharge(): void
    {

        $purchasePriceProductsTable = $this->getTableLocator()->get('PurchasePriceProducts');
        $entity = $purchasePriceProductsTable->newEntity(
            [
                'product_id' => 340,
                'tax_id' => 0,
                'price' => 10,
            ],
        );
        $purchasePriceProductsTable->save($entity);

        $productIds = [
            103, // Bratwürstel with 0 % purchase price
            163, // Mangold: no purchase price defined
            346, // Artischocke: main product with normal price
            347, // Forelle: main product with price per unit
            348, // Rindfleisch: attributes with price per unit
            60,  // Milch: attribute with normal price
        ];

        $surcharge = 40;
        $result = $purchasePriceProductsTable->getSellingPricesWithSurcharge($productIds, $surcharge);

        $this->assertEquals(5, count($result['preparedProductsForActionLog']));
        $this->assertEquals(5, count($result['pricesToChange']));

        foreach($result['pricesToChange'] as $pricesToChange) {

            $productId = key($pricesToChange);
            $values = $pricesToChange[$productId];

            if ($productId == '346') {
                $this->assertEquals(1.85, $values['gross_price']);
                $this->assertNull($values['unit_product_price_incl_per_unit']);
            }
            if ($productId == '347') {
                $this->assertEquals(0, $values['gross_price']);
                $this->assertEquals(1.34, $values['unit_product_price_incl_per_unit']);
            }
            if ($productId == '60-10') {
                $this->assertEquals(0.4, $values['gross_price']);
                $this->assertNull($values['unit_product_price_incl_per_unit']);
            }
            if ($productId == '348-12') {
                $this->assertEquals(0, $values['gross_price']);
                $this->assertEquals(19.08, $values['unit_product_price_incl_per_unit']);
            }
            if ($productId == '340') {
                $this->assertEquals(14, $values['gross_price']);
                $this->assertNull($values['unit_product_price_incl_per_unit']);
            }

        }

    }

}
