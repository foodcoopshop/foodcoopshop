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

if ($groupBy == '') {
    echo '<td class="customer-field">';
    if (
        $editRecordAllowed
        && ($appAuth->isAdmin() || $appAuth->isSuperadmin())
        && $this->Html->getNameRespectingIsDeleted($orderDetail->customer) != $this->Html->getDeletedCustomerName()
        && empty($orderDetail->timebased_currency_order_detail)) {
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light order-detail-customer-edit-button',
                    'title' => __d('admin', 'Click_to_change_member'),
                    'escape' => false
                ]
            );
        }
        echo '<span class="customer-name-for-dialog">' . $this->Html->getNameRespectingIsDeleted($orderDetail->customer) . '</span>';
        echo '<span class="customer-id-for-dialog hide">' . $orderDetail->id_customer . '</span>';
    echo '</td>';
}

?>