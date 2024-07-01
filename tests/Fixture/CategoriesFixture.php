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

class CategoriesFixture extends AppFixture
{
    public string $table = 'fcs_category';

    public array $records = [
        [
            'id_category' => 16,
            'id_parent' => 0,
            'name' => 'Fleischprodukte',
            'description' => '',
            'nleft' => 11,
            'nright' => 12,
            'active' => 1,
            'created' => '2014-05-14 21:40:51',
            'modified' => '2014-05-14 21:48:48',
        ],
        [
            'id_category' => 20,
            'id_parent' => 0,
            'name' => 'Alle Produkte',
            'description' => '',
            'nleft' => 3,
            'nright' => 4,
            'active' => 1,
            'created' => '2014-05-14 21:53:52',
            'modified' => '2014-05-17 13:14:22',
        ]
    ];

}
?>