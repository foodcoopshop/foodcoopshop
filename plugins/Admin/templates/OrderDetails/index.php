<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;
use App\Model\Entity\OrderDetail;

?>
<div id="order-details-list">

    <?php
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            $('input.datepicker').datepicker();".
            Configure::read('app.jsNamespace').".Admin.init();" .
            Configure::read('app.jsNamespace').".Helper.setFullBaseUrl('" . Configure::read('App.fullBaseUrl') . "');" .
            Configure::read('app.jsNamespace').".Helper.setIsManufacturer(" . $identity->isManufacturer() . ");" .
            Configure::read('app.jsNamespace').".Admin.selectMainMenuAdmin('".__d('admin', 'Orders')."');" .
            Configure::read('app.jsNamespace').".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ", " . ($manufacturerId != '' ? $manufacturerId : '0') . ");".
            Configure::read('app.jsNamespace').".Admin.initCustomerDropdown(" . ($customerId != '' ? $customerId : '0') . ", 0, 1);
        "
    ]);

    $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#order-detail-'
    ]);

    echo $this->element('autoPrintInvoice');

    if (Configure::read('app.isDepositEnabled')) {
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace').".ModalPaymentAdd.initDepositInList();"
        ]);
    }

    if ($groupBy == '') {
        $this->element('addScript', [
            'script' =>
            Configure::read('app.jsNamespace').".Helper.initTooltip('.product-feedback-button, i.order-state-icon');" .
            Configure::read('app.jsNamespace').".ModalOrderDetailDelete.init();" .
            Configure::read('app.jsNamespace').".ModalOrderDetailFeedbackAdd.init();" .
            Configure::read('app.jsNamespace').".ModalOrderDetailProductNameEdit.init();" .
            Configure::read('app.jsNamespace').".ModalOrderDetailProductPriceEdit.init();" .
            Configure::read('app.jsNamespace').".ModalOrderDetailProductQuantityEdit.init();" .
            Configure::read('app.jsNamespace').".ModalOrderDetailProductCustomerEdit.init();" .
            Configure::read('app.jsNamespace').".ModalOrderDetailProductAmountEdit.init();"
        ]);
    }

    if ($groupBy == 'customer' && Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $identity->isSuperadmin()) {
        $this->element('addScript', [
            'script' =>
            Configure::read('app.jsNamespace') . ".ModalInvoiceForCustomerAdd.init(" . ($this->MyHtml->paymentIsCashless() ? '1' : '0') . ");".
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('.latest-invoices-tooltip-wrapper');"
        ]);
    }

    if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
        $this->element('addScript', [
            'script' =>
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('.pickup-day-comment-edit-button');".
            Configure::read('app.jsNamespace') . ".ModalPickupDayCommentEdit.init();"
        ]);
    }

    ?>

    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php
                echo $this->element('orderDetailList/pickupDayFilter', [
                    'pickupDay' => $pickupDay
                ]);
            ?>
            <?php echo $this->Form->control('productId', ['type' => 'select', 'label' => '', 'placeholder' => __d('admin', 'all_products'), 'options' => []]); ?>
            <?php if ($identity->isSuperadmin() || $identity->isAdmin() || $identity->isCustomer()) { ?>
                <?php echo $this->Form->control('manufacturerId', ['type' => 'select', 'label' => '', 'empty' => __d('admin', 'all_manufacturers'), 'options' => $manufacturersForDropdown, 'default' => isset($manufacturerId) ? $manufacturerId: '']); ?>
            <?php } ?>
            <?php if ($identity->isSuperadmin() || $identity->isAdmin()) { ?>
                <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'placeholder' => __d('admin', 'all_members'), 'options' => []]); ?>
            <?php } ?>
            <?php if ($identity->isCustomer()) { ?>
                <?php // for preselecting customer in shop order dropdown ?>
                <?php echo $this->Form->hidden('customerId', ['value' => isset($customerId) ? $customerId: '']); ?>
<?php } ?>
            <?php echo $this->Form->control('groupBy', ['type'=>'select', 'label' =>'', 'empty' => __d('admin', 'Group_by...'), 'options' => $groupByForDropdown, 'default' => $groupBy]);?>
            <?php
                if ($filterByCartTypeEnabled) {
                    echo $this->Form->control('cartType', ['type' => 'select', 'label' => '', 'empty' => __d('admin', 'all_cart_types'), 'options' => $this->Html->getCartTypes(), 'default' => $cartType]);
                }
            ?>
        <?php echo $this->Form->end(); ?>

            <div class="right">
            <?php
            if (
                Configure::read('app.isDepositEnabled') &&
                $this->Html->paymentIsCashless() &&
                !$identity->isManufacturer() &&
                (!$identity->isCustomer() || Configure::read('app.isCustomerAllowedToModifyOwnOrders'))) {
                    $showCustomerDropdown = false;
                    if (!empty($orderDetails) && isset($orderDetails[0]->id_customer)) {
                        $customerIdForDepositOverlay = $orderDetails[0]->id_customer;
                        $customerNameForDepsitOverlay = $this->Html->getNameRespectingIsDeleted($orderDetails[0]->customer);
                    } else {
                        $customerIdForDepositOverlay = 0;
                        $showCustomerDropdown = true;
                        $customerNameForDepsitOverlay = null;
                    }
                    echo '<div class="add-payment-deposit-button-wrapper">';
                        echo $this->element('addDepositPaymentOverlay', [
                            'buttonText' => (!$isMobile ? __d('admin', 'Deposit_return') : ''),
                            'objectId' => $customerIdForDepositOverlay,
                            'userName' => $customerNameForDepsitOverlay,
                            'customerId' => $customerIdForDepositOverlay,
                            'showCustomerDropdown' => $showCustomerDropdown,
                            'manufacturerId' => null, // explicitly unset manufacturerId
                        ]);
                    echo '</div>';
            }
            if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                if ($identity->isAdmin() || $identity->isSuperadmin()) {
                    echo $this->element('addSelfServiceOrderButton');
                }
            }

            if (!$identity->isManufacturer() && ($identity->isAdmin() || $identity->isSuperadmin() || ($identity->isCustomer() && Configure::read('app.isCustomerAllowedToModifyOwnOrders')))) {
                echo $this->element('addInstantOrderButton');
            }
            
            echo $this->element('orderDetailList/moreDropdown', [
                'helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_pick_up_products')),
                'emailAddresses' => $emailAddresses,
                'pickupDay' => $pickupDay,
                'deposit' => $deposit,
                'orderDetails' => $orderDetails,
                'groupBy' => $groupBy,
                'filterByCartTypeEnabled' => $filterByCartTypeEnabled,
            ]);

            ?>
        </div>
    </div>

