<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;

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
class TimebasedCurrencyHelper extends Helper
{

    public $helpers = ['MyTime', 'MyHtml', 'MyNumber'];

    /**
     * @param boolean $showText
     * @return string
     */
    public function getOrderInformationText($showText)
    {
        $text = '';
        if ($showText) {
            $text = '<p style="clear:both;">* '.__('Mouseover_shows_the_paid_amont_in_{0}.', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]).'</p>';
        }
        return $text;
    }

    /**
     * @param boolean $showText
     * @return string
     */
    public function getOrderInformationTextForPdf($showText)
    {
        $text = '';
        if ($showText) {
            $text = '<p>* '.__('Order_contains_amount_in_{0}.', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]).'</p>';
        }
        return $text;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return __('Paying_with_time_account_name_{0}', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]);
    }

    /**
     * @param int $seconds
     * @return string
     */
    public function formatSecondsToTimebasedCurrency($seconds)
    {
        $hours = round($seconds / 3600, 2);
        return $this->MyNumber->formatAsUnit($hours, Configure::read('appDb.FCS_TIMEBASED_CURRENCY_SHORTCODE'));
    }

    /**
     * @param int $maxSeconds
     * @param float $exchangeRate
     * @return array
     */
    public function getTimebasedCurrencyHoursDropdown($maxSeconds, $exchangeRate)
    {
        $stepsInSeconds = 15 * 60;
        $dropdown = [];
        $usedValues = [];
        for($second = 0; $second <= $maxSeconds; $second++) {
            $valueWithCurrency = $this->formatSecondsToTimebasedCurrency($second) . ' (' . $this->getCartTimebasedCurrencySecondsAsCurrencyForDropdown($second, $exchangeRate) . ')';
            if ($second % $stepsInSeconds == 0 && !isset($usedValues[$second])) {
                $dropdown[$second] = $valueWithCurrency;
                $usedValues[$second] = true;
            }
        }
        $maxHoursValue = $this->formatSecondsToTimebasedCurrency($maxSeconds);
        $maxHoursValue .= ' (' . $this->getCartTimebasedCurrencySecondsAsCurrencyForDropdown($maxSeconds, $exchangeRate) . ')';

        if (!isset($usedValues[$maxHoursValue])) {
            $dropdown[$this->MyNumber->parseFloatRespectingLocale((string) $maxSeconds)] = $maxHoursValue;
        }
        $dropdown = array_reverse($dropdown, true);
        return $dropdown;
    }

    /**
     * @param int $seconds
     * @param float $exchangeRate
     * @return string
     */
    public function getCartTimebasedCurrencySecondsAsCurrencyForDropdown($seconds, $exchangeRate)
    {
        return $this->MyNumber->formatAsCurrency(
            $seconds / 3600 *
            $this->MyNumber->parseFloatRespectingLocale($exchangeRate)
        );
    }

}
