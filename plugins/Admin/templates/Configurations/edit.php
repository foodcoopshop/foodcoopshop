<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
        <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_settings'))]); ?>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($configuration, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'id' => 'configurationEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

$label = $configuration->text;

switch ($configuration->type) {
    case 'number':
    case 'text':
        echo $this->Form->control('Configurations.value', [
            'type' => 'text',
            'class' => 'long',
            'label' => $label,
            'escape' => false
        ]);
        break;
    case 'textarea':
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Helper.initCkeditor('configurations-value');"
        ]);
        echo $this->Form->control('Configurations.value', [
            'type' => 'textarea',
            'label' => $label,
            'class' => 'ckeditor',
            'escape' => false
        ]);
        break;
    case 'textarea_big':
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('configurations-value');"
        ]);
        echo $this->Form->control('Configurations.value', [
            'type' => 'textarea',
            'label' => $label . '<br /><br /><span class="small"><a href="https://foodcoopshop.github.io/de/wysiwyg-editor" target="_blank">Wie verwende ich den Editor?</a></span>',
            'class' => 'ckeditor',
            'escape' => false
        ]);
        break;
    case 'dropdown':
    case 'boolean':
        echo $this->Form->control('Configurations.value', [
            'type' => 'select',
            'label' => $label,
            'options' => $this->Configuration->getConfigurationDropdownOptions($configuration->name, $appAuth),
            'escape' => false
        ]);
        break;
    case 'multiple_dropdown':
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace') . ".Admin.setSelectPickerMultipleDropdowns('#configurations-value');"
        ]);
        // keep all checkmarks if one day does not validate
        $value = $configuration->value;
        if ($configuration->getInvalidField('value') != '') {
            $value = $configuration->getInvalidField('value');
        }
        echo $this->Form->control('Configurations.value', [
            'type' => 'select',
            'multiple' => true,
            'data-val' => $value,
            'label' => $label,
            'options' => $this->Configuration->getConfigurationDropdownOptions($configuration->name, $appAuth),
            'escape' => false
        ]);
        break;
}

echo '<div class="sc"></div>';

echo $this->Form->end();

?>

<div class="sc"></div>
