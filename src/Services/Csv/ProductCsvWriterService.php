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
namespace App\Services\Csv;

use Cake\Datasource\FactoryLocator;
use App\Model\Entity\Product;
use Cake\Core\Configure;

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

        // filter out products that are no stock products
        $this->productIds = $stockProductIds;
    }

    public function getHeader()
    {
        return [
            __('Id'),
            __('Product'),
            __('Manufacturer'),
            __('Unit'),
            __('Amount'),
            __('Gross_price'),
            __('for'),
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
            $sellingPriceGross = $this->getSellingPriceGross($product);
            $unit = $this->getUnit($product, $isMainProduct);
            $stockValue = $this->getStockValue($product, $sellingPriceGross, $availableQuantity);

            $stockValueSum += $stockValue;

            $records[] = [
                $product->id_product,
                $productName,
                $product->manufacturer->name,
                $unit,
                $availableQuantity,
                Configure::read('app.numberHelper')->formatAsDecimal($sellingPriceGross),
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

    private function getStockValue($product, $price, $availableQuantity)
    {
        if ($availableQuantity <= 0) {
            return 0;
        }

        if ($product->unit && $product->unit->price_per_unit_enabled) {
            $price = Configure::read('app.pricePerUnitHelper')->getPricePerUnit($product->unit->price_incl_per_unit, $product->unit->quantity_in_units, $product->unit->amount);
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