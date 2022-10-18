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

        <?php echo $this->element('email/greeting', ['data' => $customer]); ?>

        <tr>
            <td>

                <p>
                    <b>
                        <?php echo __('Please_click_on_this_link_to_activate_your_new_password'); ?>:<br />
                        <a href="<?php echo Configure::read('App.fullBaseUrl').$this->Slug->getActivateNewPassword($activateNewPasswordCode); ?>"><?php echo Configure::read('App.fullBaseUrl').$this->Slug->getActivateNewPassword($activateNewPasswordCode); ?></a>
                    </b>
                </p>

                <p>
                    <?php echo __('your_new_password_is:'); ?><br />
                    <b><?php echo $tmpNewPassword; ?></b>
                </p>

                <p>
                    <?php echo __('Please_be_careful_that_there_is_no_empty_space_at_the_end_of_the_password_when_you_double_click_and_copy_it.'); ?>
                </p>

            </td>

        </tr>

    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
