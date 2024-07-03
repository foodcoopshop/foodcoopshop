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


    public array $records = [
        [
            'id_category' => 20,
            'id_product' => 60,
        ],
        [
            'id_category' => 16,
            'id_product' => 102,
        ],
        [
            'id_category' => 20,
            'id_product' => 102,
        ],
        [
            'id_category' => 16,
            'id_product' => 103,
        ],
        [
            'id_category' => 20,
            'id_product' => 103,
        ],
        [
            'id_category' => 20,
            'id_product' => 163,
        ],
        [
            'id_category' => 20,
            'id_product' => 339,
        ],
        [
            'id_category' => 16,
            'id_product' => 340,
        ],
        [
            'id_category' => 20,
            'id_product' => 340,
        ],
        [
            'id_category' => 20,
            'id_product' => 344,
        ],
        [
            'id_category' => 20,
            'id_product' => 346,
        ],
        [
            'id_category' => 16,
            'id_product' => 347,
        ],
        [
            'id_category' => 20,
            'id_product' => 347,
        ],
        [
            'id_category' => 16,
            'id_product' => 348,
        ],
        [
            'id_category' => 20,
            'id_product' => 348,
        ],
        [
            'id_category' => 20,
            'id_product' => 349,
        ],
        [
            'id_category' => 20,
            'id_product' => 350,
        ],
        [
            'id_category' => 20,
            'id_product' => 351,
        ],
        [
            'id_category' => 20,
            'id_product' => 352,
        ],
    ];

}
?>