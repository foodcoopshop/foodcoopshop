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
    $widthStyle = '';
    if (!empty($orderDetail->pickup_day_entity) && $orderDetail->pickup_day_entity->products_picked_up) {
        $widthStyle = 'width:45px;';
    }
    echo '<td style="text-align:center;'.$widthStyle.'">';
        echo $this->Html->image(
            $this->MyHtml->getFamFamFamPath($this->MyHtml->getOrderStateIcon($orderDetail->order_state)),
            [
                'title' => $this->MyHtml->getOrderStates()[$orderDetail->order_state]
            ]
        );
        if (!empty($orderDetail->pickup_day_entity) && $orderDetail->pickup_day_entity->products_picked_up) {
            echo '&nbsp;' . $this->Html->image(
                $this->MyHtml->getFamFamFamPath('cart_go.png'),
                [
                    'title' => __d('admin', 'products_picked_up')
                ]
            );
        }
    echo '</td>';
}

?>