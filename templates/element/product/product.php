<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
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
    $product->stock_management_enabled,
    $product->is_stock_product,
);

echo '<div class="pw" id="pw-' . $product->id_product . '">';

    echo '<div class="c1">';

        $productImageData = $this->Html->getProductImageSrcWithManufacturerImageFallback(
            $product->id_image,
            $product->id_manufacturer,
        );

        if ($productImageData['productImageLargeExists']) {
            echo '<a class="open-with-modal" href=javascript:void(0); data-modal-title="' . h($product->name) . ', ' . $product->manufacturer->name . '" data-modal-image="'.$productImageData['productImageLargeSrc'].'">';
        }
        echo '<img class="lazyload" data-src="' . $productImageData['productImageSrc']. '" />';
        if ($productImageData['productImageLargeExists']) {
            echo '</a>';
        }
    if ($product->is_new) {
        $isNewSrc = 'javascript:void(0);';
        if ($showIsNewBadgeAsLink) {
            $isNewSrc = $this->Slug->getNewProducts();
        }
        echo '<a href="'.$isNewSrc.'" class="image-badge btn btn-outline-light" title="'.__('New').'">';
            echo '<i class="fas fa-star gold"></i> '.__('New');
        echo '</a>';
    }
    echo '</div>';

    echo '<div class="c2">';

    echo '<div class="heading">';
        echo '<h4>';
        if ($showProductDetailLink) {
            echo '<a class="product-name" href="'.$this->Slug->getProductDetail($product->id_product, $product->name).'">'.$product->name.'</a>';
        } else {
            echo $product->name;
        }
        echo '</h4>';
    echo '</div>';

if ($product->description_short != '') {
    echo $product->description_short.'<br />';
}

if ($product->description != '') {
    echo $this->Html->link(
        '<i class="fa"></i> '.__('Show_more'),
        'javascript:void(0);',
        [
        'class' => 'toggle-link',
        'title' => __('More_infos_to_product_{0}', [h($product->name)]),
        'escape' => false
        ]
    );
    echo '<div class="toggle-content description">'.$product->description.'</div>';
}

    if (!Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {

        if (!$appAuth->isOrderForDifferentCustomerMode() && !($product->stock_management_enabled && $product->is_stock_product)) {

            $lastOrderDay = $this->Time->getLastOrderDay(
                $product->next_delivery_day,
                $product->delivery_rhythm_type,
                $product->delivery_rhythm_count,
                $product->delivery_rhythm_send_order_list_weekday,
                $product->delivery_rhythm_order_possible_until,
            );

            if (!($product->delivery_rhythm_type == 'week'
                && $product->delivery_rhythm_count == 1
                && $this->Time->getSendOrderListsWeekday() == $product->delivery_rhythm_send_order_list_weekday
                )
                && $lastOrderDay != ''
                ) {
                    echo '<span class="last-order-day">';
                    echo '<br />' . __('Order_possible_until') . ': ' . $this->Time->getDateFormattedWithWeekday(strtotime($lastOrderDay));
                    echo '</span>';
            }

        }

        if (!$appAuth->isSelfServiceModeByUrl()) {
            echo '<br />'.__('Pickup_day').': ';
        }
        echo '<span class="pickup-day">';
            if ($appAuth->isOrderForDifferentCustomerMode()) {
                $pickupDayDetailText = __('Instant_order');
            } else {
                $pickupDayDetailText = $this->Html->getDeliveryRhythmString(
                    $product->is_stock_product && $product->stock_management_enabled,
                    $product->delivery_rhythm_type,
                    $product->delivery_rhythm_count,
                );
            }
            if ($product->next_delivery_day != 'delivery-rhythm-triggered-delivery-break') {
                echo $this->Time->getDateFormattedWithWeekday(strtotime($product->next_delivery_day));
            }
        echo '</span>';
        if (!$appAuth->isSelfServiceModeByUrl()) {
            echo ' (' . $pickupDayDetailText . ')';
        }
        if (!$appAuth->isSelfServiceModeByUrl() && !$appAuth->isOrderForDifferentCustomerMode()) {
            if (
                $product->next_delivery_day != 'delivery-rhythm-triggered-delivery-break'
                && strtotime($product->next_delivery_day) != $this->Time->getDeliveryDayByCurrentDay()
            ) {
                $weeksAsFloat = (strtotime($product->next_delivery_day) - strtotime(date($this->MyTime->getI18Format('DateShortAlt')))) / 24/60/60;
                $fullWeeks = (int) ($weeksAsFloat / 7);
                $days = $weeksAsFloat % 7;
                if ($days == 0) {
                    echo ' - <b>'. __('{0,plural,=1{1_week} other{#_weeks}}', [$fullWeeks]) . '</b>';
                } else {
                    echo ' - <b>'. __('{0,plural,=1{1_week} other{#_weeks}} {1,plural,=1{and_1_day} other{and_#_days}}', [$fullWeeks, $days]) . '</b>';
                }
            }
        }
    }

    if (Configure::read('app.showManufacturerListAndDetailPage')) {
        echo '<br />'.__('Manufacturer').': ';
        if ($showManufacturerDetailLink) {
            echo $this->Html->link(
                $product->manufacturer->name,
                $this->Slug->getManufacturerDetail($product->id_manufacturer, $product->manufacturer->name),
                [
                    'escape' => false
                ]
            );
        } else {
            echo $product->manufacturer->name;
        }
    }

    if (!$appAuth->isOrderForDifferentCustomerMode()) {
        if ($appAuth->isSuperadmin() || ($appAuth->isManufacturer() && $product->id_manufacturer == $appAuth->getManufacturerId())) {
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt"></i>',
                $this->Slug->getProductAdmin(($appAuth->isSuperadmin() ? $product->id_manufacturer : null), $product->id_product),
                [
                    'class' => 'btn btn-outline-light edit-shortcut-button',
                    'title' => __('Edit'),
                    'escape' => false
                ]
            );
        }
    }

    echo '</div>';

    echo '<div class="c3">';

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

    echo '</div>';

    echo '</div>';
