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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.initAddPaymentInList('.add-payment-deposit-button');"
]);

echo $this->Html->link(
    '<i class="fas fa-fw fa-'.strtolower(Configure::read('app.currencyName')).'-sign"></i>' . $buttonText,
    'javascript:void(0);',
    [
        'data-object-id' => $rowId,
        'class' => 'btn btn-outline-light add-payment-deposit-button',
        'title' => __d('admin', 'Add_deposit_amount'),
        'escape' => false
    ]
);

echo '<div id="add-payment-deposit-form-' . $rowId . '" class="add-payment-form add-payment-deposit-form">';
echo '<h3>'.__d('admin', 'Add_deposit').'</h3>';
echo '<p>'.__d('admin', 'Add_deposit_amount_for_{0}', ['<b>' . $userName . '</b>']).':</p>';

if (isset($manufacturerId)) {
    if ($appAuth->isAdmin() || $appAuth->isManufacturer()) {
        echo '<p style="margin-top:10px;">'.__d('admin', 'Please_add_value_of_empty_glasses__that_is_taken_back_by_manufacturer.').'</p>';
        echo $this->Form->hidden('Payments.text', [
            'value' => 'empty_glasses'
        ]);
    }

    if ($appAuth->isSuperadmin()) {
        echo '<p style="margin-top:10px;">'.__d('admin', 'Did_the_manufacturer_taken_away_empty_glasses_or_was_his_deposit_account_compensated_with_money?').'</p>';
        foreach ($this->Html->getManufacturerDepositPaymentTexts() as $paymentTextKey => $paymentText) {
            echo '<div class="radio-wrapper">';
            echo '<label for="payment-'.$paymentTextKey.'-'.$rowId.'">'.$paymentText.'</label><input id="payment-'.$paymentTextKey.'-'.$rowId.'"type="radio" name="payment_text" value="'.$paymentTextKey.'"/>';
            echo '</div>';
        }
    }
}

echo $this->Form->control('Payments.amount', [
    'label' => __d('admin', 'Amount_in_{0}', [Configure::read('appDb.FCS_CURRENCY_SYMBOL')]),
    'type' => 'number',
    'step' => '0.01'
]);

echo $this->Form->hidden('Payments.type', [
    'value' => 'deposit'
]);
if (isset($customerId)) {
    echo $this->Form->hidden('Payments.customerId', [
        'value' => $customerId
    ]);
}
if (isset($manufacturerId)) {
    echo $this->Form->hidden('Payments.manufacturerId', [
        'value' => $manufacturerId
    ]);
}
echo '</div>';
echo '<div class="sc"></div>';
