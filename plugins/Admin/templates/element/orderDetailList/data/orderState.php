<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if ($groupBy == '') {
    $widthStyle = '';
    if (!empty($orderDetail->pickup_day_entity) && $orderDetail->pickup_day_entity->products_picked_up) {
        $widthStyle = 'width:52px;';
    }
    echo '<td style="text-align:center;font-size:17px;'.$widthStyle.'">';
        if (isset($this->MyHtml->getOrderStates()[$orderDetail->order_state])) {
            $title = __d('admin', 'Order_state') . ': ' . $this->MyHtml->getOrderStates()[$orderDetail->order_state];
            $title .= '<br />' . __d('admin', 'Order_date') . ': ' .  $orderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'));
            echo '<i title="'.$title.'" class="order-state-icon ' . $this->MyHtml->getOrderStateFontawesomeIcon($orderDetail->order_state).'"></i>';
        }
        if (!empty($orderDetail->pickup_day_entity) && $orderDetail->pickup_day_entity->products_picked_up) {
            echo '&nbsp;<i title="'.__d('admin', 'products_picked_up').'" class="fas fa-home ok"></i>';
        }
    echo '</td>';
}

?>