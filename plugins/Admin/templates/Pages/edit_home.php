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

use Cake\Core\Configure;
use App\Controller\Component\StringComponent;
use App\Model\Entity\Page;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Editor.initBig('pages-content');" .
        Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.pages .add-image-button', foodcoopshop.Upload.savePageTmpImageInForm);" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);

$idForImageUpload = StringComponent::createRandomString(6);
$imageSrc = $this->Html->getHomeImageSrc('single');
if (!empty($this->request->getData('Pages.tmp_image'))) {
    $imageSrc = str_replace('\\', '/', (string) $this->request->getData('Pages.tmp_image'));
}
$imageExists = $imageSrc != '';
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __('Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __('Cancel'); ?></a>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create(null, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $this->Slug->getPageEditHome(),
    'id' => 'pageEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo '<div class="input">';
echo '<label>'.__('Header image');
if ($imageExists) {
    echo '<br /><span class="small">'.__('Click_on_image_to_change_it.').'</span>';
}
echo '</label>';
echo '<div class="page-image-wrapper">';
    echo $this->Html->link(
        $imageExists ? $this->Html->image($imageSrc) : '<i class="fas fa-plus-square"></i>',
        'javascript:void(0);',
        [
            'class' => 'btn btn-outline-light add-image-button ' . ($imageExists ? 'uploaded' : ''),
            'title' => __('Upload_new_image_or_change_it'),
            'data-object-id' => $idForImageUpload,
            'escape' => false,
        ]
    );
    echo '<span class="small">' . __('min {0}px width.', [number_format(Page::IMAGE_UPLOAD_MIN_WIDTH, 0, ',', '.')]) . '</span>';
echo '</div>';
echo $this->Form->hidden('Pages.tmp_image');
$this->Form->unlockField('Pages.tmp_image');
echo '</div>';

if ($imageExists) {
    echo '<div class="warning">';
        echo $this->Form->control('Pages.delete_image', [
            'label' => __('Delete_image?') . ' <span class="after small">'.__('Check_and_do_not_forget_to_click_save_button._admin').'</span>',
            'type' => 'checkbox',
            'escape' => false,
        ]);
    echo '</div>';
}

echo $this->Form->control('Configurations.FCS_FOODCOOPS_MAP_ENABLED', [
    'type' => 'checkbox',
    'label' => __('Configuration_text_FCS_FOODCOOPS_MAP_ENABLED'),
    'checked' => $foodcoopsMapEnabled,
    'escape' => false,
]);

echo $this->Form->control('Pages.content', [
    'type' => 'textarea',
    'value' => $homeText,
    'label' => __('Text') . '<br /><br /><span class="small"><a href="'.$this->Html->getDocsUrl(__('docs_route_wysiwyg_editor')).'" target="_blank">'.__('How_do_I_use_the_WYSIWYG_editor?').'</a></span>',
    'escape' => false,
]);

echo $this->Form->end();

?>

<div class="sc"></div>

<?php
echo $this->element('imageUploadForm', [
    'id' => $idForImageUpload,
    'action' => '/admin/tools/doTmpPageImageUpload/',
    'imageExists' => $imageExists,
    'existingImageSrc' => $imageSrc,
]);
?>
