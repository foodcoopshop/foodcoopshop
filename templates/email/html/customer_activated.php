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

                <p><?php echo __('Your_account_at_{0}_has_just_been_activated.', [Configure::read('appDb.FCS_APP_NAME')])?></p>

                <?php echo $this->element('email/profileLinks', ['data' => $data, 'newPassword' => $newPassword]); ?>

            </td>

        </tr>

    </tbody>
<?php echo $this->element('email/tableFoot'); ?>