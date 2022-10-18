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

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a>
        <?php echo $this->element('printIcon'); ?>
    </div>
</div>


<div class="sc"></div>

<?php

echo $this->Form->create($customer, [
    'class' => 'fcs-form'
]);

echo $this->Form->control('Customers.passwd_old', [
    'label' => __d('admin', 'Old_password'),
    'required' => true,
    'type' => 'password',
]);
echo $this->Form->control('Customers.passwd_1', [
    'label' => __d('admin', 'New_password') . '<br /><span class="small">'.__d('admin', 'min_8_characters').'</span>',
    'required' => true,
    'type' => 'password',
    'escape' => false,
]);
echo $this->Form->control('Customers.passwd_2', [
    'label' => __d('admin', 'New_password_again') . '<br /><span class="small">'.__d('admin', 'min_8_characters').'</span>',
    'required' => true,
    'type' => 'password',
    'escape' => false,
]);

echo $this->Form->end();

?>

<div class="sc"></div>

