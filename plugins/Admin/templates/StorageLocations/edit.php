<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Martin Hatlauf <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('" . __d('admin', 'Website_administration') . "', '" . __d('admin', 'Configurations') . "');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
                    class="fa-fw fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a
                href="javascript:void(0);"
                class="btn btn-outline-light cancel"><i
                    class="fa-fw fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
        <?php echo $this->element('printIcon'); ?>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->element('navTabs/configurationNavTabs', [
    'key' => 'storage_locations',
]);

echo $this->Form->create($storageLocation, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Slug->getStorageLocationEdit($storageLocation->id) : $this->Slug->getStorageLocationAdd(),
    'id' => 'storageLocationEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo $this->Form->control('StorageLocations.name', [
    'label' => __d('admin', 'Name'),
]);
echo $this->Form->control('StorageLocations.position', [
    'class' => 'short',
    'label' => __d('admin', 'Rank'),
    'type' => 'text',
]);

if ($this->request->getRequestTarget() != $this->Slug->getStorageLocationAdd()) {
    echo '<div class="warning">';
    echo $this->Form->control('StorageLocations.delete_storage_location', [
        'label' => __d('admin', 'Delete storage location?') .
            '<span class="after small">' .
            ($productCount > 0 ?
                __d('admin', 'Deleting is not possible. There are {0} products associated with this storage location.', $productCount) :
                __d('admin', 'Check_and_do_not_forget_to_click_save_button.')
            ).
            '</span>',
        'disabled' => ($productCount > 0 ? 'disabled' : ''),
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '</div>';
}

echo $this->Form->end();

?>

<div class="sc"></div>