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

class PagesFixture extends AppFixture
{
    public string $table = 'fcs_pages';

    public array $records = [
        [
            'id_page' => 3,
            'title' => 'Page',
            'content' => '',
            'position' => 1,
            'menu_type' => 'header',
            'active' => 1,
            'extern_url' => '',
            'id_customer' => 88,
            'is_private' => 0,
            'modified' => '2016-08-29 13:36:43',
            'created' => '2016-08-29 13:36:43',
            'full_width' => 0,
            'id_parent' => 0,
            'lft' => 0,
            'rght' => 0,
        ],
    ];
}
?>