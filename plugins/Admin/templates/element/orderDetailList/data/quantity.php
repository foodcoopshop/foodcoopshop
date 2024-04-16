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

 if ($groupBy == '') {
    echo '<td class="quantity-field right ' . ($orderDetail->quantityInUnitsNotYetChanged && $editRecordAllowed ? 'not-available' : '') . '">';
        if ($groupBy == '') {
            if (!empty($orderDetail->order_detail_unit)) {
            if ($editRecordAllowed) {
                echo $this->Html->link(
                    '<i class="fas fa-pencil-alt ok"></i>',
                    'javascript:void(0);',
                    [
                        'class' => 'btn btn-outline-light order-detail-product-quantity-edit-button',
                        'title' => __d('admin', 'Click_to_change_weight'),
                        'escape' => false
                    ]
                );
            }
            echo '<span class="quantity-in-units">' . $this->Number->formatUnitAsDecimal($orderDetail->order_detail_unit->product_quantity_in_units) .'</span><span class="unit-name">'. ' ' . $orderDetail->order_detail_unit->unit_name.'</span>';
            echo '<span class="hide price-per-unit-base-info">'.$this->PricePerUnit->getPricePerUnitBaseInfo($orderDetail->order_detail_unit->price_incl_per_unit, $orderDetail->order_detail_unit->unit_name, $orderDetail->order_detail_unit->unit_amount).'</span>';
        }
    }
    echo '</td>';
 }

if ($groupBy == 'product') {
    echo '<td class="right">';
        $sumUnitsString = $this->PricePerUnit->getStringFromUnitSums($sums['units'], '<br />');
        echo $sumUnitsString;
    echo '</td>';
}

?>