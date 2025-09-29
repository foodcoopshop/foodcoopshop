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

if (OrderCustomerService::isOrderForDifferentCustomerMode()) {
    return;
}
if ($identity === null) {
    return;
}

if ($identity->isSuperadmin() || ($identity->isManufacturer() && $product->id_manufacturer == $identity->getManufacturerId())) {
    echo $this->Html->link(
        '<img src="/img/badge-ring-light.svg" /><i class="fas fa-fw fa-pencil-alt"></i>',
        $this->Slug->getProductAdmin(($identity->isSuperadmin() ? $product->id_manufacturer : null), $product->id_product),
        [
            'class' => 'fcs-badge',
            'title' => __('Edit'),
            'escape' => false,
        ]
    );
}
