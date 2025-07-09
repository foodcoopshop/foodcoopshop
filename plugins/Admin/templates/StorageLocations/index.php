<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Martin Hatlauf <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();
    "
]);
$this->element('highlightRowAfterEdit', [
    'rowIdPrefix' => '#storage-location-'
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <?php
        echo '<div id="add-category-button-wrapper" class="add-button-wrapper">';
        echo $this->Html->link('<i class="fas fa-plus-circle ok"></i> '.__d('admin', 'Add {0}', [__d('admin', 'Storage_location')]).'', $this->Slug->getStorageLocationAdd(), [
            'class' => 'btn btn-outline-light',
            'escape' => false
        ]);
        echo '</div>';
        echo $this->element('printIcon');
        ?>
    </div>

</div>

<?php

$this->Paginator->setPaginated($storageLocations);
echo '<table class="list">';
echo '<tr class="sort">';
echo '<th class="hide">ID</th>';
echo '<th></th>';
echo '<th>' . $this->Paginator->sort('StorageLocations.name', __d('admin', 'Name')) . '</th>';
echo '<th>' . $this->Paginator->sort('StorageLocations.position', __d('admin', 'Rank')) . '</th>';
echo '</tr>';

$i = 0;
foreach ($storageLocations as $storageLocation) {
    $i ++;
    $rowClass = [
        'data'
    ];
    echo '<tr id="storage-location-' . $storageLocation->id . '" class="' . implode(' ', $rowClass) . '">';

    echo '<td>';
    echo $this->Html->link(
        '<i class="fas fa-pencil-alt ok"></i>',
        $this->Slug->getStorageLocationEdit($storageLocation->id),
        [
            'class' => 'btn btn-outline-light',
            'title' => __d('admin', 'Edit'),
            'escape' => false
        ]
    );
    echo '</td>';

    echo '<td class="hide">';
    echo $storageLocation->id;
    echo '</td>';


    echo '<td align="left" >';
    echo $storageLocation->name;
    echo '</td>';

    echo '<td align="center">';
    echo $storageLocation->position;
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="3"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
echo '</tr>';

echo '</table>';

?>
