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

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <?php echo $this->element('email/greeting', ['data' => $customer]); ?>

    <tr>
        <td>

            <p>
                <?php echo __d('admin', 'New_pickup_day'); ?>: <b><?php echo $newPickupDay; ?></b><br />
                <?php echo __d('admin', 'Old_pickup_day'); ?>: <?php echo $oldPickupDay; ?>
            </p>

            <?php if ($editPickupDayReason != '') { ?>
            <p>
                <?php echo __d('admin', 'Why_was_the_pickup_day_changed?'); ?><br />
                <b><?php echo '"' . $editPickupDayReason . '"'; ?></b>
            </p>
            <?php } ?>

            <p>
            <?php
            if (count($orderDetails) == 1) {
                echo __d('admin', 'The_following_product_is_affected');
            } else {
                echo __d('admin', 'The_following_{0}_products_are_affected', [count($orderDetails)]);
            }
            ?>:</p>
            <ul style="padding-left:10px;">
                <?php
                     foreach($orderDetails as $orderDetail) {
                         echo '<li>' .  $orderDetail->product_amount . 'x ' . $orderDetail->product_name . ', ' . $orderDetail->product->manufacturer->name . '</li>';
                     }
                ?>
            </ul>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
