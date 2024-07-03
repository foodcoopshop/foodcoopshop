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

class AttributesFixture extends AppFixture
{
    public string $table = 'fcs_attribute';

    public array $records = [
        [
            'id_attribute' => 33,
            'name' => '0,5l',
            'can_be_used_as_unit' => 0,
            'active' => 1,
        ],
        [
            'id_attribute' => 35,
            'name' => '1 kg',
            'can_be_used_as_unit' => 1,
            'active' => 1,
        ],
        [
            'id_attribute' => 36,
            'name' => '0,5 kg',
            'can_be_used_as_unit' => 1,
            'active' => 1,
        ],
    ];

}
?>