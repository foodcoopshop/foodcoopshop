<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$this->element('addScript', array(
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['Manufacturer']['id_manufacturer']) ? $this->request->data['Manufacturer']['id_manufacturer'] : "") . "', 'Manufacturer');".
        Configure::read('app.jsNamespace') . ".Helper.initDatepicker(); var datefieldSelector = $('input.datepicker');datefieldSelector.datepicker();
    "
));

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a>
        <?php if ($this->here != $this->Slug->getManufacturerMyOptions()) { ?>
            <a href="javascript:void(0);" class="btn btn-default cancel"><i
            class="fa fa-remove"></i> Abbrechen</a>
        <?php } ?>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du die Hersteller-Einstellungen ändern.</li>
        <?php echo $this->element('docs/hersteller'); ?>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('Manufacturer', array(
    'class' => 'fcs-form'
));

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Manufacturer.id_manufacturer');

echo '<h2>Sichtbarkeit der Produkte</h2>';

echo $this->Form->input('Manufacturer.active', array(
    'label' => 'Aktiv?',
    'disabled' => ($appAuth->isManufacturer() ? 'disabled' : ''),
    'type' => 'checkbox',
    'after' => '<span class="after small">Hersteller-Profil und Produkte werden angezeigt (vom Hersteller selbst nicht änderbar).</span>'
));

echo '<div class="holiday-wrapper">';
    echo '<div class="input">';
        echo '<label>Urlaubsmodus?';
    echo '</div>';
    echo $this->element('dateFields', array(
        'dateFrom' => $this->request->data['Manufacturer']['holiday_from'],
        'nameFrom' => 'data[Manufacturer][holiday_from]',
        'dateTo' => $this->request->data['Manufacturer']['holiday_to'],
        'nameTo' => 'data[Manufacturer][holiday_to]'
    ));
    echo '<span class="description small">Die Produkte sind im angegebenen Zeitraum im Shop nicht bestellbar, sie werden also automatisch aktiviert und wieder deaktiviert.';
        echo '<br />Beide Felder leer bedeutet: Urlaubsmodus ist <b>nicht aktiv</b>.';
    echo '</span>';
    echo '</div>';

    echo $this->Form->input('Manufacturer.is_private', array(
    'label' => 'Nur für Mitglieder?',
    'type' => 'checkbox',
    'after' => '<span class="after small">Hersteller-Profil und Produkte werden <b>nur für eingeloggte Mitglieder</b> angezeigt.</span>'
    ));
    echo '<div class="sc"></div>';

    echo '<h2>Benachrichtigungen</h2>';

    echo $this->Form->input('Manufacturer.send_order_list', array(
    'label' => 'Bestelllisten per E-Mail',
    'type' => 'checkbox',
    'after' => '<span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte wöchentlich per E-Mail die Bestelllisten erhalten.</span>'
    ));
    echo '<div class="sc"></div>';

    echo $this->Form->input('Manufacturer.send_order_list_cc', array(
    'label' => 'CC-Empfänger für Bestell-Listen-Versand',
    'after' => '<span class="after small">Mehrere Empfänger mit , trennen.</span>'
    ));

    echo $this->Form->input('Manufacturer.send_invoice', array(
    'label' => 'Rechnungen per E-Mail',
    'type' => 'checkbox',
        'after' => '<span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte monatlich per E-Mail die Rechnungen erhalten.</span>'
    ));
    echo '<div class="sc"></div>';

    echo $this->Form->input('Manufacturer.send_ordered_product_deleted_notification', array(
        'label' => 'Stornierungen',
        'type' => 'checkbox',
        'after' => '<span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte bei jeder Stornierung eine Info-Mail erhalten.</span>'
    ));
    echo '<div class="sc"></div>';

    echo $this->Form->input('Manufacturer.send_ordered_product_price_changed_notification', array(
        'label' => 'Preis-Änderungen von bestellten Produkten',
        'type' => 'checkbox',
        'after' => '<span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte bei jeder Preis-Änderung eines bereits bestellten Produktes eine Info-Mail erhalten.</span>'
    ));
    echo '<div class="sc"></div>';

    echo $this->Form->input('Manufacturer.send_ordered_product_quantity_changed_notification', array(
        'label' => 'Änderungen der bestellten Anzahl',
        'type' => 'checkbox',
        'after' => '<span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte bei jeder Änderung der Anzahl eines bereits bestellten Produktes eine Info-Mail erhalten.</span>'
    ));
    echo '<div class="sc"></div>';

    echo $this->Form->input('Manufacturer.send_shop_order_notification', array(
    'label' => 'Sofortbestellungen',
    'type' => 'checkbox',
    'after' => '<span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte bei jeder Sofort-Bestellung eine Info-Mail erhalten.</span>'
    ));
    echo '<div class="sc"></div>';

    echo '<h2>Sonstige Einstellungen</h2>';


    if (Configure::read('app.useManufacturerCompensationPercentage') && !$appAuth->isManufacturer()) {
        echo $this->Form->input('Manufacturer.compensation_percentage', array(
        'label' => 'Variabler Mitgliedsbeitrag in %',
        'div' => array(
            'class' => 'short text input'
        ),
        'type' => 'text',
        'after' => '<span class="after small">Die Rechnung für den Hersteller wird um den angegebenen Prozentwert reduziert (nur ganze Zahlen erlaubt).</span>'
        ));
    }

    echo $this->Form->input('Manufacturer.default_tax_id', array(
    'type' => 'select',
    'label' => 'Voreingestellter Steuersatz für neue Artikel',
    'options' => $taxesForDropdown
    ));

    if (!$appAuth->isManufacturer()) {
        echo $this->Form->input('Manufacturer.bulk_orders_allowed', array(
        'label' => 'Hersteller optimiert für Sammelbestellungen?',
        'type' => 'checkbox',
        'after' => '<span class="after small">Deaktiviert alle Benachrichtigungen, außer den Rechnungsversand. Mehr Infos findest du im <a href="https://foodcoopshop.github.io/de/sammelbestellungen" target="_blank">Leitfaden für Sammelbestellungen</a>.</span>'
        ));
            echo '<div class="sc"></div>';
    }

    if (!$appAuth->isManufacturer()) {
        echo $this->Form->input('Manufacturer.id_customer', array(
        'type' => 'select',
        'label' => 'Ansprechperson',
        'empty' => 'Mitglied auswählen...',
        'options' => $customersForDropdown
        ));
    }

?>
</form>
