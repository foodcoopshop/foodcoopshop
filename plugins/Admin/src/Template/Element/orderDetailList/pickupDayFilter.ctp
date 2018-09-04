<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

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
echo '</b>: ';
if (count($pickupDay) == 1) {
    echo $this->Time->getWeekdayName($this->Time->formatAsWeekday(strtotime($pickupDay[0]))) . ', ';
    echo $this->element('dateFields', ['dateFrom' => $pickupDay[0], 'nameFrom' => 'pickupDay[]', 'showDateTo' => false]);
} else {
    echo $this->element('dateFields', ['dateFrom' => $pickupDay[0], 'nameFrom' => 'pickupDay[]', 'dateTo' => $pickupDay[1], 'nameTo' => 'pickupDay[]']);
}

?>