<?php

namespace App\View\Helper;

use Cake\Log\Log;
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
    
    public $helpers = ['MyHtml'];
    
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
        $quantityInUnitsString = $this->getQuantityInUnits($unitPricePerUnitEnabled, $unitQuantityInUnits, $unitName, $amount);
        if ($quantityInUnitsString != '') {
            $unitName = $attributeName . ', ' . $quantityInUnitsString;
        }
        if ($attributeCanBeUsedAsUnit) {
            $unitName = $quantityInUnitsString;
        }
        if ($unitName == '') {
            $unitName = $attributeName;
        }
        return $unitName;
    }
    
    public function getQuantityInUnitsWithWrapper($quantityInUnitsEnabled, $quantityInUnits, $unitName)
    {
        return '<span class="quantity-in-units">' . $this->getQuantityInUnits($quantityInUnitsEnabled, $quantityInUnits, $unitName) . '</span>';
    }
    
    public function getQuantityInUnits($quantityInUnitsEnabled, $quantityInUnits, $unitName, $amount=1)
    {
        $result = '';
        if ($quantityInUnitsEnabled && $quantityInUnits > 0) {
            if ($amount > 1) {
                $result  = 'je ' . $result;
            }
            $result .= 'ca. ' . $this->MyHtml->formatUnitAsDecimal($quantityInUnits) . ' ' . $unitName;
        }
        return $result;
    }
    
    public function getPricePerUnit($priceInclPerUnit, $quantityInUnits, $amount)
    {
        return '<div class="price">' . $this->MyHtml->formatAsEuro($priceInclPerUnit * $quantityInUnits / $amount) . '</div> <div class="price-asterisk">*</div>';
    }
    
    public function getPricePerUnitInfoText($priceInclPerUnit, $unitName, $unitAmount)
    {
        $infoText = '<div class="line">';
        $infoText .= '<span class="additional-price-info">';
        $infoText .= ' * Basis-Preis: ' . $this->getPricePerUnitBaseInfo($priceInclPerUnit, $unitName, $unitAmount);
        $infoText .= ', Preis wird evtl. noch angepasst.';
        $infoText .= '</span>';
        $infoText .= '</div>';
        return $infoText;
    }
    
    public function getPricePerUnitBaseInfo($priceInclPerUnit, $unitName, $unitAmount)
    {
        return $this->MyHtml->formatAsEuro($priceInclPerUnit) . ' / ' . ($unitAmount > 1 ? $this->MyHtml->formatAsDecimal($unitAmount, 0) . ' ' : '') . $unitName;
    }
    
}
