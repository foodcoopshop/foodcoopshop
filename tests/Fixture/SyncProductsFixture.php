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

class SyncProductsFixture extends AppFixture
{

    public string $table = 'fcs_sync_products';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $records = [
        [
            'id' => 1,
            'sync_domain_id' => 1,
            'local_product_id' => ProductsFixture::ID_ARTICHOKE,
            'remote_product_id' => ProductsFixture::ID_ARTICHOKE,
            'local_product_attribute_id' => 0,
            'remote_product_attribute_id' => 0,
        ],
        [
            'id' => 2,
            'sync_domain_id' => 1,
            'local_product_id' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
            'remote_product_id' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
            'local_product_attribute_id' => 0,
            'remote_product_attribute_id' => 0,
        ],
        [
            'id' => 3,
            'sync_domain_id' => 1,
            'local_product_id' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
            'remote_product_id' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
            'local_product_attribute_id' => 14,
            'remote_product_attribute_id' => 14,
        ],
        [
            'id' => 4,
            'sync_domain_id' => 1,
            'local_product_id' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
            'remote_product_id' => ProductsFixture::ID_STOCK_PRODUCT_WITH_ATTRIBUTES,
            'local_product_attribute_id' => 13,
            'remote_product_attribute_id' => 13,
        ],
   ];
}
?>