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
    'script' => Configure::read('AppConfig.jsNamespace') . ".Admin.initAddPaymentInList('.add-payment-deposit-button');"
));

echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('money_euro.png')) . $buttonText, array(
    'title' => 'Pfand-Betrag eintragen',
    'class' => 'add-payment-deposit-button icon-with-text',
    'data-object-id' => $rowId
), 'javascript:void(0);');
echo '<div id="add-payment-deposit-form-' . $rowId . '" class="add-payment-form add-payment-deposit-form">';
echo '<h3>Pfand eintragen</h3>';
echo '<p>Pfand-Betrag für <b>' . $userName . '</b> eintragen:</p>';

if (isset($manufacturerId)) {
    if ($appAuth->isAdmin() || $appAuth->isManufacturer()) {
        echo '<p style="margin-top:10px;">Bitte trage hier den Wert des Leergebindes ein,<br />
            das vom Hersteller zurückgenommen wird.</p>';
        echo $this->Form->hidden('Payments.text', array(
            'value' => 'empty_glasses'
        ));
    }

    if ($appAuth->isSuperadmin()) {
        echo '<p style="margin-top:10px;">Hat der Hersteller Leergebinde mitgenommen<br />oder wurde sein Pfandkonto mit Geld ausgeglichen?</p>';
        foreach ($this->Html->getManufacturerDepositPaymentTexts() as $paymentTextKey => $paymentText) {
            echo '<div class="radio-wrapper">';
            echo '<label for="payment-'.$paymentTextKey.'-'.$rowId.'">'.$paymentText.'</label><input id="payment-'.$paymentTextKey.'-'.$rowId.'"type="radio" name="payment_text" value="'.$paymentTextKey.'"/>';
            echo '</div>';
        }
    }
}

echo $this->Form->input('Payments.amount', array(
    'label' => 'Betrag in €',
    'type' => 'string'
));

echo $this->Form->hidden('Payments.type', array(
    'value' => 'deposit'
));
if (isset($customerId)) {
    echo $this->Form->hidden('Payments.customerId', array(
        'value' => $customerId
    ));
}
if (isset($manufacturerId)) {
    echo $this->Form->hidden('Payments.manufacturerId', array(
        'value' => $manufacturerId
    ));
}
echo '</div>';
echo '<div class="sc"></div>';
