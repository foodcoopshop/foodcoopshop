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

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace') . ".Admin.init();" .
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

echo $this->Form->create($attribute, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Slug->getAttributeEdit($attribute->id_attribute) : $this->Slug->getAttributeAdd(),
    'id' => 'attributeEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo $this->Form->control('Attributes.name', [
    'div' => [
        'class' => 'long text input'
    ],
    'label' => __d('admin', 'Name')
]);

echo $this->Form->control('Attributes.can_be_used_as_unit', [
    'label' => __d('admin', 'Weight_unit').'? <span class="after small">'.__d('admin', 'Please_check_if_this_attribute_is_a_weight_attribute_(e_g_kg).') . ' ' . '<a href="'.$this->Html->getDocsUrl(__d('admin', 'docs_route_products')).'" target="_blank">'.__d('admin', 'Information_needed_for_function_price_per_unit.').'</a></span>',
    'type' => 'checkbox',
    'escape' => false
]);


if ($this->request->getRequestTarget() != $this->Slug->getAttributeAdd()) {
    echo $this->Form->control('Attributes.delete_attribute', [
        'label' => __d('admin', 'Delete_attribute?').' <span class="after small">' . ($attribute->has_combined_products ? __d('admin', 'Attribute_can_not_be_deleted_because_products_are_associated_with_it.') : __d('admin', 'Check_and_do_not_forget_to_click_save_button.')) . '</span>',
        'disabled' => ($attribute->has_combined_products ? 'disabled' : ''),
        'escape' => false,
        'type' => 'checkbox'
    ]);
}

echo $this->Form->end();
?>
