<?php
declare(strict_types=1);

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if (!$this->Html->paymentIsCashless() || $identity === null || $identity->isSelfServiceCustomer()) {
    return;
}
?>
<a class="not-in-moblie-menu btn btn-success btn-add-deposit" href="javascript:void(0);">
    <i class="<?php echo $this->Html->getFontAwesomeIconForCurrencyName(); ?>"></i> <?php echo __('Deposit'); ?>
</a>

<?php
    echo '<div id="add-payment-deposit-form" class="add-payment-form">';

        echo '<h3>'.__('Add_deposit').'</h3>';
        echo '<p>'.__('Add_deposit_amount_for_{0}', ['<b>' . $identity->name . '</b>']).':</p>';

        if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            echo '<p><b>'.__('The_entered_deposit_will_not_show_up_in_the_cart.').'</b></p>';
        }

        echo $this->Form->control('Payments.amount', [
            'label' => __('Amount_in_{0}', [Configure::read('appDb.FCS_CURRENCY_SYMBOL')]),
            'type' => 'number',
            'step' => '0.01'
        ]);

        echo $this->Form->hidden('Payments.type', [
            'value' => 'deposit'
        ]);

        echo $this->Form->hidden('Payments.customerId', [
            'value' => $identity->getId()
        ]);

    echo '</div>';
?>