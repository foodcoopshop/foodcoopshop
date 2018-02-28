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
            var datefieldSelector = $('input.datepicker');
            datefieldSelector.datepicker();" .
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".Admin.initCancelSelectionButton();" .
            Configure::read('app.jsNamespace') . ".Helper.setCakeServerName('" . Configure::read('app.cakeServerName') . "');" .
            Configure::read('app.jsNamespace') . ".Admin.setWeekdaysBetweenOrderSendAndDelivery('" . json_encode($this->MyTime->getWeekdaysBetweenOrderSendAndDelivery(1)) . "');".
            Configure::read('app.jsNamespace') . ".Admin.initDeleteOrderDetail();" . Configure::read('app.jsNamespace') . ".Helper.setIsManufacturer(" . $appAuth->isManufacturer() . ");" . Configure::read('app.jsNamespace') . ".Admin.initOrderDetailProductPriceEditDialog('#order-details-list');" . Configure::read('app.jsNamespace') . ".Admin.initOrderDetailProductQuantityEditDialog('#order-details-list');" . Configure::read('app.jsNamespace') . ".Admin.initEmailToAllButton();" . Configure::read('app.jsNamespace') . ".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ", " . ($manufacturerId != '' ? $manufacturerId : '0') . ");
        "
    ]);
    ?>
    
    <div class="filter-container">
    	<?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameFrom' => 'dateFrom', 'nameTo' => 'dateTo']); ?>
            <?php echo $this->Form->control('productId', ['type' => 'select', 'label' => '', 'empty' => 'alle Produkte', 'options' => []]); ?>
            <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isCustomer()) { ?>
                <?php echo $this->Form->control('manufacturerId', ['type' => 'select', 'label' => '', 'empty' => 'alle Hersteller', 'options' => $manufacturersForDropdown, 'default' => isset($manufacturerId) ? $manufacturerId: '']); ?>
            <?php } ?>
            <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) { ?>    
                <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'empty' => 'alle Mitglieder', 'options' => $customersForDropdown, 'default' => isset($customerId) ? $customerId: '']); ?>
            <?php } ?>
            <?php if ($appAuth->isCustomer()) { ?>
                <?php // for preselecting customer in shop order dropdown ?>
                <?php echo $this->Form->hidden('customerId', ['value' => isset($customerId) ? $customerId: '']); ?>
            <?php } ?>
            <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isCustomer()) { ?>
                <input id="orderId" type="text" placeholder="Bestell-Nr."
                value="<?php echo $orderId; ?>" />
            <?php } ?>
            <?php echo $this->Form->control('orderStates', ['type' => 'select', 'multiple' => true, 'label' => '', 'options' => $this->MyHtml->getVisibleOrderStates(), 'data-val' => join(',', $orderStates)]); ?>
            <?php echo $this->Form->control('groupBy', ['type'=>'select', 'label' =>'', 'empty' => 'Gruppieren nach...', 'options' => $groupByForDropdown, 'default' => $groupBy]);?>
            <div class="right">
            
            <?php
            if (Configure::read('app.isDepositPaymentCashless') && $groupBy == '' && $customerId > 0 && count($orderDetails) > 0) {
                echo '<div class="add-payment-deposit-button-wrapper">';
                    echo $this->element('addDepositPaymentOverlay', [
                        'buttonText' => (!$isMobile ? 'Pfand-Rückgabe' : ''),
                        'rowId' => $orderDetails[0]->order->id_order,
                        'userName' => $orderDetails[0]->order->customer->name,
                        'customerId' => $orderDetails[0]->order->id_customer,
                        'manufacturerId' => null // explicitly unset manufacturerId
                    ]);
                echo '</div>';
            }
            if (!$appAuth->isManufacturer()) {
                echo $this->element('addShopOrderButton', [
                'customers' => $customersForShopOrderDropdown
                ]);
            }
            ?>
            </div>
    	<?php echo $this->Form->end(); ?>
    </div>

    <div id="help-container">
        <ul>
            <?php echo $this->element('docs/abholdienst'); ?>
            <li>Auf dieser Seite werden die <b>bestellten Produkte</b>
                verwaltet.
            </li>
            <li><b>Produkt stornieren</b>: Mit einem Klick auf das Storno-Icon <?php echo $this->Html->image($this->Html->getFamFamFamPath('delete.png')); ?> ganz rechts kannst du das Produkt stornieren. Von Mittwoch bis Freitag
                <?php if (!$appAuth->isManufacturer()) { ?>
                    werden beim Stornieren das Mitglied und der Hersteller
                <?php } else { ?>
                    wird beim Stornieren das Mitglied
                <?php } ?>
                per E-Mail verständigt, dass das Produkt nicht geliefert wird. Du kannst auch angeben, warum das Produkt storniert wird.</li>
            <li><b>Preis ändern</b>: Du kannst Preise von bereits bestellten Produkten ändern und dafür auch einen Grund angeben. Das Mitglied  
            <?php if (!$appAuth->isManufacturer()) { ?>
                und der Hersteller werden
            <?php } else { ?>
                wird
            <?php } ?>
                dabei automatisch per E-Mail benachrichtigt.</li>
            <li>Wenn du auf den Button rechts unten klickst, erhältst du die
                E-Mail-Adressen von allen Mitgliedern.</li>
            <li>Wenn du auf das Häkchen ganz links klickst, ist die Zeile bis zum
                nächsten Laden der Seite grün markiert.</li>
            <?php if ($appAuth->isManufacturer()) { ?>
                <li>Du kannst nach Produkt filtern.</li>
            <?php } else { ?>
                <li>Du kannst die Liste nach verschiedensten Kriterien
                filtern.</li>
            <?php } ?>
        </ul>
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
echo '<th class="right">';
    echo $this->Paginator->sort('OrderDetails.product_quantity', 'Anzahl');
