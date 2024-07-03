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

class UnitsFixture extends AppFixture
{

    public string $table = 'fcs_units';

    public array $records = [
        [
            'id' => 1,
            'id_product' => 347,
            'id_product_attribute' => 0,
            'price_incl_per_unit' => 1.50,
            'purchase_price_incl_per_unit' => 0.98,
            'name' => 'g',
            'amount' => 100,
            'price_per_unit_enabled' => 1,
            'quantity_in_units' => 350.000
        ],
        [
            'id' => 2,
            'id_product' => 0,
            'id_product_attribute' => 11,
            'price_incl_per_unit' => 20.00,
            'purchase_price_incl_per_unit' => null,
            'name' => 'kg',
            'amount' => 1,
            'price_per_unit_enabled' => 1,
            'quantity_in_units' => 0.500
        ],
        [
            'id' => 3,
            'id_product' => 0,
            'id_product_attribute' => 12,
            'price_incl_per_unit' => 20.00,
            'purchase_price_incl_per_unit' => 14.00,
            'name' => 'g',
            'amount' => 500,
            'price_per_unit_enabled' => 1,
            'quantity_in_units' => 300.000
        ],
        [
            'id' => 4,
            'id_product' => 349,
            'id_product_attribute' => 0,
            'price_incl_per_unit' => 0.00,
            'purchase_price_incl_per_unit' => null,
            'name' => 'kg',
            'amount' => 1,
            'price_per_unit_enabled' => 0,
            'quantity_in_units' => 0.000
        ],
        [
            'id' => 5,
            'id_product' => 0,
            'id_product_attribute' => 13,
            'price_incl_per_unit' => 0.00,
            'purchase_price_incl_per_unit' => null,
            'name' => 'kg',
            'amount' => 1,
            'price_per_unit_enabled' => 0,
            'quantity_in_units' => 0.000
        ],
        [
            'id' => 6,
            'id_product' => 0,
            'id_product_attribute' => 14,
            'price_incl_per_unit' => 0.00,
            'purchase_price_incl_per_unit' => null,
            'name' => 'kg',
            'amount' => 1,
            'price_per_unit_enabled' => 0,
            'quantity_in_units' => 0.000
        ],
        [
            'id' => 7,
            'id_product' => 0,
            'id_product_attribute' => 15,
            'price_incl_per_unit' => 10.00,
            'purchase_price_incl_per_unit' => 6.00,
            'name' => 'kg',
            'amount' => 1,
            'price_per_unit_enabled' => 1,
            'quantity_in_units' => 0.500
        ],
        [
            'id' => 8,
            'id_product' => 351,
            'id_product_attribute' => 0,
            'price_incl_per_unit' => 15.00,
            'purchase_price_incl_per_unit' => null,
            'name' => 'kg',
            'amount' => 1,
            'price_per_unit_enabled' => 1,
            'quantity_in_units' => 1.000
        ],
        [
            'id' => 9,
            'id_product' => 352,
            'id_product_attribute' => 0,
            'price_incl_per_unit' => 12.00,
            'purchase_price_incl_per_unit' => null,
            'name' => 'kg',
            'amount' => 1,
            'price_per_unit_enabled' => 1,
            'quantity_in_units' => 1.000
        ]
   ];
}
?>