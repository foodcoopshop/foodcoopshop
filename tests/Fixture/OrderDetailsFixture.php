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

class OrderDetailsFixture extends AppFixture
{
    public string $table = 'fcs_order_detail';

    public array $records = [
        [
            'id_order_detail' => 1,
            'product_id' => 346,
            'product_attribute_id' => 0,
            'product_name' => 'Artischocke : Stück',
            'product_amount' => 1,
            'total_price_tax_incl' => 1.82,
            'total_price_tax_excl' => 1.65,
            'tax_unit_amount' => 0.17,
            'tax_total_amount' => 0.17,
            'tax_rate' => 10.00,
            'deposit' => 0.50,
            'id_customer' => 92,
            'id_invoice' => null,
            'id_cart_product' => 1,
            'order_state' => 3,
            'pickup_day' => '2018-02-02',
            'shopping_price' => 'SP',
            'created' => '2018-02-01 09:17:14',
            'modified' => '2021-05-04 11:10:14',
        ],
        [
            'id_order_detail' => 2,
            'product_id' => 340,
            'product_attribute_id' => 0,
            'product_name' => 'Beuschl',
            'product_amount' => 1,
            'total_price_tax_incl' => 4.54,
            'total_price_tax_excl' => 4.54,
            'tax_unit_amount' => 0.00,
            'tax_total_amount' => 0.00,
            'tax_rate' => 0.00,
            'deposit' => 0.00,
            'id_customer' => 92,
            'id_invoice' => null,
            'id_cart_product' => 2,
            'order_state' => 3,
            'pickup_day' => '2018-02-02',
            'shopping_price' => 'SP',
            'created' => '2018-02-01 09:17:14',
            'modified' => '2021-05-04 11:10:14',
        ],
        [
            'id_order_detail' => 3,
            'product_id' => 60,
            'product_attribute_id' => 10,
            'product_name' => 'Milch : 0,5l',
            'product_amount' => 1,
            'total_price_tax_incl' => 0.62,
            'total_price_tax_excl' => 0.55,
            'tax_unit_amount' => 0.07,
            'tax_total_amount' => 0.07,
            'tax_rate' => 13.00,
            'deposit' => 0.50,
            'id_customer' => 92,
            'id_invoice' => null,
            'id_cart_product' => 3,
            'order_state' => 3,
            'pickup_day' => '2018-02-02',
            'shopping_price' => 'SP',
            'created' => '2018-02-01 09:17:14',
            'modified' => '2021-05-04 11:10:14',
        ]
    ];

}
?>