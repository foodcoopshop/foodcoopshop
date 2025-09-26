<?php
declare(strict_types=1);

use App\Services\OrderCustomerService;

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

echo '<div class="fcs-badges">';
    if ($product->is_new) {
        echo '<div class="fcs-badge" title="Neu">';
            echo '<img src="/img/badge-ring-light-mode.svg" />';
            echo '<i class="fas gold fa-star"></i>';
        echo '</div>';
    }
    echo '<div class="fcs-badge" title="Vorhandene Stück">';
        echo '<img src="/img/badge-ring-light-mode.svg" />';
        echo '<span>' . rand(0, 100) . 'x</span>';
    echo '</div>';
    if (!OrderCustomerService::isSelfServiceModeByUrl() && $product->is_stock_product && $product->manufacturer->stock_management_enabled) {
        echo '<div class="fcs-badge" title="' . __('Stock_product') . '">';
            echo '<img src="/img/badge-ring-light-mode.svg" />';
            echo '<i class="fas ok fa-store"></i>';
        echo '</div>';
    }
    echo '<div class="fcs-badge" title="Bio">';
        echo '<img src="/img/badge-ring-light-mode.svg" />';
        echo '<i class="fas ok fa-leaf"></i>';
    echo '</div>';
echo '</div>';
