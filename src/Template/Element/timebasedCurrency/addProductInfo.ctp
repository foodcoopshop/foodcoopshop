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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if ($appAuth->isTimebasedCurrencyEnabledForCustomer()) {
    echo '<div class="'.$class.'">';
        if ($manufacturerLimitReached) {
            echo '<span>' . __('The_manufacturer_has_reached_the_limit_to_pay_in_{0}.', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]) . '</span>';
        } else {
            echo '<span class="timebasedCurrencySeconds">' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($seconds) . '</span>';
                $titleForOverlay =
                    '<span style="padding:2px;float:left;">'.
                        __('Part_in_{0}', [Configure::read('app.currencyName')]).': <span class="money">' . $this->Number->formatAsCurrency($money).'</span><br />' .
                        __('Part_in_{0}', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]) . ':<span class="seconds">' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($seconds) . '</span>'.
                    '</span>';
                echo '<span title="'.h($titleForOverlay).'">' . $labelPrefix . ' ' . __('in') . ' ' . Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . '</span>';
        }
    echo '</div>';
}

?>