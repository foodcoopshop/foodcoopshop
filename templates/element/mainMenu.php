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

$menu = [];
if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $identity->isLoggedIn()) {
    $menu[] = [
        'name' => __('Products'), 'slug' => $this->Slug->getAllProducts(),
        'children' => $categoriesForMenu
    ];
}

if (!empty($manufacturersForMenu)) {
    $menu[] = [
        'name' => __('Manufacturers'), 'slug' => $this->Slug->getManufacturerList(),
        'children' => $manufacturersForMenu
    ];
}

$menu = array_merge($menu, $this->Menu->buildPageMenu($pagesForHeader));

echo $this->Menu->render($menu, ['id' => 'main-menu', 'class' => 'horizontal menu']);