echo '</th>';
if ($groupBy == '' || $groupBy == 'product') {
    echo '<th>';
        echo $this->Paginator->sort('OrderDetails.product_name', 'Produkt');
    echo '</th>';
}

echo '<th class="' . ($appAuth->isManufacturer() ? 'hide' : '') . '">';
    echo $this->Paginator->sort('Manufacturers.name', 'Hersteller');
echo '</th>';
echo '<th class="right">';
    echo $this->Paginator->sort('OrderDetails.total_price_tax_incl', 'Betrag');
echo '</th>';
if ($groupBy == 'manufacturer' && Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
    echo '<th>%</th>';
    echo '<th class="right">Betrag abzügl. eventuellem variablen Mitgliedsbeitrag</th>';
}
echo '<th class="right">';
    echo $this->Paginator->sort('OrderDetails.deposit', 'Pfand');
echo '</th>';
if ($groupBy == '') {
    echo '<th>';
        $this->Paginator->sort('Orders.date_add', 'Bestell-Datum');
    echo '</th>';
    echo '<th>'.$this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), 'Mitglied').'</th>';
    echo '<th>'.$this->Paginator->sort('Orders.current_state', 'Status').'</th>';
    echo '<th style="width:25px;"></th>';
    echo '<th class="hide">' . $this->Paginator->sort('OrderDetails.order_id', 'OrderID') . '</th>';
}
echo '</tr>';

