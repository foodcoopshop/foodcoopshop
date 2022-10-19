<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
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

                <p><b><?php echo __('Your_email_address_has_been_activated_successfully.'); ?></b></p>

                <?php
                    if (Configure::read('appDb.FCS_REGISTRATION_EMAIL_TEXT') != '') {
                        echo Configure::read('appDb.FCS_REGISTRATION_EMAIL_TEXT');
                    }
                ?>

                <?php echo $this->element('email/profileLinks', ['data' => $data, 'newPassword' => $newPassword]); ?>

            </td>

        </tr>

    </tbody>
<?php echo $this->element('email/tableFoot'); ?>