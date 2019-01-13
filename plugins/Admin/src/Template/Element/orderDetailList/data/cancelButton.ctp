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

if ($groupBy == '') {
    echo '<td style="text-align:center;">';
        if ($editRecordAllowed) {
            echo $this->Html->link(
                '<i class="fas fa-times-circle not-ok"></i>',
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