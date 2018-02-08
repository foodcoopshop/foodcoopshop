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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
    
        <tr>
            <td>
                
                <p><b>Lieber Verantwortliche für neue Mitglieder,</b></p>
                
                <p>Es gab gerade eine neue Registrierung: <b><?php echo $data->firstname; ?> <?php echo $data->lastname; ?></b> (<?php echo $data['Customers']['email']; ?>) aus <b><?php echo $data->address_customer->city; ?></b></p>
                
                <?php $link = Configure::read('app.cakeServerName').'/admin/customers/index/active:'.(Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE') ? '1' : '0'); ?>
                
                <?php if (!Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) { ?>
                    <p>Hier kannst du das neue Mitglied aktivieren und die Daten einsehen:
                <?php } else { ?>
                    <p>Hier kannst du die Daten des neuen Mitglieds einsehen:
                <?php } ?>
                    <br />
                    <a href="<?php echo $link ?>"><?php echo $link; ?></a>
                </p>
                
            </td>
            
        </tr>
        
    </tbody>
</table>