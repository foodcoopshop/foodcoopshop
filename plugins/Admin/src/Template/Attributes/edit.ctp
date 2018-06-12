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
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-default cancel"><i class="fa fa-remove"></i> <?php echo __d('admin', 'Cancel'); ?></a>
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
    'label' => 'Name'
]);

echo $this->Form->control('Attributes.can_be_used_as_unit', [
    'label' => 'Gewichtseinheit? <span class="after small">Bitte anhaken, falls diese Variante einer Gewichtseinheit (z.B. kg, g) entspricht. Diese Information wird für die Funktion <a href="https://foodcoopshop.github.io/de/produkte" target="_blank">Preis nach Gewicht</a> benötigt.</span>',
    'type' => 'checkbox',
    'escape' => false
]);


if ($this->request->getRequestTarget() != $this->Slug->getAttributeAdd()) {
    echo $this->Form->control('Attributes.delete_attribute', [
        'label' => 'Variante löschen? <span class="after small">' . ($attribute->has_combined_products ? 'Das Löschen dieser Variante ist nicht möglich, weil Produkte zugewiesen sind.' : 'Anhaken und dann auf <b>Speichern</b> klicken.') . '</span>',
        'disabled' => ($attribute->has_combined_products ? 'disabled' : ''),
        'escape' => false,
        'type' => 'checkbox'
    ]);
}

echo $this->Form->end();
?>
