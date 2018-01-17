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

$this->element('addScript', array(
    'script' => Configure::read('AppConfig.jsNamespace') . ".Admin.init();" . Configure::read('AppConfig.jsNamespace') . ".Admin.initForm('" . $appAuth->getUserId() . "', 'Customers');
    "
));
?>

<div class="filter-container">
    <h1>Meine Daten ändern</h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du deine persönlichen Daten ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('Customer', array(
    'class' => 'fcs-form'
));

echo $this->Form->input('Customer.firstname', array(
    'label' => 'Vorname',
    'required' => true
));
echo $this->Form->input('Customer.lastname', array(
    'label' => 'Nachname',
    'required' => true
));
echo $this->Form->input('Customer.email', array(
    'label' => 'E-Mail-Adresse',
    'required' => true
));

echo $this->Form->input('AddressCustomer.address1', array(
    'label' => 'Straße',
    'required' => true
));
echo $this->Form->input('AddressCustomer.address2', array(
    'label' => 'Adresszusatz'
));

echo $this->Form->input('AddressCustomer.postcode', array(
    'label' => 'PLZ',
    'required' => true
));
echo $this->Form->input('AddressCustomer.city', array(
    'label' => 'Ort',
    'required' => true
));

echo $this->Form->input('AddressCustomer.phone_mobile', array(
    'label' => 'Handy',
    'required' => true
));
echo $this->Form->input('AddressCustomer.phone', array(
    'label' => 'Telefon'
));

if (Configure::read('AppConfig.emailOrderReminderEnabled')) {
    echo $this->Form->input('Customer.newsletter', array(
        'label' => 'Ich möchte wöchentlich per E-Mail ans Bestellen erinnert werden.',
        'type' => 'checkbox'
    ));
}

?>

<div class="sc"></div>

</form>

<div class="sc"></div>
