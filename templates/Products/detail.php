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
    Configure::read('app.jsNamespace').".ModalImage.addLightboxToWysiwygEditorImages('.product-wrapper .toggle-content.description img');".
    Configure::read('app.jsNamespace').".ModalImage.init('.product-wrapper a.open-with-modal');".
    Configure::read('app.jsNamespace').".Helper.bindToggleLinks(true);".
    Configure::read('app.jsNamespace').".Helper.selectMainMenuFrontend('".__('Products')."');".
    Configure::read('app.jsNamespace').".Helper.initProductAttributesButtons();".
    Configure::read('app.jsNamespace').".Cart.initAddToCartButton();".
    Configure::read('app.jsNamespace').".Helper.initAmountSwitcher();".
    Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();"
]);
echo $this->element('timebasedCurrency/addProductTooltip', ['selectorClass' => 'timebased-currency-product-info']);
?>

<h1><?php echo $title_for_layout; ?></h1>

<?php
    echo $this->element('product/product', [
        'product' => $product,
        'showProductDetailLink' => true,
        'showManufacturerDetailLink' => true,
        'showIsNewBadgeAsLink' => true
    ]);
?>
