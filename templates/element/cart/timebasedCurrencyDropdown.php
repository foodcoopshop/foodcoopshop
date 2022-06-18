<?php
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


use Cake\Core\Configure;

if (!$appAuth->isOrderForDifferentCustomerMode()
    && $appAuth->isTimebasedCurrencyEnabledForCustomer()
    && $appAuth->Cart->getTimebasedCurrencySecondsSum() > 0) {
    echo $this->Form->control('Carts.timebased_currency_seconds_sum_tmp', [
        'label' => __('How_much_of_it_do_i_want_to_pay_in_{0}?', [
            Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')
        ]),
        'type' => 'select',
        'options' => $this->TimebasedCurrency->getTimebasedCurrencyHoursDropdown(
            $appAuth->Cart->getTimebasedCurrencySecondsSumRoundedUp(),
            Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE')
        )
    ]);
}

?>