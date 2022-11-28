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
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".Helper.initTooltip('.ew .price');".
    Configure::read('app.jsNamespace').".ModalImage.addLightboxToWysiwygEditorImages('.pw .toggle-content.description img');".
    Configure::read('app.jsNamespace').".ModalImage.init('.pw a.open-with-modal');".
    Configure::read('app.jsNamespace').".Helper.bindToggleLinks(true);".
    Configure::read('app.jsNamespace').".Helper.selectMainMenuFrontend('".__('Products')."');".
    Configure::read('app.jsNamespace').".Helper.initProductAttributesButtons();".
    Configure::read('app.jsNamespace').".Cart.initAddToCartButton();".
    Configure::read('app.jsNamespace').".Helper.initAmountSwitcher();".
    Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();".
    Configure::read('app.jsNamespace').".Helper.setFutureOrderDetails('".addslashes(json_encode($appAuth->getFutureOrderDetails()))."');"
]);

if (Configure::read('app.showOrderedProductsTotalAmountInCatalog')) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace') . ".Helper.initTooltip('.ordered-products-total-amount');"
    ]);
}

?>

<h1><?php echo $title_for_layout; ?></h1>

<?php
    echo $this->element('catalog/product', [
        'product' => $product,
        'showProductDetailLink' => true,
        'showManufacturerDetailLink' => true,
        'showIsNewBadgeAsLink' => true
    ],
    [
        'cache' => [
            'key' => $this->Html->buildElementProductCacheKey($product, $appAuth),
        ],
    ]
    );
?>
