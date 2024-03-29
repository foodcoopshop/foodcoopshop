<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if (!empty($emailAddresses)) {
    $buttons[] =  $this->element('orderDetailList/button/email', [
        'emailAddresses' => $emailAddresses,
        'renderAsButtonInDropdown' => true,
    ]);
}

$buttons[] = $this->element('orderDetailList/button/generateOrderDetailsAsPdf', [
    'pickupDay' => $pickupDay
]);

$buttons[] =$this->element('orderDetailList/button/backToDepositAccount', [
    'deposit' => $deposit
]);

$buttons[] = $this->element('orderDetailList/button/allProductsPickedUp', [
    'pickupDay' => $pickupDay,
    'renderAsButtonInDropdown' => true,
]);

$buttons[] = $this->element('orderDetailList/button/changePickupDayOfSelectedOrderDetails', [
    'deposit' => $deposit,
    'orderDetails' => $orderDetails,
    'groupBy' => $groupBy
]);

$buttons[] = $this->element('orderDetailList/button/deleteSelectedOrderDetails', [
    'deposit' => $deposit,
    'orderDetails' => $orderDetails,
    'groupBy' => $groupBy
]);

$buttons[] = '<hr class="dropdown-divider" />';

$buttons[] = $this->element('orderDetailList/button/multiplePickupDays', [
    'pickupDay' => $pickupDay
]);

$buttons[] = $this->element('orderDetailList/button/filterByCartTypeEnabled', [
    'filterByCartTypeEnabled' => $filterByCartTypeEnabled,
]);

echo $this->element('dropdownWithButtons', [
    'helperLink' => $helperLink,
    'buttons' => $buttons,
    'label' => __d('admin', 'Actions') . '...',
]);

?>