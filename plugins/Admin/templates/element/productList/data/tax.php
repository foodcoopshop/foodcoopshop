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

use Cake\Core\Configure;

echo '<td>';
    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        echo $this->Form->hidden('Products.id_tax', [
            'id' => 'tax-id-' . $product->id_product,
            'value' => $product->id_tax
        ]);
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

        if ($showPurchasePriceTax) {
            if (!empty($product->purchase_price_product)) {
                echo $this->Form->hidden('PurchasePriceProducts.id_tax', [
                    'id' => 'purchase-price-tax-id-' . $product->id_product,
                    'value' => $product->purchase_price_product->tax_id,
                ]);

                echo '<span class="purchase-price-tax-for-dialog purchase-price-list-element">';
                if ($product->purchase_price_product->tax_id !== null) {
                    $taxRate = 0;
                    if (!empty($product->purchase_price_product->tax)) {
                        $taxRate = $product->purchase_price_product->tax->rate;
                    }
                    echo $this->Number->formatTaxRate($taxRate) . '%';
                } else {
                    echo '<i>--</i>';
                }
                echo '</span>';

            }
        }

        echo '<span class="tax-for-dialog">' .
            $this->Number->formatTaxRate($product->tax->rate) .
        '%' . '</span>';

    }
echo '</td>';

?>