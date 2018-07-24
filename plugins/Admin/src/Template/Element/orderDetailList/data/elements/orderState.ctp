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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if ($groupBy == '') {
    echo '<td'.(!$isMobile ? ' style="width: 247px;"' : '').'>';
        echo '<span class="truncate" style="float: left; width: 77px;">' . $this->MyHtml->getOrderStates()[$orderDetail->order_state] . '</span>';
        $statusChangeIcon = 'accept';
        if ($orderDetail->order_state == ORDER_STATE_OPEN) {
            $statusChangeIcon = 'error';
        }
        if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath($statusChangeIcon . '.png')) . (!$isMobile ? ' ' . __d('admin', 'Change_order_status') : ''), [
                'title' => __d('admin', 'Change_order_status'),
                'class' => 'change-order-state-button icon-with-text'
            ], 'javascript:void(0);');
        }
    echo '</td>';
}

?>