<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if ($groupBy == '') {
    echo '<td class="right ' . ($orderDetail->quantityInUnitsNotYetChanged ? 'not-available' : '') . '">';
    if (!empty($orderDetail->order_detail_unit)) {
        if ($editRecordAllowed) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                'class' => 'order-detail-product-quantity-edit-button',
                'title' => __d('admin', 'Click_to_change_weight')
            ], 'javascript:void(0);');
        }
        echo '<span class="quantity-in-units">' . $this->Number->formatUnitAsDecimal($orderDetail->order_detail_unit->product_quantity_in_units) .'</span><span class="unit-name">'. ' ' . $orderDetail->order_detail_unit->unit_name.'</span>';
        echo '<span class="hide price-per-unit-base-info">'.$this->PricePerUnit->getPricePerUnitBaseInfo($orderDetail->order_detail_unit->price_incl_per_unit, $orderDetail->order_detail_unit->unit_name, $orderDetail->order_detail_unit->unit_amount).'</span>';
    }
    echo '</td>';
}

?>