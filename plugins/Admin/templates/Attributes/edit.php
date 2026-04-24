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
            class="fa-fw fas fa-check"></i> <?php echo __('Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __('Cancel'); ?></a>
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
    'label' => __('Name')
]);

echo $this->Form->control('Attributes.can_be_used_as_unit', [
    'label' => __('Weight_unit').'? <span class="after small">'.__('Please_check_if_this_attribute_is_a_weight_attribute_(e_g_kg).') . ' ' . '<a href="'.$this->Html->getDocsUrl(__('docs_route_products')).'" target="_blank">'.__('Information_needed_for_function_price_per_unit.').'</a></span>',
    'type' => 'checkbox',
    'escape' => false
]);


if ($this->request->getRequestTarget() != $this->Slug->getAttributeAdd()) {
    echo '<div class="warning">';
        echo $this->Form->control('Attributes.delete_attribute', [
            'label' => __('Delete_attribute?').' <span class="after small">' . ($attribute->has_combined_products ? __('Attribute_can_not_be_deleted_because_products_are_associated_with_it.') : __('Check_and_do_not_forget_to_click_save_button._admin')) . '</span>',
            'disabled' => ($attribute->has_combined_products ? 'disabled' : ''),
            'escape' => false,
            'type' => 'checkbox'
        ]);
    echo '</div>';
}

echo $this->Form->end();
?>
