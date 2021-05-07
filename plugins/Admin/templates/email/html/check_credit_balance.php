<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <?php echo $this->element('email/greeting', ['data' => $customer]); ?>

    <tr>
        <td>

            <p>
                <?php
                    echo __d('admin', 'your_current_credit_equals_{0}.', [
                        '<b>'.$delta.'</b>',
                    ]);
                ?>
            </p>

            <?php if (Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) { ?>
                <p><?php echo __d('admin', 'Please_soon_transfer_new_credit_to_our_bank_account.'); ?></p>
                <p><?php echo __d('admin', 'Do_not_forget_to_add_it_to_our_credit_system_after_the_bank_transfer.'); ?></p>
                <p><?php echo __d('admin', 'Here_you_find_the_link_to_add_the_credit:'); ?><br />
                    <a href="<?php echo Configure::read('app.cakeServerName') . $this->Slug->getMyCreditBalance(); ?>"><?php echo Configure::read('app.cakeServerName') . $this->Slug->getMyCreditBalance(); ?></a>
                </p>
           <?php } else { ?>
                <p><?php echo __d('admin', 'Please_soon_transfer_new_credit_to_our_bank_account_and_do_not_forget_to_add_your_personal_transaction_code_{0}.', [
                        '<b>' . $personalTransactionCode . '</b>',
                ]); ?></p>
                <?php if (!is_null($lastCsvUploadDate)) { ?>
                    <p><?php echo __d('admin', 'Transactions_were_checked_until_{0}.', [
                        $lastCsvUploadDate->i18nFormat($this->MyTime->getI18Format('DateNTimeShort'))
                    ]); ?></p>
                <?php } ?>
           <?php } ?>
            <?php
                if (Configure::read('appDb.FCS_BANK_ACCOUNT_DATA') != '') {
                    echo '<p><b>'.__d('admin', 'Bank_account_data').':</b> '.Configure::read('appDb.FCS_BANK_ACCOUNT_DATA').'</p>';
                }
            ?>
        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>