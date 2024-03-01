<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\View\Helper\NumberHelper;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since FoodCoopShop 2.1.0
 * @license https://opensource.org/licenses/AGPL-3.0
 * @author Mario Rothauer <office@foodcoopshop.com>
 * @copyright Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link https://www.foodcoopshop.com
 */
class MyNumberHelper extends NumberHelper
{
    /**
     * turns eg 245 into 00245
     */
    public function addLeadingZerosToNumber(string $number, int $digits): string
    {
        return str_pad($number, $digits, '0', STR_PAD_LEFT);
    }

    public function getStringAsFloat(string $string): bool|float
    {
        $float = $this->parseFloatRespectingLocale(trim($string));
        if ($float === false) {
            return -1; // do not return false, because 0 is a valid return value!
        }
        return $float;
    }

    public function formatAsCurrency($amount): string
    {
        $amount = round((float) $amount, 2); // 3.325 was rounded to 3.32 without this line
        $currency = self::currency($amount, 'USD');
        // e.g. PLN for polish zloty does not return the polish currency symbol
        $currency = str_replace('$', Configure::read('appDb.FCS_CURRENCY_SYMBOL'), $currency);
        $currency = str_replace('USD', Configure::read('appDb.FCS_CURRENCY_SYMBOL'), $currency);
        return $currency;
    }

    public function formatAsUnit($amount, $shortcode): string
    {
        return self::formatAsDecimal($amount) . ' ' . $shortcode;
    }

    public function formatAsPercent($amount, $decimals = 2): string
    {
        return self::formatAsDecimal($amount, $decimals) . '%';
    }

    /**
     * shows decimals only if necessary
     */
    public function formatTaxRate($rate): string
    {
        return $rate != intval($rate) ? self::formatAsDecimal($rate, 1) : self::formatAsDecimal($rate, 0);
    }

    public function formatUnitAsDecimal($amount): string
    {
        return self::formatAsDecimal($amount, 3, true);
    }

    public function formatAsDecimal($amount, $decimals = 2, $removeTrailingZeros = false, $minDecimals = null): string
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
        if (!is_null($minDecimals)) {
            $options = array_merge($options, [
                'places' => $minDecimals,
            ]);
        }
        $result = self::format($amount, $options);
        return $result;
    }

    /**
     * Number::parseFloat($float, ['locale' => I18n::getLocale()]); did not work with travis!
     */
    public function parseFloatRespectingLocale($float): bool|float
    {
        if (I18n::getLocale() == 'de_DE') {
            $float = str_replace(',', '.', (string) $float); // replace decimal places
        }
        if (!is_numeric($float)) {
            return false;
        }
        return (float) $float;
    }
}
?>