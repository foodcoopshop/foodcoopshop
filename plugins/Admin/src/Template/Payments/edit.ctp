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

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Helper.initCkeditor('payments-approval-comment');" .
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('Homepage-Verwaltung', 'Finanzberichte');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();
        $('#payments-approval').on('change', function() {
            var emailCheckbox = $('#payments-send-email');
            if ($(this).val() == -1) {
                emailCheckbox.prop('checked', true);
            } else {
                emailCheckbox.prop('checked', false);
            }
        }).trigger('change');
    "
]);

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-default cancel"><i class="fa fa-remove"></i> <?php echo __d('admin', 'Cancel'); ?></a>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du die Guthaben-Aufladungen ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($payment, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate'
]);
echo $this->Form->hidden('referer', ['value' => $referer]);

echo '<p><label>Mitglied</label>' . $payment->customer->name.'</p>';
echo '<p><label>Betrag</label>' . $this->Html->formatAsEuro($payment->amount).'</p>';
echo '<p><label>Datum der Aufladung</label>' . $payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')) .'</p>';
echo '<p><label>Datum der letzten Änderung</label>' . $payment->date_changed->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')).'</p>';
echo '<p><label>Letzter Bearbeiter</label>' . (empty($payment->changed_by_customer) ? 'Diese Zahlung wurde noch nicht bearbeitet' : $payment->changed_by_customer->name).'</p>';
echo $this->Form->control('Payments.approval', [
    'type' => 'select',
    'label' => 'Status',
    'options' => $this->Html->getApprovalStates()
]);

$checkboxLabel = 'E-Mail versenden? <span class="after small multiple-lines">Wenn angehakt, wird das Mitglied beim Speichern per E-Mail<br /> über die Status-Änderung informiert (inkl. Kommentar).<br /><span style="float: left;">E-Mail-Vorschau:</span>'.
    $this->Html->getJqueryUiIcon(
        $this->Html->image($this->Html->getFamFamFamPath('accept.png')),
        [
            'class' => 'email-template-info',
            'target' => '_blank'
        ],
        '/admin/payments/previewEmail/'.$payment->id.'/1'
    ).'&nbsp;'.
    $this->Html->getJqueryUiIcon(
        $this->Html->image($this->Html->getFamFamFamPath('delete.png')),
        [
            'class' => 'email-template-info',
            'target' => '_blank'
        ],
        '/admin/payments/previewEmail/'.$payment->id.'/-1'
    ).
    '</span>';

echo $this->Form->control('Payments.send_email', [
    'label' => $checkboxLabel,
    'type' => 'checkbox',
    'escape' => false
]);

echo $this->Form->control('Payments.approval_comment', [
    'type' => 'textarea',
    'label' => 'Kommentar',
    'class' => 'ckeditor'
]);

echo $this->Form->end();

?>
