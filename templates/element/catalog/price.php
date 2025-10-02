<?php

declare(strict_types=1);

use App\Model\Entity\Customer;
use App\Services\OrderCustomerService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if (!$showProductPrice) {
    return;
}
$priceData = [];
if (empty($product->product_attributes)) {
    $tooltip = __('Tax_rate') . ': ' . $this->Number->formatTaxRate($product->tax->rate) . '%';
    if ($identity === null || $identity->shopping_price != Customer::SELLING_PRICE) {
        $sellingPrice = $product->selling_prices['gross_price'];
        if ($product->unit_product->price_per_unit_enabled) {
            $sellingPrice = $this->PricePerUnit->getPricePerUnit($product->selling_prices['price_incl_per_unit'], $product->unit_product->quantity_in_units, $product->unit_product->amount);
        }
        $tooltip .= '<br />' . __('Selling_price') . ': ' . $this->Number->formatAsCurrency($sellingPrice);
    }
    $priceHtml =  '<div class="price" title="' . h($tooltip) .  '">' . $this->Number->formatAsCurrency($product->gross_price) . '</div>';
    $pricePerUnitInfoText = '';
    if ($product->unit_product->price_per_unit_enabled) {
        $priceHtml = $this->PricePerUnit->getPricePerUnitForFrontend($product->unit_product->price_incl_per_unit, $product->unit_product->quantity_in_units, $product->unit_product->amount, $tooltip);
        $pricePerUnitInfoText = $this->PricePerUnit->getPricePerUnitInfoText(
            $product->unit_product->price_incl_per_unit,
            $product->unit_product->name,
            $product->unit_product->amount,
            !OrderCustomerService::isSelfServiceModeByUrl(),
        );
    }
    if ($product->deposit_product->deposit) {
        $priceHtml .= '<div class="deposit">+ <b>' . $this->Number->formatAsCurrency($product->deposit_product->deposit).'</b> '.__('deposit').'</div>';
    }
    $priceHtml .= '<div class="tax">'. $this->Number->formatAsCurrency($product->calculated_tax) . '</div>';
    $priceData[$product->id_product] = [
        'isActive' => true,
        'content' => $priceHtml,
    ];
} else {
    $i = 0;
    foreach ($product->product_attributes as $attribute) {
        $tooltip = __('Tax_rate') . ': ' . $this->Number->formatTaxRate($product->tax->rate) . '%';
        if ($identity === null || $identity->shopping_price != Customer::SELLING_PRICE) {
            $sellingPrice = $attribute->selling_prices['gross_price'];
            if ($attribute->unit_product_attribute->price_per_unit_enabled) {
                $sellingPrice = $this->PricePerUnit->getPricePerUnit($attribute->selling_prices['price_incl_per_unit'], $attribute->unit_product_attribute->quantity_in_units, $attribute->unit_product_attribute->amount);
            }
            $tooltip .= '<br />' . __('Selling_price') . ': ' . $this->Number->formatAsCurrency($sellingPrice);
        }
        $priceHtml =  '<div class="price" title="' . h($tooltip) .  '">' . $this->Number->formatAsCurrency($attribute->gross_price) . '</div>';
        if ($attribute->unit_product_attribute->price_per_unit_enabled) {
            $priceHtml = $this->PricePerUnit->getPricePerUnitForFrontend($attribute->unit_product_attribute->price_incl_per_unit, $attribute->unit_product_attribute->quantity_in_units, $attribute->unit_product_attribute->amount, $tooltip);
        }
        if ($attribute->deposit_product_attribute->deposit) {
            $priceHtml .= '<div class="deposit">+ <b>' . $this->Number->formatAsCurrency($attribute->deposit_product_attribute->deposit).'</b> '.__('deposit').'</div>';
        }
        $priceHtml .= '<div class="tax">'. $this->Number->formatAsCurrency($attribute->calculated_tax) . '</div>';
        $priceData[$product->id_product . '-' . $attribute->id_product_attribute] = [
            'isActive' => $i == 0,
            'content' => $priceHtml,
        ];
        $i++;
    }
}

foreach ($priceData as $productId => $data) {
    $classes = ['price-wrapper', 'attribute-wrapper', 'attribute-wrapper-' . $productId];
    if ($data['isActive']) {
        $classes[] = 'active';
    }
    echo '<div class="' . join(' ', $classes) . '">';
        echo $data['content'];
    echo '</div>';
}