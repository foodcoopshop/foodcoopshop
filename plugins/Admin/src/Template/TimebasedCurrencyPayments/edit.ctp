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

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" .
    Configure::read('app.jsNamespace') . ".Admin.initForm();".
    Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
    $('input.datepicker').datepicker();"
]);

if ($isEditMode) {
    $script = Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".$this->TimebasedCurrency->getName()."');";
} else {
    $script = Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('Meine Daten', '".$this->TimebasedCurrency->getName()."');";
}
$this->element('addScript', ['script' => $script]);

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a> <a href="javascript:void(0);"
            class="btn btn-default cancel"><i class="fa fa-remove"></i> Abbrechen</a>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du eine Zeit-Eintragung erstellen.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($payment, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Slug->getTimebasedCurrencyPaymentEdit($payment->id) : $this->Slug->getTimebasedCurrencyPaymentAdd(),
    'id' => 'timebasedCurrencyPaymentEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);
    
    echo '<div class="input">';
        echo '<label for="TimebasedCurrencyPayments.working_day">Arbeitstag</label>';
        echo $this->element('dateFields', ['dateFrom' => ($payment->working_day ? $payment->working_day->i18nFormat(Configure::read('DateFormat.de.DateLong2')) : null), 'nameFrom' => 'TimebasedCurrencyPayments[working_day]', 'showDateTo' => false]);
    echo '</div>';
    echo $this->Form->control('TimebasedCurrencyPayments.hours', [
        'label' => 'Stunden',
        'type' => 'select',
        'options' => [0,1,2,3,4,5,6,7,8,9,10,11,12],
        'class' => 'selectpicker-disabled time'
    ]);
    echo $this->Form->control('TimebasedCurrencyPayments.minutes', [
        'label' => 'Minuten',
        'type' => 'select',
        'options' => [0 => '00', 15 => '15', 30 => '30', 45 => '45'],
        'class' => 'selectpicker-disabled time'
    ]);
    if (!$isEditMode) {
        echo $this->Form->control('TimebasedCurrencyPayments.id_manufacturer', [
            'type' => 'select',
            'options' => $manufacturersForDropdown,
            'label' => 'Hersteller'
        ]);
    }
    if ($isEditMode) {
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Helper.initCkeditor('timebasedcurrencypayments-approval-comment');"
        ]);
        echo $this->Form->control('TimebasedCurrencyPayments.approval', [
            'type' => 'select',
            'label' => 'Bestätigt? <span class="after small">Ist die Eintragung vom Mitglied in Ordnung?</span>',
            'options' => $this->Html->getApprovalStates(),
            'escape' => false
        ]);
        echo $this->Form->control('TimebasedCurrencyPayments.approval_comment', [
            'label' => 'Anmerkungen<br /><br /><div class="after small">Hier ist Platz für Anmerkungen, die das Mitglied lesen kann.</div>',
            'type' => 'textarea',
            'class' => 'ckeditor',
            'escape' => false
     ]);
    } else {
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Helper.initCkeditor('timebasedcurrencypayments-text');"
        ]);
        echo $this->Form->control('TimebasedCurrencyPayments.text', [
            'label' => 'Anmerkungen<br /><br /><div class="after small">Hier ist Platz für Anmerkungen, die der Hersteller lesen kann.</div>',
            'type' => 'textarea',
            'class' => 'ckeditor',
            'escape' => false
        ]);
    }
    
echo '</div>';

echo $this->Form->end();

?>

<div class="sc"></div>
