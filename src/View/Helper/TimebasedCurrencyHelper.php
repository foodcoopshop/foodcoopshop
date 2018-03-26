<?php

namespace App\View\Helper;

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TimebasedCurrencyHelper extends Helper
{
    
    public $helpers = ['MyTime', 'MyHtml', 'MyNumber'];
    
    public function getTimebasedCurrencyHoursAndMinutesDropdown($maxHoursAsDecimal, $exchangeRate)
    {
        $stepsInMinutes = 5;
        $dropdown = [];
        $usedValues = [];
        for($i = 0; $i <= $maxHoursAsDecimal * 100; $i++) {
            $timeAsDecimal = $i / 100;
            $stringValue = (string) $timeAsDecimal;
            $minutes = $this->MyTime->getDecimalToMinutes($timeAsDecimal);
            $value = $this->MyTime->formatDecimalToHoursAndMinutes($timeAsDecimal);
            $valueWithEuro = $value . ' (' . $this->getTimebasedCurrencyTimeAsEuroForDropdown($timeAsDecimal, $exchangeRate) . ')';
            if (abs($minutes) % $stepsInMinutes == 0 && !isset($usedValues[$value])) {
                $dropdown[$stringValue] = $valueWithEuro;
                $usedValues[$value] = true;
            }
        }
        $maxHoursValue = $this->MyTime->formatDecimalToHoursAndMinutes($maxHoursAsDecimal);
        $maxHoursValue .= ' (' . $this->getTimebasedCurrencyTimeAsEuroForDropdown($maxHoursAsDecimal, $exchangeRate) . ')';
        if (!isset($usedValues[$maxHoursValue])) {
            $dropdown[(string) $maxHoursAsDecimal] = $maxHoursValue;
        }
        $dropdown = array_reverse($dropdown, true);
        return $dropdown;
    }
    
    public function getTimebasedCurrencyTimeAsEuroForDropdown($decimal, $exchangeRate)
    {
        return str_replace('&nbsp;', ' ', $this->MyHtml->formatAsEuro(
            $decimal *
            $this->MyNumber->replaceCommaWithDot($exchangeRate)
        ));
    }
    
}
