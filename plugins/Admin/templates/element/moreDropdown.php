<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (!empty($emailAddresses)) {
    $buttons[] =  $this->element('orderDetailList/button/email', [
        'emailAddresses' => $emailAddresses,
        'renderAsButtonInDropdown' => true,
    ]);
}

if ($appAuth->isSuperadmin() && Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && !Configure::read('appDb.FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED')) {
    $buttons[] =  $this->element('addInstantOrderButton', [
        'additionalClass' => 'bottom',
    ]);
}

$buttons[] = $this->element('orderDetailList/button/multiplePickupDays', [
    'pickupDay' => $pickupDay
]);

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

$buttons[] = '<a class="dropdown-item" href="javascript:window.print();" target="_blank"><i class="fas fa-print fa-fw ok"></i> ' .  __d('admin', 'Print') . '</a>';
$buttons[] = '<a class="dropdown-item" href="' . $helperLink . '" target="_blank"><i class="fas fa-question fa-fw ok"></i> ' .  __d('admin', 'Help') . '</a>';

$buttons = array_filter($buttons); // remove empty array elements

?>

<div class="dropdown">
    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-angles-right"></i> <?php echo __d('admin', 'More...'); ?>
    </button>
    <ul class="dropdown-menu">
        <?php
            foreach($buttons as $button) {
                echo '<li>';
                    echo $button;
                echo '</li>';
            }
        ?>
    </ul>
</div>
