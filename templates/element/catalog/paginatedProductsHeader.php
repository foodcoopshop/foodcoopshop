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

use App\Services\CatalogService;

 if ($totalProductCount > CatalogService::MAX_PRODUCTS_PER_PAGE) {
    echo __('Page_{0}_of_{1}', [
        $page,
        $pagesCount,
    ]) . ': ' . $totalProductCount;
} else {
    $productCount = count($products);
    echo $productCount . ' ' . ($productCount == 1 ?  __('Product') : __('Products'));
}

echo ' ' . __('found');
