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
<p>
    Zum Bestellen kannst du dich hier einloggen:<br />
    <a href="<?php echo Configure::read('AppConfig.cakeServerName').$this->Slug->getLogin(); ?>"><?php echo Configure::read('AppConfig.cakeServerName').$this->Slug->getLogin(); ?></a><br /><br />
    E-Mail-Adresse: <?php echo $data['Customers']['email']; ?><br />
    Passwort: <?php echo $newPassword; ?>
</p>

<p>
    Hier kannst du dein Passwort ändern:<br />
    <a href="<?php echo Configure::read('AppConfig.cakeServerName').$this->Slug->getChangePassword(); ?>"><?php echo Configure::read('AppConfig.cakeServerName').$this->Slug->getChangePassword(); ?></a>
</p>

<p>
    Hier kannst du dein Profil ändern:<br />
    <a href="<?php echo Configure::read('AppConfig.cakeServerName').$this->Slug->getCustomerProfile(); ?>"><?php echo Configure::read('AppConfig.cakeServerName').$this->Slug->getCustomerProfile(); ?></a>
</p>
