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

if (!OrderCustomerService::isSelfServiceModeByUrl() && $product->is_stock_product && $product->manufacturer->stock_management_enabled) {
    echo '<div class="fcs-badge" title="' . __('Stock_product') . '">';
        echo '<img src="/img/badge-ring-light.svg" />';
        echo '<i class="fas fa-fw ok fa-store"></i>';
    echo '</div>';
}
