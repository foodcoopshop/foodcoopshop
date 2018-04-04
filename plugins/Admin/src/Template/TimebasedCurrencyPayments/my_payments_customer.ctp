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
    Configure::read('app.jsNamespace').".TimebasedCurrency.initPaymentAdd('#add-timebased-currency-payment-button-wrapper .btn-success');".
    Configure::read('app.jsNamespace').".TimebasedCurrency.initDeletePayment();"
]);
?>

<div id="help-container">
    <ul>
        Hier kannst du die geleisteten <?php echo Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME'); ?> eintragen.
    </ul>
</div>    

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right"></div>
</div>

<?php
    echo $this->Form->create(null, [
        'class' => 'fcs-form'
    ]);
        echo '<div id="add-timebased-currency-payment-button-wrapper">';
        echo $this->Html->link('<i class="fa fa-clock-o fa-lg"></i> Geleistete ' . Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . ' eintragen', 'javascript:void(0);', [
                'class' => 'btn btn-success',
                'escape' => false
            ]);
            echo '<div id="add-timebased-currency-payment-form" class="add-payment-form">';
                echo '<h3>Geleistete '.Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME').' eintragen</h3>';
                echo $this->Form->control('TimebasedCurrencyPayments.hours', [
                    'label' => 'Stunden',
                    'type' => 'select',
                    'value' => 0,
                    'options' => [0,1,2,3,4,5,6,7,8,9,10,11,12],
                    'class' => 'selectpicker-disabled time'
                ]);
                echo $this->Form->control('TimebasedCurrencyPayments.minutes', [
                    'label' => 'Minuten',
                    'type' => 'select',
                    'options' => [0 => '00', 15 => '15', 30 => '30', 45 => '45'],
                    'class' => 'selectpicker-disabled time'
                ]);
                echo $this->Form->control('TimebasedCurrencyPayments.manufacturerId', [
                    'type' => 'select',
                    'options' => $manufacturersForDropdown,
                    'label' => 'Hersteller',
                    'class' => 'selectpicker-disabled'
                ]);
                echo $this->Form->control('TimebasedCurrencyPayments.text', [
                    'label' => 'Anmerkungen',
                    'type' => 'textarea',
                    'placeholder' => 'Hier ist Platz für Anmerkungen, die der Hersteller lesen kann.'
                ]);
                echo $this->Form->hidden('TimebasedCurrencyPayments.customerId', ['value' => $appAuth->getUserId()]);
            echo '</div>';
        echo '</div>';
    echo $this->Form->end();

$tableColumnHead  = '<th>Datum</th>';
$tableColumnHead .= '<th>Text</th>';
$tableColumnHead .= '<th>Hersteller</th>';
$tableColumnHead .='<th style="text-align:right;">Geleistet</th>';
$tableColumnHead .='<th style="text-align:right;">Offen</th>';
$tableColumnHead .='<th style="width:25px;"></th>';

echo '<table class="list">';

    echo '<tr class="sort">';
        echo $tableColumnHead;
    echo '</tr>';

    foreach($payments as $payment) {
        
        echo '<tr>';
            echo '<td>';
                echo $payment['dateRaw']->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
            echo '</td>';
            
            echo '<td>';
                echo $payment['text'];
            echo '</td>';
            
            echo '<td>';
                echo $payment['manufacturerName'];
            echo '</td>';
            
            echo '<td align="right">';
                if ($payment['secondsDone']) {
                    echo $this->Time->formatSecondsToHoursAndMinutes($payment['secondsDone']);
                }
            echo '</td>';
            
            echo '<td class="negative" align="right">';
                if ($payment['secondsOpen']) {
                    echo $this->Time->formatSecondsToHoursAndMinutes($payment['secondsOpen']);
                }
            echo '</td>';
        
            echo '<td style="text-align:center;">';
                if ($payment['approval'] != APP_ON) {
                    echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
                        'class' => 'delete-payment-button',
                        'title' => 'Aufladung löschen?'
                    ], 'javascript:void(0);');
                }
            echo '</td>';
            
        echo '</tr>';
        
    }
    
    
    echo '<tr class="fake-th">';
        echo str_replace('th', 'td', $tableColumnHead);
    echo '</tr>';
    
    echo '<tr>';
        echo '<td colspan="3"></td>';
        echo '<td align="right"><b>' . $this->Time->formatSecondsToHoursAndMinutes($sumPayments) . '</b></td>';
        echo '<td align="right" class="negative"><b>' . $this->Time->formatSecondsToHoursAndMinutes($sumOrders) . '</b></td>';
        echo '<td></td>';
    echo '</tr>';
    
    echo '<tr>';
        echo '<td></td>';
        $sumNumberClass = '';
        if ($creditBalance < 0) {
            $sumNumberClass = ' class="negative"';
        }
        echo '<td colspan="2" ' . $sumNumberClass . '><b style="font-size: 16px;">Dein Kontostand: ' . $this->Time->formatSecondsToHoursAndMinutes($creditBalance) . '</b></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
    echo '</tr>';
    
echo '</table>';
    