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

$this->element('addScript', array(
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['Configuration']['id_Configuration']) ? $this->request->data['Configuration']['id_Configuration'] : "") . "', 'Configuration');
    "
));

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

echo $this->Form->create('Configuration', array(
    'class' => 'fcs-form'
));

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Configuration.id_configuration');

$label = $unsavedConfiguration['Configuration']['text'];

switch ($unsavedConfiguration['Configuration']['type']) {
    case 'number':
    case 'text':
        echo $this->Form->input('Configuration.value', array(
            'type' => 'text',
            'div' => array(
                'class' => 'long text input'
            ),
            'label' => $label,
            'required' => true
        ));
        break;
    case 'textarea':
        $this->element('addScript', array(
            'script' => Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('ConfigurationValue');
                "
        ));
        echo $this->Form->input('Configuration.value', array(
            'type' => 'textarea',
            'label' => $label,
            'required' => true,
            'class' => 'ckeditor'
        ));
        break;
    case 'dropdown':
    case 'boolean':
        echo $this->Form->input('Configuration.value', array(
            'type' => 'select',
            'label' => $label,
            'options' => $this->Html->getConfigurationDropdownOptions($unsavedConfiguration['Configuration']['name']),
            'required' => true
        ));
        break;
}

echo '<div class="sc"></div>';

?>

</form>

<div class="sc"></div>
