<?php
declare(strict_types=1);

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
$belowMinimumAmount = false;

if (empty($product->product_attributes)) {
    if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
        if ($product->stock_available->quantity <= 0) {
            $available = false;
        }
        if ($product->stock_available->sold_out_limit > 0 && $product->stock_available->quantity < $product->stock_available->sold_out_limit) {
            $belowMinimumAmount = true;
        }
    } else {
        if ($product->stock_available->quantity <= 0 && !$product->stock_available->always_available) {
            $available = false;
        }
    }
}

$rowClasses = ['amount'];
if (!$available) {
    $rowClasses[] = 'not-available';
}
if ($available && $belowMinimumAmount) {
    $rowClasses[] = 'below-minimum-amount';
}

echo '<td class="' . join(' ', $rowClasses) . '">';

    if (empty($product->product_attributes)) {
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light product-quantity-edit-button',
                'title' => __d('admin', 'change_amount'),
                'escape' => false
            ]
        );

        $elementsToRender = [];

        if (!($product->is_stock_product && $product->manufacturer->stock_management_enabled) && $product->stock_available->always_available) {
            $elementsToRender[] = '<i class="always-available fas fa-infinity ok" title="'.__d('admin', 'This_product_is_always_available.').'"></i>';
        }

        $elementsToRender[] =
        '<span class="quantity-for-dialog'.(!($product->is_stock_product && $product->manufacturer->stock_management_enabled) && $product->stock_available->always_available ? ' hide' : '').'">' .
                 $this->Number->formatAsDecimal($product->stock_available->quantity, 0) .
            '</span>';

        $elementsToRender[] =
        '<span class="default-quantity-after-sending-order-lists-for-dialog'.(($product->is_stock_product && $product->manufacturer->stock_management_enabled) || $product->stock_available->always_available || is_null($product->stock_available->default_quantity_after_sending_order_lists) ? ' hide' : '').'">' .
            (!($product->is_stock_product && $product->manufacturer->stock_management_enabled) && !is_null($product->stock_available->default_quantity_after_sending_order_lists) ?
                $this->Number->formatAsDecimal($product->stock_available->default_quantity_after_sending_order_lists, 0)
            : '') .
         '</span>';

        if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
            if ($product->stock_available->quantity_limit != 0) {
                $elementsToRender[] =
                    ' <i class="small quantity-limit-for-dialog">'.
                        $this->Number->formatAsDecimal($product->stock_available->quantity_limit, 0) .
                '</i>';
            }
            if (is_null($product->stock_available->sold_out_limit) || $product->stock_available->sold_out_limit != 0) {
                $element = ' <i class="small sold-out-limit-for-dialog">';
                if (is_null($product->stock_available->sold_out_limit)) {
                    $element .= '<i class="fas fa-times" title="'.__d('admin', 'No_email_notifications_are_sent_for_this_product.').'"></i>';
                } else {
                    $element .= $this->Number->formatAsDecimal($product->stock_available->sold_out_limit, 0);
                }
                $element .= '</i>';
                $elementsToRender[] = $element;
            }
        }
        echo join('', $elementsToRender);
    }

echo '</td>';

?>