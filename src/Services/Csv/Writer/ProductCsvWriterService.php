<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Csv\Writer;

use Cake\Datasource\FactoryLocator;
use App\Model\Entity\Product;
use Cake\Core\Configure;
use Cake\Controller\Exception\InvalidParameterException;

class ProductCsvWriterService extends BaseCsvWriterService
{

    private $productIds;

    public function setProductIds($productIds)
    {
        $productsTable =FactoryLocator::get('Table')->get('Products');
        $stockProductIds = $productsTable->find()
        ->where([
            'Products.id_product IN' => $productIds,
            'Products.is_stock_product' => APP_ON,
            'Manufacturers.stock_management_enabled' => APP_ON,
        ])
        ->contain([
            'Manufacturers',
        ])
        ->all()->extract('id_product')->toArray();

        if (empty($stockProductIds)) {
            throw new InvalidParameterException('no stock products found');
        }

        $this->productIds = $stockProductIds;
    }

    public function getHeader()
    {
        return [
            __('Id'),
            __('Product'),
            __('Manufacturer'),
            __('Status'),
            __('Amount'),
            __('Unit'),
            __('Minimal_stock_amount'),
            Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') ?  __('Purchase_price') . ' ' . __('net') : __('Selling_price') . ' ' . __('gross') ,
            __('Price_per'),
            __('Stock_value'),
        ];
    }

    public function getRecords()
    {
        $productsTable =FactoryLocator::get('Table')->get('Products');
        $products = $productsTable->getProductsForBackend(
            productIds: $this->productIds,
            manufacturerId: 'all',
            active: 'all',
            addProductNameToAttributes: true,
        );

        $records = [];
        $stockValueSum = 0;
        foreach ($products as $product) {

            $isMainProduct = $productsTable->isMainProduct($product);
            if ($isMainProduct && !empty($product->product_attributes)) {
                continue;
            }

            $productName = $this->getProductName($product, $isMainProduct);
            $availableQuantity = $product->stock_available->quantity;

            $price = $this->getSellingPriceGross($product);
            $pricePerUnit = $product->unit->price_incl_per_unit ?? 0;
            if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                $price = $this->getPurchasePriceNet($product);
                $pricePerUnit = $product->unit->purchase_price_incl_per_unit ?? 0;
            }

            $unit = $this->getUnit($product, $isMainProduct);
            $stockValue = $this->getStockValue($product, $price, $availableQuantity, $pricePerUnit);

            $stockValueSum += $stockValue;

            $records[] = [
                $product->id_product,
                $productName,
                html_entity_decode($product->manufacturer->name),
                $product->active,
                Configure::read('app.numberHelper')->formatUnitAsDecimal($availableQuantity),
                $unit,
                $product->stock_available->sold_out_limit ? Configure::read('app.numberHelper')->formatUnitAsDecimal($product->stock_available->sold_out_limit) : null,
                Configure::read('app.numberHelper')->formatAsDecimal($price, 6),
                $this->getUnitForPrice($product),
                Configure::read('app.numberHelper')->formatAsDecimal($stockValue),
            ];
        }

        $records[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            Configure::read('app.numberHelper')->formatAsDecimal($stockValueSum),
        ];

        return $records;
    }

    private function getFromPattern($pattern, $string)
    {
        preg_match($pattern, $string, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return '';
    }

    private function getUnit($product, $isMainProduct)
    {
        $unit = $this->getFromPattern('/<span class="unity-for-dialog">(.*?)<\/span>/', $product->name);
        if ($unit == '') {
            $unit = $this->getFromPattern('/<span class="quantity-in-units">(.*?)<\/span>/', $product->name);
        }

        if (!$isMainProduct) {
            $explodedName = explode(Product::NAME_SEPARATOR, $product->name);
            if (count($explodedName) == 2) {
                $unit = $explodedName[1];
            }
            if ($product->unit && $product->unit->price_per_unit_enabled) {
                $unit = $product->name;
            }
        }

        return $unit;

    }

    private function getProductName($product, $isMainProduct)
    {

        $productName = $this->getFromPattern('/<span class="product-name">(.*?)<\/span>/', $product->name);

        if (!$isMainProduct && $product->unit && $product->unit->price_per_unit_enabled) {
            return $product->unchanged_name;
        }

        return $productName;
    }

    private function getSellingPriceGross($product)
    {
        $sellingPriceGross = $product->gross_price;
        if ($product->unit && $product->unit->price_per_unit_enabled) {
            $sellingPriceGross = $product->unit->price_incl_per_unit;
        }
        return $sellingPriceGross;
    }

    private function getPurchasePriceNet($product)
    {
        $purchasePriceNet = $product->purchase_net_price ?? 0;
        if ($product->unit && $product->unit->price_per_unit_enabled) {
            $purchasePriceNet = $product->unit->purchase_price_incl_per_unit ?? 0;
        }
        return $purchasePriceNet;
    }

    private function getStockValue($product, $price, $availableQuantity, $pricePerUnit)
    {
        if ($availableQuantity <= 0) {
            return 0;
        }

        if ($product->unit && $product->unit->price_per_unit_enabled) {
            $price = Configure::read('app.pricePerUnitHelper')->getPricePerUnit($pricePerUnit, $product->unit->quantity_in_units, $product->unit->amount);
        }

        $stockValue = $price * $availableQuantity;

        return $stockValue;
    }

    private function getUnitForPrice($product) {
        $unitForPrice = '';
        if ($product->unit && $product->unit->price_per_unit_enabled) {
            $unitForPrice = $product->unit->amount . ' ' . $product->unit->name;
        }
        return $unitForPrice;
    }

}