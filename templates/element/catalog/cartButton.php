<?php
declare(strict_types=1);

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

if ($hideButton) {
    return;
}
?>

<div class="line">

    <?php
    $availableQuantity = $stockAvailableQuantity;
    $disabledClass = '';

    if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
        $availableQuantity = $stockAvailableQuantity - $stockAvailableQuantityLimit;
    }
    if (((($product->is_stock_product && $product->manufacturer->stock_management_enabled) || !$stockAvailableAlwaysAvailable) && $availableQuantity <= 0)
        || $deliveryBreakEnabled) {

        $disabledClass = 'disabled ';

        if ($deliveryBreakEnabled) {
            $cartButtonIcon = 'fa-times';
            $cartButtonLabel = __('Delivery_break') . '!';
        }

    }
    ?>

    <a id="btn-cart-<?php echo $productId; ?>" class="<?php echo $disabledClass; ?>btn btn-outline-light btn-cart" href="javascript:void(0);">
        <i class="fas fa-fw fa-lg <?php echo $cartButtonIcon; ?>"></i> <?php echo $cartButtonLabel; ?>
    </a>

</div>
