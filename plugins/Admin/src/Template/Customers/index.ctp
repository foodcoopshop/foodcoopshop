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
<div id="customers-list">
    <?php
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            var datefieldSelector = $('input.datepicker');
            datefieldSelector.datepicker();" . Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initEmailToAllButton();" . Configure::read('app.jsNamespace') . ".Admin.initCustomerChangeActiveState();" . Configure::read('app.jsNamespace') . ".Admin.initCustomerGroupEditDialog('#customers-list');" . Configure::read('app.jsNamespace') . ".Helper.initTooltip('.customer-details-read-button');" . Configure::read('app.jsNamespace') . ".Helper.initTooltip('.customer-comment-edit-button', { my: \"right top\", at: \"right bottom\" }, false);" . Configure::read('app.jsNamespace') . ".Admin.initCustomerCommentEditDialog('#customers-list');"
    ]);
    ?>
    
    <div class="filter-container">
        <?php echo $this->Form->input('active', ['type' => 'select', 'label' => '', 'options' => $this->MyHtml->getActiveStates(), 'selected' => isset($active) ? $active : '']); ?>
        Anzahl Bestellungen zwischen <input id="validOrdersCountFrom"
            type="text"
            value="<?php echo isset($validOrdersCountFrom) ? $validOrdersCountFrom : ''; ?>" />
        und <input id="validOrdersCountTo" type="text"
            value="<?php echo isset($validOrdersCountTo) ? $validOrdersCountTo: ''; ?>" />
        und letztes Bestelldatum von <input id="dateFrom" type="text"
            class="datepicker" value="<?php echo $dateFrom; ?>" /> bis <input
            id="dateTo" type="text" class="datepicker"
            value="<?php echo $dateTo; ?>" />
        <div class="right"></div>
    </div>

    <div id="help-container">
        <ul>
            <?php echo $this->element('docs/abholdienst'); ?>
            <li>Auf dieser Seite werden die <b>Mitglieder</b> verwaltet.
            </li>
            <li>Mitglieder mit diesem Symbol <i class="fa fa-pagelines"></i>
                haben erst 3x oder weniger bestellt.
            </li>
            <?php if (Configure::read('app.isDepositPaymentCashless')) { ?>
                <li>Der Betrag unterhalb des Gesamt-Guthabens ist der <b>Saldo
                    des Pfandes</b>. Ist er negativ, liegt "zuviel" Pfand auf dem
                Guthaben-Konto und sollte - wenn er an einen Hersteller ausbezahlt
                wird, im FCS mit einem Foodcoop-User verbucht werden, damit der
                Pfand wieder stimmt.
            </li>
            <li>Das Gesamt-Guthaben hat diesen Pfand-Betrag inkludiert, da es ja
                die Summe aller Mitglieder-Guthaben ist.</li>
            <?php } ?>
        </ul>
    </div>    
    
<?php

echo $this->Form->input('selectGroupId', [
    'type' => 'select',
    'label' => '',
    'options' => $this->Html->getAuthDependentGroups($appAuth->getGroupId())
]);

echo '<table class="list">';
echo '<tr class="sort">';
echo '<th class="hide">' . $this->Paginator->sort('Customers.id_customer', 'ID') . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.name', 'Name') . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.id_default_group', 'Gruppe') . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.email', 'E-Mail') . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.active', 'Status') . '</th>';
echo '<th>Bestellungen</th>';
if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
    echo '<th>Guthaben</th>';
}
if (Configure::read('app.emailOrderReminderEnabled')) {
    echo '<th>' . $this->Paginator->sort('Customers.newsletter', 'Email') . '</th>';
}
echo '<th>' . $this->Paginator->sort('Customers.date_add', 'Registrier-Datum') . '</th>';
echo '<th>Letztes Bestelldatum</th>';
echo '<th>Komm.</th>';
echo '</tr>';

