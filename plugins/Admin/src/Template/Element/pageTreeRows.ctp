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
use Cake\ORM\TableRegistry;

$pageTable = TableRegistry::getTableLocator()->get('Pages');

foreach ($pages as $page) {
    $level = $pageTable->getLevel($page);

    $rowClass = [
        'data'
    ];
    if ($level > 0) {
        $rowClass[] = 'sub-row';
    }
    if (! $page->active) {
        $rowClass[] = 'deactivated';
    }
    echo '<tr id="page-' . $page->id_page . '" class="' . implode(' ', $rowClass) . '">';

    echo '<td class="hide">';
    echo $page->id_page;
    echo '</td>';

    echo '<td>';
    echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
        'title' => __d('admin', 'Edit')
    ], $this->Slug->getPageEdit($page->id_page));
    echo '</td>';

    echo '<td>';
    if ($level > 0) {
        echo '<i class="fa fa-level-up fa-rotate-90" style="margin-right:5px;margin-left:'.(($level - 1) * 10).'px;"></i>';
    }
    echo $page->title;
    echo '</td>';

    echo '<td>';
    echo $this->Html->getMenuType($page->menu_type);
    echo '</td>';

    echo '<td align="center">';
    if ($page->position > 0) {
        echo $page->position;
    }
    echo '</td>';

    echo '<td align="center">';
    if ($page->is_private == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    }
    echo '</td>';

    echo '<td align="center">';
    if ($page->full_width == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    }
    echo '</td>';

    echo '<td align="center">';
    if ($page->extern_url != '') {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('link.png')), [
            'target' => '_blank',
            'title' => $page->extern_url
        ], $page->extern_url);
    }
    echo '</td>';

    echo '<td>';
    if (!empty($page->customer)) {
        echo $page->customer->name;
    }
    echo '</td>';

    echo '<td>';
    echo $page->modified->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'));
    echo '</td>';

    echo '<td align="center">';
    if ($page->active == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    } else {
        echo $this->Html->image($this->Html->getFamFamFamPath('delete.png'));
    }
    echo '</td>';

    echo '<td>';
    if ($page->active) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('arrow_right.png')), [
            'title' => __d('admin', 'Show_page'),
            'target' => '_blank'
        ], $this->Slug->getPageDetail($page->id_page, $page->title));
    }
    echo '</td>';

    echo '</tr>';

    if (! empty($page->children)) {
        echo $this->element('pageTreeRows', [
            'pages' => $page->children
        ]);
    }
}
