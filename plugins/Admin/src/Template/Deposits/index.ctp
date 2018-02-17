<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
        var datefieldSelector = $('input.datepicker');
        datefieldSelector.datepicker();" . Configure::read('app.jsNamespace') . ".Admin.init();
    "
]);
?>

<div class="filter-container">
	<?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1><?php echo $title_for_layout; ?></h1>
        <?php
        if (!$appAuth->isManufacturer()) {
            echo $this->Form->input('manufacturerId', [
            'type' => 'select',
            'label' => '',
            'options' => $manufacturersForDropdown,
            'empty' => 'alle Hersteller',
            'default' => $manufacturerId != '' ? $manufacturerId : ''
            ]);
        }
        ?>
	    <div class="right"></div>
	<?php echo $this->Form->end(); ?>
</div>

<?php
if (empty($manufacturer)) {
    echo '<br /><h2 class="info">Bitte wähle einen Hersteller aus.</h2>';
    return;
}
?>

<div id="help-container">
    <ul>
        <?php echo $this->element('docs/abholdienst'); ?>
        <li>Hier wird das Pfand für den Hersteller <b><?php echo $manufacturer->name; ?></b> verwaltet.</li>
        <li>Pfand, das vor dem <?php echo date('d.m.Y', strtotime(Configure::read('app.depositForManufacturersStartDate')));?> verkauft / geliefert wurde, wird <b>nicht berücksichtigt</b>.</li>
        <li><b>Produkt mit Pfand geliefert</b>: Stichtag ist der Tag der Bestellung des Produktes, das "verpfandet" ist (nicht das Lieferdatum!)
        <li><b>Leergebinde zurückgenommen</b>: Stichtag ist der Tag, an dem das Retour-Pfand ins System eingetragen wurde. Dies kann entweder in Form von Leergebinde oder als Überweisung erfolgen.</li>
        <li>Ein Klick auf <b>Details</b> zeigt die genau Zusammensetzung des monatlichen Betrages an.</li>
        <?php if ($appAuth->isManufacturer() || $appAuth->isAdmin()) { ?>
            <li>Falls der Hersteller Leergebinde vom Abhollager mitnimmt, 
        <?php } ?>
        <?php if ($appAuth->isSuperadmin()) { ?>
            <li>Falls du dem Hersteller das Pfandkonto mit Geld ausgleichst, oder er Leergebinde mitnimmt, 
        <?php } ?>
            kannst du hier eine neue Pfand-Rücknahme eintragen.</li>
    </ul>
</div>    
    
<?php


echo '<div class="add-payment-deposit-wrapper">';
if ($appAuth->isManufacturer() || $appAuth->isAdmin()) {
    $buttonText = 'Leergebinde-Rücknahme eintragen';
}
if ($appAuth->isSuperadmin()) {
    $buttonText = 'Pfand-Rücknahme eintragen';
}
    echo $this->element('addDepositPaymentOverlay', [
        'buttonText' => $buttonText,
        'rowId' => $manufacturer->id_manufacturer,
        'userName' => $manufacturer->name,
        'manufacturerId' => $manufacturer->id_manufacturer
    ]);
    echo '</div>';
    echo '<div class="sc"></div>';

    if (empty($deposits)) {
        echo '<h2 class="info">Seit dem '.date('d.m.Y', strtotime(Configure::read('app.depositForManufacturersStartDate'))).' wurde noch kein Pfand geliefert oder zurückgenommen.</h2>';
    } else {
        echo '<table class="list no-clone-last-row">';

        echo '<tr class="sort">';
            echo '<th class="right">Monat</th>';
            echo '<th class="right">Produkt mit Pfand geliefert</th>';
            echo '<th class="right">Leergebinde zurückgenommen</th>';
        echo '</tr>';

        foreach ($deposits as $monthAndYear => $deposit) {
            echo '<tr>';

                echo '<td>';
                    echo $deposit['monthAndYearAsString'];
                echo '</td>';

                echo '<td class="right">';
            if (isset($deposit['delivered'])) {
                echo '<span style="float: left;">'.$this->Html->getJqueryUiIcon(
                    $this->Html->image($this->Html->getFamFamFamPath('zoom.png')) . ' Details',
                    [
                    'title' => 'Details anzeigen',
                    'class' => 'icon-with-text',
                    ],
                    '/admin/order-details/?manufacturerId='.$manufacturerId.'&dateFrom='.$deposit['dateFrom'].'&dateTo='.$deposit['dateTo'].'&deposit=1&orderStates[]='.join(',', $orderStates)
                ).'</span>';
                echo '<span style="float: right;">';
                echo $this->Html->formatAsEuro($deposit['delivered']);
                echo '</span>';
            }
                echo '</td>';

                echo '<td class="right negative">';
            if (isset($deposit['returned'])) {
                echo '<span style="float: left;">'.$this->Html->getJqueryUiIcon(
                    $this->Html->image($this->Html->getFamFamFamPath('zoom.png')) . ' Details',
                    [
                    'title' => 'Details anzeigen',
                    'class' => 'icon-with-text',
                    ],
                    $appAuth->isManufacturer() ? $this->Slug->getMyDepositDetail($monthAndYear) : $this->Slug->getDepositDetail($manufacturerId, $monthAndYear)
                ).'</span>';
                echo '<span style="float: right;">';
                echo $this->Html->formatAsEuro($deposit['returned']);
                echo '</span>';
            }
                echo '</td>';
            echo '</tr>';
        }

        echo '<tr class="fake-th">';
            echo '<td></td>';
            echo '<td class="right"><b>Pfand geliefert</b></td>';
            echo '<td class="right"><b>Pfand zurückgenommen</b></td>';
        echo '</tr>';

        echo '<tr>';
            echo '<td></td>';
            echo '<td class="right"><b>'.$this->Html->formatAsEuro($sumDepositsDelivered).'</b></td>';
            echo '<td class="right negative">';
        if ($sumDepositsReturned != 0) {
            echo '<b>'.$this->Html->formatAsEuro($sumDepositsReturned).'</b>';
        }
            echo '</td>';
        echo '</tr>';

        echo '<tr>';
            echo '<td colspan="2" class="right"><b>Dein Pfand-Kontostand</td>';
            $depositCreditBalance = $sumDepositsDelivered + $sumDepositsReturned;
            $depositCreditBalanceClasses = ['right'];
        if ($depositCreditBalance < 0) {
            $depositCreditBalanceClasses[] = 'negative';
        }
            echo '<td class="'.implode(' ', $depositCreditBalanceClasses).'"><b style="font-size: 16px;">'.$this->Html->formatAsEuro($depositCreditBalance).'</b></td>';
        echo '</tr>';

        echo '</table>';
    }

?>

<div class="sc"></div>
