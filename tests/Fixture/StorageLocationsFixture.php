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

class StorageLocationsFixture extends AppFixture
{
    public string $table = 'fcs_storage_locations';

    public array $records = [
        [
            'id' => 1,
            'name' => 'Keine Kühlung',
            //'rank' => 10, // rank is a reserved word in mysql, change that to "sortkey"
        ],
        [
            'id' => 2,
            'name' => 'Kühlschrank',
            //'rank' => 20,
        ],
        [
            'id' => 3,
            'name' => 'Tiefkühler',
            //'rank' => 30,
        ],
    ];

}
?>