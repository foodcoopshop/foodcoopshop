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
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.sliders .add-image-button', foodcoopshop.Upload.saveSliderTmpImageInForm, foodcoopshop.AppFeatherlight.closeLightbox);" . Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);
$idForImageUpload = !empty($slider->id_slider) ? $slider->id_slider : StringComponent::createRandomString(6);

$imageSrc = false;
if ($this->request->getRequestTarget() != $this->Slug->getSliderAdd()) {
    $imageSrc = $this->Html->getSliderImageSrc($slider->image);
    if (!empty($slider->tmp_image) && $slider->tmp_image != '') {
        $imageSrc = str_replace('\\', '/', $slider->tmp_image);
    }
}
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-default cancel"><i class="fa fa-remove"></i> <?php echo __d('admin', 'Cancel'); ?></a>
            <?php echo $this->element('printIcon'); ?>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($slider, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Slug->getSliderEdit($slider->id_slider) : $this->Slug->getSliderAdd(),
    'id' => 'sliderEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo '<div class="input">';
echo '<label>' . __d('admin', 'Slider_image');
if ($imageSrc) {
    echo '<br /><span class="small">'.__d('admin', 'Click_on_image_to_change_it.').'<br />'.__d('admin', 'Width').': 905px</span>';
}
echo '</label>';
echo '<div style="float:right;">';
echo $this->Html->getJqueryUiIcon($imageSrc ? $this->Html->image($imageSrc) : $this->Html->image($this->Html->getFamFamFamPath('image_add.png')), [
    'class' => 'add-image-button ' . ($imageSrc ? 'uploaded' : ''),
    'title' => __d('admin', 'Upload_new_image_or_change_it'),
    'data-object-id' => $idForImageUpload
], 'javascript:void(0);');
echo '</div>';
echo $this->Form->hidden('Sliders.tmp_image');
echo '</div>';

echo $this->Form->control('Sliders.position', [
    'class' => 'short',
    'label' => __d('admin', 'Rank'),
    'type' => 'text'
]);
echo $this->Form->control('Sliders.active', [
    'label' => __d('admin', 'Active').'?',
    'type' => 'checkbox'
]);

if ($this->request->getRequestTarget() != $this->Slug->getSliderAdd()) {
    echo $this->Form->control('Sliders.delete_slider', [
        'label' => __d('admin', 'Delete_slider_image?').' <span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
}

echo $this->Form->end();

?>

<div class="sc"></div>

<?php
echo $this->element('imageUploadForm', [
    'id' => $idForImageUpload,
    'action' => '/admin/tools/doTmpImageUpload/',
    'imageExists' => $imageSrc,
    'existingImageSrc' => $imageSrc
]);
?>
