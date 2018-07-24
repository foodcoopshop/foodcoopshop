<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div id="order-details-list">
    
    <?php
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            $('input.datepicker').datepicker();".
            Configure::read('app.jsNamespace').".Admin.init();" .
            Configure::read('app.jsNamespace').".Admin.initCancelSelectionButton();" .
            Configure::read('app.jsNamespace').".Helper.setCakeServerName('" . Configure::read('app.cakeServerName') . "');" .
            Configure::read('app.jsNamespace').".Admin.setWeekdaysBetweenOrderSendAndDelivery('" . json_encode($this->MyTime->getWeekdaysBetweenOrderSendAndDelivery(1)) . "');".
            Configure::read('app.jsNamespace').".Admin.initDeleteOrderDetail();" .
            Configure::read('app.jsNamespace').".Helper.setIsManufacturer(" . $appAuth->isManufacturer() . ");" .
            Configure::read('app.jsNamespace').".Admin.initOrderDetailProductPriceEditDialog('#order-details-list');" .
            Configure::read('app.jsNamespace').".Admin.initOrderDetailProductQuantityEditDialog('#order-details-list');" .
            Configure::read('app.jsNamespace').".Admin.initOrderDetailProductAmountEditDialog('#order-details-list');" .
            Configure::read('app.jsNamespace').".Admin.initEmailToAllButton();" .
            Configure::read('app.jsNamespace').".Admin.selectMainMenuAdmin('".__d('admin', 'Orders')."');" .
            Configure::read('app.jsNamespace').".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ", " . ($manufacturerId != '' ? $manufacturerId : '0') . ");
        "
    ]);

    if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Helper.initTooltip('.timebased-currency-time-element');"
        ]);
    }
    
    if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
        $this->element('addScript', [
            'script' =>
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('.pickup-day-comment-edit-button', false);".
            Configure::read('app.jsNamespace') . ".Admin.initPickupDayCommentEditDialog('table.list');"
        ]);
    }
    
    ?>
    
    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameFrom' => 'dateFrom', 'nameTo' => 'dateTo']); ?>
            <?php echo $this->Form->control('productId', ['type' => 'select', 'label' => '', 'empty' => __d('admin', 'all_products'), 'options' => []]); ?>
            <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isCustomer()) { ?>
                <?php echo $this->Form->control('manufacturerId', ['type' => 'select', 'label' => '', 'empty' => __d('admin', 'all_manufacturers'), 'options' => $manufacturersForDropdown, 'default' => isset($manufacturerId) ? $manufacturerId: '']); ?>
            <?php } ?>
            <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) { ?>    
                <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'empty' => __d('admin', 'all_members'), 'options' => $customersForDropdown, 'default' => isset($customerId) ? $customerId: '']); ?>
            <?php } ?>
            <?php if ($appAuth->isCustomer()) { ?>
                <?php // for preselecting customer in shop order dropdown ?>
                <?php echo $this->Form->hidden('customerId', ['value' => isset($customerId) ? $customerId: '']); ?>
            <?php } ?>
            <?php echo $this->Form->control('orderStates', ['type' => 'select', 'multiple' => true, 'label' => '', 'options' => $this->MyHtml->getVisibleOrderStates(), 'data-val' => join(',', $orderStates)]); ?>
            <?php echo $this->Form->control('groupBy', ['type'=>'select', 'label' =>'', 'empty' => __d('admin', 'Group_by...'), 'options' => $groupByForDropdown, 'default' => $groupBy]);?>
            <div class="right">
            
            <?php
            if (Configure::read('app.isDepositPaymentCashless') && $groupBy == '' && $customerId > 0 && count($orderDetails) > 0) {
                echo '<div class="add-payment-deposit-button-wrapper">';
                    echo $this->element('addDepositPaymentOverlay', [
                        'buttonText' => (!$isMobile ? __d('admin', 'Deposit_return') : ''),
                        'rowId' => $orderDetails[0]->id_order_detail,
                        'userName' => $this->Html->getNameRespectingIsDeleted($orderDetails[0]->customer),
                        'customerId' => $orderDetails[0]->id_customer,
                        'manufacturerId' => null // explicitly unset manufacturerId
                    ]);
                echo '</div>';
            }
            if (!$appAuth->isManufacturer()) {
                echo $this->element('addInstantOrderButton', [
                    'customers' => $customersForInstantOrderDropdown
                ]);
                echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_pick_up_products'))]);
            }
            ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>

