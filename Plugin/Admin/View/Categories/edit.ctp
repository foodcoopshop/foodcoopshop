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
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.categories .add-image-button', foodcoopshop.Upload.saveCategoryTmpImageInForm, foodcoopshop.AppFeatherlight.closeLightbox);" . Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('CategoryLangDescription');" . Configure::read('app.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['Category']['id_category']) ? $this->request->data['Category']['id_category'] : "") . "', 'Category');
    "
));
$idForImageUpload = isset($this->request->data['Category']['id_category']) ? $this->request->data['Category']['id_category'] : StringComponent::createRandomString(6);
$imageSrc = $this->Html->getCategoryImageSrc($idForImageUpload);
if (isset($this->request->data['Category']['tmp_image']) && $this->request->data['Category']['tmp_image'] != '') {
    $imageSrc = str_replace('\\', '/', $this->request->data['Category']['tmp_image']);
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

echo $this->Form->create('Category', array(
    'class' => 'fcs-form'
));

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Category.id_category');
echo $this->Form->hidden('CategoryLang.id_category');

echo $this->Form->input('CategoryLang.name', array(
    'label' => 'Name',
    'required' => true
));
echo $this->Form->input('Category.id_parent', array(
    'type' => 'select',
    'label' => 'Übergeordnete Kategorie',
    'empty' => 'Keine (oberste Ebene)',
    'options' => $categoriesForDropdown
));

echo '<div class="input">';
echo '<label>Bild';
if ($imageSrc) {
    echo '<br /><span class="small">Zum Ändern auf das Bild klicken.</span>';
}
echo '</label>';
echo '<div style="float:right;">';
echo $this->Html->getJqueryUiIcon($imageSrc ? $this->Html->image($imageSrc) : $this->Html->image($this->Html->getFamFamFamPath('image_add.png')), array(
    'class' => 'add-image-button ' . ($imageSrc ? 'uploaded' : ''),
    'title' => 'Neues Bild hochladen bzw. austauschen',
    'data-object-id' => $idForImageUpload
), 'javascript:void(0);');
echo '</div>';
echo $this->Form->hidden('Category.tmp_image');
echo '</div>';

echo $this->Form->input('Category.delete_image', array(
    'label' => 'Bild löschen?',
    'type' => 'checkbox',
    'after' => '<span class="after small">Speichern nicht vergessen</span>'
));

if ($this->here != $this->Slug->getCategoryAdd()) {
    echo $this->Form->input('Category.delete_category', array(
        'label' => 'Kategorie löschen?',
        'type' => 'checkbox',
        'after' => '<span class="after small">Anhaken und dann auf <b>Speichern</b> klicken.</span>'
    ));
}

echo $this->Form->input('Category.active', array(
    'label' => 'Aktiv?',
    'type' => 'checkbox'
));

echo $this->Form->input('CategoryLang.description', array(
    'class' => 'ckeditor',
    'type' => 'textarea',
    'label' => 'Beschreibung'
));

?>

</form>

<div class="sc"></div>

<?php
echo $this->element('imageUploadForm', array(
    'id' => $idForImageUpload,
    'action' => '/admin/tools/doTmpImageUpload/',
    'imageExists' => $imageSrc,
    'existingImageSrc' => $imageSrc
)
);
?>