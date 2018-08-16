<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
    
        <tr>
            <td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
                <?php echo __('Hello'); ?> <?php echo $manufacturer->address_manufacturer->firstname; ?>,
            </td>
        </tr>

        <tr>
        	<td>
        		<p>
                	<?php echo __('Product_{0}_is_almost_sold_out.', ['<b>' . $cartProduct->product->name . '</b>']); ?>
               	</p>
               	<p> 
                	<?php echo __('Units_on_stock:') . ' <b>' . $this->MyNumber->formatAsDecimal($stockAvailable->quantity, 0) . '</b>'; ?><br />
                	<?php echo __('Product_orders_possible_until:') . ' <b>' . $this->MyNumber->formatAsDecimal($stockAvailable->quantity_limit, 0) . '</b>'; ?><br />
                	<?php echo __('Notification_triggered_if_less_than:') . ' <b>' . $this->MyNumber->formatAsDecimal($stockAvailable->sold_out_limit, 0) . '</b>'; ?>
                </p>
                <p>
                	<a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getProductAdmin(null, $cartProduct->product->id_product); ?>"><?php echo __('Click_here_to_edit_the_product.'); ?></a>
                </p>
        	</td>
        </tr>
        
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
