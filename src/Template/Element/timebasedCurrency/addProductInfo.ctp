<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $appAuth->user('timebased_currency_enabled')) {
    echo '<span class="timebasedCurrencyTime">' . $this->Time->formatDecimalToHoursAndMinutes($time) . '</span>';
    echo '<'.$wrapperTag.' class="'.$class.'">';
        $titleForOverlay =
            '<span style="padding:2px;float:left;">'.
                'Anteil in Euro: <span class="money">' . $this->Html->formatAsEuro($money).'</span><br />' .
                'Anteil in ' . Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . ':<span class="time">' . $this->Time->formatDecimalToHoursAndMinutes($time) . '</span>'.
            '</span>';
        ;
        echo '<span title="'.h($titleForOverlay).'">' . $labelPrefix . ' in ' . Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . '</span>';
    echo '</'.$wrapperTag.'>';
}

?>