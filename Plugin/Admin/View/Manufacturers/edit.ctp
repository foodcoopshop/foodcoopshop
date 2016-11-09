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
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('ManufacturerLangDescription');" . Configure::read('app.jsNamespace') . ".Helper.initCkeditor('ManufacturerLangShortDescription');" . Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.manufacturers .add-image-button', foodcoopshop.Upload.saveManufacturerTmpImageInForm, foodcoopshop.AppFeatherlight.closeLightbox);" . Configure::read('app.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['Manufacturer']['id_manufacturer']) ? $this->request->data['Manufacturer']['id_manufacturer'] : "") . "', 'Manufacturer');
    "
));
$idForImageUpload = isset($this->request->data['Manufacturer']['id_manufacturer']) ? $this->request->data['Manufacturer']['id_manufacturer'] : StringComponent::createRandomString(6);
$imageSrc = $this->Html->getManufacturerImageSrc($idForImageUpload, 'large');
if (isset($this->request->data['Manufacturer']['tmp_image']) && $this->request->data['Manufacturer']['tmp_image'] != '') {
    $imageSrc = str_replace('\\', '/', $this->request->data['Manufacturer']['tmp_image']);
}
$imageExists = ! preg_match('/de-default-large_default/', $imageSrc);
?>

<div class="filter-container">
	<h1><?php echo $title_for_layout; ?></h1>
	<div class="right">
		<a href="javascript:void(0);" class="btn btn-success submit"><i
			class="fa fa-check"></i> Speichern</a>
        <?php if ($this->here != $this->Slug->getManufacturerProfile()) { ?>
    		<a href="javascript:void(0);" class="btn btn-default cancel"><i
			class="fa fa-remove"></i> Abbrechen</a>
    	<?php } ?>
    </div>
</div>

<div id="help-container">
	<ul>
		<li>Auf dieser Seite kannst du die Hersteller-Daten ändern.</li>
	</ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('Manufacturer', array(
    'class' => 'fcs-form'
));

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Manufacturer.id_manufacturer');
echo $this->Form->hidden('Address.id_address');

echo '<h2>Allgemein</h2>';

echo $this->Form->input('Manufacturer.name', array(
    'type' => 'text',
    'label' => 'Name',
    'required' => true
));
echo $this->Form->input('Address.email', array(
    'type' => 'text',
    'label' => 'E-Mail-Adresse',
    'required' => true
));
echo $this->Form->input('Address.phone_mobile', array(
    'label' => 'Handy'
));
echo $this->Form->input('Address.phone', array(
    'label' => 'Telefon'
));
echo $this->Form->input('Manufacturer.active', array(
    'label' => 'Aktiv?',
    'disabled' => ($appAuth->isManufacturer() ? 'disabled' : ''),
    'type' => 'checkbox',
    'after' => '<span class="after small">Hersteller-Profil und Produkte werden angezeigt (vom Hersteller selbst nicht änderbar).</span>'
));
echo $this->Form->input('Manufacturer.holiday', array(
    'label' => 'Urlaubsmodus?',
    'type' => 'checkbox',
    'after' => '<span class="after small">Hersteller-Profil wird angezeigt, die Produkte nicht.</span>'
));
echo $this->Form->input('Manufacturer.is_private', array(
    'label' => 'Nur für Mitglieder?',
    'type' => 'checkbox',
    'after' => '<span class="after small">Hersteller-Profil und Produkte werden angezeigt, aber nur für Mitglieder.</span>'
));
echo '<div class="sc"></div>';

echo '<h2>Profil';
if ($this->here != $this->Slug->getManufacturerAdd()) {
    echo ' <span>' . $this->Html->link('Hier geht\'s zum Hersteller-Profil', $this->Slug->getManufacturerDetail($manufacturerId, $unsavedManufacturer['Manufacturer']['name']), array(
        'target' => '_blank'
    )) . '</span>';
}
echo '</h2>';

