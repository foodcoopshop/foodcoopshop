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

class DepositsFixture extends AppFixture
{

    public string $table = 'fcs_deposits';

    public array $records = [
        [
            'id' => 1,
            'id_product' => 346,
            'id_product_attribute' => 0,
            'deposit' => 0.5
        ],
        [
            'id' => 2,
            'id_product' => 0,
            'id_product_attribute' => 9,
            'deposit' => 0.5
        ],
        [
            'id' => 3,
            'id_product' => 0,
            'id_product_attribute' => 10,
            'deposit' => 0.5
        ],
   ];
}
?>