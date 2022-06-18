<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$productImageData = $this->Html->getProductImageSrcWithManufacturerImageFallback(
    !empty($product->image) ? $product->image->id_image : 0,
    $product->id_manufacturer,
);

if ($productImageData['productImageLargeExists']) {
    echo '<a class="open-with-modal" href=javascript:void(0); data-modal-title="' . h($product->name) . ', ' . $product->manufacturer->name . '" data-modal-image="'.$productImageData['productImageLargeSrc'].'">';
}

echo '<img class="lazyload" data-src="' . $productImageData['productImageSrc']. '" />';

if ($productImageData['productImageLargeExists']) {
    echo '</a>';
}

if ($product->is_new) {
    $isNewSrc = 'javascript:void(0);';
    if ($showIsNewBadgeAsLink) {
        $isNewSrc = $this->Slug->getNewProducts();
    }
    echo '<a href="'.$isNewSrc.'" class="image-badge btn btn-outline-light" title="'.__('New').'">';
        echo '<i class="fas fa-star gold"></i> '.__('New');
    echo '</a>';
}