echo '<div class="input">';
echo '<label>Logo';
if ($imageExists) {
    echo '<br /><span class="small">Zum Ändern auf das Logo klicken.</span>';
}
echo '</label>';
echo '<div style="float:right;">';
echo $this->Html->getJqueryUiIcon($imageExists ? $this->Html->image($imageSrc) : $this->Html->image($this->Html->getFamFamFamPath('image_add.png')), array(
    'class' => 'add-image-button ' . ($imageExists ? 'uploaded' : ''),
    'title' => 'Neues Logo hochladen bzw. austauschen',
    'data-object-id' => $idForImageUpload
), 'javascript:void(0);');
echo '</div>';
echo $this->Form->hidden('Manufacturer.tmp_image');
echo '</div>';

echo $this->Form->input('Manufacturer.delete_image', array(
    'label' => 'Logo löschen?',
    'type' => 'checkbox',
    'after' => '<span class="after small">Speichern nicht vergessen</span>'
));

if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
    echo $this->Form->input('ManufacturerLang.short_description', array(
        'class' => 'ckeditor',
        'type' => 'textarea',
        'label' => 'Kurze Beschreibung<br /><br /><span class="small">Wird auf der Hersteller-Übersichtsseite angezeigt und kann vom Hersteller selbst nicht verändert werden.</span>',
        'before' => '<div style="margin-top:10px;"></div>'
    ));
}

$label = 'Lange Beschreibung';
if (is_null($manufacturerId)) {
    echo '<div class="input text">';
    echo '<label>' . $label . '</label>';
    echo '<p>Um die lange Beschreibung hinzuzufügen, bitte den Hersteller zuerst speichern und dann auf "bearbeiten" klicken.</p>';
    echo '</div>';
} else {
    echo $this->Form->input('ManufacturerLang.description', array(
        'class' => 'ckeditor',
        'type' => 'textarea',
        'label' => $label . '<br /><br /><span class="small">Wird auf der Hersteller-Seite angezeigt, es können auch Bilder eingefügt werden.</span>'
    ));
}
echo '<div class="sc"></div>';

echo '<h2>Bankdaten <span>werden nicht veröffentlicht und werden nur intern zum Überweisen deiner Erlöse verwendet</span></h2>';
echo $this->Form->input('Manufacturer.bank_name', array(
    'label' => 'Bank'
));
echo $this->Form->input('Manufacturer.iban', array(
    'label' => 'IBAN',
    'maxLength' => ''
));
echo $this->Form->input('Manufacturer.bic', array(
    'label' => 'BIC',
    'maxLength' => ''
));
echo '<div class="sc"></div>';

echo '<h2>Rechnungsdaten <span>werden zum Erstellen deiner Rechnungen verwendet</span></h2>';
echo $this->Form->input('Address.firstname', array(
    'label' => 'Vorname',
    'required' => true
));
echo $this->Form->input('Address.lastname', array(
    'label' => 'Nachname',
    'required' => true
));
echo $this->Form->input('Address.address1', array(
    'label' => 'Straße'
));
echo $this->Form->input('Address.address2', array(
    'label' => 'Adresszusatz'
));

echo $this->Form->input('Address.postcode', array(
    'label' => 'PLZ'
));
echo $this->Form->input('Address.city', array(
    'label' => 'Ort'
));

echo $this->Form->input('Manufacturer.uid_number', array(
    'label' => 'UID-Nummer'
));

echo $this->Form->input('Manufacturer.additional_text_for_invoice', array(
    'type' => 'textarea',
    'label' => 'Zusatztext für Rechnung' . '<br /><br /><span class="small">Wird am Ende der Übersichtsseite deiner Rechnung eingefügt.<br />z.B.: "Durchschnittsteuersatz 10% zzgl. Zusatzsteuer 10%"</span>',
    'cols' => 81
));

?>

</form>

<div class="sc"></div>

<?php
echo $this->element('imageUploadForm', array(
    'id' => $idForImageUpload,
    'action' => '/admin/tools/doTmpImageUpload/',
    'imageExists' => $imageExists,
    'existingImageSrc' => $imageSrc
)
);
?>