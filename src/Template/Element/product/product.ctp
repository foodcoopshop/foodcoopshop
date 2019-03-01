<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$showProductPrice = (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') && Configure::read('appDb.FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS')) || $appAuth->user();

$isStockProductOrderPossibleInOrdersWithDeliveryRhythms = $this->Html->isStockProductOrderPossibleInOrdersWithDeliveryRhythms(
    $this->request->getSession()->check('Auth.instantOrderCustomer'),
    Configure::read('appDb.FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM'),
    $product['stock_management_enabled'],
    $product['is_stock_product']
);

echo '<div class="product-wrapper">';

    echo '<div class="first-column">';
        $srcLargeImage = $this->Html->getProductImageSrc($product['id_image'], 'thickbox');
        $largeImageExists = preg_match('/de-default/', $srcLargeImage);
if (!$largeImageExists) {
    echo '<a class="lightbox" href="'.$srcLargeImage.'">';
}
echo '<img src="' . $this->Html->getProductImageSrc($product['id_image'], 'home'). '" />';
if (!$largeImageExists) {
    echo '</a>';
}
if ($product['is_new']) {
    echo '<a href="'.$this->Slug->getNewProducts().'" class="image-badge btn btn-success" title="'.__('New').'">
                    <i class="fas fa-star gold"></i> '.__('New').'
                </a>';
}
    echo '</div>';

    echo '<div class="second-column">';
    
    echo '<div class="heading">';
        echo '<h4><a class="product-name" href="'.$this->Slug->getProductDetail($product['id_product'], $product['name']).'">'.$product['name'].'</a></h4>';
    echo '</div>';
    echo '<div class="sc"></div>';

if ($product['description_short'] != '') {
    echo $product['description_short'].'<br />';
}

if ($product['description'] != '') {
    echo $this->Html->link(
        '<i class="fa"></i> '.__('Show_more'),
        'javascript:void(0);',
        [
        'class' => 'toggle-link',
        'title' => __('More_infos_to_product_{0}', [$product['name']]),
        'escape' => false
        ]
    );
    echo '<div class="toggle-content description">'.$product['description'].'</div>';
}

    if ($product['delivery_rhythm_type'] == 'individual' && !$this->Time->isDatabaseDateNotSet($product['delivery_rhythm_order_possible_until'])) {
        echo '<br />' . __('Order_possible_until') . ': ' . $this->Time->getDateFormattedWithWeekday(strtotime($product['delivery_rhythm_order_possible_until']));
    }
    
    if (!$this->request->getSession()->check('Auth.instantOrderCustomer') && $product['delivery_rhythm_type'] != 'individual' && $this->Time->getSendOrderListsWeekday() != $product['delivery_rhythm_send_order_list_weekday']) {
        echo '<span class="last-order-day">';
            echo __('Last_order_day') . ': <b>' . $this->Time->getWeekdayName(
                $this->Time->getNthWeekdayBeforeWeekday(1, $product['delivery_rhythm_send_order_list_weekday'])
            ) . '</b> ' . __('midnight');
        echo '</span>';
    }
    
    echo '<br />'.__('Pickup_day').': ';
    echo '<span class="pickup-day">';
        if ($this->request->getSession()->check('Auth.instantOrderCustomer')) {
            $pickupDayDetailText = __('Instant_order');
        } else {
            $pickupDayDetailText = $this->Html->getDeliveryRhythmString($product['is_stock_product'], $product['delivery_rhythm_type'], $product['delivery_rhythm_count']);
        }
        echo $this->Time->getDateFormattedWithWeekday(strtotime($product['next_delivery_day']));
    echo '</span>';
    echo ' (' . $pickupDayDetailText . ')';
    
    echo '<br />'.__('Manufacturer').': ';
    echo $this->Html->link(
        $product['ManufacturersName'],
        $this->Slug->getManufacturerDetail($product['id_manufacturer'], $product['ManufacturersName'])
    );

    if ($appAuth->isSuperadmin() || ($appAuth->isManufacturer() && $product['id_manufacturer'] == $appAuth->getManufacturerId())) {
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt"></i>',
            $this->Slug->getProductAdmin(($appAuth->isSuperadmin() ? $product['id_manufacturer'] : null), $product['id_product']),
            [
                'class' => 'btn btn-outline-light edit-shortcut-button',
                'title' => __('Edit'),
                'escape' => false
            ]
        );
    }

    echo '</div>';

    echo '<div class="third-column">';

    if (!empty($product['attributes'])) {
        // PRODUCT WITH ATTRIBUTES

        // 1) kick attributes if not available
        $hasCheckedAttribute = false;
        $i = 0;
        $preparedProductAttributes = [];
        foreach ($product['attributes'] as $attribute) {
            if ($attribute['StockAvailables']['quantity'] - $attribute['StockAvailables']['quantity_limit'] > 0) {
                $preparedProductAttributes[] = $attribute;
            }
            $i++;
        }

        // 2) try to define "default on" as checked radio button (if quantity is > 0)
        $i = 0;
        foreach ($preparedProductAttributes as $attribute) {
            $preparedProductAttributes[$i]['checked'] = false;
            if ($attribute['ProductAttributes']['default_on'] == 1) {
                $preparedProductAttributes[$i]['checked'] = true;
                $hasCheckedAttribute = true;
            }
            $i++;
        }

        // make first attribute checked if no attribute is checked
        // (usually if quantity of default attribute is 0)
        if (!$hasCheckedAttribute && !empty($preparedProductAttributes)) {
            $preparedProductAttributes[0]['checked'] = true;
        }

        // every attribute has quantity = 0
        if (empty($preparedProductAttributes)) {
            echo '<p>'.__('Currently_not_on_stock').'.</p>';
        }

        // render remaining attributes (with attribute "checked")
        foreach ($preparedProductAttributes as $attribute) {
            $entityClasses = ['entity-wrapper'];
            if ($attribute['checked']) {
                $entityClasses[] = 'active';
            }
            echo '<div class="'.join(' ', $entityClasses).'" id="entity-wrapper-'.$attribute['ProductAttributes']['id_product_attribute'].'">';
            if ($showProductPrice) {
                echo '<div class="line">';
                $priceHtml =  '<div class="price">' . $this->Number->formatAsCurrency($attribute['ProductAttributes']['gross_price']) . '</div>';
                $pricePerUnitInfoText = '';
                if ($attribute['Units']['price_per_unit_enabled']) {
                    $priceHtml = $this->PricePerUnit->getPricePerUnit($attribute['Units']['price_incl_per_unit'], $attribute['Units']['quantity_in_units'], $attribute['Units']['unit_amount']);
                    $pricePerUnitInfoText = $this->PricePerUnit->getPricePerUnitInfoText($attribute['Units']['price_incl_per_unit'], $attribute['Units']['unit_name'], $attribute['Units']['unit_amount']);
                }
                echo $priceHtml;
                if (!empty($attribute['DepositProductAttributes']['deposit'])) {
                    echo '<div class="deposit">+ <b>'. $this->Number->formatAsCurrency($attribute['DepositProductAttributes']['deposit']) . '</b> '.__('deposit').'</div>';
                }
                if (!$this->request->getSession()->check('Auth.instantOrderCustomer') && !empty($attribute['timebased_currency_money_incl'])) {
                    echo $this->element('timebasedCurrency/addProductInfo', [
                        'manufacturerLimitReached' => $attribute['timebased_currency_manufacturer_limit_reached'],
                        'class' => 'timebased-currency-product-info',
                        'money' => $attribute['timebased_currency_money_incl'],
                        'seconds' => $attribute['timebased_currency_seconds'],
                        'labelPrefix' => __('from_which_{0}_%', [$product['timebased_currency_max_percentage']]) . ' '
                    ]);
                }
                echo '<div class="tax">'. $this->Number->formatAsCurrency($attribute['ProductAttributes']['tax']) . '</div>';
                echo '</div>';
            }
            if (! Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
                echo $this->element('product/hiddenProductIdField', ['productId' => $product['id_product'] . '-' . $attribute['ProductAttributes']['id_product_attribute']]);
                echo $this->element('product/amountWrapper', [
                    'stockAvailable' => $attribute['StockAvailables'],
                    'hideAmountSelector' => $isStockProductOrderPossibleInOrdersWithDeliveryRhythms
                ]);
                echo $this->element('product/cartButton', [
                    'productId' => $product['id_product'] . '-' . $attribute['ProductAttributes']['id_product_attribute'],
                    'stockAvailableQuantity' => $attribute['StockAvailables']['quantity'],
                    'stockAvailableQuantityLimit' => $attribute['StockAvailables']['quantity_limit'],
                    'hideButton' => $isStockProductOrderPossibleInOrdersWithDeliveryRhythms
                ]);
                echo $this->element('product/notAvailableInfo', ['stockAvailable' => $attribute['StockAvailables']]);
                echo $this->element('product/includeStockProductsInOrdersWithDeliveryRhythmInfoText', [
                    'showInfoText' => $isStockProductOrderPossibleInOrdersWithDeliveryRhythms
                ]);
            }
            if ($showProductPrice) {
                echo $pricePerUnitInfoText;
            }
            echo '</div>';
        }

        // radio buttons for changing attributes
        foreach ($preparedProductAttributes as $attribute) {

            $radioButtonLabel = $this->PricePerUnit->getQuantityInUnitsStringForAttributes(
                $attribute['ProductAttributeCombinations']['Attributes']['name'],
                $attribute['ProductAttributeCombinations']['Attributes']['can_be_used_as_unit'],
                $attribute['Units']['price_per_unit_enabled'],
                $attribute['Units']['quantity_in_units'],
                $attribute['Units']['unit_name']
            );

            echo '<div class="radio">
                      <label class="attribute-button" id="'.'attribute-button-'.$attribute['ProductAttributes']['id_product_attribute'].'">
                          <input type="radio" name="product-'.$product['id_product'].'" '.($attribute['checked'] ? 'checked' : '').'>'.
                               $radioButtonLabel.'
                      </label>
                  </div>';

        }
    } else {
        // PRODUCT WITHOUT ATTRIBUTES
        echo '<div class="entity-wrapper active">';
        if ($showProductPrice) {
            echo '<div class="line">';
            $priceHtml =  '<div class="price">' . $this->Number->formatAsCurrency($product['gross_price']) . '</div>';
            $pricePerUnitInfoText = '';
            if ($product['price_per_unit_enabled']) {
                $priceHtml = $this->PricePerUnit->getPricePerUnit($product['price_incl_per_unit'], $product['quantity_in_units'], $product['unit_amount']);
                $pricePerUnitInfoText = $this->PricePerUnit->getPricePerUnitInfoText($product['price_incl_per_unit'], $product['unit_name'], $product['unit_amount']);
            }
            echo $priceHtml;
                if ($product['deposit']) {
                    echo '<div class="deposit">+ <b>' . $this->Number->formatAsCurrency($product['deposit']).'</b> '.__('deposit').'</div>';
                }
                echo '</div>';
                if (!$this->request->getSession()->read('Auth.instantOrderCustomer') && !empty($product['timebased_currency_money_incl'])) {
                    echo $this->element('timebasedCurrency/addProductInfo', [
                        'manufacturerLimitReached' => $product['timebased_currency_manufacturer_limit_reached'],
                        'class' => 'timebased-currency-product-info',
                        'money' => $product['timebased_currency_money_incl'],
                        'seconds' => $product['timebased_currency_seconds'],
                        'labelPrefix' => __('from_which_{0}_%', [$product['timebased_currency_max_percentage']]) . ' '
                    ]);
                }
                echo '<div class="tax">'. $this->Number->formatAsCurrency($product['tax']) . '</div>';
        }
        
        if (! Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
            echo $this->element('product/hiddenProductIdField', ['productId' => $product['id_product']]);
            echo $this->element('product/amountWrapper', [
                'stockAvailable' => $product,
                'hideAmountSelector' => $isStockProductOrderPossibleInOrdersWithDeliveryRhythms
            ]);
            echo $this->element('product/cartButton', [
                'productId' => $product['id_product'],
                'stockAvailableQuantity' => $product['quantity'],
                'stockAvailableQuantityLimit' => $product['quantity_limit'],
                'hideButton' => $isStockProductOrderPossibleInOrdersWithDeliveryRhythms
            ]);
            echo $this->element('product/notAvailableInfo', ['stockAvailable' => $product]);
            echo $this->element('product/includeStockProductsInOrdersWithDeliveryRhythmInfoText', [
                'showInfoText' => $isStockProductOrderPossibleInOrdersWithDeliveryRhythms
            ]);
        }
        if ($showProductPrice) {
            echo $pricePerUnitInfoText;
        }
        echo '</div>';

        $unityStrings = [];
        if ($product['unity'] != '') {
            $unityStrings[] = $product['unity'];
        }
        $unitString = $this->PricePerUnit->getQuantityInUnits($product['price_per_unit_enabled'], $product['quantity_in_units'], $product['unit_name']);
        if ($unitString != '') {
            $unityStrings[] = $unitString;
        }
        if (!empty($unityStrings)) {
            echo '<div class="unity">'.__('Unit').': <span class="value">' . join(', ', $unityStrings).'</span></div>';
        }
    }

    echo '</div>';

    echo '</div>';
