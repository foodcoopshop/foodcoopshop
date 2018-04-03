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

use Cake\Core\Configure;

echo '<div class="product-wrapper">';

    echo '<div class="first-column">';
        $srcLargeImage = $this->Html->getProductImageSrc($product['id_image'], 'thickbox');
        $largeImageExists = preg_match('/de-default/', $srcLargeImage);
if (!$largeImageExists) {
    echo '<a class="lightbox" href="'.$srcLargeImage.'">';
}
echo '<img src="' . $this->Html->getProductImageSrc($product['id_image'], 'home'). '" />';
if (!$largeImageExists) {
    echo '</a>';
}
if ($product['is_new']) {
    echo '<a href="/neue-produkte" class="image-badge btn btn-success" title="Neu">
                    <i class="fa fa-star gold"></i> Neu
                </a>';
}
    echo '</div>';

    echo '<div class="second-column">';

    echo '<div class="heading">';
        echo '<h4><a href="'.$this->Slug->getProductDetail($product['id_product'], $product['name']).'">'.$product['name'].'</a></h4>';
    echo '</div>';
    echo '<div class="sc"></div>';

if ($product['description_short'] != '') {
    echo $product['description_short'].'<br />';
}

if ($product['description'] != '') {
    echo $this->Html->link(
        '<i class="fa"></i> Mehr anzeigen',
        'javascript:void(0);',
        [
        'class' => 'toggle-link',
        'title' => 'Mehr Infos zu "'.$product['name'].'" anzeigen',
        'escape' => false
        ]
    );
    echo '<div class="toggle-content description">'.$product['description'].'</div>';
}

    echo '<br />Hersteller: ';
    echo $this->Html->link(
        $product['ManufacturersName'],
        $this->Slug->getManufacturerDetail($product['id_manufacturer'], $product['ManufacturersName'])
    );


    if ($appAuth->isSuperadmin() || ($appAuth->isManufacturer() && $product['id_manufacturer'] == $appAuth->getManufacturerId())) {
        echo $this->Html->getJqueryUiIcon(
            $this->Html->image($this->Html->getFamFamFamPath('page_edit.png')),
            [
                'title' => 'Produkt bearbeiten'
            ],
            $this->Slug->getProductAdmin(($appAuth->isSuperadmin() ? $product['id_manufacturer'] : null), $product['id_product'])
        );
    }

    echo '</div>';

    echo '<div class="third-column">';

    if (!empty($product['attributes'])) {
        // PRODUCT WITH ATTRIBUTES

        // 1) kick attributes if quantity = 0
        $hasCheckedAttribute = false;
        $i = 0;
        $preparedProductAttributes = [];
        foreach ($product['attributes'] as $attribute) {
            if ($attribute['StockAvailables']['quantity'] > 0) {
                $preparedProductAttributes[] = $attribute;
            }
            $i++;
        }

        // 2) try to define "default on" as checked radio button (if quantity is > 0)
        $i = 0;
        foreach ($preparedProductAttributes as $attribute) {
            $preparedProductAttributes[$i]['checked'] = false;
            if ($attribute['ProductAttributeShops']['default_on'] == 1) {
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
            $entityClasses = ['entity-wrapper'];
            if ($attribute['checked']) {
                $entityClasses[] = 'active';
            }
            echo '<div class="'.join(' ', $entityClasses).'" id="entity-wrapper-'.$attribute['ProductAttributes']['id_product_attribute'].'">';
            if (! Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
                echo '<div class="line">';
                echo '<div class="price">' . $this->Html->formatAsEuro($attribute['ProductAttributeShops']['gross_price']). '</div>';
                if (!empty($attribute['DepositProductAttributes']['deposit'])) {
                    echo '<div class="deposit">+ <b>'. $this->Html->formatAsEuro($attribute['DepositProductAttributes']['deposit']) . '</b> Pfand</div>';
                }
                if (!empty($attribute['timebased_currency_money_incl'])) {
                    echo $this->element('timebasedCurrency/addProductInfo', [
                        'wrapperTag' => 'div',
                        'class' => 'timebased-currency-product-info',
                        'money' => $attribute['timebased_currency_money_incl'],
                        'seconds' => $attribute['timebased_currency_seconds'],
                        'labelPrefix' => 'davon ' . $product['timebased_currency_max_percentage'] . '% '
                    ]);
                }
                echo '<div class="tax">'. $this->Html->formatAsEuro($attribute['ProductAttributeShops']['tax']) . '</div>';
                echo '</div>';
                echo $this->element('product/hiddenProductIdField', ['productId' => $product['id_product'] . '-' . $attribute['ProductAttributes']['id_product_attribute']]);
                echo $this->element('product/amountWrapper', ['stockAvailable' => $attribute['StockAvailables']['quantity']]);
                echo $this->element('product/cartButton', ['productId' => $product['id_product'] . '-' . $attribute['ProductAttributes']['id_product_attribute'], 'stockAvailable' => $attribute['StockAvailables']['quantity']]);
                echo $this->element('product/notAvailableInfo', ['stockAvailable' => $attribute['StockAvailables']['quantity']]);
            }
            echo '</div>';
        }

        // radio buttons for changing attributes
        foreach ($preparedProductAttributes as $attribute) {
            echo '<div class="radio">
                           <label class="attribute-button" id="'.'attribute-button-'.$attribute['ProductAttributes']['id_product_attribute'].'">
                               <input type="radio" name="product-'.$product['id_product'].'" '.($attribute['checked'] ? 'checked' : '').'>'.
                               $attribute['ProductAttributeCombinations']['Attributes']['name'].'
                           </label>
                       </div>';
        }
    } else {
        // PRODUCT WITHOUT ATTRIBUTES
        echo '<div class="entity-wrapper active">';
        if (! Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
            echo '<div class="line">';
            echo '<div class="price">' . $this->Html->formatAsEuro($product['gross_price']) . '</div>';
                if ($product['deposit']) {
                    echo '<div class="deposit">+ <b>' . $this->Html->formatAsEuro($product['deposit']).'</b> Pfand</div>';
                }
                echo '</div>';
                if (!empty($product['timebased_currency_money_incl'])) {
                    echo $this->element('timebasedCurrency/addProductInfo', [
                        'wrapperTag' => 'div',
                        'class' => 'timebased-currency-product-info',
                        'money' => $product['timebased_currency_money_incl'],
                        'seconds' => $product['timebased_currency_seconds'],
                        'labelPrefix' => 'davon ' . $product['timebased_currency_max_percentage'] . '% '
                    ]);
                }
                echo '<div class="tax">'. $this->Html->formatAsEuro($product['tax']) . '</div>';
                echo $this->element('product/hiddenProductIdField', ['productId' => $product['id_product']]);
                echo $this->element('product/amountWrapper', ['stockAvailable' => $product['quantity']]);
                echo $this->element('product/cartButton', ['productId' => $product['id_product'], 'stockAvailable' => $product['quantity']]);
                echo $this->element('product/notAvailableInfo', ['stockAvailable' => $product['quantity']]);
        }
        echo '</div>';

        if ($product['unity'] != '') {
            echo '<div class="unity">Einheit: <span class="value">' . $product['unity'].'</span></div>';
        }
    }

    echo '</div>';

    echo '</div>';
