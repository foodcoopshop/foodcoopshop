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

if ($groupBy == 'customer' && count($pickupDay) == 1) {

    echo '<td>';
        if ($orderDetail['products_picked_up']) {
            $buttonText = __d('admin', 'Yes');
            $iconClass = 'fa-home ok';
        } else {
            $buttonText = __d('admin', 'No');
            $iconClass = 'fa-exclamation-triangle neutral';
        }
        echo $this->Html->link(
            '<i class="fas fa-fw ' . $iconClass . '"></i> ' . $buttonText,
            'javascript:void(0);',
            [
                'escape' => false,
                'class' => 'change-products-picked-up-button btn btn-outline-light'
            ]
        );

    echo '</td>';
}

?>