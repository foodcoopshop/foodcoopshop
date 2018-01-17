<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$this->element('addScript', ['script' =>
    Configure::read('AppConfig.jsNamespace').".Helper.init();".
    Configure::read('AppConfig.jsNamespace').".AppFeatherlight.addLightboxToCkeditorImages('.product-wrapper .toggle-content.description img');".
    Configure::read('AppConfig.jsNamespace').".AppFeatherlight.initLightboxForImages('.product-wrapper a.lightbox, .manufacturer-infos a.lightbox');".
    Configure::read('AppConfig.jsNamespace').".Helper.initProductAttributesButtons();".
    Configure::read('AppConfig.jsNamespace').".Helper.bindToggleLinks();".
    Configure::read('AppConfig.jsNamespace').".Cart.initAddToCartButton();".
    Configure::read('AppConfig.jsNamespace').".Cart.initRemoveFromCartLinks();"
]);
?>

<h1><?php echo $manufacturer['Manufacturer']['name']; ?>

<?php
if (Configure::read('AppConfig.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
    echo '<span>'.count($manufacturer['Products']) . ' gefunden</span>';
}
?>
</h1>

<div class="manufacturer-infos">
    <?php
        $srcLargeImage = $this->Html->getManufacturerImageSrc($manufacturer['Manufacturer']['id_manufacturer'], 'large');
        $largeImageExists = preg_match('/de-default/', $srcLargeImage);
    if (!$largeImageExists) {
        echo '<a class="lightbox" href="'.$srcLargeImage.'">';
        echo '<img class="manufacturer-logo" src="' . $this->Html->getManufacturerImageSrc($manufacturer['Manufacturer']['id_manufacturer'], 'medium'). '" />';
        echo '</a>';
    }

        echo $manufacturer['Manufacturer']['description'];

    if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
        if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
            $manufacturerEditSlug = $this->Slug->getManufacturerEdit($manufacturer['Manufacturer']['id_manufacturer']);
        }
        if ($appAuth->isManufacturer() && $appAuth->getManufacturerId() == $manufacturer['Manufacturer']['id_manufacturer']) {
            $manufacturerEditSlug = $this->Slug->getManufacturerProfile();
        }
    }

    if (isset($manufacturerEditSlug)) {
        echo $this->Html->getJqueryUiIcon(
            $this->Html->image($this->Html->getFamFamFamPath('page_edit.png')),
            [
            'title' => 'Bearbeiten'
            ],
            $manufacturerEditSlug
        );
    }
    ?>
    
</div>

<?php
if (!empty($blogPosts)) {
    echo '<h2>Aktuelles von '.$manufacturer['Manufacturer']['name'].'</a><a style="float: right;margin-top: 5px;" class="btn btn-default" href="'.$this->Slug->getManufacturerBlogList($manufacturer['Manufacturer']['id_manufacturer'], $manufacturer['Manufacturer']['name']).'">Zum Blog von '.$manufacturer['Manufacturer']['name'].'</a></h2><div class="sc"></div>';
    echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts
    ]);
}

$manufacturerHolidayString = $this->Html->getManufacturerHolidayString($manufacturer['Manufacturer']['holiday_from'], $manufacturer['Manufacturer']['holiday_to'], $manufacturer[0]['IsHolidayActive'], true, $manufacturer['Manufacturer']['name']);
if ($manufacturerHolidayString != '') {
    echo '<h2 class="info">'.$manufacturerHolidayString.'</h2>';
}

if (!empty($manufacturer['Products'])) {
    foreach ($manufacturer['Products'] as $product) {
        echo $this->element('product/product', ['product' => $product]);
    }
}

    echo '<div class="imprint">';
        echo '<h2>Impressum</h2>';
        echo $this->Html->getManufacturerImprint($manufacturer, 'html', false);
    echo '</div>';
?>