$sumPrice = 0;
$sumAmount = 0;
$sumDeposit = 0;
$sumReducedPrice = 0;
$i = 0;
foreach ($orderDetails as $orderDetail) {
    $editRecordAllowed = $groupBy == '' && ($orderDetail->order->current_state == ORDER_STATE_OPEN || $orderDetail->bulkOrdersAllowed);

    $i ++;
    if ($groupBy == '') {
        $sumPrice += $orderDetail->total_price_tax_incl;
        $sumAmount += $orderDetail->product_quantity;
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

    echo '<td class="hide">';
    if ($groupBy == '') {
        echo $orderDetail->id_order_detail;
    }
    echo '</td>';

    echo '<td class="right">';
    echo '<div class="table-cell-wrapper quantity">';
    if ($groupBy == '') {
        if ($orderDetail->product_quantity > 1 && $editRecordAllowed) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                'class' => 'order-detail-product-quantity-edit-button',
                'title' => 'Zum Ändern der Anzahl anklicken'
            ], 'javascript:void(0);');
        }
        $quantity = $orderDetail->product_quantity;
        $style = '';
        if ($quantity > 1) {
            $style = 'font-weight:bold;';
        }
        echo '<span class="product-quantity-for-dialog" style="' . $style . '">' . $quantity . '</span><span style="' . $style . '">x</span>';
    } else {
        echo $this->Html->formatAsDecimal($orderDetail['sum_amount'], 0) . 'x';
    }
    echo '</div>';
    echo '</td>';

    if ($groupBy != '') {
        $groupByObjectLink = $this->MyHtml->link($orderDetail['name'], '/admin/order-details/index/?dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '&' . $groupBy.'Id=' . $orderDetail[$groupBy . '_id'] . '&orderStates[]=' . join(',', $orderStates) . '&customerId=' . $customerId);
    }

    if ($groupBy == '' || $groupBy == 'product') {
        echo '<td>';
        if ($groupBy == '') {
            echo $this->MyHtml->link($orderDetail->product_name, '/admin/order-details/index/?dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '&productId=' . $orderDetail->product_id . '&orderStates[]=' . join(',', $orderStates), [
                'class' => 'name-for-dialog'
            ]);
        }
        if ($groupBy == 'product') {
            echo $groupByObjectLink;
        }
        echo '</td>';
    }
    echo '<td class="' . ($appAuth->isManufacturer() ? 'hide' : '') . '">';
    if ($groupBy == '') {
        echo $this->MyHtml->link($orderDetail->product->manufacturer->name, '/admin/order-details/index/?dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '&manufacturerId=' . $orderDetail->product->id_manufacturer . '&orderStates[]=' . join(',', $orderStates) . '&customerId=' . $customerId . '&groupBy='.$groupBy);
    }
    if ($groupBy == 'manufacturer') {
        echo $groupByObjectLink;
    }
    if ($groupBy == 'product') {
        echo $this->MyHtml->link($orderDetail['manufacturer_name'], '/admin/order-details/index/?dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '&' . 'manufacturerId=' . $orderDetail['manufacturer_id'] . '&orderStates[]=' . join(',', $orderStates) . '&customerId=' . $customerId.'&groupBy=product');
    }
    echo '</td>';

    echo '<td class="right' . ($groupBy == '' && $orderDetail->total_price_tax_incl == 0 ? ' not-available' : '') . '">';
    echo '<div class="table-cell-wrapper price">';
    if ($groupBy == '') {
        if ($editRecordAllowed) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                'class' => 'order-detail-product-price-edit-button',
                'title' => 'Zum Ändern des Preises anklicken'
            ], 'javascript:void(0);');
        }
        echo '<span class="product-price-for-dialog">' . $this->Html->formatAsDecimal($orderDetail->total_price_tax_incl) . '</span>';
    } else {
        echo $this->Html->formatAsDecimal($orderDetail['sum_price']);
    }
    echo '</div>';
    echo '</td>';

    if ($groupBy == 'manufacturer' && Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
        $priceDiffers = $reducedPrice != $orderDetail['sum_price'];

        echo '<td>';
        echo $orderDetail['variable_member_fee'] . '%';
        echo '</td>';

        echo '<td class="right">';
        if ($priceDiffers) {
            echo '<span style="color:red;font-weight:bold;">';
        }
        echo $this->Html->formatAsDecimal($reducedPrice);
        if ($priceDiffers) {
            echo '</span>';
        }
        echo '</td>';
    }

    echo '<td class="right">';
    if ($groupBy == '') {
        if ($orderDetail->deposit > 0) {
            echo $this->Html->formatAsDecimal($orderDetail->deposit);
        }
    } else {
        if ($orderDetail['sum_deposit'] > 0) {
            echo $this->Html->formatAsDecimal($orderDetail['sum_deposit']);
        }
    }
    echo '</td>';

    if ($groupBy == '') {
        echo '<td>';
        if ($groupBy == '') {
            echo $orderDetail->order->date_add->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
        }
        echo '</td>';

        echo '<td>';
        if ($groupBy == '') {
            echo $orderDetail->order->customer->name;
        }
        echo '</td>';

        echo '<td class="hide">';
        if ($groupBy == '') {
            echo '<span class="email">' . $orderDetail->order->customer->email . '</span>';
        }
        echo '</td>';

        echo '<td>';
        if ($groupBy == '') {
            echo $this->MyHtml->getOrderStates()[$orderDetail->order->current_state];
        }
        echo '</td>';

        echo '<td style="text-align:center;">';
        if ($editRecordAllowed) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
                'class' => 'delete-order-detail',
                'id' => 'delete-order-detail-' . $orderDetail->id_order_detail,
                'title' => 'Produkt stornieren?'
            ], 'javascript:void(0);');
        }
        echo '</td>';

        echo '<td class="hide orderId">';
        if ($groupBy == '') {
            echo $orderDetail->id_order;
        }
        echo '</td>';
    }

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="1"><b>' . $this->Html->formatAsDecimal($i, 0) . '</b></td>';
echo '<td class="right"><b>' . $this->Html->formatAsDecimal($sumAmount, 0) . 'x</b></td>';
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
if ($groupBy == 'product') {
    if ($appAuth->isManufacturer()) {
        echo '<td></td>';
    } else {
        echo '<td colspan="2"></td>';
    }
}
echo '<td class="right"><b>' . $this->Html->formatAsDecimal($sumPrice) . '</b></td>';
if ($groupBy == 'manufacturer' && Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
    echo '<td></td>';
    echo '<td class="right"><b>' . $this->Html->formatAsDecimal($sumReducedPrice) . '</b></td>';
}
$sumDepositString = '';
if ($sumDeposit > 0) {
    $sumDepositString = $this->Html->formatAsDecimal($sumDeposit);
}
echo '<td class="right"><b>' . $sumDepositString . '</b></td>';
if ($groupBy == '') {
    echo '<td colspan="4"></td>';
}
echo '</tr>';
echo '</table>';

