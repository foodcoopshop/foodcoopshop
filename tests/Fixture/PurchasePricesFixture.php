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

class PurchasePricesFixture extends AppFixture
{
    public string $table = 'fcs_purchase_prices';

    public array $records = [
        [
            'id_purchase_price' => 1,
            'product_id' => 346,
            'product_attribute_id' => 0,
            'tax_id' => 1,
            'price' => 1.200000
        ],
        [
            'id_purchase_price' => 2,
            'product_id' => 0,
            'product_attribute_id' => 13,
            'tax_id' => 0,
            'price' => 1.400000
        ],
        [
            'id_purchase_price' => 3,
            'product_id' => 347,
            'product_attribute_id' => 0,
            'tax_id' => 3,
            'price' => NULL
        ],
        [
            'id_purchase_price' => 4,
            'product_id' => 348,
            'product_attribute_id' => 0,
            'tax_id' => 3,
            'price' => NULL
        ],
        [
            'id_purchase_price' => 5,
            'product_id' => 60,
            'product_attribute_id' => 0,
            'tax_id' => 2,
            'price' => NULL
        ],
        [
            'id_purchase_price' => 6,
            'product_id' => 0,
            'product_attribute_id' => 10,
            'tax_id' => 0,
            'price' => 0.250000
        ],
        [
            'id_purchase_price' => 7,
            'product_id' => 163,
            'product_attribute_id' => 0,
            'tax_id' => 0,
            'price' => 1.072727
        ],
    ];

}
?>