<?php
declare(strict_types=1);

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

echo '<div class="ew active">';
if ($showProductPrice) {
    echo '<div class="line">';
    $priceHtml =  '<div class="price" title="' . __('Tax_rate') . ': ' . $this->Number->formatTaxRate($product->tax->rate) . '%">' . $this->Number->formatAsCurrency($product->gross_price) . '</div>';
    $pricePerUnitInfoText = '';
    if ($product->unit_product->price_per_unit_enabled) {
        $priceHtml = $this->PricePerUnit->getPricePerUnitForFrontend($product->unit_product->price_incl_per_unit, $product->unit_product->quantity_in_units, $product->unit_product->amount, $product->tax->rate);
        $pricePerUnitInfoText = $this->PricePerUnit->getPricePerUnitInfoText(
            $product->unit_product->price_incl_per_unit,
            $product->unit_product->name,
            $product->unit_product->amount,
            !$appAuth->isSelfServiceModeByUrl()
            );
    }
    echo $priceHtml;
    if ($product->deposit_product->deposit) {
        echo '<div class="deposit">+ <b>' . $this->Number->formatAsCurrency($product->deposit_product->deposit).'</b> '.__('deposit').'</div>';
    }
    echo '</div>';
    echo '<div class="tax">'. $this->Number->formatAsCurrency($product->calculated_tax) . '</div>';
} else {
    // Cart.js::initAddToCartButton() needs the following elements!
    echo '<div class="price hide">' . $this->Number->formatAsCurrency(0) . '</div>';
    echo '<div class="tax hide">'. $this->Number->formatAsCurrency(0) . '</div>';
}

echo $this->element('catalog/hiddenProductIdField', ['productId' => $product->id_product]);
echo $this->element('catalog/amountWrapper', [
    'product' => $product,
    'orderedTotalAmount' => $product->ordered_total_amount ?? null,
    'stockAvailable' => $product->stock_available,
    'hideAmountSelector' => $isStockProductOrderPossible,
    'hideIsStockProductIcon' => $appAuth->isSelfServiceModeByUrl(),
]);
echo $this->element('catalog/cartButton', [
    'deliveryBreakManufacturerEnabled' => $product->delivery_break_enabled ?? false,
    'productId' => $product->id_product,
    'product' => $product,
    'stockAvailableQuantity' => $product->stock_available->quantity,
    'stockAvailableQuantityLimit' => $product->stock_available->quantity_limit,
    'stockAvailableAlwaysAvailable' => $product->stock_available->always_available,
    'hideButton' => $isStockProductOrderPossible,
    'cartButtonLabel' => $appAuth->isSelfServiceModeByUrl() ? __('Move_in_shopping_bag') : __('Move_in_cart'),
    'cartButtonIcon' => $appAuth->isSelfServiceModeByUrl() ? 'fa-shopping-bag' : 'fa-cart-plus'
]);
echo $this->element('catalog/notAvailableInfo', [
    'product' => $product,
    'stockAvailable' => $product->stock_available,
]);
echo $this->element('catalog/includeStockProductsInOrdersWithDeliveryRhythmInfoText', [
    'showInfoText' => $isStockProductOrderPossible,
    'keyword' => $appAuth->isSelfServiceModeByUrl() ? $product->ProductIdentifier : null
]);

if ($showProductPrice) {
    echo $pricePerUnitInfoText;
}
echo $this->element('catalog/quantityInUnitsInputFieldForSelfService', [
    'pricePerUnitEnabled' => $product->unit_product->price_per_unit_enabled,
    'unitName' => $product->unit_product->name,
]);

echo '</div>';

$unityStrings = [];
if ($product->unity != '') {
    $unityStrings[] = $product->unity;
}
$unitString = $this->PricePerUnit->getQuantityInUnits($product->unit_product->price_per_unit_enabled, $product->unit_product->quantity_in_units, $product->unit_product->name);
if ($unitString != '') {
    $unityStrings[] = $unitString;
}
if (!empty($unityStrings)) {
    echo '<div class="unity">'.__('Unit').': <span class="value">' . join(', ', $unityStrings).'</span></div>';
}