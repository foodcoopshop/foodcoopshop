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
<div id="manufacturers-list">
    <?php
    $this->element('addScript', array(
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            var datefieldSelector = $('input.datepicker');
            datefieldSelector.datepicker();" . Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initEmailToAllButton();" . Configure::read('app.jsNamespace') . ".AppFeatherlight.initLightboxForImages('a.lightbox');" . Configure::read('app.jsNamespace') . ".Helper.setCakeServerName('" . Configure::read('app.cakeServerName') . "');".Configure::read('app.jsNamespace') . ".Helper.initTooltip('.manufacturer-details-read-button');"
    ));
    if (Configure::read('app.allowManualOrderListSending')) {
        $this->element('addScript', array(
            'script' => Configure::read('app.jsNamespace') . ".Admin.setWeekdaysBetweenOrderSendAndDelivery('" . json_encode($this->MyTime->getWeekdaysBetweenOrderSendAndDelivery()) . "');" . Configure::read('app.jsNamespace') . ".Admin.initManualOrderListSend('#manufacturers-list .manual-order-list-send-link', " . date('N', time()) . ");"
        ));
    }
    ?>

    <div class="filter-container">
        <?php echo $this->element('dateFields', array('dateFrom' => $dateFrom, 'dateTo' => $dateTo)); ?>
        <?php echo $this->Form->input('active', array('type' => 'select', 'label' => '', 'options' => $this->MyHtml->getActiveStates(), 'selected' => isset($active) ? $active : '')); ?>
        <button id="filter" class="btn btn-success">
            <i class="fa fa-search"></i> Filtern
        </button>
        <div class="right">
            <?php
            echo '<div id="add-manufacturer-button-wrapper" class="add-button-wrapper">';
            echo $this->Html->link('<i class="fa fa-plus-square fa-lg"></i> Neuen Hersteller erstellen', $this->Slug->getManufacturerAdd(), array(
                'class' => 'btn btn-default',
                'escape' => false
            ));
            echo '</div>';
            ?>
        </div>
    </div>

    <div id="help-container">
        <ul>
            <li>Auf dieser Seite werden die <b>Hersteller</b> verwaltet.</li>
            <?php echo $this->element('docs/hersteller'); ?>
        </ul>
    </div>    
    
<?php

