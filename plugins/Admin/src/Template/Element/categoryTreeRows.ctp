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
use Cake\ORM\TableRegistry;

$categoryTable = TableRegistry::getTableLocator()->get('Categories');

foreach ($categories as $category) {
    $level = $categoryTable->getLevel($category);
    
    $rowClass = [
        'data'
    ];
    if ($level > 0) {
        $rowClass[] = 'sub-row';
    }
    if (! $category->active) {
        $rowClass[] = 'deactivated';
    }
    
    echo '<tr id="category-' . $category->id_category . '" class="' . implode(' ', $rowClass) . '">';

    echo '<td class="hide">';
        echo $category->id_category;
    echo '</td>';

    echo '<td>';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
            'title' => 'Bearbeiten'
        ], $this->Slug->getCategoryEdit($category->id_category));
    echo '</td>';

    echo '<td>';
    if ($level > 0) {
        echo '<i class="fa fa-level-up fa-rotate-90" style="margin-right:5px;margin-left:'.(($level - 1) * 10).'px;"></i>';
    }
    echo $category->name;
    echo '</td>';

    echo '<td>';
        echo $category->modified->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'));
    echo '</td>';

    echo '<td align="center">';
    if ($category->active == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    } else {
        echo $this->Html->image($this->Html->getFamFamFamPath('delete.png'));
    }
    echo '</td>';

    echo '<td style="width:20px;">';
    if ($category->active) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('arrow_right.png')), [
        'title' => 'Seite anzeigen',
        'target' => '_blank'
        ], $this->Slug->getCategoryDetail($category->id_category, $category->name));
    }
    echo '</td>';

    echo '</tr>';

    if (! empty($category->children)) {
        echo $this->element('categoryTreeRows', [
            'categories' => $category->children
        ]);
    }
}
