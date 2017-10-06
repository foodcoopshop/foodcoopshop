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
?>
<div id="lists-list">
     
        <?php
        $this->element('addScript', array(
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            var datefieldSelector = $('input.datepicker');
            datefieldSelector.datepicker();" . Configure::read('app.jsNamespace') . ".Admin.init();
        "
        ));
    ?>
    
    <div class="filter-container">
        Abholtag <?php echo $this->element('dateFields', array('dateFrom' => $dateFrom, 'showDateTo' => false)); ?>
        <button id="filter" class="btn btn-success">
            <i class="fa fa-search"></i> Filtern
        </button>
        <div class="right"></div>
    </div>

    <div id="help-container">
        <ul>
            <?php echo $this->element('docs/abholdienst'); ?>
            <li>Auf dieser Seite werden die verschickten Bestelllisten
                angezeigt.</li>
        </ul>
    </div>    
    
    <?php
    echo '<table class="list">';

    echo '<tr class="sort">';
    echo '<th>Abholdatum</th>';
    echo '<th>Hersteller</th>';
    echo '<th>Bestellliste nach Produkt</th>';
    echo '<th>Bestellliste nach Mitglied</th>';
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
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('zoom.png')) . ' Liste anzeigen (gruppiert nach Produkt)', array(
            'title' => 'Liste anzeigen (gruppiert nach Produkt)',
            'target' => '_blank',
            'class' => 'icon-with-text'
        ), $file['product_list_link']);
        echo '</td>';

        echo '<td>';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('zoom.png')) . ' Liste anzeigen (gruppiert nach Mitglied)', array(
            'title' => 'Liste anzeigen (gruppiert nach Mitglied)',
            'target' => '_blank',
            'class' => 'icon-with-text'
        ), $file['customer_list_link']);
        echo '</td>';

        echo '</tr>';
    }

    echo '<tr>';
    echo '<td colspan="4"><b>' . $i . '</b> Datensätze</td>';
    echo '</tr>';

    echo '</table>';
    ?>
    
    <div class="sc"></div>

</div>
