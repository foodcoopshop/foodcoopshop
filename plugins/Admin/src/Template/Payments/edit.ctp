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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Helper.initCkeditor('payments-approval-comment');" .
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Financial_reports')."');" . 
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
            class="fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($payment, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate'
]);
echo $this->Form->hidden('referer', ['value' => $referer]);

echo '<p><label>'.__d('admin', 'Member').'</label>' . (!empty($payment->customer) ? $payment->customer->name : '').'</p>';
echo '<p><label>'.__d('admin', 'Amount').'</label>' . $this->Number->formatAsCurrency($payment->amount).'</p>';
echo '<p><label>'.__d('admin', 'Date_of_upload').'</label>' . $payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')) .'</p>';
echo '<p><label>'.__d('admin', 'Date_last_modified').'</label>' . $payment->date_changed->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')).'</p>';
echo '<p><label>'.__d('admin', 'Last_editor').'</label>' . (empty($payment->changed_by_customer) ? __d('admin', 'This_payment_has_not_been_changed_yet.') : $payment->changed_by_customer->name).'</p>';
echo $this->Form->control('Payments.approval', [
    'type' => 'select',
    'label' => 'Status',
    'options' => $this->Html->getApprovalStates()
]);

$checkboxLabel = __d('admin', 'Send_email?').' <span class="after small multiple-lines">'.__d('admin', 'If_checked_the_member_will_be_notified_about_the_status_change_by_email_on_saving_including_the_comment.').'<br /><span style="float: left;">'.__d('admin', 'Email_preview').':</span>'.
    $this->Html->link(
        '<i class="fas fa-check-circle ok"></i>',
        '/admin/payments/previewEmail/'.$payment->id.'/1',
        [
            'class' => 'btn btn-outline-light email-template-info',
            'target' => '_blank',
            'escape' => false
        ]
    ).' '.
    $this->Html->link(
        '<i class="fas fa-minus-circle not-ok"></i>',
        '/admin/payments/previewEmail/'.$payment->id.'/-1',
        [
            'class' => 'btn btn-outline-light email-template-info',
            'target' => '_blank',
            'escape' => false
        ]
    ).
    '</span>';

echo $this->Form->control('Payments.send_email', [
    'label' => $checkboxLabel,
    'type' => 'checkbox',
    'escape' => false
]);

echo $this->Form->control('Payments.approval_comment', [
    'type' => 'textarea',
    'label' => __d('admin', 'Comment'),
    'class' => 'ckeditor'
]);

echo $this->Form->end();

?>
