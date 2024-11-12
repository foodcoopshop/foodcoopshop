<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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
            <h1><?php echo $title_for_layout; ?></h1>
            <?php
                echo $this->Form->control('year', [
                    'type' => 'select',
                    'label' => '',
                    'options' => $years,
                    'default' => $year,
                ]);
            ?>
            <div class="right">
            <?php
                echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_manufacturers'))]);
            ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>

    <?php
    echo '<table class="list">';

    echo '<tr class="sort">';
    echo '<th>'.__d('admin', 'Invoice_date').'</th>';
    echo '<th>'.__d('admin', 'Invoice_number_abbreviation').'</th>';
    echo '<th>'.__d('admin', 'Manufacturer').'</th>';
    echo '<th>'.__d('admin', 'Invoice').'</th>';
    echo '</tr>';

    $i = 0;
    foreach ($files as $file) {
        $i ++;

        echo '<tr class="data">';

        echo '<td>';
        echo $this->Time->formatToDateShort($file['invoice_date']);
        echo '</td>';

        echo '<td>';
        echo $file['invoice_number'];
        echo '</td>';

        echo '<td>';
        echo $file['manufacturer_name'];
        echo '</td>';

        echo '<td>';
            echo $this->Html->link(
                '<i class="fas ' . $file['invoice']['icon'] .' ok"></i> ' . $file['invoice']['label'],
                $file['invoice']['link'],
                [
                    'class' => 'btn btn-outline-light',
                    'target' => '_blank',
                    'escape' => false,
                ]
            );
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
