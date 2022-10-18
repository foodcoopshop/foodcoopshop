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
    Configure::read('app.jsNamespace').".Helper.addPrevAndNextManufacturerLinks();".
    Configure::read('app.jsNamespace').".Helper.initTooltip('.ew .price');".
    Configure::read('app.jsNamespace').".ModalImage.addLightboxToWysiwygEditorImages('.pw .toggle-content.description img');".
    Configure::read('app.jsNamespace').".ModalImage.init('.pw a.open-with-modal, .manufacturer-infos a.open-with-modal');".
    Configure::read('app.jsNamespace').".Helper.initProductAttributesButtons();".
    Configure::read('app.jsNamespace').".Helper.bindToggleLinks();".
    Configure::read('app.jsNamespace').".Helper.initAmountSwitcher();".
    Configure::read('app.jsNamespace').".Cart.initAddToCartButton();".
    Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();".
    Configure::read('app.jsNamespace').".Helper.setFutureOrderDetails('".addslashes(json_encode($appAuth->getFutureOrderDetails()))."');"
]);
?>

<h1><?php echo $manufacturer->name; ?>

<?php
if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
    echo '<span>'.count($manufacturer['Products']) . ' ' . __('found') . '</span>';
}
?>
</h1>

<div class="manufacturer-infos">
    <?php
        $srcLargeImage = $this->Html->getManufacturerImageSrc($manufacturer->id_manufacturer, 'large');
        $largeImageExists = $this->Html->largeImageExists($srcLargeImage);
        if ($largeImageExists) {
            echo '<a class="open-with-modal" href="javascript:void(0);" data-modal-title="' . h($manufacturer->name) . '" data-modal-image="'.$srcLargeImage.'">';
            echo '<img class="manufacturer-logo" src="' . $this->Html->getManufacturerImageSrc($manufacturer->id_manufacturer, 'medium'). '" />';
            echo '</a>';
        }

        echo $manufacturer->description;

    if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
        if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
            $manufacturerEditSlug = $this->Slug->getManufacturerEdit($manufacturer->id_manufacturer);
        }
        if ($appAuth->isManufacturer() && $appAuth->getManufacturerId() == $manufacturer->id_manufacturer) {
            $manufacturerEditSlug = $this->Slug->getManufacturerProfile();
        }
    }

    if (isset($manufacturerEditSlug)) {
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt"></i>',
            $manufacturerEditSlug,
            [
                'class' => 'btn btn-outline-light edit-shortcut-button',
                'title' => __('Edit'),
                'escape' => false
            ]
        );
    }
    ?>

</div>

<?php
if (!empty($blogPosts) && $blogPosts->count() > 0) {
    echo '<div class="sc"></div>';
    echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts
    ]);
}

if (!$appAuth->isOrderForDifferentCustomerMode() && !$appAuth->isSelfServiceModeByUrl()) {
    $manufacturerNoDeliveryDaysString = $this->Html->getManufacturerNoDeliveryDaysString($manufacturer, true);
    if ($manufacturerNoDeliveryDaysString != '') {
        echo '<h2 class="info">'.$manufacturerNoDeliveryDaysString.'</h2>';
    }
}

echo $this->element('stockProductInListInfo');

if (!empty($manufacturer['Products'])) {
    foreach ($manufacturer['Products'] as $product) {
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
    }
}

if (Configure::read('app.showManufacturerImprint')) {
    echo '<div class="imprint">';
        echo '<h2>'.__('Imprint').'</h2>';
        echo $this->Html->getManufacturerImprint($manufacturer, 'html', false);
        if (!empty($manufacturer->modified)) {
            echo '<p><i>';
            echo __('Modified_on') . ' ' . $manufacturer->modified->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'));
            echo '</i></p>';
        }
    echo '</div>';
}
?>
