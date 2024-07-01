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

class ProductAttributeCombinationFixture extends AppFixture
{
    public string $table = 'fcs_product_attribute_combination';

    public array $records = [
        [
            'id_attribute' => 33,
            'id_product_attribute' => 10,
        ],
        [
            'id_attribute' => 36,
            'id_product_attribute' => 11,
        ],
        [
            'id_attribute' => 35,
            'id_product_attribute' => 12,
        ],
        [
            'id_attribute' => 36,
            'id_product_attribute' => 13,
        ],
        [
            'id_attribute' => 35,
            'id_product_attribute' => 14,
        ],
        [
            'id_attribute' => 36,
            'id_product_attribute' => 15,
        ],
    ];
    
}
?>