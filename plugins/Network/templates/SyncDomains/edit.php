<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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
            class="fas fa-check"></i> <?php echo __('Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fas fa-times"></i> <?php echo __('Cancel'); ?></a>
    </div>
</div>

<div class="sc"></div>

<?php if ($this->request->getRequestTarget() != $this->Network->getSyncDomainAdd()) { ?>
    <h2 class="warning"><?php echo __('Caution!_Editing_a_remote_foodcoop_can_result_in_problems_if_manufacturers_already_associated_products!'); ?></h2>
<?php } ?>

<?php

echo $this->Form->create($syncDomain, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Network->getSyncDomainEdit($syncDomain->id) : $this->Network->getSyncDomainAdd()
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo $this->Form->control('SyncDomains.domain', [
    'label' => __('Remote_foodcoop') . ' <span class="after small">'.__('Domain_of_the_foodcoop_needs_to_start_with_https').'</span>',
    'required' => true,
    'escape' => false
]);

echo $this->Form->control('SyncDomains.active', [
    'label' => 'Aktiv?',
    'type' => 'checkbox'
]);
echo '<div class="sc"></div>';

if ($this->request->getRequestTarget() != $this->Network->getSyncDomainAdd()) {
    echo $this->Form->hidden('SyncDomains.delete_sync_domain', [
        'value' => 0,
        'id' => 'sync-domains-delete-sync-domain',
        'class' => 'js-delete-entity-input',
        'data-delete-label' => __('Delete'),
        'data-delete-confirm' => __('Do you really want to delete this remote foodcoop?'),
    ]);
    $this->Form->unlockField('SyncDomains.delete_sync_domain');
}

echo $this->Form->end();

?>

<div class="sc"></div>
