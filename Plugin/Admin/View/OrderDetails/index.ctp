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
?>
<div id="order-details-list">
    
    <?php
    $this->element('addScript', array(
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            var datefieldSelector = $('input.datepicker');
            datefieldSelector.datepicker();" .
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".Admin.initCancelSelectionButton();" .
            Configure::read('app.jsNamespace') . ".Admin.initDeleteOrderDetail();" . Configure::read('app.jsNamespace') . ".Helper.setIsManufacturer(" . $appAuth->isManufacturer() . ");" . Configure::read('app.jsNamespace') . ".Admin.initOrderDetailProductPriceEditDialog('#order-details-list');" . Configure::read('app.jsNamespace') . ".Admin.initOrderDetailProductQuantityEditDialog('#order-details-list');" . Configure::read('app.jsNamespace') . ".Admin.initEmailToAllButton();" . Configure::read('app.jsNamespace') . ".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ", " . ($manufacturerId != '' ? $manufacturerId : '0') . ");
        "
    ));
    ?>
    
    <div class="filter-container">
        <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isCustomer()) { ?>
	    	<?php echo $this->element('dateFields', array('dateFrom' => $dateFrom, 'dateTo' => $dateTo)); ?>
        <?php } ?>
        <?php echo $this->Form->input('productId', array('type' => 'select', 'label' => '', 'empty' => 'alle Artikel', 'options' => array())); ?>
        <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isCustomer()) { ?>
            <?php echo $this->Form->input('manufacturerId', array('type' => 'select', 'label' => '', 'empty' => 'alle Hersteller', 'options' => $manufacturersForDropdown, 'selected' => isset($manufacturerId) ? $manufacturerId: '')); ?>
        <?php } ?>
        <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) { ?>    
            <?php echo $this->Form->input('customerId', array('type' => 'select', 'label' => '', 'empty' => 'alle Mitglieder', 'options' => $customersForDropdown, 'selected' => isset($customerId) ? $customerId: '')); ?>
        <?php } ?>
        <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isCustomer()) { ?>
            <input id="orderId" type="text" placeholder="Bestell-Nr."
			value="<?php echo $orderId; ?>" />
        <?php } ?>
        <?php echo $this->Form->input('orderState', array('type' => 'select', 'multiple' => true, 'label' => '', 'options' => $this->MyHtml->getVisibleOrderStates(), 'data-val' => $orderState)); ?>
        <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isCustomer()) { ?>
            Gruppieren nach Hersteller: <?php echo $this->Form->input('groupByManufacturer', array('type'=>'checkbox', 'label' =>'', 'checked' => $groupByManufacturer));?>
        <?php } ?>
        <button id="filter" class="btn btn-success">
			<i class="fa fa-search"></i> Filtern
		</button>
		<div class="right"></div>
	</div>

	<div id="help-container">
		<ul>
            <?php echo $this->element('shopdienstInfo'); ?>
            <li>Auf dieser Seite werden die <b>bestellten Artikel</b>
				verwaltet.
			</li>
			<li><b>Artikel stornieren</b>: Mit einem Klick auf das Storno-Icon <?php echo $this->Html->image($this->Html->getFamFamFamPath('delete.png')); ?> ganz rechts kannst du den Artikel stornieren. Von Mittwoch bis Freitag
            	<?php if (!$appAuth->isManufacturer()) { ?>
            		werden beim Stornieren das Mitglied und der Hersteller
            	<?php } else { ?>
            	    wird beim Stornieren das Mitglied
            	<?php } ?>
            	per E-Mail verständigt, dass der Artikel nicht geliefert wird. Du kannst auch angeben, warum der Artikel storniert wird.</li>
			<li><b>Preis ändern</b>: Du kannst Preise von bereits bestellten Artikeln ändern und dafür auch einen Grund angeben. Das Mitglied  
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
	
	<h2 class="info">Neu: Stornieren von mehreren Artikeln: Links anhaken und ganz unten auf "Ausgewählte Artikel stornieren" klicken.</h2>
    
<?php
echo '<table class="list">';
echo '<tr class="sort">';
echo '<th style="width:20px;">';
    if (count($orderDetails) > 0) {
        $this->element('addScript', array(
            'script' => Configure::read('app.jsNamespace') . ".Admin.initRowMarkerAll();"
        ));
        echo '<input type="checkbox" id="row-marker-all" />';
    }
