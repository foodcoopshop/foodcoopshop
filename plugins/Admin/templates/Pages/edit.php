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
        Configure::read('app.jsNamespace') . ".Admin.disableSelectpickerItems('#pages-id-parent', " . json_encode($disabledSelectPageIds) . ");" .
        Configure::read('app.jsNamespace') . ".Admin.initHeaderPromoVisibility('body.pages a.add-image-button', 'body.pages input[name=\\\"Pages[delete_image]\\\"]', '.header-promo-section');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);

$idForImageUpload = (!empty($page->id_page)) ? $page->id_page : StringComponent::createRandomString(6);
$imageSrc = $this->Html->getPageImageSrc($page, 'single');
if (!empty($page->tmp_image) && $page->tmp_image != '') {
    $imageSrc = str_replace('\\', '/', $page->tmp_image);
}
$imageExists = $imageSrc != '';

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

echo $this->Form->create($page, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Slug->getPageEdit($page->id_page) : $this->Slug->getPageAdd(),
    'id' => 'pageEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo '<div class="page-header-layout">';
echo '<div class="page-header-image-column">';

echo '<section class="home-edit-section home-edit-section-header-image">';
    echo '<h2>' . __('Header image') . '</h2>';
    echo '<div class="input">';
    echo '<div class="page-image-wrapper">';
        echo $this->Html->link(
            $imageExists ? $this->Html->image($imageSrc) : '<i class="fas fa-plus-square"></i> ' . __('Header image upload'),
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light add-image-button ' . ($imageExists ? 'uploaded' : ''),
                'title' => __('Upload_new_image_or_change_it'),
                'data-object-id' => $idForImageUpload,
                'escape' => false,
            ]
        );
        $imageLabel = __('min {0}px width.', [number_format(Page::IMAGE_UPLOAD_MIN_WIDTH, 0, ',', '.')]);
        if ($imageExists) {
            $imageLabel .= '<br />' . __('Click_on_image_to_change_it.');
        }
        echo '<span class="small">' . $imageLabel . '</span>';
    echo '</div>';
    echo $this->Form->hidden('Pages.tmp_image');
    $this->Form->unlockField('Pages.tmp_image');
    echo '</div>';
echo '</section>';

if ($imageExists) {
    echo '<div class="warning">';
        echo $this->Form->control('Pages.delete_image', [
            'label' => __('Delete_image?') . ' <span class="after small">'.__('Check_and_do_not_forget_to_click_save_button._admin').'</span>',
            'type' => 'checkbox',
            'escape' => false,
        ]);
    echo '</div>';
}

echo '</div>';

echo '<section class="home-edit-section home-edit-section-header-promo page-header-info-column header-promo-section' . ($imageExists ? '' : ' hide') . '">';
    echo '<h2>' . __('Header infos') . ' ' . '(' . __('only for logged out users') . ')</h2>';
    echo $this->element('headerPromoFields');
echo '</section>';
echo '</div>';

echo '<h2>' . __('Further data') . '</h2>';

echo $this->Form->control('Pages.title', [
    'label' => __('Page_title'),
    'required' => true
]);
echo $this->Form->control('Pages.menu_type', [
    'type' => 'select',
    'label' => __('Pages_menu_type_main_description').'<br /><span class="small">'. __('Pages_menu_type_sub_description').'</span>',
    'options' => $this->Html->getMenuTypes(),
    'escape' => false
]);
echo $this->Form->control('Pages.id_parent', [
    'type' => 'select',
    'label' => __('Pages_parent_main_description').'<br /><span class="small">'.__('Pages_parent_sub_description').'</span>',
    'empty' => __('Chose_parent_menu_item'),
    'options' => $pagesForSelect,
    'escape' => false
]);
echo $this->Form->control('Pages.position', [
    'class' => 'short',
    'label' => __('Pages_position_main_description').'<br /><span class="small">'.__('Pages_position_sub_description').'</span> <span class="after small">'.__('Pages_position_sub2_description').'</span>',
    'type' => 'text',
    'escape' => false
]);

echo $this->Form->control('Pages.full_width', [
    'label' => __('Pages_full_width_main_description') . ' <span class="after small">'.__('Pages_full_width_sub_description') . '</span>',
    'type' => 'checkbox',
    'escape' => false
]);
echo $this->Form->control('Pages.extern_url', [
    'placeholder' => __('Example_given_abbreviation') . ' https://www.foodcoopshop.com',
    'label' => __('Pages_extern_url_main_description') . '<br /><span class="small">'.__('Pages_extern_url_sub_description') . '</span>',
    'div' => [
        'class' => 'long text input'
    ],
    'escape' => false
]);

if ($this->request->getRequestTarget() != $this->Slug->getPageAdd()) {
    echo '<div class="warning">';
        echo $this->Form->control('Pages.delete_page', [
            'label' => __('Pages_delete_page_main_description') . ' <span class="after small">'.__('Check_and_do_not_forget_to_click_save_button._admin').'</span>',
            'type' => 'checkbox',
            'escape' => false
        ]);
    echo '</div>';
}

echo $this->Form->control('Pages.is_private', [
    'label' => __('Only_for_members').'?',
    'type' => 'checkbox'
]);
echo $this->Form->control('Pages.active', [
    'label' => __('Active').'?',
    'type' => 'checkbox'
]);

echo $this->Form->control('Pages.content', [
    'type' => 'textarea',
    'label' => __('Text') . '<br /><br /><span class="small"><a href="'.$this->Html->getDocsUrl(__('docs_route_wysiwyg_editor')).'" target="_blank">'.__('How_do_I_use_the_WYSIWYG_editor?').'</a></span>',
    'escape' => false
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
