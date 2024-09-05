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

echo '<td class="status">';

    if (! empty($product->product_attributes) || isset($product->product_attributes)) {

        if ($product->active == 1) {
            echo $this->Html->link(
                '<i class="fas fa-check-circle ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light set-status-to-inactive product-status-edit',
                    'id' => 'product-status-edit-' . $product->id_product,
                    'title' => __d('admin', 'deactivate'),
                    'escape' => false
                ]
            );
        }

        if ($product->active == 0) {
            echo $this->Html->link(
                '<i class="fas fa-minus-circle ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light set-status-to-active product-status-edit',
                    'id' => 'product-status-edit-' . $product->id_product,
                    'title' => __d('admin', 'activate'),
                    'escape' => false
                ]
            );
        }

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') &&
            empty($product->product_attributes) &&
            !$product->purchase_price_is_set
            ) {
                echo '<i class="fas fa-exclamation not-ok purchase-price-not-set-info-text" title="' . __d('admin', 'Purchase_price_not_set_and_therefore_never_active.') . '"></i>';
        }

    }

echo '</td>';

?>