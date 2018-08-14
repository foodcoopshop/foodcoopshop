<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>
    
        <?php echo $this->element('email/greeting', ['data' => $data]); ?>
        
        <tr>
        <td>

            <p>
            	<?php
            	   echo __d('admin', 'The_status_of_your_credit_upload_of_{0}_({1}_amount)_was_changed_to_{2}.', [
            	       '<b>'.$payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')).'</b>',
            	       '<b>'.Configure::read('app.numberHelper')->formatAsCurrency($payment->amount).'</b>',
            	       $newStatusAsString
            	   ]);
            	?>
                
                <?php if ($payment->approval == -1) { ?>
                	<?php echo __d('admin', 'Please_compare_the_credit_upload_that_you_added_to_our_system_with_the_actual_bank_account_transfer.'); ?>
                <?php } ?>
                
            </p>
            
            <?php
            if ($payment->approval_comment != '') {
                echo '<p>'.__d('admin', 'Comment').':<br />';
                echo '<b>"'.$payment->approval_comment . '</b>"';
                echo '</p>';
            }
            ?>
            
            <p>
                <?php echo __d('admin', 'Here_you_find_the_link_to_add_the_credit:'); ?><br />
                <a href="<?php echo Configure::read('app.cakeServerName').'/admin/payments/product'; ?>"><?php echo Configure::read('app.cakeServerName').'/admin/payments/product'; ?></a>
            </p>
            
        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
