<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use App\Test\Fixture\ProductsFixture;

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
                'product_id' => ProductsFixture::ID_LUNG_STEW,
                'tax_id' => 0,
                'price' => 10,
            ],
        );
        $purchasePriceProductsTable->save($entity);

        $productIds = [
            ProductsFixture::ID_BRATWURST, // Bratwürstel with 0 % purchase price
            ProductsFixture::ID_CHARD, // Mangold: no purchase price defined
            ProductsFixture::ID_ARTICHOKE, // Artischocke: main product with normal price
            ProductsFixture::ID_TROUT, // Forelle: main product with price per unit
            ProductsFixture::ID_BEEF, // Rindfleisch: attributes with price per unit
            ProductsFixture::ID_MILK,  // Milch: attribute with normal price
        ];

        $surcharge = 40;
        $result = $purchasePriceProductsTable->getSellingPricesWithSurcharge($productIds, $surcharge);

        $this->assertEquals(5, count($result['preparedProductsForActionLog']));
        $this->assertEquals(5, count($result['pricesToChange']));

        foreach($result['pricesToChange'] as $pricesToChange) {

            $productId = key($pricesToChange);
            $values = $pricesToChange[$productId];

            if ($productId == ProductsFixture::ID_ARTICHOKE) {
                $this->assertEquals(1.85, $values['gross_price']);
                $this->assertNull($values['unit_product_price_incl_per_unit']);
            }
            if ($productId == ProductsFixture::ID_TROUT) {
                $this->assertEquals(0, $values['gross_price']);
                $this->assertEquals(1.34, $values['unit_product_price_incl_per_unit']);
            }
            if ($productId == ProductsFixture::ID_MILK_0_5L) {
                $this->assertEquals(0.4, $values['gross_price']);
                $this->assertNull($values['unit_product_price_incl_per_unit']);
            }
            if ($productId == ProductsFixture::ID_BEEF_1KG) {
                $this->assertEquals(0, $values['gross_price']);
                $this->assertEquals(19.08, $values['unit_product_price_incl_per_unit']);
            }
            if ($productId == ProductsFixture::ID_LUNG_STEW) {
                $this->assertEquals(14, $values['gross_price']);
                $this->assertNull($values['unit_product_price_incl_per_unit']);
            }

        }

    }

}
