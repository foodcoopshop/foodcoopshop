<?php
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
<p>
    <?php echo __('You_can_sign_in_here_for_ordering'); ?>:<br />
    <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getLogin(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getLogin(); ?></a><br /><br />
    <?php echo __('E-mail_address'); ?>: <?php echo $data->address_customer->email; ?><br />
    <?php echo __('Password'); ?>: <?php echo $newPassword; ?>
</p>

<p>
    <?php echo __('Please_be_careful_that_there_is_no_empty_space_at_the_end_of_the_password_when_you_double_click_and_copy_it.'); ?>
</p>

<p style="font-size:12px;padding-top:15px;">
    <?php echo __('You_can_change_your_password_here'); ?>:<br />
    <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getChangePassword(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getChangePassword(); ?></a>
</p>

<p style="font-size:12px;">
    <?php echo __('You_can_change_your_profile_here'); ?>:<br />
    <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getCustomerProfile(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getCustomerProfile(); ?></a>
</p>
