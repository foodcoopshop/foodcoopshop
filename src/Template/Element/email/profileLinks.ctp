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
<p>
    <?php echo __('You_can_sign_in_here_for_ordering'); ?>:<br />
    <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getLogin(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getLogin(); ?></a><br /><br />
    <?php echo __('E-mail_address'); ?>: <?php echo $data->address_customer->email; ?><br />
    <?php echo __('Password'); ?>: <?php echo $newPassword; ?>
</p>

<p>
    <?php echo __('You_can_change_your_password_here'); ?>:<br />
    <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getChangePassword(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getChangePassword(); ?></a>
</p>

<p>
    <?php echo __('You_can_change_your_profile_here'); ?>:<br />
    <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getCustomerProfile(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getCustomerProfile(); ?></a>
</p>
