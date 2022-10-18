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

echo '<div class="pw" id="pw-' . $product->id_product . '">';

    echo '<div class="c1">';
        echo $this->element('catalog/columns/column1', [
            'product' => $product,
            'showIsNewBadgeAsLink' => $showIsNewBadgeAsLink,
        ]);
    echo '</div>';

    echo '<div class="c2">';
        echo $this->element('catalog/columns/column2', [
            'product' => $product,
            'showProductDetailLink' => $showProductDetailLink,
            'showManufacturerDetailLink' => $showManufacturerDetailLink,
        ]);
    echo '</div>';

    echo '<div class="c3">';
        echo $this->element('catalog/columns/column3', [
            'product' => $product,
        ]);
    echo '</div>';

echo '</div>';
