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



echo '<div class="' . join(' ', $classes) . '" id="pw-' . $product->id_product . '" data-product-link="'.$this->Slug->getProductDetail($product->id_product, $product->name).'">';

    echo $this->element('catalog/badges/badges', [
        'product' => $product,
    ]);

    echo $this->element('catalog/productImage', [
        'product' => $product,
    ]);

    echo '<div class="content">';
        echo '<h3>' . $product->name . '</h3>';
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

        echo $this->element('catalog/units', [
            'product' => $product,
        ]);

        echo $this->element('catalog/cartButton', [
            'deliveryBreakManufacturerEnabled' => $product->delivery_break_enabled ?? false,
            'productId' => $product->id_product,
            'product' => $product,
            'stockAvailableQuantity' => $product->stock_available->quantity,
            'stockAvailableQuantityLimit' => $product->stock_available->quantity_limit,
            'stockAvailableAlwaysAvailable' => $product->stock_available->always_available,
            'hideButton' => $isStockProductOrderPossible,
            'cartButtonLabel' => OrderCustomerService::isSelfServiceModeByUrl() ? __('Shopping_bag') : __('Cart'),
            'cartButtonIcon' => OrderCustomerService::isSelfServiceModeByUrl() ? 'fa-shopping-bag' : 'fa-cart-plus'
        ]);

    echo '</div>';

echo '</div>';
