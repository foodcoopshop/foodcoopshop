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

class ProductAttributesFixture extends AppFixture
{
    public string $table = 'fcs_product_attribute';

    public array $records = [
        [
            'id_product_attribute' => 10,
            'id_product' => 60,
            'price' => 0.545455,
            'default_on' => 0,
        ],
        [
            'id_product_attribute' => 11,
            'id_product' => 348,
            'price' => 0.000000,
            'default_on' => 1,
        ],
        [
            'id_product_attribute' => 12,
            'id_product' => 348,
            'price' => 0.000000,
            'default_on' => 0,
        ],
        [
            'id_product_attribute' => 13,
            'id_product' => 350,
            'price' => 1.818182,
            'default_on' => 1,
        ],
        [
            'id_product_attribute' => 14,
            'id_product' => 350,
            'price' => 3.636364,
            'default_on' => 0,
        ],
        [
            'id_product_attribute' => 15,
            'id_product' => 350,
            'price' => 0.000000,
            'default_on' => 0,
        ]
    ];
}
?>