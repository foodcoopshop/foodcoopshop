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
    
        <tr>
            <td>
                
                <p><b><?php echo __('Dear_responsible_person_for_new_members,'); ?></b></p>
                
                <p><?php echo __('there_has_been_a_new_registration:_{0}_({1})_from_{2}.', ['<b>'.$data->firstname . ' ' . $data->lastname . '</b>', $data->email, '<b>'.$data->address_customer->city.'</b>']); ?></p>
                
                <?php $link = Configure::read('app.cakeServerName').'/admin/customers/index/active:'.(Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE') ? '1' : '0'); ?>
                
                <?php if (!Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) { ?>
                    <p><?php echo __('Here_you_can_activate_the_new_member_and_see_the_data'); ?>:
                <?php } else { ?>
                    <p><?php echo __('Here_you_can_see_the_data_of_the_new_member'); ?>:
                <?php } ?>
                    <br />
                    <a href="<?php echo $link ?>"><?php echo $link; ?></a>
                </p>
                
            </td>
            
        </tr>
        
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>