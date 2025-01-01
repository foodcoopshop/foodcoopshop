<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PricePerUnitHelper extends Helper
{

    public array $helpers = ['MyHtml', 'MyNumber'];

    public function getStringFromUnitSums($unitSum, $separator): string
    {
        $unitSumString = '';
        if (!empty($unitSum)) {
            $preparedUnitSum = [];
            foreach($unitSum as $unitName => $unitSum) {
                if ($unitSum > 0) {
                    $preparedUnitSum[] = $this->MyNumber->formatUnitAsDecimal($unitSum) . ' ' . $unitName;
                }
            }
            $unitSumString = join($separator, $preparedUnitSum);
        }
        return $unitSumString;
    }

    public function getQuantityInUnitsStringForAttributes($attributeName, $attributeCanBeUsedAsUnit, $unitPricePerUnitEnabled, $unitQuantityInUnits, $unitName, $amount=1): string
    {
        $result = $attributeName;
        $quantityInUnitsString = $this->getQuantityInUnits($unitPricePerUnitEnabled, $unitQuantityInUnits, $unitName, $amount);
        if ($quantityInUnitsString != '') {
            $result = $attributeName . ', ' . $quantityInUnitsString;
        }
        if ($attributeCanBeUsedAsUnit) {
            $result = $quantityInUnitsString;
        }
        if ($result == '') {
            $result = $attributeName;
        }
        return $result;
    }

    public function getQuantityInUnitsWithWrapper($quantityInUnitsEnabled, $quantityInUnits, $unitName): string
    {
        $quantityInUnitsString = $this->getQuantityInUnits($quantityInUnitsEnabled, $quantityInUnits, $unitName);
        if ($quantityInUnitsString != '') {
            $quantityInUnitsString = '<span class="quantity-in-units">' . $quantityInUnitsString . '</span>';
        }
        return $quantityInUnitsString;
    }

    public function getQuantityInUnits($quantityInUnitsEnabled, $quantityInUnits, $unitName, $amount=1): string
    {
        $result = '';
        if ($quantityInUnitsEnabled && $quantityInUnits > 0) {
            if ($amount > 1) {
                $result  = __('for_each') . ' ' . $result;
            }
            $result .= __('approx.') . ' ' . $this->MyNumber->formatUnitAsDecimal($quantityInUnits) . ' ' . $unitName;
        }
        return $result;
    }

    public function getPricePerUnitForFrontend($priceInclPerUnit, $quantityInUnits, $amount, $title): string
    {
        return '<div class="price" title="' . h($title) . '">' . $this->MyNumber->formatAsCurrency(
            $this->getPricePerUnit($priceInclPerUnit, $quantityInUnits, $amount)
        ) . '</div> <div class="price-asterisk">*</div>';
    }

    public function getPricePerUnit($priceInclPerUnit, $quantityInUnits, $amount): float
    {
        return $priceInclPerUnit * $quantityInUnits / $amount;
    }

    public function getPrice($priceInclPerUnit, $unitAmount, $productQuantity): float
    {
        return round((float) $priceInclPerUnit / $unitAmount * $productQuantity, 2);
    }

    public function getPricePerUnitInfoText($priceInclPerUnit, $unitName, $unitAmount, $showAdaptionMessage=true): string
    {
        $infoText = '<div class="line">';
        $infoText .= '<span class="p-info">';
        $infoText .= ' * ' . __('Base_price') . ': ' . $this->getPricePerUnitBaseInfoForCart($priceInclPerUnit, $unitName, $unitAmount);
        if ($showAdaptionMessage) {
            $infoText .= ', ' . __('price_will_be_adapted.');
        }
        $infoText .= '</span>';
        $infoText .= '</div>';
        return $infoText;
    }

    public function getPricePerUnitBaseInfo($priceInclPerUnit, $unitName, $unitAmount): string
    {
        return $this->MyNumber->formatAsCurrency($priceInclPerUnit) . ' / ' . ($unitAmount > 1 ? $this->MyNumber->formatAsDecimal($unitAmount, 0) . ' ' : '') . $unitName;
    }

    public function getPricePerUnitBaseInfoForCart($priceInclPerUnit, $unitName, $unitAmount): string
    {
        // unit-amount must be included non-formatted for locale-based usage in cart.js
        return '<span class="price-incl-per-unit">'.$this->MyNumber->formatAsCurrency($priceInclPerUnit) . '</span> / <span class="unit-amount">'.($unitAmount > 1 ? $unitAmount : '').'</span>' . ($unitAmount > 1 ? $this->MyNumber->formatAsDecimal($unitAmount, 0) . ' ' : '') . '<span class="unit-name">' . $unitName . '</span>';
    }

}
