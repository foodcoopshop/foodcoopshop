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

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a> <a href="javascript:void(0);"
            class="btn btn-default cancel"><i class="fa fa-times"></i> Abbrechen</a>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du deine persönlichen Daten ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($customer, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isOwnProfile ? $this->Slug->getCustomerProfile() : $this->Slug->getCustomerEdit($customer->id_customer)
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo $this->Form->control('Customers.firstname', [
    'label' => 'Vorname',
    'required' => true
]);
echo $this->Form->control('Customers.lastname', [
    'label' => 'Nachname',
    'required' => true
]);
echo $this->Form->control('Customers.address_customer.email', [
    'label' => 'E-Mail-Adresse'
]);

echo $this->Form->control('Customers.address_customer.address1', [
    'label' => 'Straße'
]);
echo $this->Form->control('Customers.address_customer.address2', [
    'label' => 'Adresszusatz'
]);

echo $this->Form->control('Customers.address_customer.postcode', [
    'label' => 'PLZ'
]);
echo $this->Form->control('Customers.address_customer.city', [
    'label' => 'Ort'
]);

echo $this->Form->control('Customers.address_customer.phone_mobile', [
    'label' => 'Handy'
]);
echo $this->Form->control('Customers.address_customer.phone', [
    'label' => 'Telefon'
]);

if (Configure::read('app.emailOrderReminderEnabled')) {
    echo $this->Form->control('Customers.newsletter', [
        'label' => 'Bestellerinnerung<span class="after small">Ich möchte wöchentlich per E-Mail ans Bestellen erinnert werden.</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
}

if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
    $label = 'Stundenabrechnungs-Modul aktiv? ';
    $label .= '<span class="after small">Ich möchte meine Produkte (auch) in ' . Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . ' bezahlen. Mehr Infos dazu findest du <a href="https://foodcoopshop.github.io/de/stundenabrechnungs-modul" target="_blank">in der Online-Doku</a>.';
    if (!$timebasedCurrencyDisableOptionAllowed) {
        $label .= ' Zum Deaktivieren der Option muss dein ' . $this->TimebasedCurrency->getName() . ' ausgeglichen sein, derzeit beträgt es '.$this->TimebasedCurrency->formatSecondsToTimebasedCurrency($timebasedCurrencyCreditBalance).'.';
    }
    $label .= '</span>';
    echo $this->Form->control('Customers.timebased_currency_enabled', [
        'label' => $label,
        'type' => 'checkbox',
        'disabled' => (!$timebasedCurrencyDisableOptionAllowed ? 'disabled' : ''),
        'escape' => false
    ]);
}

echo $this->Form->end(); ?>

<div class="sc"></div>
