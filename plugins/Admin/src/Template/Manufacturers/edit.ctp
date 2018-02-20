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

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('manufacturers-description');" .
        Configure::read('app.jsNamespace') . ".Helper.initCkeditor('manufacturers-short-description');" .
        Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.manufacturers .add-image-button', foodcoopshop.Upload.saveManufacturerTmpImageInForm, foodcoopshop.AppFeatherlight.closeLightbox);" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();"
]);

$idForImageUpload = !empty($manufacturer->id_manufacturer) ? $manufacturer->id_manufacturer : StringComponent::createRandomString(6);
$imageSrc = $this->Html->getManufacturerImageSrc($idForImageUpload, 'large');
if (!empty($manufacturer->tmp_image) && $manufacturer->tmp_image != '') {
    $imageSrc = str_replace('\\', '/', $manufacturer->tmp_image);
}
$imageExists = ! preg_match('/de-default-large_default/', $imageSrc);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a>
        <?php if ($this->request->here != $this->Slug->getManufacturerProfile()) { ?>
            <a href="javascript:void(0);" class="btn btn-default cancel"><i
            class="fa fa-remove"></i> Abbrechen</a>
        <?php } ?>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du die Hersteller-Daten ändern.</li>
        <?php echo $this->element('docs/hersteller'); ?>
    </ul>
</div>

<div class="sc"></div>

