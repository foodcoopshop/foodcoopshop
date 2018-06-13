<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div id="lists-list">
     
        <?php
        $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            $('input.datepicker').datepicker();".
            Configure::read('app.jsNamespace') . ".Admin.init();
        "
        ]);
    ?>
    
    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php echo __d('admin', 'Pick_up_day'); ?> <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'showDateTo' => false, 'nameFrom' => 'dateFrom']); ?>
            <div class="right">
            <?php
                echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_products'))]);
            ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>
    
    <?php
    echo '<table class="list">';

    echo '<tr class="sort">';
    echo '<th>'.__d('admin', 'Pick_up_date').'</th>';
    echo '<th>'.__d('admin', 'Manufacturer').'</th>';
    echo '<th>'.__d('admin', 'Order_list_by_product').'</th>';
    echo '<th>'.__d('admin', 'Order_list_by_member').'</th>';
    echo '</tr>';

    $i = 0;
    foreach ($files as $file) {
        $i ++;

        echo '<tr class="data">';

        echo '<td>';
        echo $this->Time->formatToDateShort($file['delivery_date']);
        echo '</td>';

        echo '<td>';
        echo $file['manufacturer_name'];
        echo '</td>';

        echo '<td>';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('zoom.png')) . ' ' . __d('admin', 'Show_list_(grouped_by_product)'), [
            'title' => __d('admin', 'Show_list_(grouped_by_product)'),
            'target' => '_blank',
            'class' => 'icon-with-text'
        ], $file['product_list_link']);
        echo '</td>';

        echo '<td>';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('zoom.png')) . ' ' . __d('admin', 'Show_list_(grouped_by_member)'), [
            'title' => __d('admin', 'Show_list_(grouped_by_member)'),
            'target' => '_blank',
            'class' => 'icon-with-text'
        ], $file['customer_list_link']);
        echo '</td>';

        echo '</tr>';
    }

    echo '<tr>';
    echo '<td colspan="4"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
    echo '</tr>';

    echo '</table>';
    ?>
    
    <div class="sc"></div>

</div>
