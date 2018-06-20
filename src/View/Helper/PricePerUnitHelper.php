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
class PricePerUnitHelper extends Helper
{
    
    public $helpers = ['MyHtml', 'MyNumber'];
    
    public function getStringFromUnitSums($unitSum, $separator)
    {
        $unitSumString = '';
        if (!empty($unitSum)) {
            $preparedUnitSum = [];
            foreach($unitSum as $unitName => $unitSum) {
                $preparedUnitSum[] = $unitSum . ' ' . $unitName;
            }
            $unitSumString = join($separator, $preparedUnitSum);
        }
        return $unitSumString;
    }
    
    public function getQuantityInUnitsStringForAttributes($attributeName, $attributeCanBeUsedAsUnit, $unitPricePerUnitEnabled, $unitQuantityInUnits, $unitName, $amount=1)
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
    
    public function getQuantityInUnitsWithWrapper($quantityInUnitsEnabled, $quantityInUnits, $unitName)
    {
        $quantityInUnitsString = $this->getQuantityInUnits($quantityInUnitsEnabled, $quantityInUnits, $unitName);
        if ($quantityInUnitsString != '') {
            $quantityInUnitsString = '<span class="quantity-in-units">' . $quantityInUnitsString . '</span>';
        }
        return $quantityInUnitsString;
    }
    
    public function getQuantityInUnits($quantityInUnitsEnabled, $quantityInUnits, $unitName, $amount=1)
    {
        $result = '';
        if ($quantityInUnitsEnabled && $quantityInUnits > 0) {
            if ($amount > 1) {
                $result  = __('for_each') . ' ' . $result;
            }
            $result .= __('approx.') . ' ' . $this->MyNumber->formatUnitAsDecimal($quantityInUnits) . ' ' . $unitName;
        }
        return $result;
    }
    
    public function getPricePerUnit($priceInclPerUnit, $quantityInUnits, $amount)
    {
        return '<div class="price">' . $this->MyNumber->formatAsCurrency($priceInclPerUnit * $quantityInUnits / $amount) . '</div> <div class="price-asterisk">*</div>';
    }
    
    public function getPricePerUnitInfoText($priceInclPerUnit, $unitName, $unitAmount)
    {
        $infoText = '<div class="line">';
        $infoText .= '<span class="additional-price-info">';
        $infoText .= ' * ' . __('Base_price') . ': ' . $this->getPricePerUnitBaseInfo($priceInclPerUnit, $unitName, $unitAmount);
        $infoText .= ', ' . __('price_will_be_eventually_adapted.');
        $infoText .= '</span>';
        $infoText .= '</div>';
        return $infoText;
    }
    
    public function getPricePerUnitBaseInfo($priceInclPerUnit, $unitName, $unitAmount)
    {
        return $this->MyNumber->formatAsCurrency($priceInclPerUnit) . ' / ' . ($unitAmount > 1 ? $this->MyNumber->formatAsDecimal($unitAmount, 0) . ' ' : '') . $unitName;
    }
    
}
