<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Admin.init();".
    Configure::read('app.jsNamespace').".Admin.initForm();".
    Configure::read('app.jsNamespace').".TimebasedCurrency.initPaymentAdd('#add-timebased-currency-payment-button-wrapper .btn-success');"
]);
?>

<div id="help-container">
    <ul>
        Hier kannst du die Zeit-Eintragungen erstellen, löschen und bestätigen.
    </ul>
</div>    

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right"></div>
</div>

<?php

$tableColumnHead  = '<th>Mitglied</th>';
$tableColumnHead .='<th style="text-align:right;">Geleistet</th>';
$tableColumnHead .='<th style="text-align:right;">Offen</th>';
$tableColumnHead .='<th style="text-align:right;">Saldo</th>';

echo '<table class="list">';

    echo '<tr class="sort">';
        echo $tableColumnHead;
    echo '</tr>';

    foreach($payments as $payment) {
        
        echo '<tr>';
            
            echo '<td>';
                echo $payment['customerName'];
                echo '<span style="float: right;">'.$this->Html->getJqueryUiIcon(
                    $this->Html->image($this->Html->getFamFamFamPath('zoom.png')) . ' Details',
                    [
                        'title' => 'Details anzeigen',
                        'class' => 'icon-with-text',
                    ],
                    $this->Slug->getTimebasedCurrencyPaymentDetailsForManufacturers($payment['customerId'])
                ).'</span>';
                    
            echo '</td>';
            
            echo '<td align="right">';
                if (isset($payment['secondsDone'])) {
                    echo $this->Time->formatSecondsToHoursAndMinutes($payment['secondsDone']);
                }
            echo '</td>';
            
            echo '<td class="negative" align="right">';
                if (isset($payment['secondsOpen'])) {
                    echo $this->Time->formatSecondsToHoursAndMinutes($payment['secondsOpen']);
                }
            echo '</td>';
            
            $creditBalanceClass = '';
            if ($payment['creditBalance'] < 0) {
                $creditBalanceClass = 'negative';
            }
            echo '<td class="'.$creditBalanceClass.'" align="right">';
                echo $this->Time->formatSecondsToHoursAndMinutes($payment['creditBalance']);
            echo '</td>';
            
        echo '</tr>';
        
    }
    
    
    echo '<tr class="fake-th">';
        echo str_replace('th', 'td', $tableColumnHead);
    echo '</tr>';
    
    echo '<tr>';
        echo '<td></td>';
        echo '<td align="right"><b>' . $this->Time->formatSecondsToHoursAndMinutes($sumPayments) . '</b></td>';
        echo '<td align="right" class="negative"><b>' . $this->Time->formatSecondsToHoursAndMinutes($sumOrders) . '</b></td>';
        echo '<td></td>';
    echo '</tr>';
    
    echo '<tr>';
        $sumNumberClass = '';
        if ($creditBalance < 0) {
            $sumNumberClass = ' class="negative"';
        }
        echo '<td colspan="2" ' . $sumNumberClass . '><b style="font-size: 16px;">Dein Kontostand: ' . $this->Time->formatSecondsToHoursAndMinutes($creditBalance) . '</b></td>';
        echo '<td></td>';
        echo '<td></td>';
    echo '</tr>';
    
echo '</table>';
    