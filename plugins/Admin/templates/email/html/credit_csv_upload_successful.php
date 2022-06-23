<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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
                    echo __d('admin', 'Amount_(Money)') . ': ' . Configure::read('app.numberHelper')->formatAsCurrency($csvPayment->amount);
                ?>
            </p>

            <p>
                <?php
                    echo __d('admin', 'Date_when_payment_was_received_on_bank_account') . ': ' . $csvPayment->date_transaction_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'));
                ?>
            </p>

            <p>
                <?php echo __d('admin', 'Here_you_can_unsubscribe_this_email_reminder'); ?>: <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getCustomerProfile(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getCustomerProfile(); ?></a>
            </p>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
