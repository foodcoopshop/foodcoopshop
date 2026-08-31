<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use App\Model\Entity\Payment;

echo $this->Html->link(
    '<i class="' . $this->Html->getFontAwesomeIconForCurrencyName() . '"></i> ' . $buttonText,
    'javascript:void(0);',
    [
        'data-object-id' => $objectId,
        'class' => 'btn btn-outline-light add-payment-deposit-button with-text',
        'title' => __('Add_deposit_amount'),
        'escape' => false
    ]
);

echo '<div id="add-payment-deposit-form-' . $objectId . '" class="add-payment-form add-payment-deposit-form">';
echo '<h3>'.__('Add_deposit').'</h3>';

if (isset($userName)) {
    echo '<p>'.__('Add_deposit_amount_for_{0}_admin', ['<b>' . $userName . '</b>']).':</p>';
}

if (isset($customerId)) {
    if (isset($showCustomerDropdown) && $showCustomerDropdown) {
        echo '<p style="margin-bottom:0;">'.__('Add_deposit_amount_for').':</p>';
        echo $this->Form->control('Payments.customerId', [
            'type' => 'select',
            'label' => '',
            'class' => 'no-tom-select',
            'value' => $customerId,
        ]);
    } else {
        echo $this->Form->control('Payments.customerId', [
            'type' => 'hidden',
            'value' => $customerId,
        ]);
    }
}

if (isset($manufacturerId)) {
    if ($identity->isAdmin() || $identity->isManufacturer()) {
        echo '<p style="margin-top:10px;">'.__('Please_add_value_of_empty_glasses__that_is_taken_back_by_manufacturer.').'</p>';
        echo $this->Form->hidden('Payments.text', [
            'value' => Payment::TEXT_EMPTY_GLASSES,
        ]);
    }

    if ($identity->isSuperadmin()) {
        echo '<p style="margin-top:10px;">'.__('Did_the_manufacturer_taken_away_empty_glasses_or_was_his_deposit_account_compensated_with_money?').'</p>';
        echo '<div class="radio-group">';
            foreach ($this->Html->getManufacturerDepositPaymentTexts() as $paymentTextKey => $paymentText) {
                echo '<div class="radio-wrapper">';
                    echo '<input id="payment-'.$paymentTextKey.'-'.$objectId.'"type="radio" name="payment_text" value="'.$paymentTextKey.'"/>';
                    echo '<label for="payment-'.$paymentTextKey.'-'.$objectId.'">'.$paymentText.'</label>';
                echo '</div>';
            }
        echo '</div>';
    }

}

echo $this->Form->control('Payments.amount', [
    'label' => __('Amount_in_{0}', [Configure::read('appDb.FCS_CURRENCY_SYMBOL')]),
    'type' => 'number',
    'step' => '0.01',
]);

if (isset($manufacturerId) && $identity->isSuperadmin()) {
    echo '<p style="margin-top:10px;">' . __('You can enter a negative amount if the manufacturer needs to pay money back to the initiative.') . '</p>';
}

if (isset($manufacturerId)) {
    echo $this->Form->control('Payments.date_add', [
        'label' => __('Date'),
        'type' => 'text',
        'value' => date($this->Time->getI18Format('DateShortAlt'), $this->Time->getCurrentDay()),
        'class' => 'datepicker',
    ]);
}

echo $this->Form->control('Payments.type', [
    'type' => 'hidden',
    'value' => Payment::TYPE_DEPOSIT,
]);
if (isset($manufacturerId)) {
    echo $this->Form->control('Payments.manufacturerId', [
        'type' => 'hidden',
        'value' => $manufacturerId,
    ]);
}
echo '</div>';
echo '<div class="sc"></div>';
