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

if ($groupBy == 'customer') {
    return false;
}

echo '<td class="right">';

    echo '<div class="table-cell-wrapper amount">';

        if ($groupBy == '') {
            if ($orderDetail->product_amount > 1 && $editRecordAllowed) {
                echo $this->Html->link(
                    '<i class="fas fa-pencil-alt ok"></i>',
                    'javascript:void(0);',
                    [
                        'class' => 'btn btn-outline-light order-detail-product-amount-edit-button',
                        'title' => __d('admin', 'Click_to_change_amount'),
                        'escape' => false
                    ]
                );
            }
            $amount = $orderDetail->product_amount;
            $style = '';
            if ($amount > 1) {
                $style = 'font-weight:bold;';
            }
            echo '<span class="product-amount-for-dialog" style="' . $style . '">' . $amount . '</span><span style="' . $style . '">x</span>';
        } else {
            echo $this->Number->formatAsDecimal($orderDetail['sum_amount'], 0) . 'x';
        }

    echo '</div>';

echo '</td>';

?>