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

use Cake\Core\Configure;

$showProductPrice = (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') && Configure::read('appDb.FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS')) || $identity->isLoggedIn();

$isStockProductOrderPossible = $this->Html->isStockProductOrderPossible(
    $orderCustomerService->isOrderForDifferentCustomerMode(),
    $orderCustomerService->isSelfServiceModeByUrl(),
    Configure::read('appDb.FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM'),
    $product->manufacturer->stock_management_enabled,
    $product->is_stock_product,
);

if (!empty($product->product_attributes)) {
    echo $this->element('catalog/productWithAttributes', [
        'product' => $product,
        'showProductPrice' => $showProductPrice,
        'isStockProductOrderPossible' => $isStockProductOrderPossible,
    ]);
} else {
    echo $this->element('catalog/productWithoutAttributes', [
        'product' => $product,
        'showProductPrice' => $showProductPrice,
        'isStockProductOrderPossible' => $isStockProductOrderPossible,
    ]);
}
