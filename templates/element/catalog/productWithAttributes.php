<?php
declare(strict_types=1);

use App\Model\Entity\Customer;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

// 1) kick attributes if not available
$hasCheckedAttribute = false;
$i = 0;
$preparedProductAttributes = [];
foreach ($product->product_attributes as $attribute) {
    if ($attribute->stock_available->always_available || $attribute->stock_available->quantity - $attribute->stock_available->quantity_limit > 0) {
        $preparedProductAttributes[] = $attribute;
    }
    $i++;
}

// 2) try to define "default on" as checked radio button (if quantity is > 0)
$i = 0;
foreach ($preparedProductAttributes as $attribute) {
    $preparedProductAttributes[$i]->checked = false;
    if ($attribute->default_on == 1) {
        $preparedProductAttributes[$i]->checked = true;
        $hasCheckedAttribute = true;
    }
    $i++;
}

// make first attribute checked if no attribute is checked
// (usually if quantity of default attribute is 0)
if (!$hasCheckedAttribute && !empty($preparedProductAttributes)) {
    $preparedProductAttributes[0]->checked = true;
}

// every attribute has quantity = 0
if (empty($preparedProductAttributes)) {
    echo '<p>'.__('Currently_not_on_stock').'.</p>';
}

// render remaining attributes (with attribute "checked")
foreach ($preparedProductAttributes as $attribute) {

    $entityClasses = ['ew'];
    if ($attribute->checked) {
        $entityClasses[] = 'active';
    }
    echo '<div class="'.join(' ', $entityClasses).'" id="ew-'.$attribute->id_product_attribute.'">';
    if ($showProductPrice) {
        echo '<div class="line">';
        $tooltip = __('Tax_rate') . ': ' . $this->Number->formatTaxRate($product->tax->rate) . '%';
        if ($identity === null || $identity->shopping_price != Customer::SELLING_PRICE) {
            $sellingPrice = $attribute->selling_prices['gross_price'];
            if ($attribute->unit_product_attribute->price_per_unit_enabled) {
                $sellingPrice = $this->PricePerUnit->getPricePerUnit($attribute->selling_prices['price_incl_per_unit'], $attribute->unit_product_attribute->quantity_in_units, $attribute->unit_product_attribute->amount);
            }
            $tooltip .= '<br />' . __('Selling_price') . ': ' . $this->Number->formatAsCurrency($sellingPrice);
        }
        $priceHtml =  '<div class="price" title="' . h($tooltip) .  '">' . $this->Number->formatAsCurrency($attribute->gross_price) . '</div>';
        $pricePerUnitInfoText = '';
        if ($attribute->unit_product_attribute->price_per_unit_enabled) {
            $priceHtml = $this->PricePerUnit->getPricePerUnitForFrontend($attribute->unit_product_attribute->price_incl_per_unit, $attribute->unit_product_attribute->quantity_in_units, $attribute->unit_product_attribute->amount, $tooltip);
            $pricePerUnitInfoText = $this->PricePerUnit->getPricePerUnitInfoText(
                $attribute->unit_product_attribute->price_incl_per_unit,
                $attribute->unit_product_attribute->name,
                $attribute->unit_product_attribute->amount,
                !$orderCustomerService->isSelfServiceModeByUrl()
            );
        }
        echo $priceHtml;
        if (!empty($attribute->deposit_product_attribute->deposit)) {
            echo '<div class="deposit">+ <b>'. $this->Number->formatAsCurrency($attribute->deposit_product_attribute->deposit) . '</b> '.__('deposit').'</div>';
        }

        echo '<div class="tax">'. $this->Number->formatAsCurrency($attribute->calculated_tax) . '</div>';
        echo '</div>';
    } else {
        // Cart.js::initAddToCartButton() needs the following elements!
        echo '<div class="price hide">' . $this->Number->formatAsCurrency(0) . '</div>';
        echo '<div class="tax hide">'. $this->Number->formatAsCurrency(0) . '</div>';
    }
    echo $this->element('catalog/hiddenProductIdField', ['productId' => $product->id_product . '-' . $attribute->id_product_attribute]);
    echo $this->element('catalog/amountWrapper', [
        'product' => $product,
        'stockAvailable' => $attribute->stock_available,
        'orderedTotalAmount' => $attribute->ordered_total_amount ?? null,
        'hideAmountSelector' => $isStockProductOrderPossible || ($orderCustomerService->isSelfServiceModeByUrl() && $attribute->unit_product_attribute->price_per_unit_enabled),
        'hideIsStockProductIcon' => $orderCustomerService->isSelfServiceModeByUrl(),
    ]);
    echo $this->element('catalog/cartButton', [
        'deliveryBreakManufacturerEnabled' => $product->delivery_break_enabled ?? false,
        'productId' => $product->id_product . '-' . $attribute->id_product_attribute,
        'product' => $product,
        'stockAvailableQuantity' => $attribute->stock_available->quantity,
        'stockAvailableQuantityLimit' => $attribute->stock_available->quantity_limit,
        'stockAvailableAlwaysAvailable' => $attribute->stock_available->always_available,
        'hideButton' => $isStockProductOrderPossible,
        'cartButtonLabel' => $orderCustomerService->isSelfServiceModeByUrl() ? __('Move_in_shopping_bag') : __('Move_in_cart'),
        'cartButtonIcon' => $orderCustomerService->isSelfServiceModeByUrl() ? 'fa-shopping-bag' : 'fa-cart-plus'
    ]);
    echo $this->element('catalog/notAvailableInfo', [
        'product' => $product,
        'stockAvailable' => $attribute->stock_available
    ]);
    echo $this->element('catalog/includeStockProductsInOrdersWithDeliveryRhythmInfoText', [
        'showInfoText' => $isStockProductOrderPossible,
        'keyword' => $orderCustomerService->isSelfServiceModeByUrl() ? $product->ProductIdentifier : null
    ]);

    if ($showProductPrice) {
        echo $pricePerUnitInfoText;
    }

    echo $this->element('catalog/quantityInUnitsInputFieldForSelfService', [
        'pricePerUnitEnabled' => $attribute->unit_product_attribute->price_per_unit_enabled,
        'unitName' => $attribute->unit_product_attribute->name
    ]);

    echo '</div>';
}

// radio buttons for changing attributes
foreach ($preparedProductAttributes as $attribute) {

    $radioButtonLabel = $this->PricePerUnit->getQuantityInUnitsStringForAttributes(
        $attribute->product_attribute_combination->attribute->name,
        $attribute->product_attribute_combination->attribute->can_be_used_as_unit,
        $attribute->unit_product_attribute->price_per_unit_enabled,
        $attribute->unit_product_attribute->quantity_in_units,
        $attribute->unit_product_attribute->name,
        );

    echo '<div class="radio">
              <label class="attribute-button" id="'.'attribute-button-'.$attribute->id_product_attribute.'">
                  <input type="radio" name="product-'.$product->id_product.'" '.($attribute->checked ? 'checked' : '').'>'.
                  $radioButtonLabel.'
              </label>
          </div>';

}