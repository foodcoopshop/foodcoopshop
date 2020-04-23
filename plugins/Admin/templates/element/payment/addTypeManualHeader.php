<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div id="payments-list">

    <?php
    $this->element('addScript', [
        'script' =>
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".Admin.initAddPayment('#add-payment-button-wrapper .btn-success');" .
            Configure::read('app.jsNamespace') . ".Admin.initDeletePayment();"
    ]);
    ?>
    
    <div class="filter-container">
        <h1><?php echo $title_for_layout; ?></h1>
        <div class="right">
        	<?php echo $icons; ?>
        </div>
    </div>

	<p><b><?php echo __d('admin', 'Bank_account_data'); ?>: </b><?php echo $extraInfo; ?></p>

<?php

echo '<div id="add-payment-button-wrapper">';
echo $this->Html->link('<i class="fas ' . $icon . '"></i> ' . $buttonText, 'javascript:void(0);', [
    'class' => 'btn btn-success',
    'escape' => false
]);
echo '<div id="add-payment-form" class="add-payment-form">';
echo '<h3>'.__d('admin', 'Add_new_payment').'</h3>';
echo '<p>'.__d('admin', 'Please_enter_the_amount_that_you_just_transfered_to_our_bank_account.').'</p>';
echo $this->Form->control('Payments.amount', [
    'label' => __d('admin', 'Amount_in_{0}', [Configure::read('appDb.FCS_CURRENCY_SYMBOL')]),
    'type' => 'number',
    'step' => '0.01'
]);
echo $this->Form->hidden('Payments.customerId', [
    'value' => $customerId
]);

if ($paymentType == 'product' && $appAuth->isSuperadmin()) {
    echo '<p style="margin-top: 10px;">'.__d('admin', 'If_payback_please_add_amount_that_you_transfered_back_to_the_bank_account_of_the_member.').'</p>';
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
    echo $this->Form->hidden('Payments.type', [
        'value' => $paymentType
    ]);
}

if ($paymentType == 'member_fee') {
    echo '<div class="multiple-checkbox-wrapper">';

    echo '<label for="PaymentMonthsRange">'.__d('admin', 'Please_chose_the_months_for_your_payment.').'</label>';

    $currentYear = date('Y');

    echo '<div style="width: 160px;float: left;opacity: 0.7">';
    $lastYear = $currentYear - 1;
    $monthsRange = $this->Time->getAllMonthsForYear($lastYear);
    echo $this->Form->control('Payments.months_range_' . $lastYear, [
        'label' => '',
        'options' => $monthsRange,
        'multiple' => 'checkbox',
        'type' => 'select'
    ]);
    echo '</div>';

    echo '<div style="width: 160px;float: left;">';
    $monthsRange = $this->Time->getAllMonthsForYear($currentYear);
    echo $this->Form->control('Payments.months_range_' . $currentYear, [
        'label' => '',
        'options' => $monthsRange,
        'multiple' => 'checkbox',
        'type' => 'select'
    ]);
    echo '</div>';

    echo '<div style="width: 160px;float: left;opacity: 0.7">';
    $nextYear = $currentYear + 1;
    $monthsRange = $this->Time->getAllMonthsForYear($nextYear);
    echo $this->Form->control('Payments.months_range_' . $nextYear, [
        'label' => '',
        'options' => $monthsRange,
        'multiple' => 'checkbox',
        'type' => 'select'
    ]);
    echo '</div>';

    echo '</div>';
}

echo '</div>';

if ($appAuth->isSuperadmin() && !Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
    echo $this->element('payment/personalTransactionCode', ['personalTransactionCode' => $personalTransactionCode]);
}

echo '</div>';
echo '<div class="sc"></div>';

?>
