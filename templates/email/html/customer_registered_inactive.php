<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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

                <p>
                    <b><?php echo __('Your_accout_was_created_but_not_activated_which_means_you_cannot_login_yet!'); ?></b>
                </p>

                <p>
                    <?php echo __('You_will_get_an_email_as_soon_as_we_activated_you.')?>
                </p>

                <?php
                if (Configure::read('appDb.FCS_REGISTRATION_EMAIL_TEXT') != '') {
                    echo Configure::read('appDb.FCS_REGISTRATION_EMAIL_TEXT');
                }
                ?>

            </td>

        </tr>

    </tbody>
<?php echo $this->element('email/tableFoot'); ?>