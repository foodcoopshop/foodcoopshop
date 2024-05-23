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

use Cake\Core\Configure;

?>
<div class="amount-wrapper">

    <?php if (!$hideAmountSelector) { ?>
        <span class="loi"><?php echo __('Amount'); ?></span>
        <input name="amount" value="1" type="text" />
        <a class="as as-plus" href="javascript:void(0);">
            <i class="fas fa-plus-circle"></i>
        </a>
        <a class="as as-minus" href="javascript:void(0);">
            <i class="fas fa-minus-circle"></i>
        </a>
    <?php } else { ?>
        <input name="amount" value="1" type="hidden" />
    <?php } ?>

    <?php
        if (Configure::read('app.showOrderedProductsTotalAmountInCatalog') && !is_null($orderedTotalAmount)) {
            if ($product->next_delivery_day != 'delivery-rhythm-triggered-delivery-break') {
                $pickupDayString = $this->Time->getDateFormattedWithWeekday(strtotime($product->next_delivery_day));
                $tooltip = __('{0}_times_ordered_for_pickup_day_{1}.', [
                    '<b>' . $orderedTotalAmount . '</b>',
                    '<b>' . $pickupDayString . '</b>',
                ]);
                echo '<div title="' . $tooltip . '" class="ordered-products-total-amount">' . $orderedTotalAmount . '</div>';
            }
        }
    ?>

    <?php

        if (!$hideIsStockProductIcon && $product->is_stock_product && $product->manufacturer->stock_management_enabled) {
            echo '<i class="is-stock-product fa fas fa-store" title="'.__('Stock_product').'"></i>';
        }

        $availableQuantity = $stockAvailable->quantity - $stockAvailable->quantity_limit;
        if ((($product->is_stock_product && $product->manufacturer->stock_management_enabled) || !$stockAvailable->always_available) && $availableQuantity <= Configure::read('appDb.FCS_PRODUCT_AVAILABILITY_LOW')) { ?>
            <span <?php echo !$hideAmountSelector ? 'class="below-input availibility"' : ''; ?>>(<?php echo $availableQuantity . ' ' . __('available'); ?>)</span>
    <?php } ?>

</div>