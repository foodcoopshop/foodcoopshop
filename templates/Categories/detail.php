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

use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".Helper.addPrevAndNextCategoryLinks();".
    Configure::read('app.jsNamespace').".ModalImage.addLightboxToWysiwygEditorImages('.product-wrapper .toggle-content.description img');".
    Configure::read('app.jsNamespace').".ModalImage.init('.product-wrapper a.open-with-modal');".
    Configure::read('app.jsNamespace').".Helper.bindToggleLinks();".
    Configure::read('app.jsNamespace').".Helper.initAmountSwitcher();".
    Configure::read('app.jsNamespace').".Helper.initProductAttributesButtons();".
    Configure::read('app.jsNamespace').".Cart.initAddToCartButton();".
    Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();"
]);
echo $this->element('timebasedCurrency/addProductTooltip', ['selectorClass' => 'timebased-currency-product-info']);
?>

<?php
if (!empty($blogPosts) && $blogPosts->count() > 0) {
    echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts
    ]);
}
?>

<h1 class="middle-line">
    <span class="left"><?php echo $title_for_layout; ?></span>
    <span class="middle"></span>
    <span class="right"><?php echo count($products); ?> <?php echo __('found'); ?></span>
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
    echo $this->element('product/product', [
        'product' => $product,
        'showProductDetailLink' => true,
        'showManufacturerDetailLink' => true,
        'showIsNewBadgeAsLink' => true
    ]);
}

?>
