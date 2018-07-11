<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\View\Helper\NumberHelper;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since FoodCoopShop 2.1.0
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @author Mario Rothauer <office@foodcoopshop.com>
 * @copyright Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link https://www.foodcoopshop.com
 */
class MyNumberHelper extends NumberHelper
{

    public function formatAsCurrency($amount)
    {
        $currency = self::currency($amount, 'USD'); // e.g. PLN for polish zloty does not return the currency symbol
        $currency = str_replace('$', Configure::read('appDb.FCS_CURRENCY_SYMBOL'), $currency);
        $currency = str_replace(' ', '&nbsp;', $currency); // careful: pattern is not a normal space but a no-break space 0xA0
        return $currency;
    }

    public function formatAsUnit($amount, $shortcode)
    {
        return self::formatAsDecimal($amount) . '&nbsp;' . $shortcode;
    }

    public function formatAsPercent($amount)
    {
        return self::formatAsDecimal($amount) . '%';
    }

    /**
     * shows decimals only if necessary
     * @param $rate
     */
    public function formatTaxRate($rate)
    {
        return $rate != intval($rate) ? self::formatAsDecimal($rate, 1) : self::formatAsDecimal($rate, 0);
    }

    public function formatUnitAsDecimal($amount)
    {
        return self::formatAsDecimal($amount, 3, true);
    }

    public function formatAsDecimal($amount, $decimals = 2, $removeTrailingZeros = false)
    {
        $options = [
            'locale' => I18n::getLocale()
        ];
        if (!$removeTrailingZeros) {
            $options = array_merge($options, [
                'places' => $decimals,
                'precision' => $decimals
            ]);
        }
        $result = self::format($amount, $options);
        return $result;
    }

    /**
     * self::parseFloat($double, ['locale' => I18n::getLocale()]); did not work with travis!
     * @return boolean|mixed
     */
    public function parseFloatRespectingLocale($double)
    {
        if (I18n::getLocale() == 'de_DE') {
            $double = str_replace(',', '.', $double); // then replace decimal places
        }
        if (!is_numeric($double)) {
            return false;
        }
        return $double;
    }
}
?>