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

if ($groupBy == '') {
    echo '<td style="text-align:center;">';
        if ($editRecordAllowed) {
            echo $this->Html->link(
                '<i class="fas fa-times-circle neutral"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light delete-order-detail',
                    'id' => 'delete-order-detail-' . $orderDetail->id_order_detail,
                    'title' => __d('admin', 'Click_to_cancel_product'),
                    'escape' => false
                ]
            );
        }
    echo '</td>';
}

?>