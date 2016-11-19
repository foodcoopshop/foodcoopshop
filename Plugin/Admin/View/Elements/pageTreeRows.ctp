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
    
    $rowClass = array(
        'data'
    );
    if ($subRow) {
        $rowClass[] = 'sub-row';
    }
    if (! $page['Page']['active']) {
        $rowClass[] = 'deactivated';
    }
    echo '<tr class="' . implode(' ', $rowClass) . '">';
    
    echo '<td class="hide">';
    echo $page['Page']['id_cms'];
    echo '</td>';
    
    echo '<td>';
    echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), array(
        'title' => 'Bearbeiten'
    ), $this->Slug->getPageEdit($page['Page']['id_cms']));
    echo '</td>';
    
    echo '<td>';
    if ($subRow) {
        echo '<i class="fa fa-level-up fa-rotate-90" style="margin-right: 5px;"></i>';
    }
    echo $page['PageLang']['meta_title'];
    echo '</td>';
    
    echo '<td>';
    echo $this->Html->getMenuType($page['Page']['menu_type']);
    echo '</td>';
    
    echo '<td align="center">';
    if ($page['Page']['position'] > 0) {
        echo $page['Page']['position'];
    }
    echo '</td>';
    
    echo '<td align="center">';
    if ($page['Page']['full_width'] == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    }
    echo '</td>';
    
    echo '<td align="center">';
    if ($page['Page']['url'] != '') {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('link.png')), array(
            'target' => '_blank',
            'title' => $page['Page']['url']
        ), $page['Page']['url']);
    }
    echo '</td>';
    
    echo '<td>';
    echo $page['Customer']['name'];
    echo '</td>';
    
    echo '<td>';
    echo $this->Time->formatToDateNTimeLongWithSecs($page['Page']['modified']);
    echo '</td>';
    
    echo '<td align="center">';
    if ($page['Page']['active'] == 1) {
        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
    } else {
        echo $this->Html->image($this->Html->getFamFamFamPath('delete.png'));
    }
    echo '</td>';
    
    echo '<td>';
    if ($page['Page']['active']) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('arrow_right.png')), array(
            'title' => 'Seite anzeigen',
            'target' => '_blank'
        ), $this->Slug->getPageDetail($page['Page']['id_cms'], $page['PageLang']['meta_title']));
    }
    echo '</td>';
    
    echo '</tr>';
    
    if (! empty($page['children'])) {
        echo $this->element('pageTreeRows', array(
            'pages' => $page['children'],
            'subRow' => true
        ));
    }
}

?>