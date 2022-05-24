<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".Admin.init();"
]);
?>

<div class="filter-container">
    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1 style="width:100%;margin-bottom:5px;"><?php echo $title_for_layout; ?></h1>
        <?php
        if (!$appAuth->isManufacturer()) {
            echo $this->Form->control('manufacturerId', [
            'type' => 'select',
            'label' => '',
            'options' => $manufacturersForDropdown,
            'default' => $manufacturerId != '' ? $manufacturerId : ''
            ]);
        }

        echo $this->Form->control('year', [
            'type' => 'select',
            'label' => '',
            'empty' => __d('admin', 'Show_all_years'),
            'options' => $years,
            'default' => $year != '' ? $year : ''
        ]);

        ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_manufacturers'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php
if (empty($manufacturers)) {
    echo '<h2 class="info">'.__d('admin', 'Please_chose_a_manufacturer.').'</h2>';
    return;
}

if (empty($xAxisDataBarChart)) {
    echo '<h2 class="info">' . __d('admin', 'No_turnover_available.') . '</h2>';
    return;
}

$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".AppChart.setColor('" . Configure::read('app.customThemeMainColor') . "');" .
    Configure::read('app.jsNamespace') . ".AppChart.initBarChart(".
        json_encode($xAxisDataBarChart).", ".
        json_encode($yAxisDataBarChart).", ".
        json_encode($yAxisData2BarChart).", ".
        json_encode($yAxisData3BarChart).", ".
        "'" . (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') ? __d('admin', 'Net_purchase_price') : __d('admin', 'Gross_turnover')) . "', ".
        "'" . __d('admin', 'Net_profit') . "', ".
        "'" . __d('admin', 'Surcharge') . " %'".
    ");"
]);
if ($year == '' && count($xAxisDataLineChart) > 1) {
    $this->element('addScript', [
        'script' =>
        Configure::read('app.jsNamespace') . ".AppChart.initLineChart(".json_encode($xAxisDataLineChart).", ".json_encode($yAxisDataLineChart).");"
    ]);
}

if ($manufacturerId == 'all') {
    $this->element('addScript', [
        'script' =>
        Configure::read('app.jsNamespace') . ".AppChart.initPieChart(".json_encode($dataPieChart).", ".json_encode($labelsPieChart).", ".json_encode($backgroundColorPieChart).");"
    ]);
}
?>

<p><?php
    if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
        echo __d('admin', 'Net_turnover') . ': <b>' . $this->Number->formatAsCurrency($totalTurnover + $totalNetProfit) . '</b>';
        echo ' / ' . __d('admin', 'Net_turnover') . ' ' . __d('admin', 'per_month') . ': <b>' . $this->Number->formatAsCurrency($averageTurnover + $averageNetProfit) . '</b>';
        echo '<br />' . __d('admin', 'Net_profit') . ': <b>' . $this->Number->formatAsCurrency($totalNetProfit) . '</b>';
        echo ' / ' . __d('admin', 'Net_profit') . ' ' . __d('admin', 'per_month') . ': <b>' . $this->Number->formatAsCurrency($averageNetProfit) . '</b>';
        echo '<br />' . __d('admin', 'Surcharge') . ': <b>' . $this->Number->formatAsPercent($averageSurcharge) . '</b>';
        echo ' / ' . $this->Html->link(__d('admin', 'Go_to_profit_detail_page'), $this->Slug->getProfit());
    } else {
        echo __d('admin', 'Gross_turnover') . ': <b>' . $this->Number->formatAsCurrency($totalTurnover) . '</b>';
        echo ' / ' . __d('admin', 'Gross_turnover') . ' ' . __d('admin', 'per_month') . ': <b>' . $this->Number->formatAsCurrency($averageTurnover) . '</b>';
    }
    if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
        echo '<br />' . __d('admin', 'Variable_member_fee_is_included_in_turnover.');
    }
    if ($manufacturerId == 'all' && Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS') != '') {
        echo '<br />' . __d('admin', 'Member_fee_products_are_excluded_from_statistics.');
    }
?></p>

<canvas id="myBarChart" width="1000" height="500" style="margin-top:10px;"></canvas>
<?php if ($year == '' && count($xAxisDataLineChart) > 1) { ?>
    <canvas id="myLineChart" width="1000" height="500" style="margin-top:30px;"></canvas>
<?php } ?>
<?php if ($manufacturerId == 'all') { ?>
    <canvas id="myPieChart" width="1000" height="500" style="margin-top:30px;margin-bottom:30px;"></canvas>
<?php } ?>
<div class="sc"></div>
