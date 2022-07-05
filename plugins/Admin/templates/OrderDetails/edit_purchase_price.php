<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Financial_reports')."');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();"
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($orderDetail, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate'
]);
echo $this->Form->hidden('referer', ['value' => $referer]);

echo '<p><label>'.__d('admin', 'Member').'</label>' . $this->Html->getNameRespectingIsDeleted($orderDetail->customer) . '</p>';
echo '<p><label>'.__d('admin', 'Amount').'</label>' . $this->Number->formatAsDecimal($orderDetail->product_amount, 0) . 'x</p>';
echo '<p><label>'.__d('admin', 'Product').'</label>' . $orderDetail->product_name.'</p>';
echo '<p><label>'.__d('admin', 'Weight').'</label>';
    if (!empty($orderDetail->order_detail_unit)) {
        echo $this->Number->formatUnitAsDecimal($orderDetail->order_detail_unit->product_quantity_in_units) . ' ' . $orderDetail->order_detail_unit->unit_name;
    }
echo '</p>';
echo '<p><label>'.__d('admin', 'Pickup_day').'</label>' . $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) .'</p>';

echo $this->Form->control('OrderDetails.order_detail_purchase_price.tax_rate', [
    'label' => __d('admin', 'Tax_rate'),
    'type' => 'select',
    'options' => $taxesForDropdown,
]);

echo $this->Form->control('OrderDetails.order_detail_purchase_price.total_price_tax_excl', [
    'label' => __d('admin', 'Purchase_price_tax_excl'),
    'step' => '0.01',
    'min' => '0.01',
]);

echo $this->Form->end();

?>
