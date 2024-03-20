<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if ($pagesCount <= 1)  {
    return;
}

echo '<div class="pagination-wrapper">';

echo '<span>' . __('Page') . ':</span>';

for ($i = 1; $i <= $pagesCount; $i++) {

    $paginationLink = $this->getRequest()->getAttribute('here') . '?page=' . $i;
    if (isset($keyword) && $keyword != '') {
        $paginationLink .= '&keyword=' . $keyword;
    }
    if (isset($categoryId) && $categoryId != '') {
        $paginationLink .= '&categoryId=' . $categoryId;
    }

    echo $this->Html->link(
        (string) $i,
        $i > 1 ? $paginationLink : $this->getRequest()->getAttribute('here'),
        [
            'class' => 'btn btn-outline-light' . ($page == $i ? ' active' : ''),
            'title' => __('Page') . ' ' . $i,
        ]
    );
}
echo '</div>';
