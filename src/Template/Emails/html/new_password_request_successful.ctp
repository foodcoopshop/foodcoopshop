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
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
    
        <?php echo $this->element('email/greeting', ['data' => $customer]); ?>
        
        <tr>
            <td>
                
                <p>
                    bitte klicke auf folgenden Link, um dein neues Passwort zu generieren:<br />
                </p>
                
                <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getApproveNewPassword($changePasswordCode); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getApproveNewPassword($changePasswordCode); ?></a>
                
            </td>
            
        </tr>
        
    </tbody>
</table>
