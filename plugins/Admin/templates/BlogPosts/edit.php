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

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
    $('input.datepicker').datepicker();".
    Configure::read('app.jsNamespace') . ".Admin.init();" .
    Configure::read('app.jsNamespace') . ".Editor.initBig('blogposts-content');" .
    Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.blog_posts .add-image-button', foodcoopshop.Upload.saveBlogPostTmpImageInForm);" .
    Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);

$idForImageUpload = (!empty($blogPost->id_blog_post)) ? $blogPost->id_blog_post : StringComponent::createRandomString(6);
$imageSrc = $this->Html->getBlogPostImageSrc($blogPost, 'single');
if (!empty($blogPost->tmp_image) && $blogPost->tmp_image != '') {
    $imageSrc = str_replace('\\', '/', $blogPost->tmp_image);
}
$imageExists = ! preg_match('/no-single-default/', $imageSrc);

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
            <?php echo $this->element('printIcon'); ?>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($blogPost, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Slug->getBlogPostEdit($blogPost->id_blog_post) : $this->Slug->getBlogPostAdd(),
    'id' => 'blogPostEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);
echo $this->Form->control('BlogPosts.title', [
    'class' => 'long',
    'label' => __d('admin', 'Title')
]);
echo $this->Form->control('BlogPosts.short_description', [
    'class' => 'long',
    'label' => __d('admin', 'Short_description')
]);

echo '<div class="input">';
echo '<label>'.__d('admin', 'Image');
if ($imageExists) {
    echo '<br /><span class="small">'.__d('admin', 'Click_on_image_to_change_it.').'</span>';
}
echo '</label>';
echo '<div class="blog-post-image-wrapper">';
    echo $this->Html->link(
        $imageExists ? $this->Html->image($imageSrc) : '<i class="fas fa-plus-square"></i>',
        'javascript:void(0);',
        [
            'class' => 'btn btn-outline-light add-image-button ' . ($imageExists ? 'uploaded' : ''),
            'title' => __d('admin', 'Upload_new_image_or_change_it'),
            'data-object-id' => $idForImageUpload,
            'escape' => false
        ]
    );
    $defaultImageExplanationText = __d('admin', 'If_the_blog_post_is_associated_to_a_manufacturer_and_no_image_selected_the_default_image_of_the_manufacturer_profile_is_shown.');
    if ($identity->isManufacturer()) {
        $defaultImageExplanationText = __d('admin', 'If_no_image_selected_the_default_image_of_your_manufacturer_profile_is_shown.');
    }
    echo '<span class="small">' . $defaultImageExplanationText . '</span>';
echo '</div>';
echo $this->Form->hidden('BlogPosts.tmp_image');
$this->Form->unlockField('BlogPosts.tmp_image');
echo '</div>';

echo '<div class="warning">';
    echo $this->Form->control('BlogPosts.delete_image', [
        'label' => __d('admin', 'Delete_image?') . ' <span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
echo '</div>';

if (Configure::read('app.showManufacturerListAndDetailPage') && ($identity->isSuperadmin() || $identity->isAdmin())) {
    echo $this->Form->control('BlogPosts.id_manufacturer', [
        'type' => 'select',
        'label' => __d('admin', 'Manufacturer'),
        'empty' => __d('admin', 'Chose_manufacturer'),
        'options' => $manufacturersForDropdown
    ]);
    echo '<span class="description small">'.__d('admin', 'Blog_post_manufacturer_description') . '</span>';
}

echo $this->Form->control('BlogPosts.show_on_start_page_until', [
    'class' => 'datepicker',
    'label' => __d('admin', 'Show_on_startpage_until'),
    'type' => 'text',
    'escape' => false,
    'value' => !is_null($blogPost->show_on_start_page_until) ? $blogPost->show_on_start_page_until->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) : null,
]);
echo '<span class="description small">' . __d('admin', 'After_that_date_the_blog_post_is_shown_in_the_blog_archive._Leave_empty_to_never_show_blog_post_on_start_page.') . '</span>';


echo $this->Form->control('BlogPosts.is_private', [
    'label' => __d('admin', 'Only_for_members').'?',
    'type' => 'checkbox'
]);
echo $this->Form->control('BlogPosts.active', [
    'label' => __d('admin', 'Active') . '?',
    'type' => 'checkbox'
]);

if (($identity->isSuperadmin() || $identity->isAdmin()) && $this->request->getRequestTarget() != $this->Slug->getBlogPostAdd()) {
    echo $this->Form->control('BlogPosts.update_modified_field', [
        'label' => __d('admin', 'Move_on_first_place?') . ' <span class="after small">'.__d('admin', 'If_checked_the_blog_post_will_be_set_to_first_place_of_list.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
}

if ($this->request->getRequestTarget() != $this->Slug->getBlogPostAdd()) {
    echo '<div class="warning">';
        echo $this->Form->control('BlogPosts.delete_blog_post', [
            'label' => __d('admin', 'Delete_blog_post?').' <span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
            'type' => 'checkbox',
            'escape' => false
        ]);
    echo '</div>';
}

echo $this->Form->control('BlogPosts.content', [
    'type' => 'textarea',
    'label' => __d('admin', 'Text').'<br /><br /><span class="small"><a href="'.$this->Html->getDocsUrl(__d('admin', 'docs_route_wysiwyg_editor')).'" target="_blank">'.__d('admin', 'How_do_I_use_the_WYSIWYG_editor?').'</a></span>',
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
