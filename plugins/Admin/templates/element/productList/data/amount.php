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
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$available = true;
$productQuantityService = new ProductQuantityService();
$isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($product, $product->unit);
$unitName = !empty($product->unit) ? $product->unit->name : '';

echo $this->element('productList/data/unitData', [
    'product' => $product,
]);

if (empty($product->product_attributes) && $product->is_stock_product && $product->manufacturer->stock_management_enabled) {
    echo $this->element('productList/data/stockProductAmount', [
        'product' => $product,
        'alignRight' => $alignRight ?? false,
    ]);
    return;
}

if (empty($product->product_attributes)) {
    if ($product->stock_available->quantity <= 0 && !$product->stock_available->always_available) {
        $available = false;
    }
}

$rowClasses = ['amount'];
if (!$available) {
    $rowClasses[] = 'not-available';
}
echo '<td class="' . join(' ', $rowClasses) . '">';

    if (empty($product->product_attributes)) {
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light product-quantity-edit-button',
                'title' => __('change_amount'),
                'escape' => false
            ]
        );

        $elementsToRender = [];
        $alwaysAvailable = (bool) $product->stock_available->always_available;
        $hasDefaultQuantityAfterSendingOrderLists = !is_null($product->stock_available->default_quantity_after_sending_order_lists);

        if ($alwaysAvailable) {
            $elementsToRender[] = '<i class="always-available fas fa-infinity ok" title="'.__('This_product_is_always_available.').'"></i>';
        }

        $formattedQuantity = $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $product->stock_available->quantity, $unitName);

        $elementsToRender[] =
        '<span class="quantity-for-dialog'.($alwaysAvailable ? ' hide' : '').'">' .
                 $formattedQuantity .
            '</span>';

        $elementsToRender[] =
        '<span class="default-quantity-after-sending-order-lists-for-dialog'.($alwaysAvailable || !$hasDefaultQuantityAfterSendingOrderLists ? ' hide' : '').'">' .
            ($hasDefaultQuantityAfterSendingOrderLists ?
                $this->Number->formatAsDecimal($product->stock_available->default_quantity_after_sending_order_lists, 0)
            : '') .
         '</span>';

        echo join('', $elementsToRender);
    }

echo '</td>';

?>