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
            echo $this->Html->link('<i class="fa fa-plus-square fa-lg"></i> Neue Steuersatz erstellen', $this->Slug->getTaxAdd(), [
                'class' => 'btn btn-default',
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
echo '<th class="hide">ID</th>';
echo '<th></th>';
echo '<th>' . $this->Paginator->sort('Taxes.rate', 'Steuersatz') . '</th>';
echo '<th>' . $this->Paginator->sort('Taxes.active', 'Aktiv') . '</th>';
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
    echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
        'title' => 'Bearbeiten'
    ], $this->Slug->getTaxEdit($tax->id_tax));
    echo '</td>';

    echo '<td>';
    echo $this->Number->formatAsPercent($tax->rate);
    echo '</td>';

    echo '<td align="center">';
    if ($tax->active == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    } else {
        echo $this->Html->image($this->Html->getFamFamFamPath('delete.png'));
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
