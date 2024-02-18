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

use App\Services\CatalogService;
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".Helper.addPrevAndNextCategoryLinks();".
    Configure::read('app.jsNamespace').".Helper.initTooltip('.ew .price, .c3 .is-stock-product');".
    Configure::read('app.jsNamespace').".ModalImage.addLightboxToWysiwygEditorImages('.pw .toggle-content.description img');".
    Configure::read('app.jsNamespace').".ModalImage.init('.pw a.open-with-modal');".
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

if (!empty($blogPosts) && $blogPosts->count() > 0) {
    echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts
    ]);
}
?>

<h1 class="middle-line">
    <span class="left"><?php echo $title_for_layout; ?></span>
    <span class="middle"></span>
    <span class="right">
        <?php
            echo $this->element('catalog/paginatedProductsHeader', [
                'page' => $page,
                'pagesCount' => $pagesCount,
                'totalProductCount' => $totalProductCount,
                'products' => $products,
            ]);
        ?>
    </span>
</h1>

<?php

if (!empty($category)) {
    $categoryImgSrc = $this->Html->getCategoryImageSrc($category->id_category, 'default');
    if ($categoryImgSrc !== false) {
        echo '<div class="img-wrapper">';
            echo '<img src="' . $categoryImgSrc. '" />';
        echo '</div>';
    }
    if ($category->description != '') {
        echo '<div class="description-wrapper">';
            echo $category->description;
        echo '</div>';
    }
}

echo $this->element('stockProductInListInfo');

foreach ($products as $product) {
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

echo $this->element('catalog/pagination', [
    'page' => $page,
    'pagesCount' => $pagesCount,
    'keyword' => $keyword ?? '',
]);

?>
