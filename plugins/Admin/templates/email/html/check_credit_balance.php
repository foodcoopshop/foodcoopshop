<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
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

            <p><?php echo __d('admin', 'your_credit_is_used_up_and_equals_{0}.', ['<b style="color: #f3515c;">'.$delta.'</b>']); ?></p>
            
            <p><?php echo __d('admin', 'Please_soon_transfer_new_credit_to_our_bank_account.'); ?></p>

            <p><?php echo __d('admin', 'Do_not_forget_to_add_it_to_our_credit_system_after_the_bank_transfer.'); ?></p>

            <p><?php echo __d('admin', 'Here_you_find_the_link_to_add_the_credit:'); ?><br />
                <a href="<?php echo Configure::read('app.cakeServerName').'/admin/payments/product'; ?>"><?php echo Configure::read('app.cakeServerName').'/admin/payments/product'; ?></a>
            </p>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>