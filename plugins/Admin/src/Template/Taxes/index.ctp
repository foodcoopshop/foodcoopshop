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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div id="taxes">

        <?php
        $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.init();"
        ]);
        $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#tax-'
        ]);
    ?>
   
    <div class="filter-container">
        <h1><?php echo $title_for_layout; ?></h1>
        <div class="right">
            <?php
            echo '<div id="add-tax-button-wrapper" class="add-button-wrapper">';
            echo $this->Html->link('<i class="fas fa-plus-circle"></i> '.__d('admin', 'Add_tax_rate').'', $this->Slug->getTaxAdd(), [
                'class' => 'btn btn-outline-light',
                'escape' => false
            ]);
            echo '</div>';
            echo $this->element('printIcon');
            ?>
        </div>

    </div>
 
<?php

echo '<table class="list">';
echo '<tr class="sort">';
echo '<th class="hide">'.__d('admin', 'ID').'</th>';
echo '<th></th>';
echo '<th>' . $this->Paginator->sort('Taxes.rate', __d('admin', 'Tax_rate')) . '</th>';
echo '<th>' . $this->Paginator->sort('Taxes.active', __d('admin', 'Active')) . '</th>';
echo '</tr>';

$i = 0;

foreach ($taxes as $tax) {
    $i ++;
    $rowClass = [
        'data'
    ];
    if (! $tax->active) {
        $rowClass[] = 'deactivated';
    }
    echo '<tr id="tax-' . $tax->id_tax . '" class="' . implode(' ', $rowClass) . '">';

    echo '<td class="hide">';
    echo $tax->id_tax;
    echo '</td>';

    echo '<td>';
    echo $this->Html->link(
        '<i class="fas fa-pencil-alt ok"></i>',
        $this->Slug->getTaxEdit($tax->id_tax),
        [
            'class' => 'btn btn-outline-light',
            'title' => __d('admin', 'Edit'),
            'escape' => false
        ]
    );
    echo '</td>';

    echo '<td>';
    echo $this->Number->formatAsPercent($tax->rate);
    echo '</td>';

    echo '<td align="center">';
    if ($tax->active == 1) {
        echo '<i class="fas fa-check-circle ok"></i>';
    } else {
        echo '<i class="fas fa-minus-circle not-ok"></i>';
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="4"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
echo '</tr>';

echo '</table>';

?>    
</div>
