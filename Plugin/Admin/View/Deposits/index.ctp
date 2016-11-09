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

$this->element('addScript', array(
    'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
        var datefieldSelector = $('input.datepicker');
        datefieldSelector.datepicker();" . Configure::read('app.jsNamespace') . ".Admin.init();
    "
));
?>

<div class="filter-container">
	<h1>Pfand-Verwaltung</h1>
	<div class="right"></div>
</div>

<div id="help-container">
	<ul>
        <?php echo $this->element('shopdienstInfo'); ?>
        <li>Hier wird seit dem <?php echo date('d.m.Y', strtotime(Configure::read('app.depositForManufacturersStartDate')));?> das Pfand für den Hersteller verwaltet.</li>
        <li>Pfand, das vor diesem Zeitraum verkauft / geliefert wurde, wird <b>nicht berücksichtigt</b>.</li>
        <li><b>Geliefertes Pfand</b>: Stichtag ist der Tag der Bestellung des Produktes, das "verpfandet" ist (nicht das Lieferdatum!)
        <li><b>Zurückgenommenes Pfand</b>: Stichtag ist der Tag, an dem das Retour-Pfand ins System eingetragen wurde. Dies kann entweder in Form von Leergebinde oder als Überweisung erfolgen.</li>
    </ul>
</div>    
    
<?php

if (empty($deposits)) {
    echo '<h2 class="warning">Seit dem '.date('d.m.Y', strtotime(Configure::read('app.depositForManufacturersStartDate'))).' wurde noch kein Pfand geliefert oder zurückgenommen.</h2>';
} else {

    echo '<table class="list no-clone-last-row">';
    
        echo '<tr class="sort">';
            echo '<th class="right">Monat</th>';
            echo '<th class="right">Pfand geliefert</th>';
            echo '<th class="right">Pfand zurückgenommen</th>';
        echo '</tr>';
    
        foreach($deposits as $deposit) {
            
            echo '<tr>';
                
                echo '<td>';
                    echo '<span style="float: left;">'.$deposit['monthAndYearAsString'].'</span>';
                    echo '<span style="float: right;">'.$this->Html->getJqueryUiIcon($this->Html->image('/js/vendor/famfamfam-silk/dist/png/zoom.png') . ' Details anzeigen',
                        array(
                            'title' => 'Details anzeigen',
                            'class' => 'icon-with-text',
                        ),
                        '/admin/order_details/index/dateFrom:'.$deposit['dateFrom'].'/dateTo:'.$deposit['dateTo'].'/deposit:1/orderState:'.$orderState
                    ).'</span>';
                '</td>';
                
                echo '<td class="right">';
                    if (isset($deposit['delivered'])) {
                        echo $this->Html->formatAsEuro($deposit['delivered']);
                    }
                echo '</td>';
                
                echo '<td class="right negative">';
                    if (isset($deposit['returned'])) {
                        echo $this->Html->formatAsEuro($deposit['returned']);
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
            $depositCreditBalanceClasses = array('right');
            if ($depositCreditBalance < 0) {
                $depositCreditBalanceClasses[] = 'negative';
            }
            echo '<td class="'.implode(' ', $depositCreditBalanceClasses).'"><b style="font-size: 16px;">'.$this->Html->formatAsEuro($depositCreditBalance).'</b></td>';
        echo '</tr>';
        
    echo '</table>';
}

?>

<div class="sc"></div>
