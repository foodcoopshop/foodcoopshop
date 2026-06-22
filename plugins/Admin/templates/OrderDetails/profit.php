<?php
declare(strict_types=1);

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
    'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
        $('input.datepicker').datepicker();" .
        Configure::read('app.jsNamespace') . ".Admin.init();".
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__('Website_administration')."', '".__('Financial_reports')."');".
        Configure::read('app.jsNamespace') . ".Admin.initCustomerMultiDropdown(" . json_encode($customerIds) . ", 0, 1);".
        Configure::read('app.jsNamespace') . ".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ", " . ($manufacturerId != '' ? $manufacturerId : '0') . ");".
        Configure::read('app.jsNamespace') . ".Admin.initCopyTableContentToClipboard();"
]);
?>

<div class="filter-container">
    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1><?php echo $title_for_layout; ?></h1>
        <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameTo' => 'dateTo', 'nameFrom' => 'dateFrom']); ?>
        <?php echo $this->Form->control('productId', ['type' => 'select', 'label' => '', 'placeholder' => __('all_products'), 'options' => []]); ?>
        <?php echo $this->Form->control('manufacturerId', ['type' => 'select', 'label' => '', 'empty' => __('all_manufacturers'), 'options' => $manufacturersForDropdown, 'default' => isset($manufacturerId) ? $manufacturerId: '']); ?>
        <?php echo $this->Form->control('customerIds', ['type' => 'select', 'multiple' => true, 'label' => '', 'placeholder' => __('all_members'), 'options' => []]); ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__('docs_route_infos_for_success'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php

echo $this->element('navTabs/reportNavTabs', [
    'key' => 'profit',
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
]);

$this->element('highlightRowAfterEdit', [
    'rowIdPrefix' => '#order-detail-'
]);

echo '<h2 style="margin-top:10px;">' . __('Net_profit') . '</h2>';

echo '<table class="list profit-table">';

    echo '<tr class="sort">';
        echo '<th>' . $this->Paginator->sort('OrderDetails.pickup_day', __('Pickup_day')) . '</th>';
        echo '<th>' . $this->Paginator->sort('OrderDetails.product_amount', __('Amount')) . '</th>';
        echo '<th>' . $this->Paginator->sort('OrderDetails.product_name', __('Product')) . '</th>';
        echo '<th>' . $this->Paginator->sort('OrderDetailUnits.product_quantity_in_units', __('Weight')) . '</th>';
        echo '<th>' . __('Manufacturer') . '</th>';
        echo '<th>' . $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __('Member')) . '</th>';
        echo '<th>' . $this->Paginator->sort('OrderDetails.total_price_tax_excl', __('Selling_price_admin')) . '</th>';
        echo '<th>' . $this->Paginator->sort('OrderDetailPurchasePrices.total_price_tax_excl', __('Purchase_price')) . '</th>';
        echo '<th>' . __('Profit') . '</th>';
    echo '</tr>';

    foreach($orderDetails as $orderDetail) {

        $rowClass = ['data'];
        if (!$orderDetail->purchase_price_ok) {
            $rowClass[] = 'deactivated';
            $rowClass[] = 'line-through';
        }
        echo '<tr class="' . join(' ', $rowClass) . '" id="order-detail-' . $orderDetail->id_order_detail . '">';

            echo '<td>';
                echo $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
            echo '</td>';

            echo '<td>';
                echo $this->Number->formatAsDecimal($orderDetail->product_amount, 0) . 'x';
            echo '</td>';

            echo '<td>';
                echo $this->Html->link(
                    '<i class="fas fa-pencil-alt"></i>',
                    $this->Slug->getProductAdmin($orderDetail->product->id_manufacturer, $orderDetail->product_id),
                    [
                        'class' => 'btn btn-outline-light edit-shortcut-button',
                        'title' => __('Edit'),
                        'escape' => false
                    ]
                );
                echo '<span class="product-name">';
                    echo $this->Html->link(
                        $orderDetail->product_name,
                        $this->Slug->getProfit($dateFrom, $dateTo, $orderDetail->id_customer, $manufacturerId, $orderDetail->product_id),
                    );
                echo '</span>';
            echo '</td>';

            echo '<td>';
                if (!empty($orderDetail->order_detail_unit)) {
                    echo $this->Number->formatUnitAsDecimal($orderDetail->order_detail_unit->product_quantity_in_units) . ' ' . $orderDetail->order_detail_unit->unit_name;
                }
            echo '</td>';

            echo '<td>';
                echo $this->Html->link(
                    $orderDetail->product->manufacturer->name,
                    $this->Slug->getProfit($dateFrom, $dateTo, $orderDetail->id_customer, $orderDetail->product->id_manufacturer, $productId),
                    [
                        'escape' => false
                    ]);
            echo '</td>';

            echo '<td>';
                echo $this->Html->link(
                    $this->Html->getNameRespectingIsDeleted($orderDetail->customer),
                    $this->Slug->getProfit($dateFrom, $dateTo, $orderDetail->id_customer, $manufacturerId, $productId),
                    [
                        'escape' => false
                    ]);
            echo '</td>';

            echo '<td>';
                echo $this->Number->formatAsDecimal($orderDetail->total_price_tax_excl);
            echo '</td>';

            echo '<td>';

                echo $this->Html->link(
                    '<i class="fas fa-pencil-alt ok"></i>',
                    $this->Slug->getOrderDetailPurchasePriceEdit($orderDetail->id_order_detail),
                    [
                        'class' => 'btn btn-outline-light',
                        'title' => __('Edit'),
                        'escape' => false,
                    ]
                );

                if (!empty($orderDetail->order_detail_purchase_price)) {
                    echo '<span class="purchase-price">';
                        echo $this->Number->formatAsDecimal($orderDetail->order_detail_purchase_price->total_price_tax_excl);
                    echo '</span>';
                }

            echo '</td>';

            echo '<td>';
                if (!empty($orderDetail->order_detail_purchase_price)) {
                    echo $this->Number->formatAsDecimal($orderDetail->profit);
                }
            echo '</td>';

        echo '</tr>';

    }

    echo '<tr style="font-weight:bold;">';

        echo '<td>';
        echo '</td>';

        echo '<td>';
            echo $this->Number->formatAsDecimal($sums['amount'], 0);
        echo '</td>';

        echo '<td colspan="4">';
            echo __('Total_sum');
        echo '</td>';

        echo '<td>';
            echo $this->Number->formatAsDecimal($sums['sellingPrice']);
        echo '</td>';

        echo '<td>';
            echo $this->Number->formatAsDecimal($sums['purchasePrice']);
        echo '</td>';

        echo '<td>';
            echo $this->Number->formatAsDecimal($sums['profit']) . '<br />' . $this->Number->formatAsPercent($sums['surcharge']);
        echo '</td>';

    echo '</tr>';


echo '</table>';

?>
