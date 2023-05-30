<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo '<div class="pickup-day-filter-wrapper'.(count($pickupDay) == 2 ? ' two-pickup-days' : '') . '">';
    echo '<b>';
    if ($appAuth->isManufacturer()) {
        if (count($pickupDay) == 1) {
            echo __d('admin', 'Delivery_day');
        } else {
            echo __d('admin', 'Delivery_days');
        }
    } else {
        if (count($pickupDay) == 1) {
            echo __d('admin', 'Pickup_day');
        } else {
            echo __d('admin', 'Pickup_days');
        }
    }
    echo ':</b>';
    if (count($pickupDay) == 1) {
        echo '<span class="weekday-as-name">';
            echo $this->Time->getWeekdayName($this->Time->formatAsWeekday(strtotime($pickupDay[0]))) . ', ';
        echo '</span>';
        echo $this->element('dateFields', ['dateFrom' => $pickupDay[0], 'nameFrom' => 'pickupDay[]', 'showDateTo' => false]);
    } else {
        echo $this->element('dateFields', ['dateFrom' => $pickupDay[0], 'nameFrom' => 'pickupDay[]', 'dateTo' => $pickupDay[1], 'nameTo' => 'pickupDay[]']);
    }
echo '</div>';
?>