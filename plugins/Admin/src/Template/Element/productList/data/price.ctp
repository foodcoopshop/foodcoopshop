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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (!empty($product->unit)) {
    echo '<span id="product-unit-object-'.$product->id_product.'" class="product-unit-object"></span>';
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.setProductUnitData($('#product-unit-object-".$product->id_product."'),'".json_encode($product->unit)."');"
    ]);
}

echo '<td class="cell-price ' . ($product->price_is_zero ? 'not-available' : '') . '">';
    echo '<div class="table-cell-wrapper price">';
    if (empty($product->product_attributes)) {
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light product-price-edit-button',
                'title' => __d('admin', 'change_price'),
                'escape' => false
            ]
        );
        echo '<span class="price-for-dialog '.(!empty($product->unit) && $product->unit->price_per_unit_enabled ? 'hide' : '').'">';
        echo $this->Number->formatAsCurrency($product->gross_price);
        echo '</span>';
        if (!empty($product->unit) && $product->unit->price_per_unit_enabled) {
            echo '<span class="unit-price-for-dialog">';
            echo $this->PricePerUnit->getPricePerUnitBaseInfo($product->unit->price_incl_per_unit, $product->unit->name, $product->unit->amount);
            echo '</span>';
        }
    }
    echo '</div>';
echo '</td>';

?>