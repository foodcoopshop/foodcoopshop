<?php
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

if (!Configure::read('app.isDepositEnabled')) {
    return false;
}

echo '<td>';
    if (empty($product->product_attributes)) {
        echo '<div class="table-cell-wrapper deposit">';
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light product-deposit-edit-button',
                    'title' => __d('admin', 'change_deposit'),
                    'escape' => false
                ]
            );
        if ($product->deposit > 0) {
            echo '<span class="deposit-for-dialog">';
            echo $this->Number->formatAsDecimal($product->deposit);
            echo '</span>';
        }
        echo '</div>';
    }
echo '</td>';

?>