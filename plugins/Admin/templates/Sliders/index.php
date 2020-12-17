<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <?php
        echo '<div id="add-category-button-wrapper" class="add-button-wrapper">';
        echo $this->Html->link('<i class="fas fa-plus-circle"></i> '.__d('admin', 'Add_slider').'', $this->Slug->getSliderAdd(), [
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
echo '<th class="hide">ID</th>';
echo '<th></th>';
echo '<th>'.__d('admin', 'Image').'</th>';
echo '<th>' . $this->Paginator->sort('Sliders.link', __d('admin', 'Link')) . '</th>';
echo '<th>' . $this->Paginator->sort('Sliders.position', __d('admin', 'Rank')) . '</th>';
echo '<th>' . $this->Paginator->sort('Sliders.is_private', __d('admin', 'Only_for_members')) . '</th>';
echo '<th>' . $this->Paginator->sort('Sliders.active', __d('admin', 'Active')) . '</th>';
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
    echo $this->Html->link(
        '<i class="fas fa-pencil-alt ok"></i>',
        $this->Slug->getSliderEdit($slider->id_slider),
        [
            'class' => 'btn btn-outline-light',
            'title' => __d('admin', 'Edit'),
            'escape' => false
        ]
    );
    echo '</td>';

    echo '<td class="hide">';
    echo $slider->id_slider;
    echo '</td>';

    echo '<td align="center" style="background-color: #fff;">';
    echo '<img width="500" src="' . $this->Html->getSliderImageSrc($slider->image) . '" />';
    echo '</td>';

    echo '<td align="center">';
        if ($slider->link != '') {
            echo $this->Html->link(
                '<i class="fas fa-link ok"></i>',
                $slider->link,
                [
                    'class' => 'btn btn-outline-light',
                    'target' => '_blank',
                    'title' => $slider->link,
                    'escape' => false
                ]
            );
        }
    echo '</td>';

    echo '<td align="center">';
    echo $slider->position;
    echo '</td>';

    echo '<td align="center">';
    if ($slider->is_private == 1) {
        echo '<i class="fas fa-check-circle ok"></i>';
    }
    echo '</td>';

    echo '<td align="center">';
    if ($slider->active == 1) {
        echo '<i class="fas fa-check-circle ok"></i>';
    } else {
        echo '<i class="fas fa-minus-circle ok"></i>';
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="5"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
echo '</tr>';

echo '</table>';

?>