echo '</th>';
echo '<th class="hide">' . $this->Paginator->sort('OrderDetail.detail_order_id', 'ID') . '</th>';
echo '<th class="right">' . $this->Paginator->sort('OrderDetail.product_quantity', 'Anzahl') . '</th>';
echo '<th>' . $this->Paginator->sort('OrderDetail.product_name', 'Artikel') . '</th>';
echo '<th class="' . ($appAuth->isManufacturer() ? 'hide' : '') . '">Hersteller</th>';
echo '<th class="right">' . $this->Paginator->sort('OrderDetail.total_price_tax_incl', 'Betrag') . '</th>';
if ($groupByManufacturer && Configure::read('app.useManufacturerCompensationPercentage')) {
    echo '<th>%</th>';
    echo '<th class="right">Betrag abzügl. eventueller Aufwandsentschädigung</th>';
}
echo '<th class="right">Pfand</th>';
echo '<th>' . $this->Paginator->sort('Order.date_add', 'Bestell-Datum') . '</th>';
echo '<th>Mitglied</th>';
echo '<th>Status</th>';
echo '<th style="width:25px;"></th>';
echo '<th class="hide">' . $this->Paginator->sort('OrderDetail.order_id', 'OrderID') . '</th>';
echo '</tr>';

$sumPrice = 0;
$sumAmount = 0;
$sumDeposit = 0;
$sumReducedPrice = 0;
$i = 0;
foreach ($orderDetails as $orderDetail) {
    
    $editRecordAllowed = ! $groupByManufacturer && ($orderDetail['Order']['current_state'] == ORDER_STATE_OPEN || $orderDetail['bulkOrdersAllowed']);
    
    $i ++;
    if (! $groupByManufacturer) {
        $sumPrice += $orderDetail['OrderDetail']['total_price_tax_incl'];
        $sumAmount += $orderDetail['OrderDetail']['product_quantity'];
        $sumDeposit += $orderDetail['OrderDetail']['deposit'];
    } else {
        $sumPrice += $orderDetail['sum_price'];
        $sumAmount += $orderDetail['sum_amount'];
        $reducedPrice = $orderDetail['sum_price'] * (100 - $orderDetail['compensation_percentage']) / 100;
        $sumReducedPrice += $reducedPrice;
        $sumDeposit += $orderDetail['sum_deposit'];
    }
    
    echo '<tr class="data ' . (isset($orderDetail['rowClass']) ? implode(' ', $orderDetail['rowClass']) : '') . '">';
    
    echo '<td style="text-align: center;">';
        if ($editRecordAllowed) {
            echo '<input type="checkbox" class="row-marker" />';
        }
    echo '</td>';
    
    echo '<td class="hide">';
    if (! $groupByManufacturer) {
        echo $orderDetail['OrderDetail']['id_order_detail'];
    }
    echo '</td>';
    
    echo '<td class="right">';
    echo '<div class="table-cell-wrapper quantity">';
    if (! $groupByManufacturer) {
        if ($orderDetail['OrderDetail']['product_quantity'] > 1 && $editRecordAllowed) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), array(
                'class' => 'order-detail-product-quantity-edit-button',
                'title' => 'Zum Ändern der Anzahl anklicken'
            ), 'javascript:void(0);');
        }
        $quantity = $orderDetail['OrderDetail']['product_quantity'];
        $style = '';
        if ($quantity > 1) {
            $style = 'font-weight:bold;';
        }
        echo '<span class="product-quantity-for-dialog" style="' . $style . '">' . $quantity . '</span><span style="' . $style . '">x</span>';
    } else {
        echo $orderDetail['sum_amount'] . 'x';
    }
    echo '</div>';
    echo '</td>';
    
    echo '<td>';
    if (! $groupByManufacturer) {
        echo $this->MyHtml->link($orderDetail['OrderDetail']['product_name'], '/admin/order_details/index/dateFrom:' . $dateFrom . '/dateTo:' . $dateTo . '/productId:' . $orderDetail['Product']['id_product'] . '/orderState:' . $orderState, array(
            'class' => 'name-for-dialog'
        ));
    }
    echo '</td>';
    
    echo '<td class="' . ($appAuth->isManufacturer() ? 'hide' : '') . '">';
    if (! $groupByManufacturer) {
        echo $this->MyHtml->link($orderDetail['Product']['Manufacturer']['name'], '/admin/order_details/index/dateFrom:' . $dateFrom . '/dateTo:' . $dateTo . '/manufacturerId:' . $orderDetail['Product']['Manufacturer']['id_manufacturer'] . '/orderState:' . $orderState . '/customerId:' . $customerId . '/groupByManufacturer:0');
    } else {
        echo $this->MyHtml->link($orderDetail['manufacturer_name'], '/admin/order_details/index/dateFrom:' . $dateFrom . '/dateTo:' . $dateTo . '/manufacturerId:' . $orderDetail['manufacturer_id'] . '/orderState:' . $orderState . '/customerId:' . $customerId . '/groupByManufacturer:0');
    }
    echo '</td>';
    
    echo '<td class="right">';
    echo '<div class="table-cell-wrapper price">';
    if (! $groupByManufacturer) {
        if ($editRecordAllowed) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), array(
                'class' => 'order-detail-product-price-edit-button',
                'title' => 'Zum Ändern des Preises anklicken'
            ), 'javascript:void(0);');
        }
        echo '<span class="product-price-for-dialog">' . $this->Html->formatAsDecimal($orderDetail['OrderDetail']['total_price_tax_incl']) . '</span>';
    } else {
        echo $this->Html->formatAsDecimal($orderDetail['sum_price']);
    }
    echo '</div>';
    echo '</td>';
    
    if ($groupByManufacturer && Configure::read('app.useManufacturerCompensationPercentage')) {
        $priceDiffers = $reducedPrice != $orderDetail['sum_price'];
        
        echo '<td>';
        echo $orderDetail['compensation_percentage'] . '%';
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
    if (! $groupByManufacturer) {
        if ($orderDetail['OrderDetail']['deposit'] > 0) {
            echo $this->Html->formatAsDecimal($orderDetail['OrderDetail']['deposit']);
        }
    } else {
        if ($orderDetail['sum_deposit'] > 0) {
            echo $this->Html->formatAsDecimal($orderDetail['sum_deposit']);
        }
    }
    echo '</td>';
    
    echo '<td>';
    if (! $groupByManufacturer) {
        echo $this->Time->formatToDateNTimeLong($orderDetail['Order']['date_add']);
    }
    echo '</td>';
    
    echo '<td>';
    if (! $groupByManufacturer) {
        echo $orderDetail['Order']['Customer']['name'];
    }
    echo '</td>';
    
    echo '<td class="hide">';
    if (! $groupByManufacturer) {
        echo '<span class="email">' . $orderDetail['Order']['Customer']['email'] . '</span>';
    }
    echo '</td>';
    
    echo '<td>';
    if (! $groupByManufacturer) {
        echo $this->MyHtml->getOrderStates()[$orderDetail['Order']['current_state']];
    }
    echo '</td>';
    
    echo '<td style="text-align:center;">';
    if ($editRecordAllowed) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), array(
            'class' => 'delete-order-detail',
            'id' => 'delete-order-detail-' . $orderDetail['OrderDetail']['id_order_detail'],
            'title' => 'Artikel stornieren?'
        ), 'javascript:void(0);');
    }
    echo '</td>';
    
    echo '<td class="hide orderId">';
    if (! $groupByManufacturer) {
        echo $orderDetail['OrderDetail']['id_order'];
    }
    echo '</td>';
    
    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="1"><b>' . $i . '</b></td>';
