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

echo '<h2 style="margin-top:10px;">' . __d('admin', 'Deposit_overview_for_all_manufacturers') . '</h2>';

    if (!isset($xAxisData1LineChart)) {
        $deposit = __d('admin', 'Deposit');
        echo '<p>'.__d('admin', 'There_is_no_{0}_available.', [$deposit]) . '</p>';
        return;
    }

    echo '<table class="list no-clone-last-row" style="width:615px;margin-top:10px;">';
    echo '<tr>';
        echo '<th>' . __d('admin', 'Year') . '</th>';
        echo '<th>' . __d('admin', 'Delivered_deposit') . '</th>';
        echo '<th>' . __d('admin', 'Empty_glasses_returned') . '</th>';
        echo '<th>' . __d('admin', 'Compensation_payments') . '</th>';
        echo '<th>' . __d('admin', 'Open_deposit_demands') . '</th>';
    echo '</tr>';
    foreach($years as $year) {
        echo '<tr>';
            echo '<td style="width:50px;">' . $year . '</td>';
            $depositDelivered = $yearlyDepositsDelivered[$year] ?? 0;
            echo '<td class="'.($depositDelivered < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($depositDelivered) . '</td>';
            echo '<td class="'.($yearlyManufacturerEmptyGlasses[$year] < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($yearlyManufacturerEmptyGlasses[$year]) . '</td>';
            echo '<td class="'.($yearlyManufacturerMoney[$year] < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($yearlyManufacturerMoney[$year]) . '</td>';
            echo '<td class="'.($yearlyOverallDeltas[$year] < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($yearlyOverallDeltas[$year]) . '</td>';
        echo '</tr>';
    }
    echo '<tr>';
        echo '<td><b>' . __d('admin', 'Sum') . '</b></td>';
        echo '<td><b class="'.($depositsDeliveredSum < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($depositsDeliveredSum) . '</b></td>';
        echo '<td><b class="'.($manufacturerEmptyGlassesSum < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($manufacturerEmptyGlassesSum) . '</b></td>';
        echo '<td><b class="'.($manufacturerMoneySum < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($manufacturerMoneySum) . '</b></td>';
        echo '<td><b class="'.($overallDeltaSum < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($overallDeltaSum) . '</b></td>';
    echo '</tr>';
    echo '</table>';

echo '<br />'.__d('admin', 'Reserved_for_compensation_payments').': <b class="'.($paymentDepositDelta < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($paymentDepositDelta) . '</b>';
echo '<br />'.__d('admin', 'Difference_to_open_deposit_demands').': <b class="'.($differenceToOpenDepositDemands < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($differenceToOpenDepositDemands) . '</b>';


echo '<h2 style="margin-top:30px;">' . __d('admin', 'Deposit_diagram:_Data_per_week') . '</h2>';
echo '<p>'.__d('admin', 'Deposit_diagram_explanation_text.') . '</p>';
$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".AppChart.initLineChartDepositOverview(
        ".json_encode($xAxisData1LineChart).",
        ".json_encode($xAxisData2LineChart).",
        ".json_encode($xAxisData3LineChart).",
        ".json_encode($yAxisDataLineChart).",
        '".__d('admin', 'Empty_glasses_returned_by_manufacturer') . "',
        '".__d('admin', 'Returned_deposit_by_member') . "',
        '".__d('admin', 'Compensation_payments') . "'
    );"
]);
?>

<canvas id="myLineChart" width="1000" height="500" style="margin-top:30px;"></canvas>
