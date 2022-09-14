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

$this->element('addScript', [ 'script' =>
    Configure::read('app.jsNamespace') . ".Admin.init();" .
    Configure::read('app.jsNamespace') . ".Admin.initForm();".
    Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Configurations')."');
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
        <?php echo $this->element('printIcon'); ?>
    </div>
</div>

<?php
    echo $this->element('navTabs/configurationNavTabs', [
        'key' => 'tax_rates',
    ]);
?>
<div class="sc"></div>

<?php

echo $this->Form->create($tax, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Slug->getTaxEdit($tax->id_tax) : $this->Slug->getTaxAdd(),
    'id' => 'taxEditForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

if ($this->request->getRequestTarget() != $this->Slug->getTaxAdd()) {
    echo '<label>'.__d('admin', 'Tax_rate').'<br /><span class="small">'.__d('admin', 'Tax_rates_can_not_be_changed.').'</span></label><p>' . $this->Number->formatAsPercent($tax->rate) . '</p>';
} else {
    echo $this->Form->control('Taxes.rate', [
        'class' => 'long',
        'label' => __d('admin', 'Tax_rate') . '<br /><span class="small">'.__d('admin', 'e.g._10_for_10%').'<br />'.__d('admin', 'Tax_rates_can_not_be_changed_later.').'</span>',
        'escape' => false
    ]);
}

echo $this->Form->control('Taxes.active', [
    'label' => __d('admin', 'Active').'?',
    'type' => 'checkbox',
]);

echo $this->Form->end();

?>