echo '<td class="right"><b>' . $sumAmount . 'x</b></td>';
if ($appAuth->isManufacturer()) {
    echo '<td></td>';
} else {
    echo '<td colspan="2"></td>';
}
echo '<td class="right"><b>' . $this->Html->formatAsDecimal($sumPrice) . '</b></td>';
if ($groupByManufacturer && Configure::read('app.useManufacturerCompensationPercentage')) {
    echo '<td></td>';
    echo '<td class="right"><b>' . $this->Html->formatAsDecimal($sumReducedPrice) . '</b></td>';
}
$sumDepositString = '';
if ($sumDeposit > 0) {
    $sumDepositString = $this->Html->formatAsDecimal($sumDeposit);
}
echo '<td class="right"><b>' . $sumDepositString . '</b></td>';
if ($orderState == 3) {
    echo '<td colspan="4"></td>';
} else {
    echo '<td colspan="3"></td>';
}
echo '</tr>';
echo '</table>';

$buttonExists = false;
$buttonHtml = '';

if (! $groupByManufacturer && ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isManufacturer())) {
    $buttonExists = true;
    $buttonHtml .= '<button class="email-to-all btn btn-default" data-column="10"><i class="fa fa-envelope-o"></i> Alle E-Mail-Adressen kopieren</button>';
}

if (! $groupByManufacturer && $productId == '' && $manufacturerId == '' && $customerId != '') {
    $this->element('addScript', array(
        'script' => Configure::read('app.jsNamespace') . ".Admin.setAdditionalOrderStatusChangeInfo('" . Configure::read('app.additionalOrderStatusChangeInfo') . "');" . Configure::read('app.jsNamespace') . ".Helper.setPaymentMethods(" . json_encode(Configure::read('app.paymentMethods')) . ");" . Configure::read('app.jsNamespace') . ".Admin.setVisibleOrderStates('" . json_encode(Configure::read('app.visibleOrderStates')) . "');"
    ));
    if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
        $this->element('addScript', array(
            'script' => Configure::read('app.jsNamespace') . ".Admin.initChangeOrderStateFromOrderDetails();"
        ));
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

$buttonHtml .= '<a id="cancelSelectedProductsButton" class="btn btn-default" href="javascript:void(0);"><i class="fa fa-remove"></i> Ausgewählte Artikel stornieren</a>';

if ($buttonExists) {
    echo '<div class="bottom-button-container">';
        echo $buttonHtml;
    echo '</div>';
    echo '<div class="sc"></div>';
}

?>
    <div class="sc"></div>

</div>