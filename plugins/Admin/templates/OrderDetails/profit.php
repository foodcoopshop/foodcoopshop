<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
        $('input.datepicker').datepicker();" .
        Configure::read('app.jsNamespace') . ".Admin.init();".
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Financial_reports')."');".
        Configure::read('app.jsNamespace') . ".Admin.initCustomerDropdown(" . ($customerId != '' ? $customerId : '0') . ", 0, 1);".
        Configure::read('app.jsNamespace') . ".Admin.initCopyTableContentToClipboard();"
]);
?>

<div class="filter-container">
    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1><?php echo $title_for_layout; ?></h1>
        <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameTo' => 'dateTo', 'nameFrom' => 'dateFrom']); ?>
        <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'empty' => __d('admin', 'all_members'), 'options' => []]); ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_infos_for_success'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php

echo $this->element('reportNavTabs', [
    'key' => 'profit',
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
]);



echo '<h2 style="margin-top:10px;">' . __d('admin', 'Profit') . '</h2>';

echo '<table class="list profit-table">';

    echo '<tr class="sort">';
        echo '<th>' . $this->Paginator->sort('OrderDetails.pickup_day', __d('admin', 'Pickup_day')) . '</th>';
        echo '<th>' . $this->Paginator->sort('OrderDetails.product_name', __d('admin', 'Product')) . '</th>';
        echo '<th>' . __d('admin', 'Manufacturer') . '</th>';
        echo '<th>' . $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Name')) . '</th>';
        echo '<th>' . $this->Paginator->sort('OrderDetails.total_price_tax_excl', __d('admin', 'Selling_price')) . '</th>';
        echo '<th>' . $this->Paginator->sort('OrderDetailsPurchasePrices.order_detail_purchase_price', __d('admin', 'Purchase_price')) . '</th>';
        echo '<th>' . __d('admin', 'Profit') . '</th>';
    echo '</tr>';

    foreach($orderDetails as $orderDetail) {

        echo '<tr class="data" data-invoice-id="'.$orderDetail->id_order_detail.'">';

            echo '<td>';
                echo $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
            echo '</td>';

            echo '<td>';
                echo $orderDetail->product_name;
            echo '</td>';

            echo '<td>';
                echo $orderDetail->product->manufacturer->name;
            echo '</td>';

            echo '<td>';
                echo $this->Html->getNameRespectingIsDeleted($orderDetail->customer);
            echo '</td>';

            echo '<td style="text-align:right;">';
                echo $this->Number->formatAsDecimal($orderDetail->total_price_tax_excl);
            echo '</td>';

            echo '<td style="text-align:right;">';
                if (!empty($orderDetail->order_detail_purchase_price)) {
                    echo $this->Number->formatAsDecimal($orderDetail->order_detail_purchase_price->total_price_tax_excl);
                }
            echo '</td>';

            echo '<td style="text-align:right;">';
                if (!empty($orderDetail->order_detail_purchase_price)) {
                    $profit = $orderDetail->total_price_tax_excl - $orderDetail->order_detail_purchase_price->total_price_tax_excl;
                    echo $this->Number->formatAsDecimal($profit);
                }
            echo '</td>';

        echo '</tr>';

    }

    echo '<tr style="font-weight:bold;">';

        echo '<td colspan="4" style="text-align:right;">';
            echo __d('admin', 'Total_sum');
        echo '</td>';

        echo '<td style="text-align:right;">';
            echo $this->Number->formatAsDecimal($sums['sellingPrice']);
        echo '</td>';

        echo '<td style="text-align:right;">';
            echo $this->Number->formatAsDecimal($sums['purchasePrice']);
        echo '</td>';

        echo '<td style="text-align:right;">';
            echo $this->Number->formatAsDecimal($sums['profit']);
        echo '</td>';

    echo '</tr>';


echo '</table>';

?>
