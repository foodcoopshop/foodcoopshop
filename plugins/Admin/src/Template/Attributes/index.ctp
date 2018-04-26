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
use Cake\Utility\Hash;

?>
<div id="attribues">

        <?php
        $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Helper.bindToggleLinks();"
        ]);
        $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#attribute-'
        ]);
    ?>
   
    <div class="filter-container">
        <h1><?php echo $title_for_layout; ?></h1>
        <div class="right">
            <?php
            echo '<div id="add-attribute-button-wrapper" class="add-button-wrapper">';
            echo $this->Html->link('<i class="fa fa-plus-circle"></i> Neue Variante erstellen', $this->Slug->getAttributeAdd(), [
                'class' => 'btn btn-default',
                'escape' => false
            ]);
            echo '</div>';
            ?>
        </div>

    </div>

    <div id="help-container">
        <ul>
            <li>Auf dieser Seite kannst du Varianten verwalten.</li>
        </ul>
    </div>    
    
<?php

echo '<table class="list">';
echo '<tr class="sort">';
echo '<th class="hide">ID</th>';
echo '<th></th>';
echo '<th>' . $this->Paginator->sort('Attributes.name', 'Name') . '</th>';
echo '<th>Aktivierten Produkten zugewiesen?</th>';
echo '<th>Deaktivierten Produkten zugewiesen?</th>';
echo '<th>' . $this->Paginator->sort('Attributes.modified', 'geändert am') . '</th>';
echo '</tr>';

$i = 0;

foreach ($attributes as $attribute) {
    $i ++;
    $rowClass = [
        'data'
    ];
    if (! $attribute->active) {
        $rowClass[] = 'deactivated';
    }
    echo '<tr id="attribute-' . $attribute->id_attribute . '" class="' . implode(' ', $rowClass) . '">';

    echo '<td class="hide">';
    echo $attribute->id_attribute;
    echo '</td>';

    echo '<td>';
    echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
        'title' => 'Bearbeiten'
    ], $this->Slug->getAttributeEdit($attribute->id_attribute));
    echo '</td>';

    echo '<td>';
    echo $attribute->name;
    echo '</td>';

    echo '<td style="width:300px;">';
    if (! empty($attribute->combination_product['online'])) {
        echo $this->Html->link('<i class="fa fa-plus-circle"></i> Zugewiesene Produkte (' . count($attribute->combination_product['online']) . ')', 'javascript:void(0);', [
            'class' => 'toggle-link',
            'title' => 'Zugewiesene Produkte anzeigen',
            'escape' => false
        ]);
        echo '<div class="toggle-content">' . join('<br /> ', Hash::extract($attribute->combination_product['online'], '{n}.link')) . '</div>';
    }
    echo '</td>';

    echo '<td style="width:300px;">';
    if (! empty($attribute->combination_product['offline'])) {
        echo $this->Html->link('<i class="fa fa-plus-circle"></i> Zugewiesene Produkte (' . count($attribute->combination_product['offline']) . ')', 'javascript:void(0);', [
            'class' => 'toggle-link',
            'title' => 'Zugewiesene Produkte anzeigen',
            'escape' => false
        ]);
        echo '<div class="toggle-content">' . join('<br /> ', Hash::extract($attribute->combination_product['offline'], '{n}.name')) . '</div>';
    }
    echo '</td>';

    echo '<td>';
    if ($attribute->modified != '') {
        echo $attribute->modified->i18nFormat(Configure::read('DateFormat.de.DateNTimeLongWithSecs'));
        ;
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="11"><b>' . $i . '</b> Datensätze</td>';
echo '</tr>';

echo '</table>';

?>    
</div>