echo '<table class="list">';
echo '<tr class="sort">';
echo '<th class="hide">' . $this->Paginator->sort('Manufacturer.id_manufacturer', 'ID') . '</th>';
echo '<th>Logo</th>';
echo '<th></th>';
echo '<th>' . $this->Paginator->sort('Manufacturer.name', 'Name') . '</th>';
echo '<th style="width:83px;">Artikel</th>';
echo '<th>Pfand</th>';
echo '<th>' . $this->Paginator->sort('Customer.name', 'Ansprechperson') . '</th>';
echo '<th>' . $this->Paginator->sort('Manufacturer.iban', 'IBAN') . '</th>';
echo '<th>' . $this->Paginator->sort('Manufacturer.active', 'Aktiv') . '</th>';
echo '<th>' . $this->Paginator->sort('Manufacturer.holiday_from', 'Urlaub') . '</th>';
echo '<th>' . $this->Paginator->sort('Manufacturer.is_private', 'Nur für Mitglieder') . '</th>';
echo '<th>Opt.</th>';
if (Configure::read('app.db_config_FCS_USE_VARIABLE_MEMBER_FEE')) {
    echo '<th>%</th>';
}
echo '<th></th>';
if (Configure::read('app.allowManualOrderListSending')) {
    echo '<th></th>';
}
echo '<th></th>';
echo '<th></th>';
echo '</tr>';
$i = 0;
$productCountSum = 0;
foreach ($manufacturers as $manufacturer) {
    $i ++;
    echo '<tr id="manufacturer-' . $manufacturer['Manufacturer']['id_manufacturer'] . '" class="data">';
    echo '<td class="hide">';
    echo $manufacturer['Manufacturer']['id_manufacturer'];
    echo '</td>';
    echo '<td align="center" style="background-color: #fff;">';
    $srcLargeImage = $this->Html->getManufacturerImageSrc($manufacturer['Manufacturer']['id_manufacturer'], 'large');
    $largeImageExists = preg_match('/de-default-large_default/', $srcLargeImage);
    if (! $largeImageExists) {
        echo '<a class="lightbox" href="' . $srcLargeImage . '">';
    }
    echo '<img width="50" src="' . $this->Html->getManufacturerImageSrc($manufacturer['Manufacturer']['id_manufacturer'], 'medium') . '" />';
    if (! $largeImageExists) {
        echo '</a>';
    }
    echo '</td>';
    echo '<td>';
    echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), array(
        'title' => 'Bearbeiten'
    ), $this->Slug->getManufacturerEdit($manufacturer['Manufacturer']['id_manufacturer']));
    echo '</td>';

    echo '<td>';

    $details = $manufacturer['Address']['firstname'] . ' ' . $manufacturer['Address']['lastname'];
    if ($manufacturer['Address']['phone_mobile'] != '') {
        $details .= '<br />'.$manufacturer['Address']['phone_mobile'];
    }
    if ($manufacturer['Address']['phone'] != '') {
        $details .= '<br />' . $manufacturer['Address']['phone'];
    }
        echo '<div class="manufacturer-details-wrapper">';
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('telephone.png')), array(
                'class' => 'manufacturer-details-read-button',
                'title' => $details
            ), 'javascript:void(0);');
        echo '</div>';

        echo '<b>' . $manufacturer['Manufacturer']['name'] . '</b><br />';
        echo $manufacturer['Address']['city'];
        echo '<br /><span class="email">' . $manufacturer['Address']['email'] . '</span><br />';
    echo '</td>';

    echo '<td style="width:130px;">';
    $productCountSum += $manufacturer['product_count'];
    echo $this->Html->getJqueryUiIcon(
        $this->Html->image($this->Html->getFamFamFamPath('tag_green.png')) . $manufacturer['product_count'] . '&nbsp;Artikel',
        array(
        'title' => 'Alle Artikel von ' . $manufacturer['Manufacturer']['name'] . ' anzeigen',
        'class' => 'icon-with-text'
        ),
        $this->Slug->getProductAdmin($manufacturer['Manufacturer']['id_manufacturer'])
    );
    echo '</td>';

    echo '<td>';
    if ($manufacturer['sum_deposit_delivered'] > 0) {
        $depositSaldoClasses = array();
        if ($manufacturer['deposit_credit_balance'] < 0) {
            $depositSaldoClasses[] = 'negative';
        }
        $depositSaldoHtml = '<span class="'.implode(' ', $depositSaldoClasses).'">' . $this->Html->formatAsEuro($manufacturer['deposit_credit_balance']);

        if ($appAuth->isManufacturer()) {
            $depositOverviewUrl = $this->Slug->getMyDepositList();
        } else {
            $depositOverviewUrl = $this->Slug->getDepositList($manufacturer['Manufacturer']['id_manufacturer']);
        }
        echo $this->Html->getJqueryUiIcon(
            'Pfand:&nbsp;' . $depositSaldoHtml,
            array(
            'class' => 'icon-with-text',
            'title' => 'Pfandkonto anzeigen'
            ),
            $depositOverviewUrl
        );
    }
    echo '</td>';

    echo '<td>';
    if (!empty($manufacturer['Customer'])) {
        echo $manufacturer['Customer']['firstname'] . ' ' . $manufacturer['Customer']['lastname'];
    }
    echo '</td>';

    echo '<td style="text-align:center;width:42px;">';
    if ($manufacturer['Manufacturer']['iban'] != '') {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    }
    echo '</td>';
    echo '<td style="text-align:center;padding-left:5px;width:42px;">';
    if ($manufacturer['Manufacturer']['active'] == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    }
    if ($manufacturer['Manufacturer']['active'] == '') {
        echo $this->Html->image($this->Html->getFamFamFamPath('delete.png'));
    }
    echo '</td>';

    echo '<td>';
        echo $this->Html->getManufacturerHolidayString($manufacturer['Manufacturer']['holiday_from'], $manufacturer['Manufacturer']['holiday_to'], $manufacturer[0]['IsHolidayActive']);
    echo '</td>';

    echo '<td align="center">';
    if ($manufacturer['Manufacturer']['is_private'] == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    }
    echo '</td>';

    echo '<td>';
    echo $this->Html->getJqueryUiIcon(
        $this->Html->image($this->Html->getFamFamFamPath('page_white_gear.png')),
        array(
            'title' => 'Hersteller-Einstellungen bearbeiten'
        ),
        $this->Slug->getManufacturerEditOptions($manufacturer['Manufacturer']['id_manufacturer'])
    );
    echo '</td>';

    if (Configure::read('app.db_config_FCS_USE_VARIABLE_MEMBER_FEE')) {
        echo '<td>';
            echo $manufacturer['Manufacturer']['variable_member_fee'].'%';
        echo '</td>';
    }

    echo '<td style="width:140px;">';
    echo 'Bestellliste prüfen<br />';
    echo $this->Html->link('Artikel', '/admin/manufacturers/getOrderListByProduct/' . $manufacturer['Manufacturer']['id_manufacturer'] . '/' . $dateFrom . '/' . $dateTo . '.pdf', array(
            'target' => '_blank'
        ));
    echo ' / ';
    echo $this->Html->link('Mitglied', '/admin/manufacturers/getOrderListByCustomer/' . $manufacturer['Manufacturer']['id_manufacturer'] . '/' . $dateFrom . '/' . $dateTo . '.pdf', array(
        'target' => '_blank'
    ));
    echo '</td>';
    if (Configure::read('app.allowManualOrderListSending')) {
        echo '<td>';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('email.png')), array(
            'title' => 'Bestellliste manuell versenden',
            'class' => 'manual-order-list-send-link'
        ), 'javascript:void(0);');
        echo '</td>';
    }

    echo '<td>';
    echo $this->Html->link('Rechnung prüfen', '/admin/manufacturers/getInvoice/' . $manufacturer['Manufacturer']['id_manufacturer'] . '/' . $dateFrom . '/' . $dateTo . '.pdf', array(
        'target' => '_blank'
    ));
    echo '</td>';
    echo '<td style="width: 29px;">';
    if ($manufacturer['Manufacturer']['active']) {
        $manufacturerLink = $this->Slug->getManufacturerDetail($manufacturer['Manufacturer']['id_manufacturer'], $manufacturer['Manufacturer']['name']);
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('arrow_right.png')), array(
            'title' => 'Hersteller-Seite',
            'target' => '_blank'
        ), $manufacturerLink);
    }
    echo '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="3"><b>' . $i . '</b> Datensätze</td>';
echo '<td><b>' . $productCountSum . '</b></td>';
$colspan = 10;
if (Configure::read('app.db_config_FCS_USE_VARIABLE_MEMBER_FEE')) {
    $colspan ++;
}
if (Configure::read('app.allowManualOrderListSending')) {
    $colspan ++;
}
echo '<td colspan="' . $colspan . '"></td>';
echo '</tr>';
echo '</table>';
echo '<div class="sc"></div>';
echo '<div class="bottom-button-container">';
echo '<button class="email-to-all btn btn-default" data-column="4"><i class="fa fa-envelope-o"></i> Alle E-Mail-Adressen kopieren</button>';
echo '</div>';
echo '<div class="sc"></div>';

?>
</div>
