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
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div id="pages">

        <?php
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Admin.init();" .
                Configure::read('app.jsNamespace') . ".ModalImage.init('a.open-with-modal');
            ",
        ]);
        $this->element('highlightRowAfterEdit', [
            'rowIdPrefix' => '#page-'
        ]);
    ?>

    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <h1><?php echo $title_for_layout; ?></h1>
            <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'empty' => __('all_users'), 'options' => $customersForDropdown, 'default' => isset($customerId) ? $customerId: '']); ?>
            <div class="right">
                <?php
                echo '<div id="add-page-button-wrapper" class="add-button-wrapper">';
                echo $this->Html->link('<i class="fas fa-plus-circle ok"></i>', $this->Slug->getPageAdd(), [
                    'class' => 'btn btn-outline-light',
                    'title' => __('Add_page'),
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
echo '<th class="hide"></th>';
echo '<th>'.__('Image').'</th>';
echo '<th></th>';
echo '<th>'.__('Title').'</th>';
echo '<th>'.__('Menu').'</th>';
echo '<th>'.__('Rank_in_menu').'</th>';
echo '<th>'.__('Only_for_members').'</th>';
echo '<th>'.__('Full_width').'</th>';
echo '<th>'.__('Link').'</th>';
echo '<th>'.__('Modified_by').'</th>';
echo '<th>'.__('Modified_on').'</th>';
echo '<th>'.__('Active').'</th>';
echo '<th></th>';
echo '</tr>';

echo '<tr id="page-home" class="data">';
echo '<td class="hide">0</td>';
echo '<td align="center" class="image">';
$srcLargeImage = $this->Html->getHomeImageSrc('single');
$srcSmallImage = $this->Html->getHomeImageSrc('home');
if ($srcSmallImage != '') {
    echo '<a class="open-with-modal" href="javascript:void(0);" data-modal-title="' . h(__('homepage')) . '" data-modal-image="' . $srcLargeImage . '">';
    echo '<img width="90" src="' . $srcSmallImage . '" />';
    echo '</a>';
}
echo '</td>';
echo '<td>';
echo $this->Html->link(
    '<i class="fas fa-pencil-alt ok"></i>',
    $this->Slug->getPageEditHome(),
    [
        'class' => 'btn btn-outline-light',
        'title' => __('Edit'),
        'escape' => false,
    ],
);
echo '</td>';
echo '<td>' . __('homepage') . '</td>';
echo '<td></td>';
echo '<td align="center"></td>';
echo '<td align="center"></td>';
echo '<td align="center"></td>';
echo '<td align="center"></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td align="center"><i class="fas fa-check-circle ok"></i></td>';
echo '<td>';
echo $this->Html->link(
    '<i class="fas fa-arrow-right ok"></i>',
    '/',
    [
        'class' => 'btn btn-outline-light',
        'title' => __('Show_page'),
        'target' => '_blank',
        'escape' => false,
    ],
);
echo '</td>';
echo '</tr>';

echo $this->element('pageTreeRows', [
    'pages' => $pages
]);

echo '<tr>';
echo '<td colspan="13"><b>' . $totalPagesCount . '</b> '.__('{0,plural,=1{record} other{records}}', $totalPagesCount).'</td>';
echo '</tr>';

echo '</table>';

?>
</div>
