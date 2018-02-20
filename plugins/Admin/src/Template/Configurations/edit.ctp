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

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['Configurations']['id_Configuration']) ? $this->request->data['Configurations']['id_Configuration'] : "") . "', 'Configurations');
    "
]);

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a> <a href="javascript:void(0);"
            class="btn btn-default cancel"><i class="fa fa-remove"></i> Abbrechen</a>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du die Einstellungen ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('Configurations', [
    'class' => 'fcs-form'
]);

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Configuration.id_configuration');

$label = $unsavedConfiguration['Configurations']['text'];

switch ($unsavedConfiguration['Configurations']['type']) {
    case 'number':
    case 'text':
        echo $this->Form->input('Configuration.value', [
            'type' => 'text',
            'div' => [
                'class' => 'long text input'
            ],
            'label' => $label
        ]);
        break;
    case 'textarea':
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Helper.initCkeditor('configurations-value');"
        ]);
        echo $this->Form->input('Configuration.value', [
            'type' => 'textarea',
            'label' => $label,
            'class' => 'ckeditor'
        ]);
        break;
    case 'textarea_big':
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('configuration-value');"
        ]);
        echo $this->Form->input('Configuration.value', [
            'type' => 'textarea',
            'label' => $label . '<br /><br /><span class="small"><a href="https://foodcoopshop.github.io/de/wysiwyg-editor" target="_blank">Wie verwende ich den Editor?</a></span>',
            'class' => 'ckeditor'
        ]);
        break;
    case 'dropdown':
    case 'boolean':
        echo $this->Form->input('Configuration.value', [
            'type' => 'select',
            'label' => $label,
            'options' => $this->Html->getConfigurationDropdownOptions($unsavedConfiguration['Configurations']['name'])
        ]);
        break;
}

echo '<div class="sc"></div>';

?>

</form>

<div class="sc"></div>
