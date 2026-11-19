<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) FoodCoopShop, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Test\Fixture;

class ShoppingListsFixture extends AppFixture
{

    public const int ID_CUSTOMER_WEEKLY = 1;
    public const int ID_SUPERADMIN = 2;

    public string $table = 'fcs_shopping_lists';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $records = [
        [
            'id' => self::ID_CUSTOMER_WEEKLY,
            'id_customer' => 87,
            'name' => 'Wocheneinkauf',
            'position' => 10,
            'is_default' => 1,
            'created' => '2026-11-01 10:00:00',
            'modified' => '2026-11-01 10:00:00',
        ],
        [
            'id' => self::ID_SUPERADMIN,
            'id_customer' => 92,
            'name' => 'Superadmin-Liste',
            'position' => 0,
            'is_default' => 1,
            'created' => '2026-11-01 10:00:00',
            'modified' => '2026-11-01 10:00:00',
        ],
    ];

}
