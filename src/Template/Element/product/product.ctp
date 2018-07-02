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

$showProductPrice = (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') && Configure::read('appDb.FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS')) || $appAuth->user();

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
    echo '<a href="/'.$this->Slug->getNewProducts().'" class="image-badge btn btn-success" title="'.__('New').'">
                    <i class="fa fa-star gold"></i> '.__('New').'
                </a>';
}
    echo '</div>';

    echo '<div class="second-column">';

    echo '<div class="heading">';
        echo '<h4><a class="product-name" href="'.$this->Slug->getProductDetail($product['id_product'], $product['name']).'">'.$product['name'].'</a></h4>';
    echo '</div>';
    echo '<div class="sc"></div>';

if ($product['description_short'] != '') {
    echo $product['description_short'].'<br />';
}

if ($product['description'] != '') {
    echo $this->Html->link(
        '<i class="fa"></i> '.__('Show_more'),
        'javascript:void(0);',
        [
        'class' => 'toggle-link',
        'title' => __('More_infos_to_product_{0}', [$product['name']]),
        'escape' => false
        ]
    );
    echo '<div class="toggle-content description">'.$product['description'].'</div>';
}

    echo '<br />'.__('Manufacturer').': ';
    echo $this->Html->link(
        $product['ManufacturersName'],
        $this->Slug->getManufacturerDetail($product['id_manufacturer'], $product['ManufacturersName'])
    );


    if ($appAuth->isSuperadmin() || ($appAuth->isManufacturer() && $product['id_manufacturer'] == $appAuth->getManufacturerId())) {
        echo $this->Html->getJqueryUiIcon(
            $this->Html->image($this->Html->getFamFamFamPath('page_edit.png')),
            [
                'title' => __('Edit_product')
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
            echo '<p>'.__('Currently_not_on_stock').'.</p>';
        }

        // render remaining attributes (with attribute "checked")
        foreach ($preparedProductAttributes as $attribute) {
            $entityClasses = ['entity-wrapper'];
            if ($attribute['checked']) {
                $entityClasses[] = 'active';
            }
            echo '<div class="'.join(' ', $entityClasses).'" id="entity-wrapper-'.$attribute['ProductAttributes']['id_product_attribute'].'">';
            if ($showProductPrice) {
                echo '<div class="line">';
                $priceHtml =  '<div class="price">' . $this->Number->formatAsCurrency($attribute['ProductAttributeShops']['gross_price']) . '</div>';
                $pricePerUnitInfoText = '';
                if ($attribute['Units']['price_per_unit_enabled']) {
                    $priceHtml = $this->PricePerUnit->getPricePerUnit($attribute['Units']['price_incl_per_unit'], $attribute['Units']['quantity_in_units'], $attribute['Units']['unit_amount']);
                    $pricePerUnitInfoText = $this->PricePerUnit->getPricePerUnitInfoText($attribute['Units']['price_incl_per_unit'], $attribute['Units']['unit_name'], $attribute['Units']['unit_amount']);
                }
                echo $priceHtml;
                if (!empty($attribute['DepositProductAttributes']['deposit'])) {
                    echo '<div class="deposit">+ <b>'. $this->Number->formatAsCurrency($attribute['DepositProductAttributes']['deposit']) . '</b> '.__('deposit').'</div>';
                }
                if (!$this->request->getSession()->check('Auth.instantOrderCustomer') && !empty($attribute['timebased_currency_money_incl'])) {
                    echo $this->element('timebasedCurrency/addProductInfo', [
                        'class' => 'timebased-currency-product-info',
                        'money' => $attribute['timebased_currency_money_incl'],
                        'seconds' => $attribute['timebased_currency_seconds'],
                        'labelPrefix' => __('from_which_{0}_%', [$product['timebased_currency_max_percentage']]) . ' '
                    ]);
                }
                echo '<div class="tax">'. $this->Number->formatAsCurrency($attribute['ProductAttributeShops']['tax']) . '</div>';
                echo '</div>';
            }
            if (! Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
                echo $this->element('product/hiddenProductIdField', ['productId' => $product['id_product'] . '-' . $attribute['ProductAttributes']['id_product_attribute']]);
                echo $this->element('product/amountWrapper', ['stockAvailable' => $attribute['StockAvailables']['quantity']]);
                echo $this->element('product/cartButton', ['productId' => $product['id_product'] . '-' . $attribute['ProductAttributes']['id_product_attribute'], 'stockAvailable' => $attribute['StockAvailables']['quantity']]);
                echo $this->element('product/notAvailableInfo', ['stockAvailable' => $attribute['StockAvailables']['quantity']]);
            }
            if ($showProductPrice) {
                echo $pricePerUnitInfoText;
            }
            echo '</div>';
        }

        // radio buttons for changing attributes
        foreach ($preparedProductAttributes as $attribute) {

            $radioButtonLabel = $this->PricePerUnit->getQuantityInUnitsStringForAttributes(
                $attribute['ProductAttributeCombinations']['Attributes']['name'],
                $attribute['ProductAttributeCombinations']['Attributes']['can_be_used_as_unit'],
                $attribute['Units']['price_per_unit_enabled'],
                $attribute['Units']['quantity_in_units'],
                $attribute['Units']['unit_name']
            );

            echo '<div class="radio">
                      <label class="attribute-button" id="'.'attribute-button-'.$attribute['ProductAttributes']['id_product_attribute'].'">
                          <input type="radio" name="product-'.$product['id_product'].'" '.($attribute['checked'] ? 'checked' : '').'>'.
                               $radioButtonLabel.'
                      </label>
                  </div>';

        }
    } else {
        // PRODUCT WITHOUT ATTRIBUTES
        echo '<div class="entity-wrapper active">';
        if ($showProductPrice) {
            echo '<div class="line">';
            $priceHtml =  '<div class="price">' . $this->Number->formatAsCurrency($product['gross_price']) . '</div>';
            $pricePerUnitInfoText = '';
            if ($product['price_per_unit_enabled']) {
                $priceHtml = $this->PricePerUnit->getPricePerUnit($product['price_incl_per_unit'], $product['quantity_in_units'], $product['unit_amount']);
                $pricePerUnitInfoText = $this->PricePerUnit->getPricePerUnitInfoText($product['price_incl_per_unit'], $product['unit_name'], $product['unit_amount']);
            }
            echo $priceHtml;
                if ($product['deposit']) {
                    echo '<div class="deposit">+ <b>' . $this->Number->formatAsCurrency($product['deposit']).'</b> '.__('deposit').'</div>';
                }
                echo '</div>';
                if (!$this->request->getSession()->read('Auth.instantOrderCustomer') && !empty($product['timebased_currency_money_incl'])) {
                    echo $this->element('timebasedCurrency/addProductInfo', [
                        'class' => 'timebased-currency-product-info',
                        'money' => $product['timebased_currency_money_incl'],
                        'seconds' => $product['timebased_currency_seconds'],
                        'labelPrefix' => __('from_which_{0}_%', [$product['timebased_currency_max_percentage']]) . ' '
                    ]);
                }
                echo '<div class="tax">'. $this->Number->formatAsCurrency($product['tax']) . '</div>';
        }
        if (! Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
            echo $this->element('product/hiddenProductIdField', ['productId' => $product['id_product']]);
            echo $this->element('product/amountWrapper', ['stockAvailable' => $product['quantity']]);
            echo $this->element('product/cartButton', ['productId' => $product['id_product'], 'stockAvailable' => $product['quantity']]);
            echo $this->element('product/notAvailableInfo', ['stockAvailable' => $product['quantity']]);
        }
        if ($showProductPrice) {
            echo $pricePerUnitInfoText;
        }
        echo '</div>';

        $unityStrings = [];
        if ($product['unity'] != '') {
            $unityStrings[] = $product['unity'];
        }
        $unitString = $this->PricePerUnit->getQuantityInUnits($product['price_per_unit_enabled'], $product['quantity_in_units'], $product['unit_name']);
        if ($unitString != '') {
            $unityStrings[] = $unitString;
        }
        if (!empty($unityStrings)) {
            echo '<div class="unity">'.__('Unit').': <span class="value">' . join(', ', $unityStrings).'</span></div>';
        }
    }

    echo '</div>';

    echo '</div>';
