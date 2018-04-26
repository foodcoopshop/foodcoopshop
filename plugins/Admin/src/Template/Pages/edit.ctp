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
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('pages-content');" .
        Configure::read('app.jsNamespace') . ".Admin.disableSelectpickerItems('#pages-id-parent', " . json_encode($disabledSelectPageIds) . ");" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();
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
        <li>Auf dieser Seite kannst du die Seite ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($page, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Slug->getPageEdit($page->id_page) : $this->Slug->getPageAdd(),
    'id' => 'pageEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);
echo $this->Form->hidden('Pages.id_page');
echo $this->Form->control('Pages.title', [
    'label' => 'Seitentitel',
    'required' => true
]);

echo $this->Form->control('Pages.menu_type', [
    'type' => 'select',
    'label' => 'In welchem Menü<br /><span class="small">soll die Seite angezeigt werden?</span>',
    'options' => $this->Html->getMenuTypes(),
    'escape' => false
]);
echo $this->Form->control('Pages.id_parent', [
    'type' => 'select',
    'label' => 'Übergeordneter Menüpunkt<br /><span class="small">Hauptmenü: Auswahl leer lassen</span>',
    'empty' => 'Übergeordneten Menüpunkt auswählen...',
    'options' => $pagesForSelect,
    'escape' => false
]);
echo $this->Form->control('Pages.position', [
    'class' => 'short',
    'label' => 'Reihenfolge im Menü<br /><span class="small">Zahl von 0 bis 100</span> <span class="after small">"0" zeigt die Seite nicht im Menü an, sie bleibt aber über den Link erreichbar.</span>',
    'type' => 'text',
    'escape' => false
]);

echo $this->Form->control('Pages.full_width', [
    'label' => 'Ganze Breite? <span class="after small">Inhalt der Seite wird verbreitert, indem das linke Menü ausgeblendet wird.</span>',
    'type' => 'checkbox',
    'escape' => false
]);
echo $this->Form->control('Pages.extern_url', [
    'placeholder' => 'z.B. https://www.foodcoopshop.com',
    'label' => 'Link auf externe Seite?<br /><span class="small">Menüpunkt führt auf diese Webseite (der Inhalt der Seite wird nicht angezeigt).</span>',
    'div' => [
        'class' => 'long text input'
    ],
    'escape' => false
]);

if ($this->request->getRequestTarget() != $this->Slug->getPageAdd()) {
    echo $this->Form->control('Pages.delete_page', [
        'label' => 'Seite löschen? <span class="after small">Anhaken und dann auf <b>Speichern</b> klicken.</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
}

echo $this->Form->control('Pages.is_private', [
    'label' => 'Nur für Mitglieder sichtbar?',
    'type' => 'checkbox'
]);
echo $this->Form->control('Pages.active', [
    'label' => 'Aktiv?',
    'type' => 'checkbox'
]);

echo $this->Form->control('Pages.content', [
    'class' => 'ckeditor',
    'type' => 'textarea',
    'label' => 'Text<br /><br /><span class="small"><a href="https://foodcoopshop.github.io/de/wysiwyg-editor" target="_blank">Wie verwende ich den Editor?</a></span>',
    'escape' => false
]);

?>

</form>

<div class="sc"></div>
