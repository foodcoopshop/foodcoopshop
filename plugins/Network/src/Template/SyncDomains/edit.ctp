<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('Homepage-Verwaltung', 'Einstellungen');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> <?php echo __d('network', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa fa-close"></i> <?php echo __d('network', 'Cancel'); ?></a>
    </div>
</div>

<div class="sc"></div>

<?php if ($this->request->getRequestTarget() != $this->Network->getSyncDomainAdd()) { ?>
    <h2 class="warning"><?php echo __d('network', 'Caution!_Editing_a_remote_foodcoop_can_result_in_problems_if_manufacturers_already_associated_products!'); ?></h2>
<?php } ?>

<?php

echo $this->Form->create($syncDomain, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Network->getSyncDomainEdit($syncDomain->id) : $this->Network->getSyncDomainAdd()
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo $this->Form->control('SyncDomains.domain', [
    'label' => __d('network', 'Remote_foodcoop') . ' <span class="after small">'.__d('network', 'Domain_of_the_foodcoop_needs_to_start_with_https').'</span>',
    'required' => true,
    'escape' => false
]);

echo $this->Form->control('SyncDomains.active', [
    'label' => 'Aktiv?',
    'type' => 'checkbox'
]);

if ($this->request->getRequestTarget() != $this->Network->getSyncDomainAdd()) {
    echo $this->Form->control('SyncDomains.delete_sync_domain', [
        'label' => __d('network', 'Delete_remote_foodcoop') . ' <span class="after small">'.__d('network', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
}

echo $this->Form->end();

?>

<div class="sc"></div>
