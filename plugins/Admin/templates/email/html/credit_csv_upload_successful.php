<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.2.0
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
                    echo __d('admin', 'Amount_(Money)') . ': ' . Configure::read('app.numberHelper')->formatAsCurrency($csvPayment->amount);
                ?>
            </p>

            <p>
                <?php
                    echo __d('admin', 'Date_when_payment_was_received_on_bank_account') . ': ' . $csvPayment->date_transaction_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'));
                ?>
            </p>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
