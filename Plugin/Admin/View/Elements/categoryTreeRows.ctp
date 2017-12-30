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

foreach ($categories as $category) {
    $rowClass = array(
        'data'
    );
    if ($subRow) {
        $rowClass[] = 'sub-row';
    }
    if (! $category['Category']['active']) {
        $rowClass[] = 'deactivated';
    }
    echo '<tr id="category-' . $category['Category']['id_category'] . '" class="' . implode(' ', $rowClass) . '">';

    echo '<td class="hide">';
        echo $category['Category']['id_category'];
    echo '</td>';

    echo '<td>';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), array(
            'title' => 'Bearbeiten'
        ), $this->Slug->getCategoryEdit($category['Category']['id_category']));
    echo '</td>';

    echo '<td>';
    if ($subRow) {
        echo '<i class="fa fa-level-up fa-rotate-90" style="margin-right:5px;margin-left: ' . (($category['Category']['level_depth'] - 2) * 10) . 'px;"></i>';
    }
        echo $category['Category']['name'];
    echo '</td>';

    echo '<td>';
        echo $this->Time->formatToDateNTimeLongWithSecs($category['Category']['date_upd']);
    echo '</td>';

    echo '<td align="center">';
    if ($category['Category']['active'] == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    } else {
        echo $this->Html->image($this->Html->getFamFamFamPath('delete.png'));
    }
    echo '</td>';

    echo '<td style="width:20px;">';
    if ($category['Category']['active']) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('arrow_right.png')), array(
        'title' => 'Seite anzeigen',
        'target' => '_blank'
        ), $this->Slug->getCategoryDetail($category['Category']['id_category'], $category['Category']['name']));
    }
    echo '</td>';

    echo '</tr>';

    if (! empty($category['children'])) {
        echo $this->element('categoryTreeRows', array(
            'categories' => $category['children'],
            'subRow' => true
        ));
    }
}
