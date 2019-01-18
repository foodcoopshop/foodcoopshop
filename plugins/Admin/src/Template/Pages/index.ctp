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

?>
<div id="pages">

        <?php
        $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.init();
        "
        ]);
        $this->element('highlightRowAfterEdit', [
            'rowIdPrefix' => '#page-'
        ]);
    ?>
   
    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <h1><?php echo $title_for_layout; ?></h1>
            <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'empty' => 'alle Benutzer', 'options' => $customersForDropdown, 'default' => isset($customerId) ? $customerId: '']); ?>
            <div class="right">
                <?php
                echo '<div id="add-page-button-wrapper" class="add-button-wrapper">';
                echo $this->Html->link('<i class="fas fa-plus-circle"></i> '.__d('admin', 'Add_page').'', $this->Slug->getPageAdd(), [
                    'class' => 'btn btn-outline-light',
                    'escape' => false
                ]);
                echo '</div>';
                echo $this->element('printIcon');
                ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>
    
<?php

echo '<table class="list">';

echo '<tr class="sort">';
echo '<th class="hide">' . $this->Paginator->sort('Pages.id_page', 'ID') . '</th>';
echo '<th></th>';
echo '<th>'.__d('admin', 'Title').'</th>';
echo '<th>'.__d('admin', 'Menu').'</th>';
echo '<th>'.__d('admin', 'Rank_in_menu').'</th>';
echo '<th>'.__d('admin', 'Only_for_members').'</th>';
echo '<th>'.__d('admin', 'Full_width').'</th>';
echo '<th>'.__d('admin', 'Link').'</th>';
echo '<th>'.__d('admin', 'Modified_by').'</th>';
echo '<th>'.__d('admin', 'Modified_on').'</th>';
echo '<th>'.__d('admin', 'Active').'</th>';
echo '<th></th>';
echo '</tr>';

echo $this->element('pageTreeRows', [
    'pages' => $pages
]);

echo '<tr>';
echo '<td colspan="12"><b>' . $totalPagesCount . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $totalPagesCount).'</td>';
echo '</tr>';

echo '</table>';

?>    
</div>
