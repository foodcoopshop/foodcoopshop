<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;
use Cake\Routing\Router;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".SelfService.init();".
    Configure::read('app.jsNamespace').".ModalImage.addLightboxToWysiwygEditorImages('.product-wrapper .toggle-content.description img');".
    Configure::read('app.jsNamespace').".ModalImage.init('.product-wrapper a.open-with-modal');".
    Configure::read('app.jsNamespace').".Helper.bindToggleLinks();".
    Configure::read('app.jsNamespace').".Helper.initProductAttributesButtons();".
    Configure::read('app.jsNamespace').".Helper.initAmountSwitcher();".
    Configure::read('app.jsNamespace').".Cart.initAddToCartButton();".
    Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();".
    Configure::read('app.jsNamespace').".ModalText.init('.input.checkbox label a.open-with-modal');".
    Configure::read('app.jsNamespace').".Cart.initCartFinish();"
]);

if (!$isMobile && Configure::read('app.selfServiceModeAutoLogoutDesktopEnabled')) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".SelfService.initAutoLogout();"
    ]);
}

echo $this->element('timebasedCurrency/addProductTooltip', ['selectorClass' => 'timebased-currency-product-info']);

if ($isMobile) {
    if ($appAuth->user('use_camera_for_barcode_scanning')) {
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

    if ($isMobile && $appAuth->user('use_camera_for_barcode_scanning')) {
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
    <h2><?php echo __('Self_service_mode'); ?></h2>
    <?php if (!$isMobile) { ?>
        <h1><span><?php echo count($products); ?> <?php echo __('found'); ?></span></h1>
    <?php } ?>
    <?php echo $this->element('productSearch', [
        'action' => __('route_self_service'),
        'placeholder' => __('Search:_name_id_or_barcode'),
        'resetSearchUrl' => $this->Slug->getSelfService(),
        'includeCategoriesDropdown' => true
    ]); ?>
    <hr />
</div>

<div id="products">
    <?php
    foreach ($products as $product) {
        echo $this->element('product/product', [
            'product' => $product,
            'showProductDetailLink' => false,
            'showManufacturerDetailLink' => false,
            'showIsNewBadgeAsLink' => false
        ]);
    }
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
        echo $this->element('cart/generalTermsAndConditionsCheckbox');
        echo $this->element('cart/cancellationTermsCheckbox');
    ?>
    <button type="submit" class="btn btn-success btn-order">
        <i class="fas fa-check"></i> <?php echo __('Finish_pickup'); ?>
    </button>
    <?php echo $this->Form->end(); ?>
    <?php if ($isMobile && !$appAuth->user('use_camera_for_barcode_scanning')) { ?>
        <a class="btn btn-outline-light continue-shopping" href="<?php echo Router::reverse($this->request, true); ?>"><?php echo __('Continue_shopping?')?></a>
    <?php } ?>
</div>