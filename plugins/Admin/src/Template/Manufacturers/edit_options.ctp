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

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".Admin.init();" .
    Configure::read('app.jsNamespace') . ".Admin.initForm();".
    Configure::read('app.jsNamespace') . ".Helper.initDatepicker(); var datefieldSelector = $('input.datepicker');datefieldSelector.datepicker();
    "
]);

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a>
        <?php if ($this->request->here != $this->Slug->getManufacturerMyOptions()) { ?>
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

$url = $this->Slug->getManufacturerEditOptions($manufacturer->id_manufacturer);
if ($appAuth->isManufacturer()) {
    $url = $this->Slug->getManufacturerMyOptions();
}
echo $this->Form->create($manufacturer, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $url,
    'id' => 'manufacturersEditOptionsForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo '<h2>Sichtbarkeit der Produkte</h2>';

echo $this->Form->control('Manufacturers.active', [
    'label' => 'Aktiv? <span class="after small">Hersteller-Profil und Produkte werden angezeigt (vom Hersteller selbst nicht änderbar).</span>',
    'disabled' => ($appAuth->isManufacturer() ? 'disabled' : ''),
    'type' => 'checkbox',
    'escape' => false
]);

echo '<div class="holiday-wrapper">';
    echo '<div class="input">';
        echo '<label>Lieferpause?';
    echo '</div>';
    echo $this->element('dateFields', [
        'dateFrom' => !empty($manufacturer->holiday_from) ? $manufacturer->holiday_from->i18nFormat(Configure::read('DateFormat.de.DateLong2')) : '',
        'nameFrom' => 'Manufacturers[holiday_from]',
        'dateTo' => !empty($manufacturer->holiday_to) ? $manufacturer->holiday_to->i18nFormat(Configure::read('DateFormat.de.DateLong2')) : '',
        'nameTo' => 'Manufacturers[holiday_to]'
    ]);
    echo '<span class="description small"><a href="https://foodcoopshop.github.io/de/hersteller" target="_blank">Wie verwende ich die Funktion "Lieferpause"?</a>';
    echo '</span>';
    echo '</div>';

    echo $this->Form->control('Manufacturers.is_private', [
    'label' => 'Nur für Mitglieder? <span class="after small">Hersteller-Profil und Produkte werden <b>nur für eingeloggte Mitglieder</b> angezeigt.</span>',
    'type' => 'checkbox',
    'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo '<h2>Benachrichtigungen</h2>';

    echo $this->Form->control('Manufacturers.send_order_list', [
    'label' => 'Bestelllisten per E-Mail <span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte - falls es Bestellungen gibt - diese am '.$this->Time->getWeekdayName(Configure::read('app.sendOrderListsWeekday')).' per E-Mail erhalten.</span>',
    'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo $this->Form->control('Manufacturers.send_order_list_cc', [
    'label' => 'CC-Empfänger für Bestell-Listen-Versand <span class="after small">Mehrere Empfänger mit , trennen.</span>',
    'escape' => false
    ]);

    echo $this->Form->control('Manufacturers.send_invoice', [
    'label' => 'Rechnungen per E-Mail <span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte monatlich per E-Mail die Rechnungen erhalten.</span>',
    'type' => 'checkbox',
    'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo $this->Form->control('Manufacturers.send_ordered_product_deleted_notification', [
        'label' => 'Stornierungen <span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte bei jeder Stornierung eine Info-Mail erhalten.</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo $this->Form->control('Manufacturers.send_ordered_product_price_changed_notification', [
        'label' => 'Preis-Änderungen von bestellten Produkten <span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte bei jeder Preis-Änderung eines bereits bestellten Produktes eine Info-Mail erhalten.</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo $this->Form->control('Manufacturers.send_ordered_product_quantity_changed_notification', [
        'label' => 'Änderungen der bestellten Anzahl <span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte bei jeder Änderung der Anzahl eines bereits bestellten Produktes eine Info-Mail erhalten.</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo $this->Form->control('Manufacturers.send_shop_order_notification', [
    'label' => 'Sofortbestellungen <span class="after small">'.($appAuth->isManufacturer() ? 'Ich' : 'Der Hersteller') . ' möchte bei jeder Sofort-Bestellung eine Info-Mail erhalten.</span>',
    'type' => 'checkbox',
    'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo '<h2>Sonstige Einstellungen</h2>';

    if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && !$appAuth->isManufacturer()) {
        echo $this->Form->control('Manufacturers.variable_member_fee', [
        'label' => 'Variabler Mitgliedsbeitrag in % <span class="after small">Die Rechnung für den Hersteller wird um den angegebenen Prozentwert reduziert (nur ganze Zahlen erlaubt).</span>',
        'class' => 'short',
        'type' => 'text',
        'escape' => false
        ]);
    }

    echo $this->Form->control('Manufacturers.default_tax_id', [
    'type' => 'select',
    'label' => 'Voreingestellter Steuersatz für neue Produkte',
    'options' => $taxesForDropdown
    ]);

    if (!$appAuth->isManufacturer()) {
        echo $this->Form->control('Manufacturers.bulk_orders_allowed', [
        'label' => 'Hersteller optimiert für Sammelbestellungen? <span class="after small">Deaktiviert alle Benachrichtigungen, außer den Rechnungsversand. Mehr Infos findest du im <a href="https://foodcoopshop.github.io/de/sammelbestellungen" target="_blank">Leitfaden für Sammelbestellungen</a>.</span>',
        'type' => 'checkbox',
        'escape' => false
        ]);
        echo '<div class="sc"></div>';
    }

    if (!$appAuth->isManufacturer()) {
        echo $this->Form->control('Manufacturers.id_customer', [
        'type' => 'select',
        'label' => 'Ansprechperson',
        'empty' => 'Mitglied auswählen...',
        'options' => $customersForDropdown
        ]);
    }
    echo '<div class="sc"></div>';

    if (isset($isAllowedEditManufacturerOptionsDropdown) && $isAllowedEditManufacturerOptionsDropdown) {
        $this->element('addScript', [
            'script' =>
                Configure::read('app.jsNamespace') . ".Admin.setSelectPickerMultipleDropdowns('#manufacturers-enabled-sync-domains');
            "
        ]);
        echo $this->Form->control('Manufacturers.enabled_sync_domains', [
            'type' => 'select',
            'multiple' => true,
            'data-val' => $manufacturer->enabled_sync_domains,
            'label' => 'Remote-Foodcoops <span class="small"><a href="'.$this->Network->getNetworkPluginDocs().'" target="_blank">Infos zum Netzwerk-Modul</a></span>',
            'options' => $syncDomainsForDropdown,
            'escape' => false
        ]);
        echo '<div class="sc"></div>';
    }

    if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
        echo '<h2>Zeitwährung</h2>';
        echo $this->Form->control('Manufacturers.timebased_currency_enabled', [
            'label' => 'Zeitwährungs-Modul aktiv? <span class="after small">Mehr Infos dazu findest du <a href="https://foodcoopshop.github.io/de/zeitwaehrungs-modul" target="_blank">in der Online-Doku</a>.</span>',
            'type' => 'checkbox',
            'escape' => false
        ]);
        if ($manufacturer->timebased_currency_enabled) {
            echo $this->Form->control('Manufacturers.timebased_currency_max_percentage', [
                'label' => 'Maximaler Anteil der Zeitwährung in Prozent <span class="after small">gültig für alle Produkte - bei 0 ist die Zeitwährungsfunktion im Shop deaktiviert</span>',
                'type' => 'text',
                'class' => 'short',
                'escape' => false
            ]);
            echo $this->Form->control('Manufacturers.timebased_currency_max_credit_balance', [
                'label' => 'Maximaler Kontostand in Stunden <span class="after small">bis zu dem in der Zeitwährung bezahlt werden kann</span>',
                'type' => 'text',
                'class' => 'short',
                'escape' => false
            ]);
        }
    }
    
    echo $this->Form->end();

?>
