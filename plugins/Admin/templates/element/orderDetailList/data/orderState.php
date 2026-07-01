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

if ($groupBy == '') {
    echo '<td class="center">';
        if (isset($this->MyHtml->getOrderStates()[$orderDetail->order_state])) {
            $title = 'ID: ' .  $orderDetail->id_order_detail;
            $title .= '<br />' . __('Order_state') . ': ' . $this->MyHtml->getOrderStates()[$orderDetail->order_state];
            $title .= '<br />' . __('Cart_type') . ': ' . (Configure::read('app.htmlHelper')->getCartTypes()[$orderDetail->cart_product->cart->cart_type]);
            $title .= '<br />' . __('Order_date') . ': ' .  $orderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'));
            echo '<i title="'.$title.'" class="order-state-icon ' . $this->MyHtml->getOrderStateFontawesomeIcon($orderDetail->order_state).'"></i>';
        }
        if (!empty($orderDetail->pickup_day_entity) && $orderDetail->pickup_day_entity->products_picked_up) {
            echo '&nbsp;<i title="'.__('products_picked_up').'" class="fas fa-home ok"></i>';
        }
    echo '</td>';
}

?>