<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * Cronjob works properly if it's called on Configure::read('app.timeHelper')->getSendOrderListsDay() -1 or -2
 * eg: Order lists are sent on Wednesday => EmailOrderReminder can be called on Tuesday or Monday
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Command\Traits;

use Cake\Core\Configure;
use Cake\Console\Arguments;

trait CronjobCommandTrait
{
    public ?string $cronjobRunDay;

    public function setCronjobRunDay(Arguments $args = null): void
    {
        if (!$args->getArgumentAt(0)) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        } else {
            $this->cronjobRunDay = $args->getArgumentAt(0);
        }
    }

}