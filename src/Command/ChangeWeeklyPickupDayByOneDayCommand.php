<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;

class ChangeWeeklyPickupDayByOneDayCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io): int
    {

        if (empty($args->getArguments())) {
            throw new \Exception('args not set');
        }

        if (!in_array($args->getArgumentAt(0), ['increase', 'decrease'])) {
            throw new \Exception('args wrong');
        }

        $configurationsTable = $this->getTableLocator()->get('Configurations');

        if ($args->getArgumentAt(0) == 'increase') {
            $newWeeklyPickupDay = Configure::read('app.timeHelper')->getNthWeekdayAfterWeekday(1, (int) Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY'));
        }
        if ($args->getArgumentAt(0) == 'decrease') {
            $newWeeklyPickupDay = Configure::read('app.timeHelper')->getNthWeekdayBeforeWeekday(1, (int) Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY'));
        }

        if (isset($newWeeklyPickupDay)) {
            $configurationsTable->updateAll([
                'value' => $newWeeklyPickupDay,
            ], [
                'name' => 'FCS_WEEKLY_PICKUP_DAY',
            ]);
        }

        $configurationsTable->loadConfigurations();

        $productsTable = $this->getTableLocator()->get('Products');
        $products = $productsTable->find('all');
        foreach($products as $product) {
            if ($args->getArgumentAt(0) == 'increase') {
                $newDeliveryRhythmSendOrderListWeekday = Configure::read('app.timeHelper')->getNthWeekdayAfterWeekday(1, $product->delivery_rhythm_send_order_list_weekday);
            }
            if ($args->getArgumentAt(0) == 'decrease') {
                $newDeliveryRhythmSendOrderListWeekday = Configure::read('app.timeHelper')->getNthWeekdayBeforeWeekday(1, $product->delivery_rhythm_send_order_list_weekday);
            }
            if (isset($newDeliveryRhythmSendOrderListWeekday)) {
                $productsTable->updateAll([
                    'delivery_rhythm_send_order_list_weekday' => $newDeliveryRhythmSendOrderListWeekday,
                ], [
                    'id_product' => $product->id_product,
                ]);
            }
        }

        if (isset($newWeeklyPickupDay)) {
            $io->out('Changed FCS_WEEKLY_PICKUP_DAY to ' . Configure::read('app.timeHelper')->getWeekdayName($newWeeklyPickupDay) . '.');
        }

        return static::CODE_SUCCESS;

    }

}
