<?php

declare(strict_types=1);

use Cake\Utility\Hash;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if (empty($product->category_products)) {
    return;
}

$i = 0;
$categories = [];
foreach($product->category_products as $categoryProduct) {
    $categoryWithIcon = array_filter($categoriesForMenu, function($cat) use ($categoryProduct) {
        return isset($cat['id']) && $cat['id'] == $categoryProduct->id_category && isset($cat['options']['fa-icon']);
    });
    $categoryWithIcon = array_shift($categoryWithIcon);
    if (empty($categoryWithIcon)) {
        continue;
    }
    if ($i >= 3) {
        break;
    }
    $categories[] = $categoryWithIcon;
}
$categories = Hash::sort($categories, '{n}.name', 'DESC');
foreach ($categories as $category) {
    echo $this->Html->link(
        '<img src="/img/badge-ring-light.svg" /><i class="' . h($category['options']['fa-icon']) . '"></i>',
        $category['slug'],
        [
            'class' => 'fcs-badge',
            'title' => $category['name'],
            'escape' => false,
        ]
    );
    $i++;
}