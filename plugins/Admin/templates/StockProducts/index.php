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
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__('Manufacturers_admin')."', '".__('Stock products')."');"
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
        if (!$identity->isManufacturer()) {
            echo $this->Form->control('manufacturerId', [
                'type' => 'select',
                'label' => '',
                'options' => $manufacturersForDropdown,
                'default' => $manufacturerId,
            ]);
        }
        echo $this->Form->control('active', [
            'type' => 'select',
            'label' => '',
            'options' => $activeStates,
            'default' => $active,
        ]);
        echo $this->Form->control('stockFilter', [
            'type' => 'select',
            'label' => '',
            'options' => $stockFiltersForDropdown,
            'default' => $stockFilter,
        ]);
        ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__('docs_route_products'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php

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
        echo '<td>';
            if ($identity->isManufacturer()) {
                echo h($product->manufacturer->name);
            } else {
                echo $this->Html->link(
                    $product->manufacturer->name,
                    $this->Slug->getStockProducts() . '?' . http_build_query([
                        'manufacturerId' => $product->id_manufacturer_for_edit,
                        'active' => $active,
                        'stockFilter' => $stockFilter,
                    ]),
                );
            }
        echo '</td>';
        $unitName = !empty($product->unit) ? $product->unit->name : '';
        $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($product, $product->unit);
        $amountClasses = [];
        if ($product->stock_available->quantity < 0) {
            $amountClasses[] = 'negative-stock';
        }
        if ($product->stock_available->quantity == 0) {
            $amountClasses[] = 'not-available';
        }
        if ($product->stock_available->quantity > 0 && $product->stock_available->sold_out_limit > 0 && $product->stock_available->quantity < $product->stock_available->sold_out_limit) {
            $amountClasses[] = 'below-minimum-amount';
        }
        echo '<td class="' . join(' ', $amountClasses) . '" style="text-align:right;">' . $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $product->stock_available->quantity, $unitName) . '</td>';
        echo '<td style="text-align:right;">' . $this->Number->formatAsDecimal($product->price, 6, true, 2) . ' ' . Configure::read('appDb.FCS_CURRENCY_SYMBOL') . '</td>';
        echo '<td style="text-align:right;">' . $this->Number->formatAsCurrency($product->stock_value) . '</td>';
    echo '</tr>';
}

echo '<tr style="font-weight:bold;">';
    echo '<td colspan="4" style="text-align:right;">' . __('Total_sum') . '</td>';
    echo '<td style="text-align:right;">' . $this->Number->formatAsCurrency($stockValueSum) . '</td>';
echo '</tr>';
echo '</table>';

?>