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

    public function getStringFromUnitSums(array $unitSums, string $separator): string
    {
        $result = '';
        if (!empty($unitSums)) {
            $preparedUnitSum = [];
            foreach($unitSums as $unitName => $unitSum) {
                if ($unitSum > 0) {
                    $preparedUnitSum[] = $this->MyNumber->formatUnitAsDecimal($unitSum) . ' ' . $unitName;
                }
            }
            $result = join($separator, $preparedUnitSum);
        }
        return $result;
    }

    public function getQuantityInUnitsStringForAttributes(
        string $attributeName,
        bool $attributeCanBeUsedAsUnit,
        bool|int|string $unitPricePerUnitEnabled,
        string|float $unitQuantityInUnits,
        string $unitName,
        string|float $amount=1,
        ): string
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

    public function getQuantityInUnitsWithWrapper(
        bool|int|string $quantityInUnitsEnabled,
        string|float $quantityInUnits,
        string $unitName,
        ): string
    {
        $quantityInUnitsString = $this->getQuantityInUnits($quantityInUnitsEnabled, $quantityInUnits, $unitName);
        if ($quantityInUnitsString != '') {
            $quantityInUnitsString = '<span class="quantity-in-units">' . $quantityInUnitsString . '</span>';
        }
        return $quantityInUnitsString;
    }

    public function getQuantityInUnits(
        bool|int|string $quantityInUnitsEnabled,
        string|float $quantityInUnits,
        string $unitName,
        string|float $amount=1,
        ): string
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

    public function getPricePerUnitForFrontend(
        string|float $priceInclPerUnit,
        string|float $quantityInUnits,
        string|float $amount,
        string $title,
        ): string
    {
        return '<div class="price" title="' . h($title) . '">' . $this->MyNumber->formatAsCurrency(
            $this->getPricePerUnit($priceInclPerUnit, $quantityInUnits, $amount)
        ) . '</div> <div class="price-asterisk">*</div>';
    }

    public function getPricePerUnit(
        string|float $priceInclPerUnit,
        string|float $quantityInUnits,
        string|float $amount,
        ): float
    {
        return $priceInclPerUnit * $quantityInUnits / $amount;
    }

    public function getPrice(
        string|float $priceInclPerUnit,
        string|float $unitAmount,
        string|float $productQuantity,
        ): float
    {
        return round((float) $priceInclPerUnit / $unitAmount * $productQuantity, 2);
    }

    public function getPricePerUnitInfoText(
        string|float $priceInclPerUnit,
        string $unitName,
        string|float $unitAmount,
        bool $showAdaptionMessage=true): string
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

    public function getPricePerUnitBaseInfo(
        string|float $priceInclPerUnit,
        string $unitName,
        string|float $unitAmount,
        ): string
    {
        return $this->MyNumber->formatAsCurrency($priceInclPerUnit) . ' / ' . ($unitAmount > 1 ? $this->MyNumber->formatAsDecimal($unitAmount, 0) . ' ' : '') . $unitName;
    }

    public function getPricePerUnitBaseInfoForCart(
        string|float $priceInclPerUnit,
        string $unitName,
        string|float $unitAmount,
        ): string
    {
        // unit-amount must be included non-formatted for locale-based usage in cart.js
        return '<span class="price-incl-per-unit">'.$this->MyNumber->formatAsCurrency($priceInclPerUnit) . '</span> / <span class="unit-amount">'.($unitAmount > 1 ? $unitAmount : '').'</span>' . ($unitAmount > 1 ? $this->MyNumber->formatAsDecimal($unitAmount, 0) . ' ' : '') . '<span class="unit-name">' . $unitName . '</span>';
    }

}
