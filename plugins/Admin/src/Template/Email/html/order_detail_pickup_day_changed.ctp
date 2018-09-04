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
			
            <p>
                <?php echo __d('admin', 'New_pickup_day'); ?>: <b><?php echo $newPickupDay; ?></b><br />
                <?php echo __d('admin', 'Old_pickup_day'); ?>: <?php echo $oldPickupDay; ?>
            </p>
            
            <p>  
                <?php echo __d('admin', 'Why_was_the_pickup_day_changed?'); ?><br />
                <b><?php echo '"' . $changePickupDayReason . '"'; ?></b>
            </p>

            
            <p>
            <?php
            if (count($orderDetails) == 1) {
                echo __d('admin', 'The_following_product_is_affected');
            } else {
                echo __d('admin', 'The_following_{0}_products_are_affected', [count($orderDetails)]);
            }
            ?>:</p>
            <ul style="padding-left: 10px;">
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