<?php

if (count($orderDetails) == 0) {
    echo '<h2 class="info">';
    if (count($pickupDay) == 1) {
        echo __d('admin', 'No_orders_found_for_pickup_day_{0}.', [$this->Time->formatToDateShort($pickupDay[0])]);
    } else {
        echo __d('admin', 'No_orders_found_for_delivery_period_{0}_-_{1}.', [$this->Time->formatToDateShort($pickupDay[0]), $this->Time->formatToDateShort($pickupDay[1])]);
    }
    echo '</h2>';
}

echo '<table class="list">';
echo '<tr class="sort">';
    echo $this->element('rowMarker/rowMarkerAll', [
        'enabled' => count($orderDetails) > 0 && $groupBy == ''
    ]);
    echo '<th class="hide">ID</th>';
    $orderDetailTemplateElement = 'default';
    if ($groupBy != '') {
        $orderDetailTemplateElement = 'groupBy' . ucfirst($groupBy);
    }
    echo $this->element('orderDetailList/header/'.$orderDetailTemplateElement);

echo '</tr>';

foreach ($orderDetails as $orderDetail) {

    $editRecordAllowed = $groupBy == '' && (
        in_array($orderDetail->order_state, [
            OrderDetail::STATE_OPEN,
            OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER,
        ]))
        && (!$identity->isCustomer() || Configure::read('app.isCustomerAllowedToModifyOwnOrders'));

    $rowClasses = [];
    if (isset($orderDetail->row_class)) {
        $rowClasses = $orderDetail->row_class;
    }
    if (isset($orderDetail['row_class'])) {
        $rowClasses = $orderDetail['row_class'];
    }

    $rowIdHtml = '';
    if ($groupBy == '') {
        $rowIdHtml = ' id="order-detail-' . $orderDetail->id_order_detail . '"';
    }
    echo '<tr' . $rowIdHtml . ' class="data ' . (!empty($rowClasses) ? implode(' ', $rowClasses) : '') . '">';

    echo $this->element('rowMarker/rowMarker', [
        'show' => $editRecordAllowed,
        'id' => $orderDetail->id_order_detail ?? '',
    ]);

    echo $this->element('orderDetailList/data/id', [
        'orderDetail' => $orderDetail,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/amount', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/mainObject', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/price', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/deposit', [
        'orderDetail' => $orderDetail,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/quantity', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/productsPickedUp', [
        'orderDetail' => $orderDetail,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/invoiceLink', [
        'orderDetail' => $orderDetail,
        'groupBy' => $groupBy,
    ]);

    echo $this->element('orderDetailList/data/customer', [
        'editRecordAllowed' => $editRecordAllowed,
        'orderDetail' => $orderDetail
    ]);

    echo $this->element('orderDetailList/data/pickupDay', [
        'orderDetail' => $orderDetail
    ]);

    echo $this->element('orderDetailList/data/orderState', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/cancelButton', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="1"><b>' . $this->Number->formatAsDecimal($sums['records_count'], 0) . '</b></td>';
if ($groupBy != 'customer') {
    echo '<td class="right"><b>' . $this->Number->formatAsDecimal($sums['amount'], 0) . 'x</b></td>';
}
if ($groupBy == '') {
    if ($identity->isManufacturer()) {
        echo '<td></td>';
    } else {
        echo '<td colspan="2"></td>';
    }
}

if ($groupBy == 'manufacturer') {
    echo '<td></td>';
}

if ($groupBy == 'customer') {
    $showAllOrderDetailsLink = '';
    if (!empty($orderDetails)) {

        $showAllOrderDetailsLink = $this->Html->link(
            '<i class="fas fa-shopping-cart ok"></i>' . (!$isMobile ? ' ' . __d('admin', 'All_products') : ''),
            '/admin/order-details/index/?pickupDay[]=' . join(',', $pickupDay) . '&productId=' . $productId. '&manufacturerId=' . $manufacturerId,
            [
                'class' => 'btn btn-outline-light',
                'title' => __d('admin', 'Show_all_ordered_products'),
                'escape' => false
            ]
        );
    }
    echo '<td></td>';
    echo '<td>'.$showAllOrderDetailsLink.'</td>';
}
if ($groupBy == 'product') {
    if ($identity->isManufacturer()) {
        echo '<td></td>';
    } else {
        echo '<td colspan="2"></td>';
    }
}
echo '<td class="right"><b>' . $this->Number->formatAsCurrency($sums['price']) . '</b></td>';
if ($groupBy != 'customer') {
    $sumDepositString = '';
    if ($sums['deposit']> 0) {
        $sumDepositString = $this->Number->formatAsCurrency($sums['deposit']);
    }
    if (Configure::read('app.isDepositEnabled')) {
        echo '<td class="right"><b>' . $sumDepositString . '</b></td>';
    }
} else {
    if (Configure::read('app.isDepositEnabled') && $this->Html->paymentIsCashless()) {
        echo '<td></td>';
    }
    if (count($pickupDay) == 1) {
        echo '<td></td>';
    }
    if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $identity->isSuperadmin()) {
        echo '<td></td>';
    }
}
if ($groupBy == '') {
    $sumUnitsString = $this->PricePerUnit->getStringFromUnitSums($sums['units'], '<br />');
    echo '<td class="right slim"><b>' . $sumUnitsString . '</b></td>';
    $colspan = 3;
    if (count($pickupDay) == 2) {
        $colspan++;
    }
    echo '<td colspan="'.$colspan.'"></td>';
}

if ($groupBy == 'product') {
    echo '<td></td>';
}

echo '</tr>';
echo '</table>';

?>
    <div class="sc"></div>
</div>
<?php
    if ($groupBy == '') {
        $this->element('addScript', [
            'script' =>
            // needs to be rendered after button js
            Configure::read('app.jsNamespace').".Admin.initKeepSelectedCheckbox();"
        ]);
    }
?>