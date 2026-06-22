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

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();".
    Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__('Manufacturers_admin')."', '".__('Stock products')."');" .
    Configure::read('app.jsNamespace') . ".Admin.initProductQuantityList('#stock-products');" .
    Configure::read('app.jsNamespace') . ".ModalProductQuantityEdit.init();"
]);
?>

<?php
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

echo '<div id="stock-products">';
echo '<table class="list">';
echo '<tr class="sort">';
    echo '<th class="stretch">' . __('Product') . '</th>';
    echo '<th class="stretch">' . __('Manufacturer') . '</th>';
    echo '<th>' . __('Amount') . '</th>';
    echo '<th>' . $priceLabel . '</th>';
    echo '<th>' . __('Stock_value') . '</th>';
echo '</tr>';

foreach ($products as $product) {
    echo '<tr class="data ' . h($product->row_class) . '">';
        echo '<td class="hide cell-id">' . h($product->id_product) . '</td>';
        echo '<td class="hide is-stock-product"><i class="fas fa-check ok no-button"></i></td>';
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
            echo '<span class="product-name name-for-dialog">' . $product->name . '</span>';
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
        echo $this->element('productList/data/amount', [
            'product' => $product,
            'alignRight' => true,
        ]);
        echo '<td class="right">' . $this->Number->formatAsDecimal($product->price, 6, true, 2) . ' ' . Configure::read('appDb.FCS_CURRENCY_SYMBOL') . '</td>';
        echo '<td class="right">' . $this->Number->formatAsCurrency($product->stock_value) . '</td>';
    echo '</tr>';
}

echo '<tr>';
    $count = count($products);
    echo '<td colspan="2"><b>' . $count . '</b> '.__('{0,plural,=1{record} other{records}}', $count).'</td>';
    echo '<td colspan="2"><b>' . __('Sum') . '</b></td>';
    echo '<td><b>' . $this->Number->formatAsCurrency($stockValueSum) . '</b></td>';
echo '</tr>';
echo '</table>';
echo '</div>';

?>