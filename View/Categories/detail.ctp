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

$this->element('addScript', array('script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".AppFeatherlight.addLightboxToCkeditorImages('.product-wrapper .toggle-content.description img');".
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForImages('.product-wrapper a.lightbox');".
    Configure::read('app.jsNamespace').".Helper.bindToggleLinks();".
    Configure::read('app.jsNamespace').".Helper.initProductAttributesButtons();".
    Configure::read('app.jsNamespace').".Cart.initAddToCartButton();".
    Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();"
));
?>

<?php
if (!empty($blogPosts)) {
    echo '<h2><a href="'.$this->Slug->getBlogList().'">Aktuelles</a></h2>';
    echo $this->element('blogPosts', array(
    'blogPosts' => $blogPosts
    ));
}
?>

<h1><?php echo $title_for_layout; ?> <span><?php echo count($products); ?> gefunden</span></h1>

<?php

if (!empty($category)) {
    $categoryImgSrc = $this->Html->getCategoryImageSrc($category['Category']['id_category'], 'default');
    if ($categoryImgSrc !== false) {
        echo '<div class="img-wrapper">';
            echo '<img src="' . $categoryImgSrc. '" />';
        echo '</div>';
    }
    if ($category['CategoryLang']['description'] != '') {
        echo '<div class="description-wrapper">';
            echo $category['CategoryLang']['description'];
        echo '</div>';
    }
}

foreach ($products as $product) {
    echo $this->element('product/product', array('product' => $product));
}

?>
