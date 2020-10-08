<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
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
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Financial_reports')."');"
]);
?>

<div class="filter-container">
    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1><?php echo $title_for_layout; ?></h1>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_infos_for_success'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php

echo $this->element('reportNavTabs', [
    'key' => 'deposit_overview',
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
]);

if (!isset($xAxisData1LineChart)) {
    return;
}

$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".AppChart.initLineChartDepositOverview(
        ".json_encode($xAxisData1LineChart).",
        ".json_encode($xAxisData2LineChart).",
        ".json_encode($yAxisDataLineChart).",
        '".__d('admin', 'Taken_back_empty_glasses_of_all_manufacturers') . " " . __d('admin', 'per_week') . "',
        '".__d('admin', 'Returned_deposit_of_all_members') . " " . __d('admin', 'per_week') . "'
    );"
]);
?>

<canvas id="myLineChart" width="1000" height="500" style="margin-top:30px;"></canvas>

<p style="margin-top:30px;">
    <?php
        echo __d('admin', 'Returned_deposit_of_all_members').': <b>' . $this->Number->formatAsCurrency($customerDepositSum) . '</b><br />';
        echo __d('admin', 'Taken_back_empty_glasses_of_all_manufacturers').': <b>' . $this->Number->formatAsCurrency($manufacturerDepositSum) . '</b><br />';
        echo __d('admin', 'Difference').': <b class="'.($depositDelta < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($depositDelta) . '</b><br />';
        echo '<br /><b>'.__d('admin', 'Difference_per_year').'</b><br />';
        foreach($yearlyDeltas as $year => $yearlyDelta) {
            echo $year . ': <b class="'.($yearlyDelta < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($yearlyDelta) . '</b><br />';
        }
        echo '<br />'.__d('admin', 'Reserved_for_compensation_payments').': <b class="'.($paymentDepositDelta < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($paymentDepositDelta) . '</b><br />';
    ?>
</p>