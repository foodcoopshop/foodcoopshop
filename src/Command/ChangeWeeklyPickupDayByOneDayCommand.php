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
use App\Lib\Error\Exception\InvalidParameterException;

class ChangeWeeklyPickupDayByOneDayCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        if (empty($args->getArguments())) {
            throw new InvalidParameterException('args not set');
        }

        if (!in_array($args->getArgumentAt(0), ['increase', 'decrease'])) {
            throw new InvalidParameterException('args wrong');
        }

        $this->Product = $this->getTableLocator()->get('Products');
        $this->Configuration = $this->getTableLocator()->get('Configurations');

        $statement = $this->Product->getConnection()->prepare(
            "UPDATE fcs_configuration SET value = :newWeeklyPickupDay WHERE name = 'FCS_WEEKLY_PICKUP_DAY';"
        );

        if ($args->getArgumentAt(0) == 'increase') {
            $newWeeklyPickupDay = Configure::read('app.timeHelper')->getNthWeekdayAfterWeekday(1, Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY'));
        }
        if ($args->getArgumentAt(0) == 'decrease') {
            $newWeeklyPickupDay = Configure::read('app.timeHelper')->getNthWeekdayBeforeWeekday(1, Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY'));
        }

        $params = ['newWeeklyPickupDay' => $newWeeklyPickupDay];
        $statement->execute($params);

        $this->Configuration->loadConfigurations();

        $products = $this->Product->find('all');
        foreach($products as $product) {
            if ($args->getArgumentAt(0) == 'increase') {
                $newDeliveryRhythmSendOrderListWeekday = Configure::read('app.timeHelper')->getNthWeekdayAfterWeekday(1, $product->delivery_rhythm_send_order_list_weekday);
            }
            if ($args->getArgumentAt(0) == 'decrease') {
                $newDeliveryRhythmSendOrderListWeekday = Configure::read('app.timeHelper')->getNthWeekdayBeforeWeekday(1, $product->delivery_rhythm_send_order_list_weekday);
            }
            $statement = $this->Product->getConnection()->prepare(
                "UPDATE fcs_product SET delivery_rhythm_send_order_list_weekday = :newDeliveryRhythmSendOrderListWeekday WHERE id_product = :productId;"
            );
            $params = [
                'newDeliveryRhythmSendOrderListWeekday' => $newDeliveryRhythmSendOrderListWeekday,
                'productId' => $product->id_product
            ];
            $statement->execute($params);
        }

        $io->out('Changed FCS_WEEKLY_PICKUP_DAY to ' . Configure::read('app.timeHelper')->getWeekdayName($newWeeklyPickupDay) . '.');

        return static::CODE_SUCCESS;

    }

}
