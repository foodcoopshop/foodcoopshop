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

foreach ($pages as $page) {
    $rowClass = [
        'data'
    ];
    if ($subRow) {
        $rowClass[] = 'sub-row';
    }
    if (! $page['Pages']['active']) {
        $rowClass[] = 'deactivated';
    }
    echo '<tr class="' . implode(' ', $rowClass) . '">';

    echo '<td class="hide">';
    echo $page['Pages']['id_page'];
    echo '</td>';

    echo '<td>';
    echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
        'title' => 'Bearbeiten'
    ], $this->Slug->getPageEdit($page['Pages']['id_page']));
    echo '</td>';

    echo '<td>';
    if ($subRow) {
        echo '<i class="fa fa-level-up fa-rotate-90" style="margin-right: 5px;"></i>';
    }
    echo $page['Pages']['title'];
    echo '</td>';

    echo '<td>';
    echo $this->Html->getMenuType($page['Pages']['menu_type']);
    echo '</td>';

    echo '<td align="center">';
    if ($page['Pages']['position'] > 0) {
        echo $page['Pages']['position'];
    }
    echo '</td>';

    echo '<td align="center">';
    if ($page['Pages']['is_private'] == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    }
    echo '</td>';

    echo '<td align="center">';
    if ($page['Pages']['full_width'] == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    }
    echo '</td>';

    echo '<td align="center">';
    if ($page['Pages']['extern_url'] != '') {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('link.png')), [
            'target' => '_blank',
            'title' => $page['Pages']['extern_url']
        ], $page['Pages']['extern_url']);
    }
    echo '</td>';

    echo '<td>';
    echo $page['Customers']['name'];
    echo '</td>';

    echo '<td>';
    echo $this->Time->formatToDateNTimeLongWithSecs($page['Pages']['modified']);
    echo '</td>';

    echo '<td align="center">';
    if ($page['Pages']['active'] == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    } else {
        echo $this->Html->image($this->Html->getFamFamFamPath('delete.png'));
    }
    echo '</td>';

    echo '<td>';
    if ($page['Pages']['active']) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('arrow_right.png')), [
            'title' => 'Seite anzeigen',
            'target' => '_blank'
        ], $this->Slug->getPageDetail($page['Pages']['id_page'], $page['Pages']['title']));
    }
    echo '</td>';

    echo '</tr>';

    if (! empty($page['children'])) {
        echo $this->element('pageTreeRows', [
            'pages' => $page['children'],
            'subRow' => true
        ]);
    }
}
