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
<div id="orders-list">
     
        <?php
        $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            var datefieldSelector = $('input.datepicker');
            datefieldSelector.datepicker();" . Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Helper.setCakeServerName('" . Configure::read('app.cakeServerName') . "');" . Configure::read('app.jsNamespace') . ".Admin.setVisibleOrderStates('" . json_encode(Configure::read('app.visibleOrderStates')) . "');" . Configure::read('app.jsNamespace') . ".Admin.setWeekdaysBetweenOrderSendAndDelivery('" . json_encode($this->MyTime->getWeekdaysBetweenOrderSendAndDelivery(1)) . "');" . Configure::read('app.jsNamespace') . ".Admin.setAdditionalOrderStatusChangeInfo('" . Configure::read('app.additionalOrderStatusChangeInfo') . "');" . Configure::read('app.jsNamespace') . ".Helper.setPaymentMethods(" . json_encode(Configure::read('app.paymentMethods')) . ");" . Configure::read('app.jsNamespace') . ".Admin.initOrderEditDialog('#orders-list');" . Configure::read('app.jsNamespace') . ".Helper.bindToggleLinks();" . Configure::read('app.jsNamespace') . ".Admin.initChangeOrderStateFromOrders();
        "
        ]);

        if (Configure::read('app.memberFeeFlexibleEnabled')) {
            $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Admin.initAddPaymentInList('.add-payment-member-fee-flexible-button');"
            ]);
        }
        if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
            $this->element('addScript', [
                'script' =>
                    Configure::read('app.jsNamespace') . ".Helper.initTooltip('.order-comment-edit-button', { my: \"left top\", at: \"left bottom\" }, false);".
                    Configure::read('app.jsNamespace') . ".Admin.initOrderCommentEditDialog('#orders-list');"
            ]);
        }
        $this->element('highlightRowAfterEdit', [
            'rowIdPrefix' => '#order-'
        ]);
    ?>
    
    <div class="filter-container">
        <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo]); ?>
        <?php echo $this->Form->input('orderState', ['type' => 'select', 'multiple' => true, 'label' => '', 'options' => $this->MyHtml->getVisibleOrderStates(), 'data-val' => $orderState]); ?>
        <?php if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) { ?>
            Gruppieren nach Mitglied: <?php echo $this->Form->input('groupByCustomer', ['type'=>'checkbox', 'label' =>'', 'checked' => $groupByCustomer]);?>
        <?php } ?>
        <div class="right">
            <?php
            echo $this->element('addShopOrderButton', [
                'customers' => $customersForDropdown
            ]);
            ?>
        </div>

    </div>

    <div id="help-container">
        <ul>
            <?php echo $this->element('docs/abholdienst'); ?>
            <li>Auf dieser Seite werden die <b>Bestellungen</b>
                verwaltet.
            </li>
            <li>Eine Bestellung (im Unterschied zum <b>bestellten Produkt</b>)
                beinhaltet einen oder mehrere bestellte Produkte.
            </li>
            <li>Ein Klick auf <?php echo $this->Html->image($this->Html->getFamFamFamPath('cart.png')); ?> "Bestellte Produkte anzeigen" neben dem Namen bringt dich direkt in die Liste der bestellten Produkte des Mitglieds. Es werden dort alle Bestellungen dieser Bestellperiode zusammengefasst angezeigt.</li>
            <li><b>Bestellung rückdatieren</b>: Falls du während eines Abholdienstes eine Bestellung rückdatieren musst (damit das Mitglied das Produkt sofort mitnehmen kann und die Bestellung nicht in der nächsten Bestellperiode aufscheint), klicke bitte auf <?php echo $this->Html->image($this->Html->getFamFamFamPath('calendar.png')); ?> "rückdatieren" ganz rechts wähle einen Tag der letzten Bestellperiode aus. Ein Beispiel wäre: Freitag abholdienst => neuer Wert: 3 Tage früher (Dienstag).</li>
            <li><b>Gruppieren nach Mitglied</b> bedeutet, dass alle Bestellungen
                der gleichen Mitgliedern zusammengefasst werden. Somit sieht man,
                wieviel jedes Mitglied tatsächlich zu bezahlen hat. Diese Liste ist
                ideal für eine Gesamtübersicht des Abholdienstes (nach allen
                Stornierungen).</li>
            <li>Unten rechts ist ein Button, mit dem man alle E-Mail-Adressen der
                Mitglieder in der Liste erhält. So kann man Informationen an alle
                Leute aussenden, die bestellt haben.</li>
            <li>Mit Klick <?php echo $this->Html->image($this->Html->getFamFamFamPath('money_euro.png')); ?> "Bestellstatus ändern" kannst du den Bestellstatus der Bestellung ändern.</li>
            <li>Mitglieder mit diesem Symbol <i class="fa fa-pagelines"></i>
                haben erst 3x oder weniger bestellt.
            </li>
            <?php if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) { ?>
                    <li>Das Symbol <?php echo $this->Html->image($this->Html->getFamFamFamPath('exclamation.png')); ?> zeigt an, ob das Mitglied einen Kommentar zur Bestellung verfasst hat. Dieser kann auch geändert werden. Wenn das Symbol ausgegraut ist, kann ein neuer Kommentar erstellt werden.</li>
            <?php } ?>
        </ul>
    </div>
    
    <?php
    echo '<table class="list">';

    echo '<tr class="sort">';
    echo '<th class="hide">' . $this->Paginator->sort('Orders.id_order', 'ID') . '</th>';
    echo '<th>' . $this->Paginator->sort('Customers.name', 'Mitglied') . '</th>';
    echo '<th></th>';
    echo '<th class="hide">' . $this->Paginator->sort('Customers.email', 'E-Mail') . '</th>';
    if (! $groupByCustomer) {
        echo '<th class="right">' . $this->Paginator->sort('Orders.total_paid', 'Betrag') . '</th>';
    } else {
        echo '<th class="right">Betrag</th>';
    }
    if (Configure::read('app.isDepositPaymentCashless')) {
        echo '<th>Pfand</th>';
    }
    if (Configure::read('app.memberFeeFlexibleEnabled')) {
        echo '<th>Flexi</th>';
    }
    if (! $groupByCustomer) {
        echo '<th>' . $this->Paginator->sort('Orders.date_add', 'Bestelldatum') . '</th>';
    } else {
        echo '<th>Anzahl Bestellungen</th>';
    }
    echo '<th>Status</th>';
    echo '<th></th>';
    echo '</tr>';

    $sumPrice = 0;
    $i = 0;

    foreach ($orders as $order) {
        $paidField = $order['Orders']['total_paid'];
        if ($groupByCustomer) {
            $paidField = $order[0]['Order_total_paid'];
        }

        $sumPrice += $paidField;
        $i ++;

        $rowClass = [
            'data'
        ];
        if (! $groupByCustomer && in_array($order['Orders']['current_state'], [
            ORDER_STATE_CASH,
            ORDER_STATE_CASH_FREE
        ])) {
            $rowClass[] = 'selected';
        }

        echo '<tr id="order-' . $order['Orders']['id_order'] . '" class="' . join(' ', $rowClass) . '">';

        echo '<td class="hide order-id">';
        if (! $groupByCustomer) {
            echo $order['Orders']['id_order'];
        }
        echo '</td>';

        echo '<td style="max-width: 200px;">';
        if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED') && !$groupByCustomer) {
            echo '<span class="order-comment-wrapper">';
                echo $this->Html->getJqueryUiIcon(
                    $this->Html->image($this->Html->getFamFamFamPath('exclamation.png')),
                    [
                    'class' => 'order-comment-edit-button' . ($order['Orders']['comment'] == '' ? ' disabled' : ''),
                    'title' => $order['Orders']['comment'] != '' ? $order['Orders']['comment'] : 'Kommentar hinzufügen',
                    'data-title-for-overlay' => $order['Orders']['comment'] != '' ? $order['Orders']['comment'] : 'Kommentar hinzufügen'
                    ],
                    'javascript:void(0);'
                );
            echo '</span>';
        }
        if ($order['Customers']['order_count'] <= 3) {
            echo '<span class="customer-is-new"><i class="fa fa-pagelines" title="Neuling: Hat erst ' . $order['Customers']['order_count'] . 'x bestellt."></i></span>';
        }
        echo '<span class="customer-name">'.$order['Orders']['name'].'</span>'; // !sic Order.name, related virtual field is copied in controller
        echo '</td>';

        echo '<td'.(!$isMobile ? ' style="width: 157px;"' : '').'>';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('cart.png')) . (!$isMobile ? ' Bestellte Produkte' : ''), [
            'title' => 'Alle bestellten Produkte von ' . $order['Orders']['name'] . ' anzeigen',
            'class' => 'icon-with-text'
        ], '/admin/order_details/index/dateFrom:' . $dateFrom . '/dateTo:' . $dateTo . '/customerId:' . $order['Customers']['id_customer'] . '/orderState:' . $orderState);
        echo '</td>';

        echo '<td class="hide">';
        echo '<span class="email">' . $order['Customers']['email'] . '</span>';
        echo '</td>';

        echo '<td class="right">';
        echo $this->Html->formatAsEuro($paidField);
        echo '</td>';

        if (Configure::read('app.isDepositPaymentCashless')) {
            echo '<td'.(!$isMobile ? ' style="width: 144px;"' : '').'>';
                echo $this->element('addDepositPaymentOverlay', [
                    'buttonText' => (!$isMobile ? 'Pfand-Rückgabe' : ''),
                    'rowId' => $order['Orders']['id_order'],
                    'userName' => $order['Orders']['name'],
                    'customerId' => $order['Customers']['id_customer']
                ]);
            echo '</td>';
        }

        if (Configure::read('app.memberFeeFlexibleEnabled')) {
            echo '<td style="width:72px;">';
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('heart.png')) . ' Flexi', [
                'title' => 'Flexiblen Mitgliedsbeitrag eintragen',
                'class' => 'add-payment-member-fee-flexible-button icon-with-text',
                'data-object-id' => $order['Orders']['id_order']
            ], 'javascript:void(0);');
            echo '<div id="add-payment-member-fee-flexible-form-' . $order['Orders']['id_order'] . '" class="add-payment-form add-payment-member-fee-flexible-form">';
            echo '<h3>Flexiblen Mitgliedsbeitrag eintragen</h3>';
            echo '<p>Flexiblen Mitgliedsbeitrag für <b>' . $order['Orders']['name'] . '</b> eintragen:</p>';
            echo $this->Form->input('Payments.amount', [
                'label' => 'Betrag in €',
                'type' => 'string'
            ]);
            echo $this->Form->hidden('Payments.type', [
                'value' => 'member_fee_flexible'
            ]);
            echo $this->Form->hidden('Payments.customerId', [
                'value' => $order['Customers']['id_customer']
            ]);
            echo '</div>';
            echo '<div class="sc"></div>';
            echo '</td>';
        }

        echo '<td style="width: 100px;">';
        if (! $groupByCustomer) {
            echo $this->Time->formatToDateNTimeShort($order['Orders']['date_add']);
        } else {
            echo $order[0]['Order_count'];
        }
        echo '</td>';

        echo '<td'.(!$isMobile ? ' style="width: 247px;"' : '').'>';
        if (! $groupByCustomer) {
            echo '<span class="truncate" style="float: left; width: 77px;">' . $this->MyHtml->getOrderStates()[$order['Orders']['current_state']] . '</span>';
            $statusChangeIcon = 'accept';
            if ($order['Orders']['current_state'] == ORDER_STATE_OPEN) {
                $statusChangeIcon = 'error';
            }
            if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
                echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath($statusChangeIcon . '.png')) . (!$isMobile ? ' Bestellstatus ändern' : ''), [
                    'title' => 'Bestellstatus ändern',
                    'class' => 'change-order-state-button icon-with-text'
                ], 'javascript:void(0);');
            }
        }
        echo '</td>';

        echo '<td class="date-icon icon">';
        if ($order['Orders']['current_state'] == 3) {
            echo '<div class="last-n-days-dropdown">';
            echo $this->Form->input('date_add_' . $order['Orders']['id_order'], [
                'type' => 'select',
                'label' => '',
                'options' => $this->MyTime->getLastNDays(5, $order['Orders']['date_add'])
            ]);
            echo '</div>';
            if (! $groupByCustomer) {
                echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('calendar.png')), [
                    'title' => 'Bestellung rückdatieren',
                    'class' => 'edit-button'
                ], 'javascript:void(0);');
            }
        }
        echo '</td>';

        echo '</tr>';
    }

    echo '<tr>';
    echo '<td colspan="2"><b>' . $i . '</b> Datensätze</td>';
    echo '<td class="right"><b>' . $this->Html->formatAsEuro($sumPrice) . '</b></td>';
    echo '<td colspan="5"></td>';
    echo '</tr>';

    echo '</table>';
    ?>
    
    <div class="sc"></div>
    
    <?php

    echo '<div class="bottom-button-container">';

    if (count($orders) > 0 && ($appAuth->isSuperadmin() || $appAuth->isAdmin())) {
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Admin.initEmailToAllButton();"
        ]);
        echo '<button class="email-to-all btn btn-default" data-column="4"><i class="fa fa-envelope-o"></i> Alle E-Mail-Adressen kopieren</button>';
        if (! $groupByCustomer && ($appAuth->isSuperadmin() || $appAuth->isAdmin())) {
            $this->element('addScript', [
                'script' => Configure::read('app.jsNamespace') . ".Admin.initCloseOrdersButton();" . Configure::read('app.jsNamespace') . ".Admin.initGenerateOrdersAsPdf();"
            ]);
            echo '<button class="btn btn-default generate-orders-as-pdf"><i class="fa fa-file-pdf-o"></i> Bestellungen als PDF generieren</button>';
            echo '<button id="closeOrdersButton" class="btn btn-default"><i class="fa fa-check-square-o"></i> Alle Bestellungen abschließen</button>';
        }
    }

    echo '</div>';
    echo '<div class="sc"></div>';

    ?>
    
</div>
