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
<div id="payments-list">

    <?php
    $this->element('addScript', array(
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            var datefieldSelector = $('input.datepicker');
            datefieldSelector.datepicker();" . Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initAddPayment('#add-payment-button-wrapper .btn-success');" . Configure::read('app.jsNamespace') . ".Admin.initDeletePayment();"
    ));
    ?>
    
    <div id="help-container">
        <ul>
            <?php echo $helpText; ?>
        </ul>
    </div>    
    
    <?php /* filter container rendered to show print and help icons */ ?>
    <div class="filter-container">
        <h1><?php echo $title_for_layout; ?></h1>
        <div class="right"></div>
    </div>

<?php

echo '<div id="add-payment-button-wrapper">';
echo $this->Html->link('<i class="fa ' . $icon . ' fa-lg"></i> ' . $buttonText, 'javascript:void(0);', array(
    'class' => 'btn btn-success',
    'escape' => false
));
echo '<div id="add-payment-form" class="add-payment-form">';
echo '<h3>Neue Zahlung eintragen</h3>';
echo '<p>Bitte tippe hier den Betrag ein, den du <br />soeben auf unser Konto überwiesen hast.</p>';
echo $this->Form->input('Payment.amount', array(
    'label' => 'Betrag in €',
    'type' => 'string'
));
echo $this->Form->hidden('Payment.customerId', array(
    'value' => $customerId
));

if ($paymentType == 'product' && $appAuth->isSuperadmin()) {
    echo '<p style="margin-top: 10px;">Wenn es sich um eine Rückzahlung handelt,<br />trage bitte ein, wie viel du dem Mitgiled zurücküberwiesen hast.</p>';
    $i = 0;
    foreach ($this->Html->getSuperadminProductPaymentTexts($appAuth) as $paymentTextKey => $paymentText) {
        echo '<div class="radio-wrapper">';
            $checked = '';
        if ($i == 0) {
            $checked = 'checked="checked"';
        }
            echo '<label for="type-'.$paymentTextKey.'">'.$paymentText.'</label><input '.$checked.' id="type-'.$paymentTextKey.'"type="radio" name="type" value="'.$paymentTextKey.'"/>';
        echo '</div>';
        $i++;
    }
} else {
    echo $this->Form->hidden('Payment.type', array(
        'value' => $paymentType
    ));
}

if ($paymentType == 'member_fee') {
    echo '<div class="multiple-checkbox-wrapper">';

    echo '<label for="PaymentMonthsRange">Bitte wähle die Monate aus, für die deine Zahlung gedacht ist.</label>';

    $currentYear = date('Y');

    echo '<div style="width: 160px;float: left;opacity: 0.7">';
    $lastYear = $currentYear - 1;
    $monthsRange = $this->Time->getAllMonthsForYear($lastYear);
    echo $this->Form->input('Payment.months_range_' . $lastYear, array(
        'label' => '',
        'options' => $monthsRange,
        'multiple' => 'checkbox',
        'type' => 'select'
    ));
    echo '</div>';

    echo '<div style="width: 160px;float: left;">';
    $monthsRange = $this->Time->getAllMonthsForYear($currentYear);
    echo $this->Form->input('Payment.months_range_' . $currentYear, array(
        'label' => '',
        'options' => $monthsRange,
        'multiple' => 'checkbox',
        'type' => 'select'
    ));
    echo '</div>';

    echo '<div style="width: 160px;float: left;opacity: 0.7">';
    $nextYear = $currentYear + 1;
    $monthsRange = $this->Time->getAllMonthsForYear($nextYear);
    echo $this->Form->input('Payment.months_range_' . $nextYear, array(
        'label' => '',
        'options' => $monthsRange,
        'multiple' => 'checkbox',
        'type' => 'select'
    ));
    echo '</div>';

    echo '</div>';
}

echo '</div>';
echo '</div>';
echo '<div class="sc"></div>';

?>
