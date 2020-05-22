<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Lib\Error\Exception\InvalidParameterException;

class ChangeWeeklyPickupDayByOneDayShell extends Shell
{

    public function main()
    {

        if (empty($this->args)) {
            throw new InvalidParameterException('args not set');
        }

        if (!in_array($this->args[0], ['increase', 'decrease'])) {
            throw new InvalidParameterException('args wrong');
        }

        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');

        $statement = $this->Product->getConnection()->prepare(
            "UPDATE fcs_configuration SET value = :newWeeklyPickupDay WHERE name = 'FCS_WEEKLY_PICKUP_DAY';"
        );

        if ($this->args[0] == 'increase') {
            $newWeeklyPickupDay = Configure::read('app.timeHelper')->getNthWeekdayAfterWeekday(1, Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY'));
        }
        if ($this->args[0] == 'decrease') {
            $newWeeklyPickupDay = Configure::read('app.timeHelper')->getNthWeekdayBeforeWeekday(1, Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY'));
        }

        $params = ['newWeeklyPickupDay' => $newWeeklyPickupDay];
        $statement->execute($params);

        $this->Configuration->loadConfigurations();

        $products = $this->Product->find('all');
        foreach($products as $product) {
            if ($this->args[0] == 'increase') {
                $newDeliveryRhythmSendOrderListWeekday = Configure::read('app.timeHelper')->getNthWeekdayAfterWeekday(1, $product->delivery_rhythm_send_order_list_weekday);
            }
            if ($this->args[0] == 'decrease') {
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

        $this->out('Changed FCS_WEEKLY_PICKUP_DAY to ' . Configure::read('app.timeHelper')->getWeekdayName($newWeeklyPickupDay) . '.');

    }

}

