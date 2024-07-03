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

class TaxesFixture extends AppFixture
{
    public string $table = 'fcs_tax';

    public array $records = [
        [
            'id_tax' => 1,
            'rate' => 20,
            'active' => 1,
            'deleted' => 0,
        ],
        [
            'id_tax' => 2,
            'rate' => 10,
            'active' => 1,
            'deleted' => 0,
        ],
        [
            'id_tax' => 3,
            'rate' => 13,
            'active' => 1,
            'deleted' => 0,
        ]
    ];

}
?>