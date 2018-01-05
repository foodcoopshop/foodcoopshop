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
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.sliders .add-image-button', foodcoopshop.Upload.saveSliderTmpImageInForm, foodcoopshop.AppFeatherlight.closeLightbox);" . Configure::read('app.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['Slider']['id_slider']) ? $this->request->data['Slider']['id_slider'] : "") . "', 'Slider');
    "
));
$idForImageUpload = isset($this->request->data['Slider']['id_slider']) ? $this->request->data['Slider']['id_slider'] : StringComponent::createRandomString(6);

$imageSrc = false;
if ($this->here != $this->Slug->getSliderAdd()) {
    $imageSrc = $this->Html->getSliderImageSrc($this->request->data['Slider']['image']);
    if (isset($this->request->data['Slider']['tmp_image']) && $this->request->data['Slider']['tmp_image'] != '') {
        $imageSrc = str_replace('\\', '/', $this->request->data['Slider']['tmp_image']);
    }
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
        <li>Auf dieser Seite kannst du das Slideshow-Bild ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('Slider', array(
    'class' => 'fcs-form'
));

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Slider.id_slider');
echo $this->Form->hidden('Slider.image');

echo '<div class="input">';
echo '<label>Slideshow-Bild';
if ($imageSrc) {
    echo '<br /><span class="small">Zum Ändern auf das Bild klicken.<br />Breite: 905px</span>';
}
echo '</label>';
echo '<div style="float:right;">';
echo $this->Html->getJqueryUiIcon($imageSrc ? $this->Html->image($imageSrc) : $this->Html->image($this->Html->getFamFamFamPath('image_add.png')), array(
    'class' => 'add-image-button ' . ($imageSrc ? 'uploaded' : ''),
    'title' => 'Neues Bild hochladen bzw. austauschen',
    'data-object-id' => $idForImageUpload
), 'javascript:void(0);');
echo '</div>';
echo $this->Form->hidden('Slider.tmp_image');
echo '</div>';

echo $this->Form->input('Slider.position', array(
    'div' => array(
        'class' => 'short text input'
    ),
    'label' => 'Reihenfolge',
    'type' => 'text'
));
echo $this->Form->input('Slider.active', array(
    'label' => 'Aktiv?',
    'type' => 'checkbox'
));

if ($this->here != $this->Slug->getSliderAdd()) {
    echo $this->Form->input('Slider.delete_slider', array(
        'label' => 'Slideshow-Bild löschen?',
        'type' => 'checkbox',
        'after' => '<span class="after small">Anhaken und dann auf <b>Speichern</b> klicken.</span>'
    ));
}

?>

</form>

<div class="sc"></div>

<?php
echo $this->element('imageUploadForm', array(
    'id' => $idForImageUpload,
    'action' => '/admin/tools/doTmpImageUpload/',
    'imageExists' => $imageSrc,
    'existingImageSrc' => $imageSrc
));
?>
