<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$this->element('addScript', array(
    'script' => 
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Helper.initCkeditor('CakePaymentApprovalComment');" .
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('Homepage-Verwaltung', 'Finanzberichte');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm('" .$this->request->data['CakePayment']['id'] . "', 'CakePayment');
        $('#CakePaymentApproval').on('change', function() {
            var emailCheckbox = $('#CakePaymentSendEmail');
            if ($(this).val() == -1) {
                emailCheckbox.prop('checked', true);
            } else {
                emailCheckbox.prop('checked', false);
            }
        });    
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
		<li>Auf dieser Seite kannst du die Guthaben-Aufladungen ändern.</li>
	</ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('CakePayment', array(
    'class' => 'fcs-form'
));

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('CakePayment.id');
echo $this->Form->hidden('Customer.name');
echo $this->Form->hidden('ChangedBy.name');
echo $this->Form->hidden('CakePayment.amount');
echo $this->Form->hidden('CakePayment.date_add');
echo $this->Form->hidden('CakePayment.date_changed');

echo '<p><label>Mitglied</label>' . $this->request->data['Customer']['name'].'</p>';
echo '<p><label>Betrag</label>' . $this->Html->formatAsEuro($this->request->data['CakePayment']['amount']).'</p>';
echo '<p><label>Datum der Aufladung</label>' . $this->Time->formatToDateNTimeLong($this->request->data['CakePayment']['date_add']).'</p>';
echo '<p><label>Datum der letzten Änderung</label>' . $this->Time->formatToDateNTimeLong($this->request->data['CakePayment']['date_changed']).'</p>';
echo '<p><label>Letzter Bearbeiter</label>' . $this->request->data['ChangedBy']['name'].'</p>';

echo $this->Form->input('CakePayment.approval', array(
    'type' => 'select',
    'label' => 'Status',
    'options' => $this->Html->getApprovalStates()
));

echo $this->Form->input('CakePayment.send_email', array(
    'label' => 'E-Mail versenden?',
    'type' => 'checkbox',
    'after' => '<span class="after small">Wenn angehakt, wird das Mitglied beim Speichern per E-Mail<br /> über die Status-Änderung informiert (inkl. Kommentar).</span>'
));

echo $this->Form->input('CakePayment.approval_comment', array(
    'type' => 'textarea',
    'label' => 'Kommentar',
    'class' => 'ckeditor'
));

?>

</form>