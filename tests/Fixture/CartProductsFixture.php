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

class CartProductsFixture extends AppFixture
{
    public string $table = 'fcs_cart_products';

    public array $records = [
        [
            'id_cart_product' => 1,
            'id_cart' => 1,
            'id_product' => 346,
            'id_product_attribute' => 0,
            'amount' => 1,
            'created' => '2018-03-01 10:17:14',
            'modified' => '2018-03-01 10:17:14',
        ], 
        [
            'id_cart_product' => 2,
            'id_cart' => 1,
            'id_product' => 340,
            'id_product_attribute' => 0,
            'amount' => 1,
            'created' => '2018-03-01 10:17:14',
            'modified' => '2018-03-01 10:17:14',
        ],
        [
            'id_cart_product' => 3,
            'id_cart' => 1,
            'id_product' => 60,
            'id_product_attribute' => 10,
            'amount' => 1,
            'created' => '2018-03-01 10:17:14',
            'modified' => '2018-03-01 10:17:14',
        ],
    ];

}
?>