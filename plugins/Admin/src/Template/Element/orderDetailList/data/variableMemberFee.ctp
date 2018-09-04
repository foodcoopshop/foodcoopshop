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

use Cake\Core\Configure;

if ($groupBy == 'manufacturer' && Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
    $priceDiffers = $orderDetail['reduced_price'] != $orderDetail['sum_price'];
    
    echo '<td>';
        echo $orderDetail['variable_member_fee'] . '%';
    echo '</td>';
    
    echo '<td class="right">';
        if ($priceDiffers) {
            echo '<span style="color:red;font-weight:bold;">';
        }
        echo $this->Number->formatAsDecimal($orderDetail['reduced_price']);
        if ($priceDiffers) {
            echo '</span>';
        }
    echo '</td>';
}


?>