$buttonExists = false;
$buttonHtml = '';

if ($groupBy == '' && ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isManufacturer())) {
    $buttonExists = true;
    $buttonHtml .= '<button class="email-to-all btn btn-default" data-column="10"><i class="fa fa-envelope-o"></i> Alle E-Mail-Adressen kopieren</button>';
}

if ($groupBy == '' && $productId == '' && $manufacturerId == '' && $customerId != '') {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.setAdditionalOrderStatusChangeInfo('" . Configure::read('app.additionalOrderStatusChangeInfo') . "');" . Configure::read('app.jsNamespace') . ".Helper.setPaymentMethods(" . json_encode(Configure::read('app.paymentMethods')) . ");" . Configure::read('app.jsNamespace') . ".Admin.setVisibleOrderStates('" . json_encode(Configure::read('app.visibleOrderStates')) . "');"
    ]);
    if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Admin.initChangeOrderStateFromOrderDetails();"
        ]);
        $buttonExists = true;
        $buttonHtml .= '<button class="change-order-state-button btn btn-default"><i class="fa fa-check-square-o"></i> Bestellstatus ändern</button>';
    }
}

if ($deposit != '') {
    if ($appAuth->isManufacturer()) {
        $depositOverviewUrl = $this->Slug->getMyDepositList();
    } else {
        $depositOverviewUrl = $this->Slug->getDepositList($manufacturerId);
    }
    $buttonHtml .= '<a class="btn btn-default" href="'.$depositOverviewUrl.'"><i class="fa fa-arrow-circle-left"></i> Zurück zum Pfandkonto</a>';
}

if (count($orderDetails) > 0) {
    $buttonHtml .= '<a id="cancelSelectedProductsButton" class="btn btn-default" href="javascript:void(0);"><i class="fa fa-minus-circle"></i> Ausgewählte Produkte stornieren</a>';
}

if ($buttonExists) {
    echo '<div class="bottom-button-container">';
        echo $buttonHtml;
    echo '</div>';
    echo '<div class="sc"></div>';
}

?>
    <div class="sc"></div>

</div>
