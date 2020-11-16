<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo '<td>';
    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        echo $this->Form->hidden('Products.id_tax', [
            'id' => 'tax-id-' . $product->id_product,
            'value' => $product->id_tax
        ]);
        $taxRate = $product->tax->rate;
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light product-tax-edit-button',
                'title' => __d('admin', 'change_tax_rate'),
                'data-object-id' => $product->id_product,
                'escape' => false
            ]
        );
        echo '<span class="tax-for-dialog">' . $this->Number->formatTaxRate($taxRate) . '%' . '</span>';
    }
echo '</td>';

?>