<?php
echo '<table class="list">';
echo '<tr class="sort">';
    echo '<th style="width:20px;">';
    if (count($orderDetails) > 0 && $groupBy == '') {
        $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.initRowMarkerAll();"
        ]);
        echo '<input type="checkbox" id="row-marker-all" />';
    }
    echo '</th>';
    echo '<th class="hide">' . $this->Paginator->sort('OrderDetails.detail_order_id', 'ID') . '</th>';
    
    $orderDetailTemplateElement = 'default';
    if ($groupBy != '') {
        $orderDetailTemplateElement = 'groupBy' . ucfirst($groupBy);
    }
    echo $this->element('orderDetailList/header/'.$orderDetailTemplateElement);
    
echo '</tr>';

$sumPrice = 0;
$sumAmount = 0;
$sumDeposit = 0;
$sumReducedPrice = 0;
$sumUnits = [];
$i = 0;
foreach ($orderDetails as $orderDetail) {
    
    $editRecordAllowed = $groupBy == '' && ($orderDetail->order_state == ORDER_STATE_OPEN || $orderDetail->bulkOrdersAllowed);

    $i ++;
    if ($groupBy == '') {
        $sumPrice += $orderDetail->total_price_tax_incl;
        $sumAmount += $orderDetail->product_amount;
        $sumDeposit += $orderDetail->deposit;
    } else {
        $sumPrice += $orderDetail['sum_price'];
        $sumAmount += $orderDetail['sum_amount'];
        if ($groupBy == 'manufacturer') {
            $reducedPrice = $orderDetail['sum_price'] * (100 - $orderDetail['variable_member_fee']) / 100;
            $sumReducedPrice += $reducedPrice;
        }
        $sumDeposit += $orderDetail['sum_deposit'];
    }

    echo '<tr class="data ' . (isset($orderDetail->row_class) ? implode(' ', $orderDetail->row_class) : '') . '">';

    echo '<td style="text-align: center;">';
        if ($editRecordAllowed) {
            echo '<input type="checkbox" class="row-marker" />';
        }
    echo '</td>';

    echo $this->element('orderDetailList/data/elements/id', [
        'orderDetail' => $orderDetail,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/elements/amount', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/elements/mainObject', [
        'orderDetail' => $orderDetail,
        'groupBy' => $groupBy
    ]);
    
    echo $this->element('orderDetailList/data/elements/price', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    if ($groupBy == 'manufacturer') {
        echo $this->element('orderDetailList/data/elements/variableMemberFee', [
            'orderDetail' => $orderDetail,
            'reducedPrice' => $reducedPrice,
            'groupBy' => $groupBy
        ]);
    }
    
    echo $this->element('orderDetailList/data/elements/deposit', [
        'orderDetail' => $orderDetail,
        'groupBy' => $groupBy
    ]);
    
    echo $this->element('orderDetailList/data/elements/quantity', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/elements/pickupDay', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);
    
    echo $this->element('orderDetailList/data/elements/customer', [
        'orderDetail' => $orderDetail
    ]);
    
    echo $this->element('orderDetailList/data/elements/orderState', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    echo $this->element('orderDetailList/data/elements/cancelButton', [
        'orderDetail' => $orderDetail,
        'editRecordAllowed' => $editRecordAllowed,
        'groupBy' => $groupBy
    ]);

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="1"><b>' . $this->Number->formatAsDecimal($i, 0) . '</b></td>';
echo '<td class="right"><b>' . $this->Number->formatAsDecimal($sumAmount, 0) . 'x</b></td>';
if ($groupBy == '') {
    if ($appAuth->isManufacturer()) {
        echo '<td></td>';
    } else {
        echo '<td colspan="2"></td>';
    }
}

if ($groupBy == 'manufacturer') {
    echo '<td></td>';
}

if ($groupBy == 'customer') {
    echo '<td colspan="2"></td>';
}
if ($groupBy == 'product') {
    if ($appAuth->isManufacturer()) {
        echo '<td></td>';
    } else {
        echo '<td colspan="2"></td>';
    }
}
echo '<td class="right"><b>' . $this->Number->formatAsDecimal($sumPrice) . '</b></td>';
if ($groupBy == 'manufacturer' && Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
    echo '<td></td>';
    echo '<td class="right"><b>' . $this->Number->formatAsDecimal($sumReducedPrice) . '</b></td>';
}
if ($groupBy != 'customer') {
    $sumDepositString = '';
    if ($sumDeposit > 0) {
        $sumDepositString = $this->Number->formatAsDecimal($sumDeposit);
    }
    echo '<td class="right"><b>' . $sumDepositString . '</b></td>';
} else {
    echo '<td colspan="2"></td>';
}
if ($groupBy == '') {
    $sumUnitsString = $this->PricePerUnit->getStringFromUnitSums($sumUnits, '<br />');
    echo '<td class="right slim"><b>' . $sumUnitsString . '</b></td>';
    echo '<td colspan="4"></td>';
}
echo '</tr>';
echo '</table>';

$buttonExists = false;
$buttonHtml = '';

if ($groupBy == '' && ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isManufacturer())) {
    $buttonExists = true;
    $buttonHtml .= '<button class="email-to-all btn btn-default" data-column="11"><i class="fa fa-envelope-o"></i> '.__d('admin', 'Copy_all_email_addresses').'</button>';
}

if ($groupBy == '' && $productId == '' && $manufacturerId == '') {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace') . ".Admin.setAdditionalOrderStatusChangeInfo('" .
        Configure::read('app.additionalOrderStatusChangeInfo') . "');" .
        Configure::read('app.jsNamespace') . ".Helper.setPaymentMethods(" . json_encode(Configure::read('app.paymentMethods')) . ");" .
        Configure::read('app.jsNamespace') . ".Admin.setVisibleOrderStates('" . json_encode(Configure::read('app.visibleOrderStates')) . "');"
    ]);
    if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Admin.initChangeOrderStateFromOrderDetails();"
        ]);
        $buttonExists = true;
        $buttonHtml .= '<button class="change-order-state-button btn btn-default"><i class="fa fa-check-square-o"></i> ' . __d('admin', 'Change_order_status') . '</button>';
    }
}

if ($deposit != '') {
    if ($appAuth->isManufacturer()) {
        $depositOverviewUrl = $this->Slug->getMyDepositList();
    } else {
        $depositOverviewUrl = $this->Slug->getDepositList($manufacturerId);
    }
    $buttonHtml .= '<a class="btn btn-default" href="'.$depositOverviewUrl.'"><i class="fa fa-arrow-circle-left"></i> ' . __d('admin', 'Back_to_deposit_account') . '</a>';
}

if (count($orderDetails) > 0) {
    $buttonHtml .= '<a id="cancelSelectedProductsButton" class="btn btn-default" href="javascript:void(0);"><i class="fa fa-minus-circle"></i> ' . __d('admin', 'Cancel_selected_products') . '</a>';
}

if ($buttonExists) {
    echo '<div class="bottom-button-container">';
        echo $buttonHtml;
    echo '</div>';
    echo '<div class="sc"></div>';
}

echo $this->TimebasedCurrency->getOrderInformationText($timebasedCurrencyOrderInList);

?>
    <div class="sc"></div>

</div>
