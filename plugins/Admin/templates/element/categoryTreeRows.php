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
use Cake\ORM\TableRegistry;

$categoriesTable = TableRegistry::getTableLocator()->get('Categories');

foreach ($categories as $category) {
    /** @phpstan-ignore-next-line */
    $level = $categoriesTable->getLevel($category);

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
    echo $this->Html->link(
        '<i class="fas fa-pencil-alt ok"></i>',
        $this->Slug->getCategoryEdit($category->id_category),
        [
            'class' => 'btn btn-outline-light',
            'title' => __d('admin', 'Edit'),
            'escape' => false
        ]
    );
    echo '<td>';
    if ($level > 0) {
        echo '<i class="fas fa-level-up-alt fa-rotate-90" style="margin-right:5px;margin-left:'.(($level - 1) * 10).'px;"></i>';
    }
    echo $category->name;
    echo '</td>';

    echo '<td>';
        echo $category->modified->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'));
    echo '</td>';

    echo '<td align="center">';
    if ($category->active == 1) {
        echo '<i class="fas fa-check-circle ok"></i>';
    } else {
        echo '<i class="fas fa-minus-circle ok"></i>';
    }
    echo '</td>';

    echo '<td style="width:20px;">';
    if ($category->active) {
        echo $this->Html->link(
            '<i class="fas fa-arrow-right ok"></i>',
            $this->Slug->getCategoryDetail($category->id_category, $category->name),
            [
                'class' => 'btn btn-outline-light',
                'title' => __d('admin', 'Show_category'),
                'target' => '_blank',
                'escape' => false
            ]
        );
    }
    echo '</td>';

    echo '</tr>';

    if (! empty($category->children)) {
        echo $this->element('categoryTreeRows', [
            'categories' => $category->children
        ]);
    }
}
