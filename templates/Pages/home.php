<?php

use Cake\Core\Configure;

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

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);

echo $this->element('acceptUpdatedTermsOfUseForm');

if (!empty($blogPosts) && $blogPosts->count() > 0) {
    echo '<h1>'.__('News').'</h1>';
}
echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts,
    'useCarousel' => false
]);

if (!empty($newProducts)) {
    
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".AppFeatherlight.addLightboxToCkeditorImages('.product-wrapper .toggle-content.description img');".
        Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForImages('.product-wrapper a.lightbox');".
        Configure::read('app.jsNamespace').".Helper.bindToggleLinks();".
        Configure::read('app.jsNamespace').".Helper.initAmountSwitcher();".
        Configure::read('app.jsNamespace').".Helper.initProductAttributesButtons();".
        Configure::read('app.jsNamespace').".Cart.initAddToCartButton();".
        Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();"
    ]);
    
    echo $this->element('timebasedCurrency/addProductTooltip', ['selectorClass' => 'timebased-currency-product-info']);
    
    $isFirstElement = empty($blogPosts) || $blogPosts->count() == 0;
    echo '<h1 style="float:left;' . (!$isFirstElement ? 'margin-top:20px;' : '') . '">';
        echo __('New_products');
    echo '</h2>';
    
    foreach ($newProducts as $product) {
        echo $this->element('product/product', [
            'product' => $product,
            'showProductDetailLink' => true,
            'showManufacturerDetailLink' => true,
            'showIsNewBadgeAsLink' => true
        ]);
    }
    
}

if (Configure::read('appDb.FCS_FOODCOOPS_MAP_ENABLED')) {
    echo $this->element('foodCoopShopInstancesMap', [
        'isFirstElement' => (empty($blogPosts) || $blogPosts->count() == 0) && empty($newProducts)
    ]);
}
?>
