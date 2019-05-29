<?php

use Cake\Core\Configure;

if (!Configure::read('app.isDepositPaymentCashless')) {
    return;
}
?>
<a class="btn btn-success btn-add-deposit" href="javascript:void(0);">
	<i class="<?php echo $this->Html->getFontAwesomeIconForCurrencyName(Configure::read('app.currencyName')); ?>"></i> <?php echo (!$isMobile ? __('Deposit_return') : ''); ?>
</a>

<?php
    echo '<div id="add-payment-deposit-form" class="add-payment-form">';
        echo '<h3>'.__('Add_deposit').'</h3>';
        echo '<p>'.__('Add_deposit_amount_for_{0}', ['<b>' . $appAuth->getUsername() . '</b>']).':</p>';
        
        echo $this->Form->control('Payments.amount', [
            'label' => __('Amount_in_{0}', [Configure::read('appDb.FCS_CURRENCY_SYMBOL')]),
            'type' => 'number',
            'step' => '0.01'
        ]);
        
        echo $this->Form->hidden('Payments.type', [
            'value' => 'deposit'
        ]);
        echo $this->Form->hidden('Payments.customerId', [
            'value' => $appAuth->getUserId()
        ]);
    echo '</div>';
?>