$i = 0;
$sumPaymentsProductDelta = 0;
$sumPaymentsDepositDelta = 0;
$sumOrdersCount = 0;
$sumEmailReminders = 0;
foreach ($customers as $customer) {
    $i ++;

    if ($this->Html->paymentIsCashless()) {
        $sumPaymentsDepositDelta += $customer['payment_deposit_delta'];
    }

    echo '<tr class="data">';

    echo '<td class="hide">';
    echo $customer['Customers']['id_customer'];
    echo '</td>';

    echo '<td>';

    $customerName = $customer['Customers']['name'];
    if ($customer['order_count'] <= 3) {
        $customerName = '<i class="fa fa-pagelines" title="Neuling: Hat erst ' . $customer['order_count'] . 'x bestellt."></i> ' . $customerName;
    }

    echo '<span clas="name">' . $this->Html->link($customerName, '/admin/orders/index/orderState:' . Configure::read('app.htmlHelper')->getOrderStateIdsAsCsv() . '/dateFrom:01.01.2014/dateTo:' . date('d.m.Y') . '/customerId:' . $customer['Customers']['id_customer'] . '/sort:Order.date_add/direction:desc/', [
        'title' => 'Zu allen Bestellungen von ' . $customer['Customers']['name'],
        'escape' => false
    ]) . '</span>';

    $details = $customer['AddressCustomers']['address1'];
    if ($customer['AddressCustomers']['address2'] != '') {
        $details .= '<br />' . $customer['AddressCustomers']['address2'];
    }
    $details .= '<br />' . $customer['AddressCustomers']['postcode'] . ' ' . $customer['AddressCustomers']['city'];

    if ($customer['AddressCustomers']['phone_mobile'] != '') {
        $details .= '<br />Tel.: ' . $customer['AddressCustomers']['phone_mobile'];
    }
    if ($customer['AddressCustomers']['phone'] != '') {
        $details .= '<br />Tel.: ' . $customer['AddressCustomers']['phone'];
    }

    echo '<div class="customer-details-wrapper">';
    echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('telephone.png')), [
        'class' => 'customer-details-read-button',
        'title' => $details
    ], 'javascript:void(0);');
    echo '</div>';

    echo '</td>';

    echo '<td>';

    if ($appAuth->getGroupId() >= $customer['Customers']['id_default_group']) {
        echo '<div class="table-cell-wrapper group">';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
            'class' => 'customer-group-edit-button',
            'title' => 'Zum Ändern der Gruppe anklicken'
        ], 'javascript:void(0);');
        echo '<span>' . $this->Html->getGroupName($customer['Customers']['id_default_group']) . '</span>';
        echo '</div>';
    } else {
        echo $this->Html->getGroupName($customer['Customers']['id_default_group']);
    }
    echo '<span class="group-for-dialog">' . $customer['Customers']['id_default_group'] . '</span>';
    echo '</td>';

    echo '<td>';
    echo '<span class="email">' . $customer['Customers']['email'] . '</span>';
    echo '</td>';

    echo '<td style="text-align:center;padding-left:10px;width:42px;">';

    if ($customer['Customers']['active'] == 1) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('accept.png')), [
            'class' => 'set-state-to-inactive change-active-state',
            'id' => 'change-active-state-' . $customer['Customers']['id_customer'],
            'title' => 'Zum Deaktivieren anklicken'
        ], 'javascript:void(0);');
    }

    if ($customer['Customers']['active'] == '') {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
            'class' => 'set-state-to-active change-active-state',
            'id' => 'change-active-state-' . $customer['Customers']['id_customer'],
            'title' => 'Zum Aktivieren anklicken'
        ], 'javascript:void(0);');
    }

    echo '</td>';

    echo '<td>';
    echo $customer['valid_orders_count'];
    $sumOrdersCount += $customer['valid_orders_count'];
    echo '</td>';

    if ($this->Html->paymentIsCashless()) {
        $negativeClass = $customer['payment_product_delta'] < 0 ? 'negative' : '';
        echo '<td align="center" class="' . $negativeClass . '">';

        if ($appAuth->isSuperadmin()) {
            $creditBalanceHtml = '<span class="'.$negativeClass.'">' . $this->Html->formatAsEuro($customer['payment_product_delta']);
            echo $this->Html->getJqueryUiIcon(
                $creditBalanceHtml,
                [
                'class' => 'icon-with-text',
                'title' => 'Guthaben anzeigen'
                ],
                $this->Slug->getCreditBalance($customer['Customers']['id_customer'])
            );
        } else {
            if ($customer['payment_product_delta'] != 0) {
                echo $this->Html->formatAsEuro($customer['payment_product_delta']);
            }
        }

        $sumPaymentsProductDelta += $customer['payment_product_delta'];
        echo '</td>';
    }

    if (Configure::read('app.emailOrderReminderEnabled')) {
        echo '<td>';
        echo $customer['Customers']['newsletter'];
        $sumEmailReminders += $customer['Customers']['newsletter'];
        echo '</td>';
    }

    echo '<td>';
    echo $this->Time->formatToDateShort($customer['Customers']['date_add']);
    echo '</td>';

    echo '<td>';
    echo $this->Time->formatToDateShort($customer['last_valid_order_date']);
    echo '</td>';

    echo '<td style="padding-left: 11px;">';
        echo $this->Html->getJqueryUiIcon(
            $this->Html->image($this->Html->getFamFamFamPath('user_comment.png')),
            [
                'class' => 'customer-comment-edit-button' . ($customer['AddressCustomers']['comment'] == '' ? ' disabled' : ''),
                'title' => $customer['AddressCustomers']['comment'] != '' ? $customer['AddressCustomers']['comment'] : 'Kommentar hinzufügen',
                'data-title-for-overlay' => $customer['AddressCustomers']['comment'] != '' ? $customer['AddressCustomers']['comment'] : 'Kommentar hinzufügen'
            ],
            'javascript:void(0);'
        );
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="4"><b>' . $i . '</b> Datensätze</td>';
echo '<td><b>' . $this->Html->formatAsDecimal($sumOrdersCount, 0) . '</b></td>';
if ($this->Html->paymentIsCashless()) {
    $sumPaymentsDepositDelta += $manufacturerDepositMoneySum;
    echo '<td>';
    echo '<b class="' . ($sumPaymentsProductDelta < 0 ? 'negative' : '') . '">€&nbsp;' . $this->Html->formatAsDecimal($sumPaymentsProductDelta) . '</b>';
    if (Configure::read('app.isDepositPaymentCashless')) {
        echo '<br /><b class="' . ($sumPaymentsDepositDelta < 0 ? 'negative' : '') . '">€&nbsp;' . $this->Html->formatAsDecimal($sumPaymentsDepositDelta) . '&nbsp;Pf.</b>';
    }
    echo '</td>';
}
if (Configure::read('app.emailOrderReminderEnabled')) {
    echo '<td><b>' . $sumEmailReminders . '</b></td>';
}
echo '<td colspan="5"></td>';
echo '</tr>';

echo '</table>';

echo '<div class="sc"></div>';

echo '<div class="bottom-button-container">';
echo '<button class="email-to-all btn btn-default" data-column="4"><i class="fa fa-envelope-o"></i> Alle E-Mail-Adressen kopieren</button>';
echo '</div>';
echo '<div class="sc"></div>';

?>
    <div class="sc"></div>
</div>
