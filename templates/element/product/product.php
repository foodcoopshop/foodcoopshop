<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo '<div class="pw" id="pw-' . $product->id_product . '">';

    echo '<div class="c1">';
        echo $this->element('product/columns/column1', [
            'product' => $product,
            'showIsNewBadgeAsLink' => $showIsNewBadgeAsLink,
        ]);
    echo '</div>';

    echo '<div class="c2">';
        echo $this->element('product/columns/column2', [
            'product' => $product,
            'showProductDetailLink' => $showProductDetailLink,
            'showManufacturerDetailLink' => $showManufacturerDetailLink,
        ]);
    echo '</div>';

    echo '<div class="c3">';
        echo $this->element('product/columns/column3', [
            'product' => $product,
        ]);
    echo '</div>';

echo '</div>';
