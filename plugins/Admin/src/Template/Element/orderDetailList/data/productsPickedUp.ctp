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

if ($groupBy == 'customer' && count($pickupDay) == 1) {

    echo '<td>';
        if ($orderDetail['products_picked_up']) {
            $buttonText = __d('admin', 'Yes');
            $iconClass = 'fa-home ok';
        } else {
            $buttonText = __d('admin', 'No');
            $iconClass = 'fa-exclamation-triangle not-ok';
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