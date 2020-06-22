<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
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
            <?php echo __d('admin', 'Hello'); ?> <?php echo $orderDetail->product->manufacturer->address_manufacturer->firstname; ?>,
        </td>
    </tr>

    <tr>
        <td>

            <p>
                <?php echo __d('admin', '{0}_has_written_a_feedback_to_product_{1}.', [
                    '<b>'.$orderDetail->customer->name . '</b>',
                    '<b>'.$orderDetail->product_name . '</b>',
                ])?>
                <?php echo __d('admin', 'Delivery_day'); ?>: <b><?php echo $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateShort')); ?></b>
            </p>

            <p>
                <?php echo __d('admin', 'Feedback'); ?>:<br />
                <?php echo '"' . $orderDetailFeedback . '"'; ?>
            </p>

            <p>
                <?php echo __d('admin', 'If_you_want_to_answer_please_write_an_email_to_{0}_and_do_not_reply_to_this_email!', ['<b>'.$orderDetail->customer->email.'</b>']); ?>
            </p>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
