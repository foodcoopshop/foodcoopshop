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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.categories .add-image-button', foodcoopshop.Upload.saveCategoryTmpImageInForm, foodcoopshop.AppFeatherlight.closeLightbox);" .
        Configure::read('app.jsNamespace') . ".Admin.disableSelectpickerItems('#categories-id-parent', " . json_encode($disabledSelectCategoryIds) . ");" .
        Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('categories-description');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);
$idForImageUpload = !empty($category->id_category) ? $category->id_category : StringComponent::createRandomString(6);
$imageSrc = $this->Html->getCategoryImageSrc($idForImageUpload);
if (!empty($category->tmp_image != '')) {
    $imageSrc = str_replace('\\', '/', $category->tmp_image);
}

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
            <?php echo $this->element('printIcon'); ?>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($category, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Slug->getCategoryEdit($category->id_category) : $this->Slug->getCategoryAdd(),
    'id' => 'categoryEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo $this->Form->control('Categories.name', [
    'label' => __d('admin', 'Name')
]);
echo $this->Form->control('Categories.id_parent', [
    'type' => 'select',
    'label' => __d('admin', 'Parent_category'),
    'empty' => __d('admin', 'No_parent_category_(highest_level)'),
    'options' => $categoriesForSelect
]);

echo '<div class="input">';
echo '<label>'.__d('admin', 'Image');
if ($imageSrc) {
    echo '<br /><span class="small">'.__d('admin', 'Click_on_image_to_change_it.').'</span>';
}
echo '</label>';
echo '<div style="float:right;">';
echo $this->Html->link(
    $imageSrc ? $this->Html->image($imageSrc) : '<i class="fas fa-plus-square"></i>',
    'javascript:void(0);',
    [
        'class' => 'btn btn-outline-light add-image-button ' . ($imageSrc ? 'uploaded' : ''),
        'title' => __d('admin', 'Upload_new_image_or_change_it'),
        'data-object-id' => $idForImageUpload,
        'escape' => false
    ]
);
echo '</div>';
echo $this->Form->hidden('Categories.tmp_image');
echo '</div>';

echo $this->Form->control('Categories.delete_image', [
    'label' => __d('admin', 'Delete_image?') . ' <span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
    'type' => 'checkbox',
    'escape' => false
]);

if ($this->request->getRequestTarget() != $this->Slug->getCategoryAdd()) {
    echo $this->Form->control('Categories.delete_category', [
        'label' => __d('admin', 'Delete_category?').' <span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
}

echo $this->Form->control('Categories.active', [
    'label' => __d('admin', 'Active').'?',
    'type' => 'checkbox'
]);

echo $this->Form->control('Categories.description', [
    'class' => 'ckeditor',
    'type' => 'textarea',
    'label' => __d('admin', 'Description') . '<br /><br /><span class="small"><a href="'.$this->Html->getDocsUrl(__d('admin', 'docs_route_wysiwyg_editor')).'" target="_blank">'.__d('admin', 'How_do_I_use_the_WYSIWYG_editor?').'</a></span>',
    'escape' => false
]);

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
