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

class BarcodesFixture extends AppFixture
{
    public string $table = 'fcs_barcodes';

    public array $records = [
        [
            'id' => 1,
            'product_id' => 349,
            'product_attribute_id' => 0,
            'barcode' => '1234567890123',
        ],
        [
            'id' => 2,
            'product_id' => 0,
            'product_attribute_id' => 13,
            'barcode' => '2145678901234',
        ],
        [
            'id' => 3,
            'product_id' => 352,
            'product_attribute_id' => 0,
            'barcode' => '2712345000000',
        ],
        [
            'id' => 4,
            'product_id' => 0,
            'product_attribute_id' => 15,
            'barcode' => '2112345000000',
        ]
    ];

}
?>