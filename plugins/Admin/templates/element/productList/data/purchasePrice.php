<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
    return;
}

$rowClasses = ['cell-purchase-price'];

if (empty($product->product_attributes)) {
    if (!$product->purchase_price_is_set || $product->purchase_price_is_zero) {
        $rowClasses[] = 'not-available';
    }
}

echo '<td class="' . join(' ', $rowClasses) . '">';

    echo '<div class="table-cell-wrapper price">';

    if (empty($product->product_attributes)) {

        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light product-purchase-price-edit-button',
                'title' => __d('admin', 'change_price'),
                'escape' => false,
            ]
        );

        echo '<span class="purchase-price-for-dialog '.(!empty($product->unit) && $product->unit->price_per_unit_enabled ? 'hide' : '').'">';
            if (!is_null($product->purchase_gross_price)) {
                echo $this->Number->formatAsCurrency($product->purchase_gross_price);
            }
        echo '</span>';

        if (!empty($product->unit) && $product->unit->price_per_unit_enabled) {
            echo '<span class="unit-purchase-price-for-dialog">';
                if (!is_null($product->unit->purchase_price_incl_per_unit)) {
                    echo $this->PricePerUnit->getPricePerUnitBaseInfo($product->unit->purchase_price_incl_per_unit, $product->unit->name, $product->unit->amount);
                }
            echo '</span>';
        }

    }

    echo '</div>';

echo '</td>';

?>