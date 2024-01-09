<?php
declare(strict_types=1);

use Cake\Core\Configure;

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

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);

echo $this->element('acceptUpdatedTermsOfUseForm');

if (Configure::read('appDb.FCS_HOME_TEXT') != '') {
    echo '<div class="home-text">';
        echo Configure::read('appDb.FCS_HOME_TEXT');
    echo '</div>';
    echo '<hr />';
}
if (!empty($blogPosts) && $blogPosts->count() > 0) {
    echo '<h1 class="news">'.__('News').'</h1>';
}
echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts,
    'useCarousel' => false
]);

if (!empty($newProducts)) {

    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".ModalImage.addLightboxToWysiwygEditorImages('.pw .toggle-content.description img');".
        Configure::read('app.jsNamespace').".ModalImage.init('.pw a.open-with-modal');".
        Configure::read('app.jsNamespace').".Helper.initTooltip('.ew .price, .c3 .is-stock-product');".
        Configure::read('app.jsNamespace').".Helper.bindToggleLinks();".
        Configure::read('app.jsNamespace').".Helper.initAmountSwitcher();".
        Configure::read('app.jsNamespace').".Helper.initProductAttributesButtons();".
        Configure::read('app.jsNamespace').".Cart.initAddToCartButton();".
        Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();"
    ]);

    if ($identity !== null) {
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace').".Helper.setFutureOrderDetails('".addslashes(json_encode($identity->getFutureOrderDetails()))."');"
        ]);
    }

    if (Configure::read('app.showOrderedProductsTotalAmountInCatalog')) {
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('.ordered-products-total-amount');"
        ]);
    }

    $isFirstElement = empty($blogPosts) || $blogPosts->count() == 0;
    echo '<h1 style="float:left;' . (!$isFirstElement ? 'margin-top:10px;' : '') . '">';
        echo __('New_products');
    echo '</h2>';

    foreach ($newProducts as $product) {
        echo $this->element('catalog/product', [
            'product' => $product,
            'showProductDetailLink' => true,
            'showManufacturerDetailLink' => true,
            'showIsNewBadgeAsLink' => true
        ],
        [
            'cache' => [
                'key' => $this->Html->buildElementProductCacheKey($product, $identity, $this->request),
            ],
        ]
        );
    }

}

if (Configure::read('appDb.FCS_FOODCOOPS_MAP_ENABLED')) {
    echo $this->element('foodCoopShopInstancesMap', [
        'isFirstElement' => (empty($blogPosts) || $blogPosts->count() == 0) && empty($newProducts)
    ]);
}
?>
