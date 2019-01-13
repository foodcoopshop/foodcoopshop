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

echo '<td>';
    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        if (! $product->is_new) {
            echo $this->Html->link(
                '<i class="fas fa-minus-circle not-ok"></i>'  . ' ' . __d('admin', 'New'),
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light change-new-state change-new-state-active',
                    'id' => 'change-new-state-' . $product->id_product,
                    'title' => __d('admin', 'Mark_product_as_new_for_the_next_{0}_days?', [Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW')]),
                    'escape' => false
                ]
            );
        } else {
            echo $this->Html->link(
                '<i class="fas fa-check-circle ok"></i>'  . ' ' . __d('admin', 'New'),
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light change-new-state change-new-state-inactive',
                    'id' => 'change-new-state-' . $product->id_product,
                    'title' => __d('admin', 'Do_not_mark_product_as_new_any_more?'),
                    'escape' => false
                ]
            );
        }
    }
echo '</td>';

?>