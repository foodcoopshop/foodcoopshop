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
        Configure::read('app.jsNamespace') . ".Helper.initCkeditor('PaymentApprovalComment');" .
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('Homepage-Verwaltung', 'Finanzberichte');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm('" .$this->request->data['Payment']['id'] . "', 'Payment');
        $('#PaymentApproval').on('change', function() {
            var emailCheckbox = $('#PaymentSendEmail');
            if ($(this).val() == -1) {
                emailCheckbox.prop('checked', true);
            } else {
                emailCheckbox.prop('checked', false);
            }
        }).trigger('change');
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

echo $this->Form->create('Payment', array(
    'class' => 'fcs-form'
));

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Payment.id');
echo $this->Form->hidden('Customer.name');
echo $this->Form->hidden('ChangedBy.name');
echo $this->Form->hidden('Payment.amount');
echo $this->Form->hidden('Payment.date_add');
echo $this->Form->hidden('Payment.date_changed');

echo '<p><label>Mitglied</label>' . $this->request->data['Customer']['name'].'</p>';
echo '<p><label>Betrag</label>' . $this->Html->formatAsEuro($this->request->data['Payment']['amount']).'</p>';
echo '<p><label>Datum der Aufladung</label>' . $this->Time->formatToDateNTimeLong($this->request->data['Payment']['date_add']).'</p>';
echo '<p><label>Datum der letzten Änderung</label>' . $this->Time->formatToDateNTimeLong($this->request->data['Payment']['date_changed']).'</p>';
echo '<p><label>Letzter Bearbeiter</label>' . $this->request->data['ChangedBy']['name'].'</p>';

echo $this->Form->input('Payment.approval', array(
    'type' => 'select',
    'label' => 'Status',
    'options' => $this->Html->getApprovalStates()
));

echo $this->Form->input('Payment.send_email', array(
    'label' => 'E-Mail versenden?',
    'type' => 'checkbox',
    'after' => '<span class="after small">Wenn angehakt, wird das Mitglied beim Speichern per E-Mail<br /> über die Status-Änderung informiert (inkl. Kommentar).<br /><span style="float: left;">E-Mail-Vorschau:</span>'.
        $this->Html->getJqueryUiIcon(
            $this->Html->image($this->Html->getFamFamFamPath('accept.png')),
            array(
                'class' => 'email-template-info',
                'target' => '_blank'
            ),
            '/admin/payments/previewEmail/'.$this->request->data['Payment']['id'].'/1'
        ).'&nbsp;'.
        $this->Html->getJqueryUiIcon(
            $this->Html->image($this->Html->getFamFamFamPath('delete.png')),
            array(
                'class' => 'email-template-info',
                'target' => '_blank'
            ),
            '/admin/payments/previewEmail/'.$this->request->data['Payment']['id'].'/-1'
        ).
    '</span>'
));

echo $this->Form->input('Payment.approval_comment', array(
    'type' => 'textarea',
    'label' => 'Kommentar',
    'class' => 'ckeditor'
));

?>

</form>
