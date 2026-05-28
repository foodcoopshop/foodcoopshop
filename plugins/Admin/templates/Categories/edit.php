<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.categories .add-image-button', foodcoopshop.Upload.saveCategoryTmpImageInForm);" .
        Configure::read('app.jsNamespace') . ".Admin.disableSelectpickerItems('#categories-id-parent', " . json_encode($disabledSelectCategoryIds) . ");" .
        Configure::read('app.jsNamespace') . ".Editor.initBig('categories-description');" .
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
            class="fa-fw fas fa-check"></i> <?php echo __('Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __('Cancel'); ?></a>
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
    'label' => __('Name')
]);
echo $this->Form->control('Categories.id_parent', [
    'type' => 'select',
    'label' => __('Parent_category'),
    'empty' => __('No_parent_category_(highest_level)'),
    'options' => $categoriesForSelect
]);

echo '<div class="input">';
echo '<label>'.__('Image');
if ($imageSrc) {
    echo '<br /><span class="small">'.__('Click_on_image_to_change_it.').'</span>';
}
echo '</label>';
echo '<div style="float:right;">';
echo $this->Html->link(
    $imageSrc ? $this->Html->image($imageSrc) : '<i class="fas fa-plus-square"></i>',
    'javascript:void(0);',
    [
        'class' => 'btn btn-outline-light add-image-button ' . ($imageSrc ? 'uploaded' : ''),
        'title' => __('Upload_new_image_or_change_it'),
        'data-object-id' => $idForImageUpload,
        'escape' => false
    ]
);
echo '</div>';
echo $this->Form->hidden('Categories.tmp_image');
$this->Form->unlockField('Categories.tmp_image');
echo '</div>';

echo '<div class="warning">';
    echo $this->Form->control('Categories.delete_image', [
        'label' => __('Delete_image?') . ' <span class="after small">'.__('Check_and_do_not_forget_to_click_save_button._admin').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
echo '</div>';

if ($this->request->getRequestTarget() != $this->Slug->getCategoryAdd()) {
    echo $this->Form->hidden('Categories.delete_category', [
        'value' => 0,
        'id' => 'categories-delete-category',
        'class' => 'js-delete-entity-input',
        'data-delete-label' => __('Delete'),
        'data-delete-confirm' => __('Do you really want to delete this category?'),
    ]);
    $this->Form->unlockField('Categories.delete_category');
}

echo $this->Form->control('Categories.active', [
    'label' => __('Active').'?',
    'type' => 'checkbox'
]);

echo $this->Form->control('Categories.description', [
    'type' => 'textarea',
    'label' => __('Description') . '<br /><br /><span class="small"><a href="'.$this->Html->getDocsUrl(__('docs_route_wysiwyg_editor')).'" target="_blank">'.__('How_do_I_use_the_WYSIWYG_editor?').'</a></span>',
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
