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
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$productQuantityService = new ProductQuantityService();
$isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($product, $product->unit);
$unitName = !empty($product->unit) ? $product->unit->name : '';
$rowClasses = ['amount'];
if ($product->stock_available->quantity < 0) {
    $rowClasses[] = 'negative-stock';
}
if ($product->stock_available->quantity == 0) {
    $rowClasses[] = 'not-available';
}
if ($product->stock_available->quantity > 0 && $product->stock_available->sold_out_limit > 0 && $product->stock_available->quantity < $product->stock_available->sold_out_limit) {
    $rowClasses[] = 'below-minimum-amount';
}

$style = ($alignRight ?? false) ? ' style="text-align:right;"' : '';
echo '<td class="' . join(' ', $rowClasses) . '"' . $style . '>';

    echo '<div class="td-spanned">';

        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light product-quantity-edit-button',
                'title' => __('change_amount'),
                'escape' => false,
            ],
        );

        echo '<span class="quantity-for-dialog right">' . $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $product->stock_available->quantity, $unitName) . '</span>';

        if ($product->stock_available->quantity_limit != 0) {
            $formattedQuantityLimit = $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $product->stock_available->quantity_limit, $unitName);
            echo '<i style="display: none;" class="small quantity-limit-for-dialog">' . $formattedQuantityLimit . '</i>';
        }
        if (is_null($product->stock_available->sold_out_limit) || $product->stock_available->sold_out_limit != 0) {
            echo ' <i class="small sold-out-limit-for-dialog">';
            if (is_null($product->stock_available->sold_out_limit)) {
                echo '<i class="fas fa-times" title="' . __('No_email_notifications_are_sent_for_this_product.') . '"></i>';
            } else {
                $formattedSoldOutLimit = $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $product->stock_available->sold_out_limit, $unitName);
                echo $formattedSoldOutLimit;
            }
            echo '</i>';
        }

        echo '</div>';

echo '</td>';

?>
