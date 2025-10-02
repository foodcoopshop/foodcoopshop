<?php
declare(strict_types=1);

use App\Services\ProductQuantityService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo '<div class="units-wrapper">';
    $preparedProductAttributes = [];
    if (!empty($product->product_attributes)) {
        foreach ($product->product_attributes as $attribute) {
            $radioButtonLabel = $this->PricePerUnit->getQuantityInUnitsStringForAttributes(
                $attribute->product_attribute_combination->attribute->name,
                $attribute->product_attribute_combination->attribute->can_be_used_as_unit,
                $attribute->unit_product_attribute->price_per_unit_enabled,
                $attribute->unit_product_attribute->quantity_in_units,
                $attribute->unit_product_attribute->name,
            );
            $preparedProductAttributes[$product->id_product . '-' . $attribute->id_product_attribute] = $radioButtonLabel;
        }
        if (!empty($preparedProductAttributes)) {
            echo $this->Form->control('product-attributes-' . $product->id_product, [
                'type' => 'select',
                'label' => false,
                'options' => $preparedProductAttributes,
            ]);
        }
    } else {
        $unityStrings = [];
        if ($product->unity != '') {
            $unityStrings[] = $product->unity;
        }
        $isAmountBasedOnQuantityInUnitsIncludingSelfServiceCheck = (new ProductQuantityService())->isAmountBasedOnQuantityInUnitsIncludingSelfServiceCheck($product, $product->unit_product);
        if (!$isAmountBasedOnQuantityInUnitsIncludingSelfServiceCheck) {
            $unitString = $this->PricePerUnit->getQuantityInUnits($product->unit_product->price_per_unit_enabled, $product->unit_product->quantity_in_units, $product->unit_product->name);
            if ($unitString != '') {
                $unityStrings[] = $unitString;
            }
        }
        if (!empty($unityStrings)) {
            echo '<div class="unity"><span class="value">' . join(', ', $unityStrings).'</span></div>';
        }
    }
echo '</div>';