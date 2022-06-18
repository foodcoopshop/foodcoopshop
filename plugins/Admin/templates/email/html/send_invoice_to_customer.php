<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <tr>
        <td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
                <?php echo __d('admin', 'Hello'); ?> <?php echo $customerName; ?>,
            </td>
    </tr>

    <tr>
        <td>

            <p><?php echo __d('admin', 'Please_find_your_invoice_attached.'); ?></p>

            <p><?php echo __d('admin', '{0}_thanks_you_for_your_purchase!', [Configure::read('appDb.FCS_APP_NAME')]); ?></p>

            <p>
                <?php echo __d('admin', 'Here_you_can_unsubscribe_this_email_reminder'); ?>: <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getCustomerProfile(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getCustomerProfile(); ?></a>
            </p>

            <?php if (!$paidInCash) { ?>
                <p><br />
                    <?php
                        echo __d('admin', 'Post_scriptum_abbreviation') . ': ';
                        echo __d('admin', 'Your_current_credit_equals_{0}.', [
                            '<b>'.$this->MyNumber->formatAsCurrency($creditBalance).'</b>',
                        ]);
                    ?>
                </p>
            <?php } ?>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
