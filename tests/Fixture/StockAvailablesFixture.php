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

use App\Test\Fixture\ProductsFixture;

class StockAvailablesFixture extends AppFixture
{
    public string $table = 'fcs_stock_available';    

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $records = [
        [
            'id_stock_available' => 132,
            'id_product' => ProductsFixture::ID_MILK,
            'id_product_attribute' => 0,
            'quantity' => 1015,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 195,
            'id_product' => ProductsFixture::ID_FRANKFURTERS,
            'id_product_attribute' => 0,
            'quantity' => 2996,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 196,
            'id_product' => ProductsFixture::ID_BRATWURST,
            'id_product_attribute' => 0,
            'quantity' => 990,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 318,
            'id_product' => ProductsFixture::ID_CHARD,
            'id_product_attribute' => 0,
            'quantity' => 988,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 674,
            'id_product' => 339,
            'id_product_attribute' => 0,
            'quantity' => 2959,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 678,
            'id_product' => ProductsFixture::ID_LUNG_STEW,
            'id_product_attribute' => 0,
            'quantity' => 990,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 680,
            'id_product' => ProductsFixture::ID_GARLIC,
            'id_product_attribute' => 0,
            'quantity' => 78,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 0,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 686,
            'id_product' => ProductsFixture::ID_ARTICHOKE,
            'id_product_attribute' => 0,
            'quantity' => 97,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 0,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 692,
            'id_product' => ProductsFixture::ID_MILK,
            'id_product_attribute' => 9,
            'quantity' => 996,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 693,
            'id_product' => ProductsFixture::ID_MILK,
            'id_product_attribute' => 10,
            'quantity' => 19,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 0,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 704,
            'id_product' => ProductsFixture::ID_TROUT,
            'id_product_attribute' => 0,
            'quantity' => 999,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 705,
            'id_product' => ProductsFixture::ID_BEEF,
            'id_product_attribute' => 0,
            'quantity' => 1998,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 706,
            'id_product' => ProductsFixture::ID_BEEF,
            'id_product_attribute' => 11,
            'quantity' => 999,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 707,
            'id_product' => ProductsFixture::ID_BEEF,
            'id_product_attribute' => 12,
            'quantity' => 999,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 708,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_A,
            'id_product_attribute' => 0,
            'quantity' => 5,
            'quantity_limit' => -5,
            'sold_out_limit' => 0,
            'always_available' => 0,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 709,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
            'id_product_attribute' => 0,
            'quantity' => 1004,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 710,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
            'id_product_attribute' => 13,
            'quantity' => 5,
            'quantity_limit' => -5,
            'sold_out_limit' => 0,
            'always_available' => 0,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 711,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
            'id_product_attribute' => 14,
            'quantity' => 999,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 712,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
            'id_product_attribute' => 15,
            'quantity' => 999,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 713,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_B,
            'id_product_attribute' => 0,
            'quantity' => 999,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],
        [
            'id_stock_available' => 714,
            'id_product' => ProductsFixture::ID_STOCK_PRODUCT_WITH_WEIGHT_BARCODE,
            'id_product_attribute' => 0,
            'quantity' => 999,
            'quantity_limit' => 0,
            'sold_out_limit' => NULL,
            'always_available' => 1,
            'default_quantity_after_sending_order_lists' => NULL,
        ],

    ];
}
?>