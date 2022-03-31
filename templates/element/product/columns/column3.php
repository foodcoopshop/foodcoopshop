<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$showProductPrice = (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') && Configure::read('appDb.FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS')) || $appAuth->user();

$isStockProductOrderPossible = $this->Html->isStockProductOrderPossible(
    $appAuth->isOrderForDifferentCustomerMode(),
    $appAuth->isSelfServiceModeByUrl(),
    Configure::read('appDb.FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM'),
    $product->manufacturer->stock_management_enabled,
    $product->is_stock_product,
);

if (!empty($product->product_attributes)) {

    // PRODUCT WITH ATTRIBUTES

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
            $priceHtml =  '<div class="price" title="' . __('Tax_rate') . ': ' . $this->Number->formatTaxRate($product->tax->rate) . '%">' . $this->Number->formatAsCurrency($attribute->gross_price) . '</div>';
            $pricePerUnitInfoText = '';
            if ($attribute->unit_product_attribute->price_per_unit_enabled) {
                $priceHtml = $this->PricePerUnit->getPricePerUnitForFrontend($attribute->unit_product_attribute->price_incl_per_unit, $attribute->unit_product_attribute->quantity_in_units, $attribute->unit_product_attribute->amount, $product->tax->rate);
                $pricePerUnitInfoText = $this->PricePerUnit->getPricePerUnitInfoText(
                    $attribute->unit_product_attribute->price_incl_per_unit,
                    $attribute->unit_product_attribute->name,
                    $attribute->unit_product_attribute->amount,
                    !$appAuth->isSelfServiceModeByUrl()
                    );
            }
            echo $priceHtml;
            if (!empty($attribute->deposit_product_attribute->deposit)) {
                echo '<div class="deposit">+ <b>'. $this->Number->formatAsCurrency($attribute->deposit_product_attribute->deposit) . '</b> '.__('deposit').'</div>';
            }

            if (!$appAuth->isOrderForDifferentCustomerMode() && !empty($attribute->timebased_currency_money_incl)) {
                echo $this->element('timebasedCurrency/addProductInfo', [
                    'manufacturerLimitReached' => $attribute->timebased_currency_manufacturer_limit_reached,
                    'class' => 'timebased-currency-product-info',
                    'money' => $attribute->timebased_currency_money_incl,
                    'seconds' => $attribute->timebased_currency_seconds,
                    'labelPrefix' => __('from_which_{0}_%', [$product->manufacturer->timebased_currency_max_percentage]) . ' '
                ]);
            }

            echo '<div class="tax">'. $this->Number->formatAsCurrency($attribute->calculated_tax) . '</div>';
            echo '</div>';
        } else {
            // Cart.js::initAddToCartButton() needs the following elements!
            echo '<div class="price hide">' . $this->Number->formatAsCurrency(0) . '</div>';
            echo '<div class="tax hide">'. $this->Number->formatAsCurrency(0) . '</div>';
        }
        echo $this->element('product/hiddenProductIdField', ['productId' => $product->id_product . '-' . $attribute->id_product_attribute]);
        echo $this->element('product/amountWrapper', [
            'product' => $product,
            'stockAvailable' => $attribute->stock_available,
            'hideAmountSelector' => $isStockProductOrderPossible
        ]);
        echo $this->element('product/cartButton', [
            'deliveryBreakEnabled' => $product->delivery_break_enabled ?? false,
            'productId' => $product->id_product . '-' . $attribute->id_product_attribute,
            'product' => $product,
            'stockAvailableQuantity' => $attribute->stock_available->quantity,
            'stockAvailableQuantityLimit' => $attribute->stock_available->quantity_limit,
            'stockAvailableAlwaysAvailable' => $attribute->stock_available->always_available,
            'hideButton' => $isStockProductOrderPossible,
            'cartButtonLabel' => $appAuth->isSelfServiceModeByUrl() ? __('Move_in_shopping_bag') : __('Move_in_cart'),
            'cartButtonIcon' => $appAuth->isSelfServiceModeByUrl() ? 'fa-shopping-bag' : 'fa-cart-plus'
        ]);
        echo $this->element('product/notAvailableInfo', [
            'product' => $product,
            'stockAvailable' => $attribute->stock_available
        ]);
        echo $this->element('product/includeStockProductsInOrdersWithDeliveryRhythmInfoText', [
            'showInfoText' => $isStockProductOrderPossible,
            'keyword' => $appAuth->isSelfServiceModeByUrl() ? $product->ProductIdentifier : null
        ]);

        if ($showProductPrice) {
            echo $pricePerUnitInfoText;
        }

        echo $this->element('product/quantityInUnitsInputFieldForSelfService', [
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
} else {
    // PRODUCT WITHOUT ATTRIBUTES
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
        if (!$this->request->getSession()->read('Auth.orderCustomer') && !empty($product->timebased_currency_money_incl)) {
            echo $this->element('timebasedCurrency/addProductInfo', [
                'manufacturerLimitReached' => $product->timebased_currency_manufacturer_limit_reached,
                'class' => 'timebased-currency-product-info',
                'money' => $product->timebased_currency_money_incl,
                'seconds' => $product->timebased_currency_seconds,
                'labelPrefix' => __('from_which_{0}_%', [$product->manufacturer->timebased_currency_max_percentage]) . ' '
            ]);
        }
        echo '<div class="tax">'. $this->Number->formatAsCurrency($product->calculated_tax) . '</div>';
    } else {
        // Cart.js::initAddToCartButton() needs the following elements!
        echo '<div class="price hide">' . $this->Number->formatAsCurrency(0) . '</div>';
        echo '<div class="tax hide">'. $this->Number->formatAsCurrency(0) . '</div>';
    }

    echo $this->element('product/hiddenProductIdField', ['productId' => $product->id_product]);
    echo $this->element('product/amountWrapper', [
        'product' => $product,
        'stockAvailable' => $product->stock_available,
        'hideAmountSelector' => $isStockProductOrderPossible,
    ]);
    echo $this->element('product/cartButton', [
        'deliveryBreakEnabled' => $product->delivery_break_enabled ?? false,
        'productId' => $product->id_product,
        'product' => $product,
        'stockAvailableQuantity' => $product->stock_available->quantity,
        'stockAvailableQuantityLimit' => $product->stock_available->quantity_limit,
        'stockAvailableAlwaysAvailable' => $product->stock_available->always_available,
        'hideButton' => $isStockProductOrderPossible,
        'cartButtonLabel' => $appAuth->isSelfServiceModeByUrl() ? __('Move_in_shopping_bag') : __('Move_in_cart'),
        'cartButtonIcon' => $appAuth->isSelfServiceModeByUrl() ? 'fa-shopping-bag' : 'fa-cart-plus'
    ]);
    echo $this->element('product/notAvailableInfo', [
        'product' => $product,
        'stockAvailable' => $product->stock_available,
    ]);
    echo $this->element('product/includeStockProductsInOrdersWithDeliveryRhythmInfoText', [
        'showInfoText' => $isStockProductOrderPossible,
        'keyword' => $appAuth->isSelfServiceModeByUrl() ? $product->ProductIdentifier : null
    ]);

    if ($showProductPrice) {
        echo $pricePerUnitInfoText;
    }
    echo $this->element('product/quantityInUnitsInputFieldForSelfService', [
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
}
