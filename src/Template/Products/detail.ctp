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
    Configure::read('AppConfig.jsNamespace').".Helper.init();".
    Configure::read('AppConfig.jsNamespace').".AppFeatherlight.addLightboxToCkeditorImages('.product-wrapper .toggle-content.description img');".
    Configure::read('AppConfig.jsNamespace').".AppFeatherlight.initLightboxForImages('.product-wrapper a.lightbox');".
    Configure::read('AppConfig.jsNamespace').".Helper.bindToggleLinks(true);".
    Configure::read('AppConfig.jsNamespace').".Helper.selectMainMenuFrontend('Produkte');".
    Configure::read('AppConfig.jsNamespace').".Helper.initProductAttributesButtons();".
    Configure::read('AppConfig.jsNamespace').".Cart.initAddToCartButton();".
    Configure::read('AppConfig.jsNamespace').".Cart.initRemoveFromCartLinks();"
));
?>

<h1><?php echo $title_for_layout; ?></h1>

<?php
    echo $this->element('product/product', array('product' => $product));
?>

<?php
if (!empty($blogPosts)) {
    echo '<h2><a href="'.$this->Slug->getBlogList().'">Aktuelles</a></h2>';
    echo $this->element('blogPosts', array(
    'blogPosts' => $blogPosts
    ));
}
?>
