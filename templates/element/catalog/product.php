<?php

declare(strict_types=1);

use Cake\Core\Configure;
use App\Services\OrderCustomerService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$classes = ['pw'];
$deliveryBreakManufacturerEnabled = $product->delivery_break_enabled ?? false;
if ($deliveryBreakManufacturerEnabled) {
    $classes[] = 'delivery-break-enabled';
}

$isStockProductOrderPossible = $this->Html->isStockProductOrderPossible(
    OrderCustomerService::isOrderForDifferentCustomerMode(),
    OrderCustomerService::isSelfServiceModeByUrl(),
    (bool) Configure::read('appDb.FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM'),
    (bool) $product->manufacturer->stock_management_enabled,
    (bool) $product->is_stock_product,
);

echo '<div class="' . join(' ', $classes) . '" id="pw-' . $product->id_product . '">';

    echo '<div class="fcs-badges">';
        if ($product->is_new) {
            echo '<div class="fcs-badge" title="Neu">';
                echo '<img src="/img/badge-ring-light-mode.svg" />';
                echo '<i class="fas gold fa-star"></i>';
            echo '</div>';
        }
        echo '<div class="fcs-badge" title="Vorhandene Stück">';
            echo '<img src="/img/badge-ring-light-mode.svg" />';
            echo '<span>' . rand(0, 100) . 'x</span>';
        echo '</div>';
        echo '<div class="fcs-badge" title="Lagerprodukt">';
            echo '<img src="/img/badge-ring-light-mode.svg" />';
            echo '<i class="fas ok fa-store"></i>';
        echo '</div>';
        echo '<div class="fcs-badge" title="Bio">';
            echo '<img src="/img/badge-ring-light-mode.svg" />';
            echo '<i class="fas ok fa-leaf"></i>';
        echo '</div>';
    echo '</div>';

    echo $this->element('catalog/productImage', [
        'product' => $product,
    ]);

    echo '<div class="content">';

        echo '<h3>';
            if ($showProductDetailLink) {
                echo '<a class="product-name" href="'.$this->Slug->getProductDetail($product->id_product, $product->name).'">'.$product->name.'</a>';
            } else {
                echo $product->name;
            }
        echo '</h3>';
        if (Configure::read('app.showManufacturerListAndDetailPage')) {
            echo '<h4>';
                echo $product->manufacturer->name;
            echo '</h4>';
        }
        echo '<div class="price-wrapper">';
            echo '<div class="price">';
                echo $this->Number->formatAsCurrency(rand(100, 10000) / 100);
            echo '</div>';
        echo '</div>';

    echo '</div>';

    echo '<div class="actions">';

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
                    $preparedProductAttributes[$attribute->id_product_attribute] = $radioButtonLabel;
                }
            }
            if (!empty($preparedProductAttributes)) {
                echo $this->Form->control('attributes.' . $product->id_product, [
                    'type' => 'select',
                    'label' => false,
                    'options' => $preparedProductAttributes,
                ]);
            }
        echo '</div>';

        echo $this->element('catalog/cartButton', [
            'deliveryBreakManufacturerEnabled' => $product->delivery_break_enabled ?? false,
            'productId' => $product->id_product,
            'product' => $product,
            'stockAvailableQuantity' => $product->stock_available->quantity,
            'stockAvailableQuantityLimit' => $product->stock_available->quantity_limit,
            'stockAvailableAlwaysAvailable' => $product->stock_available->always_available,
            'hideButton' => $isStockProductOrderPossible,
            'cartButtonLabel' => OrderCustomerService::isSelfServiceModeByUrl() ? __('Move_in_shopping_bag') : __('Move_in_cart'),
            'cartButtonIcon' => OrderCustomerService::isSelfServiceModeByUrl() ? 'fa-shopping-bag' : 'fa-cart-plus'
        ]);

    echo '</div>';

echo '</div>';
