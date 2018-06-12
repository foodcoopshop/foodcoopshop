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
<div id="categories">

        <?php
        $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".AppFeatherlight.initLightboxForImages('a.lightbox');
        "
        ]);
        $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#category-'
        ]);
    ?>
   
    <div class="filter-container">
        <h1>Kategorien</h1>
        <div class="right">
            <?php
            echo '<div id="add-category-button-wrapper" class="add-button-wrapper">';
            echo $this->Html->link('<i class="fa fa-plus-square fa-lg"></i> Neue Kategorie erstellen', $this->Slug->getCategoryAdd(), [
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
echo '<th class="hide">' . $this->Paginator->sort('Categories.id_category', 'ID') . '</th>';
echo '<th></th>';
echo '<th>Name</th>';
echo '<th>geändert am</th>';
echo '<th>Aktiv</th>';
echo '<th></th>';
echo '</tr>';

echo $this->element('categoryTreeRows', [
    'categories' => $categories,
    'subRow' => false
]);

echo '<tr>';
echo '<td colspan="12"><b>' . $totalCategoriesCount . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $totalCategoriesCount).'</td>';
echo '</tr>';

echo '</table>';

?>    
</div>
