<?php
declare(strict_types=1);

use App\Services\OrderCustomerService;
use Cake\Log\Log;
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

echo '<div class="fcs-badges">';
    if (!OrderCustomerService::isOrderForDifferentCustomerMode()) {
        if ($identity !== null) {
            if ($identity->isSuperadmin() || ($identity->isManufacturer() && $product->id_manufacturer == $identity->getManufacturerId())) {
                echo $this->Html->link(
                    '<img src="/img/badge-ring-light.svg" /><i class="fas fa-fw not-ok fa-pencil-alt"></i>',
                    $this->Slug->getProductAdmin(($identity->isSuperadmin() ? $product->id_manufacturer : null), $product->id_product),
                    [
                        'class' => 'fcs-badge',
                        'title' => __('Edit'),
                        'escape' => false,
                    ]
                );
            }
        }
    }
    if ($product->is_new) {
        echo '<div class="fcs-badge" title="Neu">';
            echo '<img src="/img/badge-ring-light.svg" />';
            echo '<i class="fas fa-fw gold fa-star"></i>';
        echo '</div>';
    }
    echo '<div class="fcs-badge" title="Vorhandene Stück">';
        echo '<img src="/img/badge-ring-light.svg" />';
        echo '<span>' . rand(0, 99) . 'x</span>';
    echo '</div>';
    if (!OrderCustomerService::isSelfServiceModeByUrl() && $product->is_stock_product && $product->manufacturer->stock_management_enabled) {
        echo '<div class="fcs-badge" title="' . __('Stock_product') . '">';
            echo '<img src="/img/badge-ring-light.svg" />';
            echo '<i class="fas fa-fw ok fa-store"></i>';
        echo '</div>';
    }
    echo '<div class="fcs-badge" title="Lieferrhythmus">';
        echo '<img src="/img/badge-ring-light.svg" />';
        echo '<i class="far fa-fw ok fa-clock"></i>';
    echo '</div>';
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
        $i++;
    }
    $categories = Hash::sort($categories, '{s}.name', 'DESC');
    foreach($categories as $category) {
        echo '<div class="fcs-badge" title="' . h($category['name']) . '">';
            echo '<img src="/img/badge-ring-light.svg" />';
            echo '<i class="fa-fw ok ' . h($category['options']['fa-icon']) . '"></i>';
        echo '</div>';
    }
echo '</div>';