<?php

    if ($appAuth->isManufacturer()) {
        $url = $this->Slug->getManufacturerProfile();
    } else {
        if ($isEditMode) {
            $url = $this->Slug->getManufacturerEdit($manufacturer->id_manufacturer);
        } else {
            $url = $this->Slug->getManufacturerAdd();
        }
    }
    echo $this->Form->create($manufacturer, [
        'class' => 'fcs-form',
        'novalidate' => 'novalidate',
        'url' => $url,
        'id' => 'manufacturerEditForm'
    ]);
    
    echo $this->Form->hidden('referer', ['value' => $referer]);
    
    echo '<h2>Allgemein</h2>';

    $imprintString = $appAuth->isManufacturer() ? 'in deinem Impressum' : 'im Impressum des Herstellers';

    echo $this->Form->control('Manufacturers.name', [
        'type' => 'text',
        'label' => 'Name'
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.email', [
        'type' => 'text',
        'label' => 'E-Mail-Adresse <span class="after small">Wird '.$imprintString.'  spamgeschützt angezeigt</span>',
        'escape' => false
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.phone_mobile', [
        'label' => 'Handy <span class="after small">Wird '.$imprintString.' angezeigt</span>',
        'escape' => false
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.phone', [
        'label' => 'Telefon <span class="after small">Wird '.$imprintString.' angezeigt</span>',
        'escape' => false
    ]);
    echo $this->Form->control('Manufacturers.homepage', [
        'placeholder' => 'z.B. https://www.foodcoopshop.com',
        'label' => 'Homepage <span class="after small">Wird '.$imprintString.' angezeigt</span>',
        'escape' => false
    ]);

    if ($isEditMode) {
        $buttonOptions = ['class' => 'btn btn-default', 'escape' => false];
        $buttonIcon = '<i class="fa fa-cogs fa-lg"></i> ';
        if ($appAuth->isManufacturer()) {
            $optionsLink = $this->Html->link($buttonIcon . 'Hier geht\'s zu deinen Einstellungen', $this->Slug->getManufacturerMyOptions(), $buttonOptions);
        } else {
            $optionsLink = $this->Html->link($buttonIcon . 'Hier geht\'s zu den Hersteller-Einstellungen', $this->Slug->getManufacturerEditOptions($manufacturer->id_manufacturer), $buttonOptions);
        }
        echo ' <span class="description">' . $optionsLink . '</span>';
    }

    echo '<div class="sc"></div>';

    echo '<h2>Profil';
    if ($this->request->here != $this->Slug->getManufacturerAdd()) {
        echo ' <span>' . $this->Html->link('Hier geht\'s zum Hersteller-Profil', $this->Slug->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name), [
        'target' => '_blank'
        ]) . '</span>';
    }
    echo '</h2>';

    echo '<div class="input">';
    echo '<label>Logo';
    if ($imageExists) {
        echo '<br /><span class="small">Zum Ändern auf das Logo klicken.</span>';
    }
    echo '</label>';
    echo '<div style="float:right;">';
    echo $this->Html->getJqueryUiIcon($imageExists ? $this->Html->image($imageSrc) : $this->Html->image($this->Html->getFamFamFamPath('image_add.png')), [
    'class' => 'add-image-button ' . ($imageExists ? 'uploaded' : ''),
    'title' => 'Neues Logo hochladen bzw. austauschen',
    'data-object-id' => $idForImageUpload
    ], 'javascript:void(0);');
    echo '</div>';
    echo $this->Form->hidden('Manufacturers.tmp_image');
    echo '</div>';

    echo $this->Form->control('Manufacturers.delete_image', [
    'label' => 'Logo löschen? <span class="after small">Speichern nicht vergessen</span>',
    'type' => 'checkbox',
    'escape' => false
    ]);

    if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
        echo '<div style="margin-top:10px;"></div>';
        echo $this->Form->control('Manufacturers.short_description', [
        'class' => 'ckeditor',
        'type' => 'textarea',
        'label' => 'Kurze Beschreibung<br /><br /><span class="small">Wird auf der Hersteller-Übersichtsseite angezeigt und kann vom Hersteller selbst nicht verändert werden.</span>',
        'escape' => false
        ]);
    }

    $label = 'Lange Beschreibung';
    if (!$isEditMode) {
        echo '<div class="input text">';
        echo '<label>' . $label . '</label>';
        echo '<p>Um die lange Beschreibung hinzuzufügen, bitte den Hersteller zuerst speichern und dann auf "bearbeiten" klicken.</p>';
        echo '</div>';
    } else {
        echo $this->Form->control('Manufacturers.description', [
        'class' => 'ckeditor',
        'type' => 'textarea',
        'label' => $label . '<br /><br /><span class="small">Wird auf der Hersteller-Seite angezeigt.<br /><br /><a href="https://foodcoopshop.github.io/de/wysiwyg-editor" target="_blank">Wie verwende ich den Editor?</a></span>',
        'escape' => false
        ]);
    }
    echo '<div class="sc"></div>';

    echo '<h2>Bankdaten <span>werden nicht veröffentlicht und werden nur intern zum Überweisen deiner Erlöse verwendet</span></h2>';
    echo $this->Form->control('Manufacturers.bank_name', [
    'label' => 'Bank'
    ]);
    echo $this->Form->control('Manufacturers.iban', [
    'label' => 'IBAN',
    'maxLength' => ''
    ]);
    echo $this->Form->control('Manufacturers.bic', [
    'label' => 'BIC',
    'maxLength' => ''
    ]);
    echo '<div class="sc"></div>';

    echo '<h2>Firmendaten <span>für dein Impressum deine Rechnungen. Das Impressum befindet sich auf deinem Hersteller-Profil ganz unten rechts.</span></h2>';
    echo $this->Form->control('Manufacturers.address_manufacturer.firstname', [
    'label' => 'Vorname'
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.lastname', [
    'label' => 'Nachname'
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.address1', [
    'label' => 'Straße'
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.address2', [
    'label' => 'Adresszusatz'
    ]);

    echo $this->Form->control('Manufacturers.address_manufacturer.postcode', [
    'label' => 'PLZ'
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.city', [
    'label' => 'Ort'
    ]);

    echo $this->Form->control('Manufacturers.uid_number', [
    'label' => 'UID-Nummer <span class="after small">sofern vorhanden</span>',
    'escape' => false
    ]);

    echo $this->Form->control('Manufacturers.firmenbuchnummer', [
    'label' => 'Firmenbuchnummer <span class="after small">sofern vorhanden</span>',
    'escape' => false
    ]);

    echo $this->Form->control('Manufacturers.firmengericht', [
    'label' => 'Firmengericht <span class="after small">sofern vorhanden</span>',
    'escape' => false
    ]);

    echo $this->Form->control('Manufacturers.aufsichtsbehoerde', [
    'label' => 'Aufsichtsbehörde <span class="after small">sofern vorhanden</span>',
    'escape' => false
    ]);

    echo $this->Form->control('Manufacturers.kammer', [
    'placeholder' => 'z.B. Landwirtschaftskammer',
    'label' => 'Kammer <span class="after small">sofern vorhanden</span>',
    'escape' => false
    ]);

    echo $this->Form->control('Manufacturers.additional_text_for_invoice', [
    'type' => 'textarea',
    'label' => 'Zusatztext für Rechnung' . '<br /><br /><span class="small">Wird am Ende der Übersichtsseite deiner Rechnung eingefügt.<br />z.B.: "Durchschnittsteuersatz 10% zzgl. Zusatzsteuer 10%"</span>',
    'cols' => 81,
    'escape' => false
    ]);
    
echo $this->Form->end();

?>


<div class="sc"></div>

<?php
echo $this->element('imageUploadForm', [
    'id' => $idForImageUpload,
    'action' => '/admin/tools/doTmpImageUpload/',
    'imageExists' => $imageExists,
    'existingImageSrc' => $imageSrc
]);
?>
