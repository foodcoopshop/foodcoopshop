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

$this->element('addScript', array(
    'script' => Configure::read('AppConfig.jsNamespace') . ".Admin.init();" . Configure::read('AppConfig.jsNamespace') . ".Admin.initForm();
    "
));
?>

<div class="filter-container">
    <h1>Passwort ändern</h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du dein Passwort ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('Customer', array(
    'class' => 'fcs-form'
));

echo $this->Form->input('Customer.passwd', array(
    'label' => __('Password : Old Password *')
));
echo $this->Form->input('Customer.passwd_new_1', array(
    'label' => __('Password : New Password *'),
    'type' => 'password'
));
echo $this->Form->input('Customer.passwd_new_2', array(
    'label' => __('Password : New Password Repeat *'),
    'type' => 'password'
));

?>

<div class="sc"></div>

</form>

<div class="sc"></div>
