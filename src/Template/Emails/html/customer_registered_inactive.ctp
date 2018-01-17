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
    
        <?php echo $this->element('email/greeting', array('data' => $data)); ?>
    
        <tr>
            <td>
                
                <p>Deine Registrierung bei "<?php echo Configure::read('AppConfig.db_config_FCS_APP_NAME'); ?>" war erfolgreich!</p>
                
                <p>
                    <b>Dein Mitgliedskonto ist zwar erstellt, aber noch nicht aktiviert. Das heißt, du kannst dich noch nicht einloggen!</b>
                </p>
                
                <p>
                    Du wirst per E-Mail benachrichtigt, sobald wir dich aktiviert haben.
                </p>
                
                <?php
                if (Configure::read('AppConfig.db_config_FCS_REGISTRATION_EMAIL_TEXT') != '') {
                    echo Configure::read('AppConfig.db_config_FCS_REGISTRATION_EMAIL_TEXT');
                }
                ?>
                
            </td>
            
        </tr>
        
    </tbody>
</table>