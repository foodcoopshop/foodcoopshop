<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
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
        <h1><?php echo $title_for_layout; ?></h1>
        <?php
        if (!$appAuth->isManufacturer()) {
            echo $this->Form->control('manufacturerId', [
            'type' => 'select',
            'label' => '',
            'options' => $manufacturersForDropdown,
            'empty' => __d('admin', 'All_manufacturers'),
            'default' => $manufacturerId != '' ? $manufacturerId : ''
            ]);
        }
        ?>
        <div class="right">
        	<?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_manufacturers'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php
if (empty($manufacturer)) {
    echo '<br /><h2 class="info">'.__d('admin', 'Please_chose_a_manufacturer.').'</h2>';
    return;
}

$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".AppChart.init('".json_encode($xAxisData)."', '".json_encode($yAxisData)."');"
]);
?>

<canvas id="myChart" width="1000" height="500"></canvas>

<div class="sc"></div>
