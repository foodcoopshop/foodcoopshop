<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) FoodCoopShop, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Test\Fixture;

class ShoppingListProductsFixture extends AppFixture
{

    public string $table = 'fcs_shopping_list_products';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $records = [
        [
            'id' => 1,
            'shopping_list_id' => ShoppingListsFixture::ID_CUSTOMER_WEEKLY,
            'id_product' => ProductsFixture::ID_ARTICHOKE,
            'id_product_attribute' => 0,
            'amount' => 2,
            'created' => '2026-11-01 10:00:00',
            'modified' => '2026-11-01 10:00:00',
        ],
        [
            'id' => 2,
            'shopping_list_id' => ShoppingListsFixture::ID_CUSTOMER_WEEKLY,
            'id_product' => 60,
            'id_product_attribute' => 10,
            'amount' => 1,
            'created' => '2026-11-01 10:00:01',
            'modified' => '2026-11-01 10:00:01',
        ],
        [
            'id' => 3,
            'shopping_list_id' => ShoppingListsFixture::ID_SUPERADMIN,
            'id_product' => ProductsFixture::ID_GARLIC,
            'id_product_attribute' => 0,
            'amount' => 1,
            'created' => '2026-11-01 10:00:00',
            'modified' => '2026-11-01 10:00:00',
        ],
    ];

}
