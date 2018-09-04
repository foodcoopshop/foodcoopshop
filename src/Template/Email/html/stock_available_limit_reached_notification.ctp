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
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
    
        <tr>
            <td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
                <?php echo $greeting; ?>,
            </td>
        </tr>

        <tr>
        	<td>
               	<p> 
					<?php
                	    if (isset($showManufacturerName) && $showManufacturerName) {
                	        echo __('Manufacturer') . ': <b>' . $cartProduct->product->manufacturer->name . '</b><br />'; 
                	    }
            	    ?>
					<?php echo __('Product') . ': <b>' . $cartProduct->order_detail->product_name . '</b><br />'; ?>
                	<?php echo __('Units_on_stock:') . ' <b>' . $this->MyNumber->formatAsDecimal($stockAvailable->quantity, 0) . '</b>'; ?><br />
                	<?php
                    	if ($stockAvailable->quantity_limit == $stockAvailable->quantity) {
                    	    echo __('Product_orders_are_not_possible_any_more!');
                    	} else {
                    	    echo __('Product_orders_possible_until:') . ' <b>' . $this->MyNumber->formatAsDecimal($stockAvailable->quantity_limit, 0) . '</b>';
                    	}
                	?>
                	<br />
                	<?php echo __('This_notification_triggered_if_stock_available_is_less_than:') . ' <b>' . $this->MyNumber->formatAsDecimal($stockAvailable->sold_out_limit, 0) . '</b>'; ?>
                </p>
                <p>
                	<a href="<?php echo Configure::read('app.cakeServerName').$productEditLink; ?>"><?php echo __('Click_here_to_edit_the_product.'); ?></a>
                	<?php
                    	if (isset($notificationEditLink)) {
                            echo $notificationEditLink;
                    	}
                	?>
        	</td>
        </tr>
        
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
