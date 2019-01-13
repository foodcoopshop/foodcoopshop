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
            class="fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
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
echo $this->Form->hidden('Pages.id_page');
echo $this->Form->control('Pages.title', [
    'label' => __d('admin', 'Page_title'),
    'required' => true
]);

echo $this->Form->control('Pages.menu_type', [
    'type' => 'select',
    'label' => __d('admin', 'Pages_menu_type_main_description').'<br /><span class="small">'. __d('admin', 'Pages_menu_type_sub_description').'</span>',
    'options' => $this->Html->getMenuTypes(),
    'escape' => false
]);
echo $this->Form->control('Pages.id_parent', [
    'type' => 'select',
    'label' => __d('admin', 'Pages_parent_main_description').'<br /><span class="small">'.__d('admin', 'Pages_parent_sub_description').'</span>',
    'empty' => __d('admin', 'Chose_parent_menu_item'),
    'options' => $pagesForSelect,
    'escape' => false
]);
echo $this->Form->control('Pages.position', [
    'class' => 'short',
    'label' => __d('admin', 'Pages_position_main_description').'<br /><span class="small">'.__d('admin', 'Pages_position_sub_description').'</span> <span class="after small">'.__d('admin', 'Pages_position_sub2_description').'</span>',
    'type' => 'text',
    'escape' => false
]);

echo $this->Form->control('Pages.full_width', [
    'label' => __d('admin', 'Pages_full_width_main_description') . ' <span class="after small">'.__d('admin', 'Pages_full_width_sub_description') . '</span>',
    'type' => 'checkbox',
    'escape' => false
]);
echo $this->Form->control('Pages.extern_url', [
    'placeholder' => __d('admin', 'Example_given_abbreviation') . ' https://www.foodcoopshop.com',
    'label' => __d('admin', 'Pages_extern_url_main_description') . '<br /><span class="small">'.__d('admin', 'Pages_extern_url_sub_description') . '</span>',
    'div' => [
        'class' => 'long text input'
    ],
    'escape' => false
]);

if ($this->request->getRequestTarget() != $this->Slug->getPageAdd()) {
    echo $this->Form->control('Pages.delete_page', [
        'label' => __d('admin', 'Pages_delete_page_main_description') . ' <span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
}

echo $this->Form->control('Pages.is_private', [
    'label' => __d('admin', 'Only_for_members').'?',
    'type' => 'checkbox'
]);
echo $this->Form->control('Pages.active', [
    'label' => __d('admin', 'Active').'?',
    'type' => 'checkbox'
]);

echo $this->Form->control('Pages.content', [
    'class' => 'ckeditor',
    'type' => 'textarea',
    'label' => __d('admin', 'Text') . '<br /><br /><span class="small"><a href="'.$this->Html->getDocsUrl(__d('admin', 'docs_route_wysiwyg_editor')).'" target="_blank">'.__d('admin', 'How_do_I_use_the_WYSIWYG_editor?').'</a></span>',
    'escape' => false
]);

?>

</form>

<div class="sc"></div>
