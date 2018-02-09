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
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.categories .add-image-button', foodcoopshop.Upload.saveCategoryTmpImageInForm, foodcoopshop.AppFeatherlight.closeLightbox);" . Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('CategoryDescription');" . Configure::read('app.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['Categories']['id_category']) ? $this->request->data['Categories']['id_category'] : "") . "', 'Categories');
    "
]);
$idForImageUpload = isset($this->request->data['Categories']['id_category']) ? $this->request->data['Categories']['id_category'] : StringComponent::createRandomString(6);
$imageSrc = $this->Html->getCategoryImageSrc($idForImageUpload);
if (isset($this->request->data['Categories']['tmp_image']) && $this->request->data['Categories']['tmp_image'] != '') {
    $imageSrc = str_replace('\\', '/', $this->request->data['Categories']['tmp_image']);
}

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a> <a href="javascript:void(0);"
            class="btn btn-default cancel"><i class="fa fa-remove"></i> Abbrechen</a>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du die Kategorie ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('Categories', [
    'class' => 'fcs-form'
]);

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Categories.id_category');

echo $this->Form->input('Categories.name', [
    'label' => 'Name',
    'required' => true
]);
echo $this->Form->input('Categories.id_parent', [
    'type' => 'select',
    'label' => 'Übergeordnete Kategorie',
    'empty' => 'Keine (oberste Ebene)',
    'options' => $categoriesForSelect
]);

echo '<div class="input">';
echo '<label>Bild';
if ($imageSrc) {
    echo '<br /><span class="small">Zum Ändern auf das Bild klicken.</span>';
}
echo '</label>';
echo '<div style="float:right;">';
echo $this->Html->getJqueryUiIcon($imageSrc ? $this->Html->image($imageSrc) : $this->Html->image($this->Html->getFamFamFamPath('image_add.png')), [
    'class' => 'add-image-button ' . ($imageSrc ? 'uploaded' : ''),
    'title' => 'Neues Bild hochladen bzw. austauschen',
    'data-object-id' => $idForImageUpload
], 'javascript:void(0);');
echo '</div>';
echo $this->Form->hidden('Categories.tmp_image');
echo '</div>';

echo $this->Form->input('Categories.delete_image', [
    'label' => 'Bild löschen?',
    'type' => 'checkbox',
    'after' => '<span class="after small">Speichern nicht vergessen</span>'
]);

if ($this->request->here != $this->Slug->getCategoryAdd()) {
    echo $this->Form->input('Categories.delete_category', [
        'label' => 'Kategorie löschen?',
        'type' => 'checkbox',
        'after' => '<span class="after small">Anhaken und dann auf <b>Speichern</b> klicken.</span>'
    ]);
}

echo $this->Form->input('Categories.active', [
    'label' => 'Aktiv?',
    'type' => 'checkbox'
]);

echo $this->Form->input('Categories.description', [
    'class' => 'ckeditor',
    'type' => 'textarea',
    'label' => 'Beschreibung<br /><br /><span class="small"><a href="https://foodcoopshop.github.io/de/wysiwyg-editor" target="_blank">Wie verwende ich den Editor?</a></span>'
]);

?>

</form>

<div class="sc"></div>

<?php
echo $this->element('imageUploadForm', [
    'id' => $idForImageUpload,
    'action' => '/admin/tools/doTmpImageUpload/',
    'imageExists' => $imageSrc,
    'existingImageSrc' => $imageSrc
]);
?>
