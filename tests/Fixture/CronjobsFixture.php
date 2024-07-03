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

class CronjobsFixture extends AppFixture
{
    
    public string $table = 'fcs_cronjobs';

    public array $records = [
        [
            'id' => 1,
            'name' => 'TestCronjob',
            'time_interval' => 'day',
            'day_of_month' => null,
            'weekday' => null,
            'not_before_time' => '22:30:00',
            'active' => 1,
        ],
        [
            'id' => 2,
            'name' => 'TestCronjob',
            'time_interval' => 'week',
            'day_of_month' => null,
            'weekday' => 'Monday',
            'not_before_time' => '09:00:00',
            'active' => 1,
        ],
        [
            'id' => 3,
            'name' => 'TestCronjob',
            'time_interval' => 'month',
            'day_of_month' => 11,
            'weekday' => null,
            'not_before_time' => '07:30:00',
            'active' => 1,
        ]
    ];
}
?>