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
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')) . ' ' . __d('admin', 'New'), [
                'class' => 'icon-with-text change-new-state change-new-state-active',
                'id' => 'change-new-state-' . $product->id_product,
                'title' => __d('admin', 'Mark_product_as_new_for_the_next_{0}_days?', [Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW')])
            ], 'javascript:void(0);');
        } else {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('accept.png')) . ' Neu', [
                'class' => 'icon-with-text change-new-state change-new-state-inactive',
                'id' => 'change-new-state-' . $product->id_product,
                'title' => __d('admin', 'Do_not_mark_product_as_new_any_more?')
            ], 'javascript:void(0);');
        }
    }
echo '</td>';

?>