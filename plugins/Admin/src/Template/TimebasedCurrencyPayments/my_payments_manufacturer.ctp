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
        Hier kannst du die geleisteten <?php echo Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME'); ?> eintragen bzw. bestätigen.
    </ul>
</div>    

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right"></div>
</div>

<?php

$tableColumnHead  = '<th>Mitglied</th>';
$tableColumnHead .= '<th>Text</th>';
$tableColumnHead .='<th style="text-align:right;">Geleistet</th>';
$tableColumnHead .='<th style="text-align:right;">Offen</th>';

echo '<table class="list">';

    echo '<tr class="sort">';
        echo $tableColumnHead;
    echo '</tr>';

    foreach($payments as $payment) {
        
        echo '<tr>';
            
            echo '<td>';
                echo $payment['text'];
            echo '</td>';
            
            echo '<td>';
                echo $payment['customerName'];
            echo '</td>';
            
            echo '<td align="right">';
                if ($payment['timeDone']) {
                    echo $this->Time->formatDecimalToHoursAndMinutes($payment['timeDone']);
                }
            echo '</td>';
            
            echo '<td class="negative" align="right">';
                if ($payment['timeOpen']) {
                    echo $this->Time->formatDecimalToHoursAndMinutes($payment['timeOpen']);
                }
            echo '</td>';
            
        echo '</tr>';
        
    }
    
    
    echo '<tr class="fake-th">';
        echo str_replace('th', 'td', $tableColumnHead);
    echo '</tr>';
    
    echo '<tr>';
        echo '<td colspan="2"></td>';
        echo '<td align="right"><b>' . $this->Time->formatDecimalToHoursAndMinutes($sumPayments) . '</b></td>';
        echo '<td align="right" class="negative"><b>' . $this->Time->formatDecimalToHoursAndMinutes($sumOrders) . '</b></td>';
    echo '</tr>';
    
    echo '<tr>';
        echo '<td></td>';
        $sumNumberClass = '';
        if ($creditBalance < 0) {
            $sumNumberClass = ' class="negative"';
        }
        echo '<td colspan="2" ' . $sumNumberClass . '><b style="font-size: 16px;">Dein Kontostand: ' . $this->Time->formatDecimalToHoursAndMinutes($creditBalance) . '</b></td>';
        echo '<td></td>';
    echo '</tr>';
    
echo '</table>';
    