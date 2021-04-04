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

        <?php echo $this->element('email/greeting', ['data' => $data]); ?>

        <tr>
            <td>

                <p><?php echo __('Your_registration_at_{0}_has_just_been_successful!', [Configure::read('appDb.FCS_APP_NAME')])?></p>

                <p><b>
                    <?php echo __('Please_confirm_your_email_address'); ?>:<br />
                    <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getActivateEmailAddress($data->activate_email_code); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getActivateEmailAddress($data->activate_email_code); ?></a>
                </b></p>

            </td>

        </tr>

    </tbody>
<?php echo $this->element('email/tableFoot'); ?>