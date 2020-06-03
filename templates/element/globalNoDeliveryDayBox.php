<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.7.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if ($appAuth->isInstantOrderMode() || $appAuth->isSelfServiceModeByUrl()) {
    return;
}

$globalNoDeliveryDaysString = $this->Html->getGlobalNoDeliveryDaysString();
if ($globalNoDeliveryDaysString != '') {
    echo '<div id="global-no-delivery-day-box" class="box">';
        echo '<h3>' . __('Attention_delivery_break!') . '</h3>';
        echo '<p>' . $globalNoDeliveryDaysString . '</p>';
    echo '</div>';
}
