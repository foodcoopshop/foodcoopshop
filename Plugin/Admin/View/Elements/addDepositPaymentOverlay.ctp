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
    'script' => Configure::read('app.jsNamespace') . ".Admin.initAddPaymentInList('.add-payment-deposit-button');"
));

echo $this->Html->getJqueryUiIcon($this->Html->image('/js/vendor/famfamfam-silk/dist/png/money_euro.png') . $buttonText, array(
    'title' => 'Pfand-Betrag eintragen',
    'class' => 'add-payment-deposit-button icon-with-text',
    'data-object-id' => $rowId
), 'javascript:void(0);');
echo '<div id="add-payment-deposit-form-' . $rowId . '" class="add-payment-form add-payment-deposit-form">';
echo '<h3>Pfand eintragen</h3>';
echo '<p>Pfand-Betrag für <b>' . $userName . '</b> eintragen:</p>';
echo $this->Form->input('CakePayment.amount', array(
    'label' => 'Betrag in €',
    'type' => 'string'
));
echo $this->Html->link('<i class="fa"></i> Kommentar hinzufügen', 'javascript:void(0);', array(
    'class' => 'toggle-link',
    'title' => 'Kommentar hinzufügen',
    'escape' => false
));
echo '<div class="toggle-content">';
echo $this->Form->textarea('CakePayment.text');
echo '</div>';
echo $this->Form->hidden('CakePayment.type', array(
    'value' => 'deposit'
));
if (isset($customerId)) {
    echo $this->Form->hidden('CakePayment.customerId', array(
        'value' => $customerId
    ));
}
if (isset($manufacturerId)) {
    echo $this->Form->hidden('CakePayment.manufacturerId', array(
        'value' => $manufacturerId
    ));
}
echo '</div>';
echo '<div class="sc"></div>';

?>