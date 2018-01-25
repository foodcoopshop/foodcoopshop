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

$this->element('addScript', [
    'script' => Configure::read('AppConfig.jsNamespace') . ".Admin.init();" . Configure::read('AppConfig.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['Taxes']['id_tax']) ? $this->request->data['Taxes']['id_tax'] : "") . "', 'Taxes');
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
        <li>Auf dieser Seite kannst du den Steuersatz ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('Taxes', [
    'class' => 'fcs-form'
]);

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Taxes.id_tax');

if ($this->request->here != $this->Slug->getTaxAdd()) {
    echo '<label>Steuersatz<br /><span class="small">Steuersätze sind nicht änderbar</span></label><p>' . $this->Html->formatAsPercent($unsavedTax['Taxes']['rate']) . '</p>';
} else {
    echo $this->Form->input('Taxes.rate', [
        'div' => [
            'class' => 'long text input'
        ],
        'label' => 'Steuersatz<br /><span class="small">z.B. "10" für 10%<br />Steuersätze sind später nicht änderbar</span>',
        'required' => true
    ]);
}

echo $this->Form->input('Taxes.active', [
    'label' => 'Steuersatz aktiv?'
]);

?>

</form>
