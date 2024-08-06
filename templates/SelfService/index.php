<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;
use Cake\Routing\Router;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".SelfService.init();".
    Configure::read('app.jsNamespace').".ModalImage.addLightboxToWysiwygEditorImages('.pw .toggle-content.description img');".
    Configure::read('app.jsNamespace').".ModalImage.init('.pw a.open-with-modal');".
    Configure::read('app.jsNamespace').".Helper.bindToggleLinks();".
    Configure::read('app.jsNamespace').".Helper.initProductAttributesButtons();".
    Configure::read('app.jsNamespace').".Helper.initAmountSwitcher();".
    Configure::read('app.jsNamespace').".Cart.initAddToCartButton();".
    Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();".
    Configure::read('app.jsNamespace').".ModalText.init('.input.checkbox label a.open-with-modal');".
    Configure::read('app.jsNamespace').".Helper.setFutureOrderDetails('".addslashes(json_encode($identity->getFutureOrderDetails()))."');"
]);

if (!Configure::read('app.selfServiceShowConfirmDialogOnSubmit')){
    $this->element('addScript', ['script' => Configure::read('app.jsNamespace').".Cart.initCartFinish();"
    ]);
}
else{
    $this->element('addScript', ['script' => Configure::read('app.jsNamespace').".ModalSelfServiceConfirmDialog.init();"
    ]);      
}

if (!$isMobile && !$orderCustomerService->isOrderForDifferentCustomerMode() && Configure::read('app.selfServiceModeAutoLogoutDesktopEnabled')) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".SelfService.initAutoLogout();"
    ]);
}

if ($orderCustomerService->isSelfServiceModeByUrl()) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Calculator.init('.quantity-in-units-input-field-wrapper');"
    ]);
}

echo $this->element('autoPrintInvoice');

if ($isMobile) {
    if ($identity->use_camera_for_barcode_scanning) {
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace') . ".SelfService.initMobileBarcodeScanningWithCamera('.sb-toggle-left', '#content .header', " . Configure::read('app.jsNamespace') . ".SelfService.mobileScannerCallbackForProducts);".
            Configure::read('app.jsNamespace') . ".Mobile.showSelfServiceCart();"
        ]);
    } else {
        $js = Configure::read('app.jsNamespace').".Mobile.hideSelfServiceCart();";
        if (!empty($_POST)) {
            $js = Configure::read('app.jsNamespace').".Mobile.showSelfServiceCart();";
        }
        $this->element('addScript', ['script' => $js]);
    }
}

if ($this->request->getSession()->read('highlightedProductId')) {

    if ($isMobile && $identity->use_camera_for_barcode_scanning) {
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".SelfService.initHighlightedProductIdForMobileBarcodeScanning('" . $this->request->getSession()->read('highlightedProductId') . "');"
        ]);
    } else {
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".SelfService.initHighlightedProductId('" . $this->request->getSession()->read('highlightedProductId') . "');"
        ]);
    }
    $this->request->getSession()->delete('highlightedProductId');
}

?>

<div class="header">
    <h2>
    <?php
        if ($orderCustomerService->isOrderForDifferentCustomerMode()) {
            echo __('Stock_products');
        } else {
            echo __('Self_service');
        }
    ?>
    </h2>
    <?php if (!$isMobile) { ?>
        <h1><span><?php echo $totalProductCount; ?> <?php echo __('found'); ?></span></h1>
        <h4><?php echo __('Scan_product');?></h4>
    <?php } ?>
    <?php echo $this->element('productSearch', [
        'action' => __('route_self_service'),
        'placeholder' => __('Search:_name_id_or_barcode'),
        'resetSearchUrl' => $this->Slug->getSelfService(),
        'includeCategoriesDropdown' => true
    ]); ?>
</div>

<div id="products">

<?php

    if (count($products) == 0) {
        echo '<p class="info">';
        if (!isset($keyword) && $categoryId == 0) {
            echo __('Please_search_or_scan_a_product_or_chose_a_category.');
        } else {
            ?></br><?php
            echo __('No_products_found.');
        }
        echo '</p>';
    }

    foreach ($products as $product) {
        echo $this->element('catalog/product', [
            'product' => $product,
            'showProductDetailLink' => false,
            'showManufacturerDetailLink' => false,
            'showIsNewBadgeAsLink' => false
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
        'categoryId' => $categoryId ?? 0,
    ]);

?>
</div>

<div class="right-box">
    <?php echo $this->element('cart', [
        'selfServiceModeEnabled' => true,
        'showLoadLastOrderDetailsDropdown' => false,
        'showCartDetailButton' => false,
        'showFutureOrderDetails' => false,
        'icon' => 'fa-shopping-bag',
        'name' => __('Shopping_bag'),
        'docsLink' => $this->Html->getDocsUrl(__('docs_route_self_service')),
        'cartButtonIcon' => 'fa-shopping-bag',
        'cartEmptyMessage' => __('Your_shopping_bag_is_empty.')
    ]); ?>
    <?php
        echo $this->Form->create($cart, [
            'class' => 'fcs-form',
            'id' => 'SelfServiceForm',
            'novalidate' => 'novalidate',
            'url' => $this->Slug->getSelfService()
        ]);
        if (!$orderCustomerService->isOrderForDifferentCustomerMode() && !Configure::read('app.selfServiceShowConfirmDialogOnSubmit')){
            echo $this->element('cart/generalTermsAndConditionsCheckbox');
            echo $this->element('cart/cancellationTermsCheckbox');
        }
        echo $this->element('selfService/paymentType');
    if (Configure::read('app.selfServiceShowConfirmDialogOnSubmit')){
        ?>
        <button type="button" class="btn btn-success btn-order btn-order-self-service">
           <i class="fa-fw fas fa-check"></i> <?php echo __('Finish_pickup'); ?>
        </button>
        <?php   
    }
    else{
        ?>
        <button type="submit" class="btn btn-success btn-order btn-order-self-service">
           <i class="fa-fw fas fa-check"></i> <?php echo __('Finish_pickup'); ?>
        </button>
        <?php   
    }
    echo $this->Form->end(); ?>
    <?php if ($isMobile && !$identity->use_camera_for_barcode_scanning) { ?>
        <a class="btn btn-outline-light continue-shopping" href="<?php echo Router::reverse($this->request, true); ?>"><?php echo __('Continue_shopping?')?></a>
    <?php } ?>
</div>