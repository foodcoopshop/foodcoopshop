<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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

        echo $this->Form->control('range', [
            'type' => 'select',
            'label' => '',
            'options' => $ranges,
            'default' => $range != '' ? $range : ''
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
if ($range == '' && count($xAxisDataLineChart) > 1) {
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
        echo '<table class="list no-clone-last-row" style="width:350px;margin-bottom:5px;"><tr>';

            echo '<td><b>' . __d('admin', 'Net_turnover_selling_price') . '</b></td>';
            echo '<td>'. __d('admin', 'total') . '</td>';
            echo '<td style="text-align:right;"><b>' . $this->Number->formatAsCurrency($totalNetTurnover) . '</b></td>';
            echo '</tr><tr>';
            echo '<td></td>';
            echo '<td>'. __d('admin', 'per_month') . '</td>';
            echo '<td style="text-align:right;">' . $this->Number->formatAsCurrency($averageTurnover + $averageNetProfit) . '</td>';
            echo '</tr>';

            echo '<td><b>' . __d('admin', 'Net_profit') . '</b></td>';
            echo '<td>'. __d('admin', 'total') . '</td>';
            echo '<td style="text-align:right;"><b>' . $this->Number->formatAsCurrency($totalNetProfit) . '</b></td>';
            echo '</tr><tr>';
            echo '<td></td>';
            echo '<td>'. __d('admin', 'per_month') . '</td>';
            echo '<td style="text-align:right;">' . $this->Number->formatAsCurrency($averageNetProfit) . '</td>';
            echo '</tr><tr>';
            echo '<td><b>' . __d('admin', 'Surcharge') . '</b></td>';
            echo '<td></td>';
            echo '<td style="text-align:right;"><b>' . $this->Number->formatAsPercent($averageSurcharge) . '</b></td>';
            echo '</tr>';

        echo '</table>';

        echo $this->Html->link(__d('admin', 'Go_to_profit_detail_page'), $this->Slug->getProfit());
        echo '<i class="fa fas fa-question-circle" style="position:absolute;top:10px;right:10px;" title="' . h(__d('admin', 'For_filtering_data_click_on_legend_on_top_of_chart.')) . '"></i>';
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Helper.initTooltip('.fa-question-circle');"
        ]);

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
<?php if ($range == '' && count($xAxisDataLineChart) > 1) { ?>
    <canvas id="myLineChart" width="1000" height="500" style="margin-top:30px;"></canvas>
<?php } ?>
<?php if ($manufacturerId == 'all') { ?>
    <canvas id="myPieChart" width="1000" height="500" style="margin-top:30px;margin-bottom:30px;"></canvas>
<?php } ?>
<div class="sc"></div>
