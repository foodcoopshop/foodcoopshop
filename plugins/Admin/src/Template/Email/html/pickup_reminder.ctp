<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
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
        	<p><?php echo __d('admin', 'Please_do_not_forget_that_you_have_products_to_pick_up_on_{0}.', ['<b>' . $formattedPickupDay . '</b>']); ?></p>
            <ul style="padding-left:10px;">
    			<?php
    			     foreach($futureOrderDetails as $orderDetail) {
    			         echo '<li>' .  $orderDetail->product_amount . 'x ' . $orderDetail->product_name . ', ' . $orderDetail->product->manufacturer->name . '</li>';
    			     }
    			?>
			</ul>
			<p><?php echo __d('admin', 'As_your_order_is_longer_than_{0}_days_ago_you_get_this_reminder.', [$diffOrderAndPickupInDays]); ?></p>
			
        </td>
    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
