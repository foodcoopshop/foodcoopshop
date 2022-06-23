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

echo '<td class="right' . ($groupBy == '' && $orderDetail->total_price_tax_incl <= 0 ? ' not-available' : '') . '">';
    echo '<div class="table-cell-wrapper price">';
    if ($groupBy == '') {
        if ($editRecordAllowed) {
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light order-detail-product-price-edit-button',
                    'title' => __d('admin', 'Click_to_change_price'),
                    'escape' => false
                ]
            );
        }
        echo '<span class="product-price-for-dialog">' . $this->Number->formatAsCurrency($orderDetail->total_price_tax_incl) . '</span>';
    } else {
        echo $this->Number->formatAsCurrency($orderDetail['sum_price']);
    }
    echo '</div>';
echo '</td>';

?>