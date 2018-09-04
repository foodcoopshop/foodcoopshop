<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
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

            <p><?php echo __d('admin', '{0}_is_the_last_day_to_order._You_can_do_that_until_{0}_midnight.', [$lastOrderDayAsString]); ?></p>

            <p><?php echo __d('admin', 'Do_you_want_to_load_your_last_order_into_your_shopping_cart?_The_current_cart_will_be_emptied_for_that.'); ?><br />
                 <a href="<?php echo Configure::read('app.cakeServerName'); ?>/<?php echo __d('admin', 'route_cart'); ?>/addLastOrderToCart"><?php echo Configure::read('app.cakeServerName'); ?>/<?php echo __d('admin', 'route_cart'); ?>/addLastOrderToCart</a>
            </p>

            <p>
            	<?php echo __d('admin', 'Click_here_to_open_the') . ' ' . __d('admin', 'Website'); ?>:<br /> <a href="<?php echo Configure::read('app.cakeServerName'); ?>"><?php echo Configure::read('app.cakeServerName'); ?></a>
            </p>

            <p>
            	<?php echo __d('admin', 'And_here_you_can_unsubscribe_this_email_reminder'); ?>: <br /> <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getCustomerProfile(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getCustomerProfile(); ?></a>
            </p>

        </td>
    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
