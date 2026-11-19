<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) FoodCoopShop, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".Helper.initTooltip('.ew .price, .c3 .is-stock-product');".
    Configure::read('app.jsNamespace').".ModalImage.addLightboxToWysiwygEditorImages('.pw .toggle-content.description img');".
    Configure::read('app.jsNamespace').".ModalImage.init('.pw a.open-with-modal');".
    Configure::read('app.jsNamespace').".Helper.bindToggleLinks();".
    Configure::read('app.jsNamespace').".Helper.initAmountSwitcher();".
    Configure::read('app.jsNamespace').".Helper.initProductAttributesButtons();".
    Configure::read('app.jsNamespace').".Cart.initAddToCartButton();".
    Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();".
    Configure::read('app.jsNamespace').".ShoppingListPage.init(" . $shoppingList->id . ", " . json_encode($amounts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_FORCE_OBJECT) . ");"
]);

if ($identity !== null) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Helper.setFutureOrderDetails('".addslashes(json_encode($identity->getFutureOrderDetails()))."');"
    ]);
}
?>

<h1 class="middle-line shopping-list-heading">
    <span class="left">
        <?php echo h($shoppingList->name); ?>
    </span>
    <span class="middle"></span>
    <span class="right shopping-list-buttons">
        <?php
        if (!empty($amounts)) {
            echo $this->Form->create(null, [
                'url' => $this->Slug->getAddShoppingListToCart($shoppingList->id),
                'class' => 'btn-load-shopping-list-into-cart',
            ]);
            echo $this->Form->button('<i class="fas fa-cart-plus"></i> ' . __('Load_shopping_list_into_cart'), [
                'class' => 'btn btn-success',
                'escapeTitle' => false,
            ]);
            echo $this->Form->end();
        }
        ?>
        <a href="javascript:void(0);" class="btn btn-outline-light btn-edit-shopping-list" data-bs-toggle="modal" data-bs-target="#modal-shopping-list-edit" title="<?php echo h(__('Edit_shopping_list')); ?>">
            <i class="fas fa-pencil-alt"></i>
        </a>
    </span>
</h1>

<?php

// always rendered: is shown by javascript if the last product is removed
echo '<p class="shopping-list-empty' . (empty($amounts) ? '' : ' hide') . '">' . __('This_shopping_list_is_empty_add_products_with_the_heart.', ['<i class="fas fa-heart"></i>']) . '</p>';

if (!empty($unavailableProductIds)) {
    echo '<div class="shopping-list-unavailable-products">';
        echo '<p>' . __('The_following_products_of_this_shopping_list_cannot_be_ordered_at_the_moment:') . ' <b>' . join(', ', array_map('h', $unavailableProductNames)) . '</b></p>';
        echo $this->Form->create(null, [
            'url' => $this->Slug->getShoppingListRemoveUnavailableProducts($shoppingList->id),
        ]);
        foreach ($unavailableProductIds as $index => $unavailableProductId) {
            echo $this->Form->hidden('productIds.' . $index, ['value' => $unavailableProductId]);
        }
        echo $this->Form->button('<i class="fas fa-trash-alt"></i> ' . __('Remove_these_products_from_the_shopping_list'), [
            'class' => 'btn btn-outline-light btn-sm',
            'escapeTitle' => false,
        ]);
        echo $this->Form->end();
    echo '</div>';
}

echo $this->element('stockProductInListInfo');

echo '<div class="products-wrapper">';
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
echo '</div>';

echo $this->element('shoppingList/editModal', [
    'shoppingList' => $shoppingList,
]);
