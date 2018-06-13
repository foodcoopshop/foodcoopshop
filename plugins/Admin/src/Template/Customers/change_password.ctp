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

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);
?>

<div class="filter-container">
    <h1>Passwort ändern</h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a>
        <?php echo $this->element('printIcon'); ?>
    </div>
</div>


<div class="sc"></div>

<?php

echo $this->Form->create($customer, [
    'class' => 'fcs-form'
]);

echo $this->Form->control('Customers.passwd_old', [
    'label' => 'Altes Passwort *',
    'type' => 'password',
]);
echo $this->Form->control('Customers.passwd_1', [
    'label' => 'Neues Passwort *<br /><span class="small">mindestens 8 Zeichen</span>',
    'type' => 'password',
    'escape' => false,
]);
echo $this->Form->control('Customers.passwd_2', [
    'label' => 'Neues Passwort (nochmal) * <br /><span class="small">mindestens 8 Zeichen</span>',
    'type' => 'password',
    'escape' => false,
]);

echo $this->Form->end();

?>

<div class="sc"></div>

