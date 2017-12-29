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
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['Attribute']['id_attribute']) ? $this->request->data['Attribute']['id_attribute'] : "") . "', 'Attribute');
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
        <li>Auf dieser Seite kannst du die Variante ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('Attribute', array(
    'class' => 'fcs-form'
));

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Attribute.id_attribute');

echo $this->Form->input('Attribute.name', array(
    'div' => array(
        'class' => 'long text input'
    ),
    'label' => 'Name',
    'required' => true
));

if ($this->here != $this->Slug->getAttributeAdd()) {
    $hasCombinedProducts = count($unsavedAttribute['CombinationProducts']['online']) > 0 || count($unsavedAttribute['CombinationProducts']['offline']) > 0;
    echo $this->Form->input('Attribute.delete_attribute', array(
        'label' => 'Variante löschen?',
        'disabled' => ($hasCombinedProducts ? 'disabled' : ''),
        'type' => 'checkbox',
        'after' => '<span class="after small">' . ($hasCombinedProducts ? 'Das Löschen dieser Variante ist nicht möglich, weil Produkte zugewiesen sind.' : 'Anhaken und dann auf <b>Speichern</b> klicken.') . '</span>'
    ));
}

?>

</form>
