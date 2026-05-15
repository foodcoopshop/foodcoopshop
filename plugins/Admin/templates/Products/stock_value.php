<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use App\Services\ProductQuantityService;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();".
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__('Website_administration')."', '".__('Financial_reports')."');"
]);
?>

<?php
$productQuantityService = new ProductQuantityService();
$activeStates = array_map(
    fn(string $activeState): string => __('Products') . ': ' . $activeState,
    $this->MyHtml->getActiveStates(),
);
?>

<div class="filter-container">
    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1><?php echo $title_for_layout; ?></h1>
        <?php
        echo $this->Form->control('manufacturerId', [
            'type' => 'select',
            'label' => '',
            'options' => $manufacturersForDropdown,
            'default' => $manufacturerId,
        ]);
        echo $this->Form->control('active', [
            'type' => 'select',
            'label' => '',
            'options' => $activeStates,
            'default' => $active,
        ]);
        ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__('docs_route_products'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php

echo $this->element('navTabs/reportNavTabs', [
    'key' => 'products',
    'dateFrom' => '',
    'dateTo' => '',
]);

echo '<table class="list">';
echo '<tr class="sort">';
    echo '<th>' . __('Product') . '</th>';
    echo '<th>' . __('Manufacturer') . '</th>';
    echo '<th style="text-align:right;">' . __('Amount') . '</th>';
    echo '<th style="text-align:right;">' . $priceLabel . '</th>';
    echo '<th style="text-align:right;">' . __('Stock_value') . '</th>';
echo '</tr>';

foreach ($products as $product) {
    echo '<tr class="data ' . h($product->row_class) . '">';
        echo '<td>';
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt"></i>',
                $this->Slug->getProductAdmin($product->id_manufacturer_for_edit, $product->id_product_for_edit),
                [
                    'class' => 'btn btn-outline-light edit-shortcut-button',
                    'title' => __('Edit'),
                    'escape' => false,
                ],
            );
            echo '<span class="product-name">' . $product->name . '</span>';
        echo '</td>';
        echo '<td>' . h($product->manufacturer->name) . '</td>';
        $unitName = !empty($product->unit) ? $product->unit->name : '';
        $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($product, $product->unit);
        echo '<td style="text-align:right;">' . $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $product->stock_available->quantity, $unitName) . '</td>';
        echo '<td style="text-align:right;">' . $this->Number->formatAsCurrencyWithDecimals($product->price, 6) . '</td>';
        echo '<td style="text-align:right;">' . $this->Number->formatAsCurrency($product->stock_value) . '</td>';
    echo '</tr>';
}

echo '<tr style="font-weight:bold;">';
    echo '<td colspan="4" style="text-align:right;">' . __('Total_sum') . '</td>';
    echo '<td style="text-align:right;">' . $this->Number->formatAsCurrency($stockValueSum) . '</td>';
echo '</tr>';
echo '</table>';

?>