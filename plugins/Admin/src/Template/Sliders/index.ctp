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

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();
    "
]);
$this->element('highlightRowAfterEdit', [
    'rowIdPrefix' => '#slider-'
]);
?>

<div class="filter-container">
    <h1>Slideshow</h1>
    <div class="right">
        <?php
        echo '<div id="add-category-button-wrapper" class="add-button-wrapper">';
        echo $this->Html->link('<i class="fa fa-plus-square fa-lg"></i> Neues Slideshow-Bild erstellen', $this->Slug->getSliderAdd(), [
            'class' => 'btn btn-default',
            'escape' => false
        ]);
        echo '</div>';
        ?>
    </div>

</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du die Slideshow-Bilder verwalten.</li>
    </ul>
</div>

<?php

echo '<table class="list">';

echo '<tr class="sort">';
echo '<th class="hide">ID</th>';
echo '<th></th>';
echo '<th>Bild</th>';
echo '<th>' . $this->Paginator->sort('Sliders.position', 'Reihenfolge') . '</th>';
echo '<th>' . $this->Paginator->sort('Sliders.active', 'Aktiv') . '</th>';
echo '</tr>';

$i = 0;
foreach ($sliders as $slider) {
    $i ++;
    $rowClass = [
        'data'
    ];
    if (! $slider->active) {
        $rowClass[] = 'deactivated';
    }
    echo '<tr id="slider-' . $slider->id_slider . '" class="' . implode(' ', $rowClass) . '">';

    echo '<td>';
    echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
        'title' => 'Bearbeiten'
    ], $this->Slug->getSliderEdit($slider->id_slider));
    echo '</td>';

    echo '<td class="hide">';
    echo $slider->id_slider;
    echo '</td>';

    echo '<td align="center" style="background-color: #fff;">';
    echo '<img width="500" src="' . $this->Html->getSliderImageSrc($slider->image) . '" />';
    echo '</td>';

    echo '<td align="center">';
    echo $slider->position;
    echo '</td>';

    echo '<td align="center">';
    if ($slider->active == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    } else {
        echo $this->Html->image($this->Html->getFamFamFamPath('delete.png'));
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="5"><b>' . $i . '</b> Datensätze</td>';
echo '</tr>';

echo '</table>';

?>
