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

echo '<div class="product-wrapper">';

    echo '<div class="first-column">';
        $srcLargeImage = $this->Html->getProductImageSrc($product['Image']['id_image'], 'thickbox');
        $largeImageExists = preg_match('/de-default/', $srcLargeImage);
if (!$largeImageExists) {
    echo '<a class="lightbox" href="'.$srcLargeImage.'">';
}
            echo '<img src="' . $this->Html->getProductImageSrc($product['Image']['id_image'], 'home'). '" />';
if (!$largeImageExists) {
    echo '</a>';
}
if ($product['Product']['is_new']) {
    echo '<a href="/neue-produkte" class="image-badge btn btn-success" title="Neu">
                    <i class="fa fa-star gold"></i> Neu
                </a>';
}
    echo '</div>';

    echo '<div class="second-column">';

    echo '<div class="heading">';
        echo '<h4><a href="'.$this->Slug->getProductDetail($product['Product']['id_product'], $product['ProductLang']['name']).'">'.$product['ProductLang']['name'].'</a></h4>';
    echo '</div>';
    echo '<div class="sc"></div>';

if ($product['ProductLang']['description_short'] != '') {
    echo $product['ProductLang']['description_short'].'<br />';
}

if ($product['ProductLang']['description'] != '') {
    echo $this->Html->link(
        '<i class="fa"></i> Mehr anzeigen',
        'javascript:void(0);',
        array(
        'class' => 'toggle-link',
        'title' => 'Mehr Infos zu "'.$product['ProductLang']['name'].'" anzeigen',
        'escape' => false
        )
    );
    echo '<div class="toggle-content description">'.$product['ProductLang']['description'].'</div>';
}

    echo '<br />Hersteller: ';
    echo $this->Html->link(
        $product['Manufacturer']['name'],
        $this->Slug->getManufacturerDetail($product['Manufacturer']['id_manufacturer'], $product['Manufacturer']['name'])
    );


    if ($appAuth->isSuperadmin() || ($appAuth->isManufacturer() && $product['Manufacturer']['id_manufacturer'] == $appAuth->getManufacturerId())) {
        echo $this->Html->getJqueryUiIcon(
            $this->Html->image($this->Html->getFamFamFamPath('page_edit.png')),
            array(
                'title' => 'Produkt bearbeiten'
            ),
            $this->Slug->getProductAdmin(($appAuth->isSuperadmin() ? $product['Manufacturer']['id_manufacturer'] : null), $product['Product']['id_product'])
        );
    }

    echo '</div>';

    echo '<div class="third-column">';

    if (!empty($product['attributes'])) {
        // PRODUCT WITH ATTRIBUTES

        // 1) kick attributes if quantity = 0
        $hasCheckedAttribute = false;
        $i = 0;
        $preparedProductAttributes = array();
        foreach ($product['attributes'] as $attribute) {
            if ($attribute['StockAvailable']['quantity'] > 0) {
                $preparedProductAttributes[] = $attribute;
            }
            $i++;
        }

        // 2) try to define "default on" as checked radio button (if quantity is > 0)
        $i = 0;
        foreach ($preparedProductAttributes as $attribute) {
            $preparedProductAttributes[$i]['checked'] = false;
            if ($attribute['ProductAttributeShop']['default_on'] == 1) {
                $preparedProductAttributes[$i]['checked'] = true;
                $hasCheckedAttribute = true;
            }
            $i++;
        }

        // make first attribute checked if no attribute is checked
        // (usually if quantity of default attribute is 0)
        if (!$hasCheckedAttribute && !empty($preparedProductAttributes)) {
            $preparedProductAttributes[0]['checked'] = true;
        }

        // every attribute has quantity = 0
        if (empty($preparedProductAttributes)) {
            echo '<p>Derzeit leider nicht verfügbar.</p>';
        }

        // render remaining attributes (with attribute "checked")
        foreach ($preparedProductAttributes as $attribute) {
            $entityClasses = array('entity-wrapper');
            if ($attribute['checked']) {
                $entityClasses[] = 'active';
            }
            echo '<div class="'.join(' ', $entityClasses).'" id="entity-wrapper-'.$attribute['ProductAttribute']['id_product_attribute'].'">';
            if (! Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->loggedIn()) {
                echo '<div class="line">';
                echo '<div class="price">' . $this->Html->formatAsEuro($attribute['ProductAttributeShop']['gross_price']). '</div>';
                if (!empty($attribute['DepositProductAttribute']['deposit'])) {
                    echo '<div class="deposit">+ <b>'. $this->Html->formatAsEuro($attribute['DepositProductAttribute']['deposit']) . '</b> Pfand</div>';
                }
                echo '<div class="tax">'. $this->Html->formatAsEuro($attribute['ProductAttributeShop']['tax']) . '</div>';
                echo '</div>';
                echo $this->element('product/hiddenProductIdField', array('productId' => $product['Product']['id_product'] . '-' . $attribute['ProductAttribute']['id_product_attribute']));
                echo $this->element('product/amountWrapper', array('stockAvailable' => $attribute['StockAvailable']['quantity']));
                echo $this->element('product/cartButton', array('productId' => $product['Product']['id_product'] . '-' . $attribute['ProductAttribute']['id_product_attribute'], 'stockAvailable' => $attribute['StockAvailable']['quantity']));
                echo $this->element('product/notAvailableInfo', array('stockAvailable' => $attribute['StockAvailable']['quantity']));
            }
            echo '</div>';
        }

        // radio buttons for changing attributes
        foreach ($preparedProductAttributes as $attribute) {
            echo '<div class="radio">
                           <label class="attribute-button" id="'.'attribute-button-'.$attribute['ProductAttribute']['id_product_attribute'].'">
                               <input type="radio" name="product-'.$product['Product']['id_product'].'" '.($attribute['checked'] ? 'checked' : '').'>'.
                           $attribute['ProductAttributeCombination']['Attribute']['name'].'
                           </label>
                       </div>';
        }
    } else {
        // PRODUCT WITHOUT ATTRIBUTES
        echo '<div class="entity-wrapper active">';
        if (! Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->loggedIn()) {
            echo '<div class="line">';
            echo '<div class="price">' . $this->Html->formatAsEuro($product['Product']['gross_price']) . '</div>';
            if ($product['Deposit']['deposit']) {
                echo '<div class="deposit">+ <b>' . $this->Html->formatAsEuro($product['Deposit']['deposit']).'</b> Pfand</div>';
            }
                echo '</div>';
                echo '<div class="tax">'. $this->Html->formatAsEuro($product['Product']['tax']) . '</div>';
                echo $this->element('product/hiddenProductIdField', array('productId' => $product['Product']['id_product']));
                echo $this->element('product/amountWrapper', array('stockAvailable' => $product['StockAvailable']['quantity']));
                echo $this->element('product/cartButton', array('productId' => $product['Product']['id_product'], 'stockAvailable' => $product['StockAvailable']['quantity']));
                echo $this->element('product/notAvailableInfo', array('stockAvailable' => $product['StockAvailable']['quantity']));
        }
        echo '</div>';

        if ($product['ProductLang']['unity'] != '') {
            echo '<div class="unity">Einheit: <span class="value">' . $product['ProductLang']['unity'].'</span></div>';
        }
    }

    echo '</div>';

    echo '</div>';
