<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Test\Fixture;

class CategoryProductsFixture extends AppFixture
{
    public string $table = 'fcs_category_product';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $records = [
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_MILK,
        ],
        [
            'id_category' => 16,
            'id_product' => ProductsFixture::ID_FRANKFURTERS,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_FRANKFURTERS,
        ],
        [
            'id_category' => 16,
            'id_product' => ProductsFixture::ID_BRATWURST,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_BRATWURST,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_CHARD,
        ],
        [
            'id_category' => 20,
            'id_product' => 339,
        ],
        [
            'id_category' => 16,
            'id_product' => ProductsFixture::ID_LUNG_STEW,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_LUNG_STEW,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_GARLIC,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_ARTICHOKE,
        ],
        [
            'id_category' => 16,
            'id_product' => ProductsFixture::ID_TROUT,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_TROUT,
        ],
        [
            'id_category' => 16,
            'id_product' => ProductsFixture::ID_BEEF,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_BEEF,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_A,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_B,
        ],
        [
            'id_category' => 20,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_WITH_WEIGHT_BARCODE,
        ],
    ];

}
?>