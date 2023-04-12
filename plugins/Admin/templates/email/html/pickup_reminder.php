<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
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

            <p><?php echo __d('admin', 'Please_do_not_forget_that_you_have_products_to_pick_up_on_{0}.', ['<b>' . $formattedPickupDay . '</b>']); ?></p>

            <ul style="padding-left:10px;">
                <?php
                     foreach($futureOrderDetails as $orderDetail) {
                         echo '<li>' .  $orderDetail->product_amount . 'x ' . $orderDetail->product_name . ', ' . $orderDetail->product->manufacturer->name . '</li>';
                     }
                ?>
            </ul>

            <p><?php echo __d('admin', 'As_your_order_is_longer_than_{0}_days_ago_you_get_this_reminder.', [$diffOrderAndPickupInDays]); ?></p>

            <p>
                <?php echo __d('admin', 'Here_you_can_unsubscribe_this_email_reminder'); ?>: <a href="<?php echo Configure::read('App.fullBaseUrl').$this->Slug->getCustomerProfile(); ?>"><?php echo Configure::read('App.fullBaseUrl').$this->Slug->getCustomerProfile(); ?></a>
            </p>

        </td>
    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
