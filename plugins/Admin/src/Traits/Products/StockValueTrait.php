<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Services\ProductsForBackendService;
use App\Services\ProductStockValueService;
use Cake\Core\Configure;
use stdClass;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait StockValueTrait
{

    private const string STOCK_FILTER_ALL = 'all';
    private const string STOCK_FILTER_EMPTY = 'empty';
    private const string STOCK_FILTER_RUNNING_OUT_OF_STOCK = 'running-out-of-stock';
    private const array STOCK_FILTER_MAP = [
        self::STOCK_FILTER_ALL => 'all',
        self::STOCK_FILTER_EMPTY => 'Stock <= 0',
        self::STOCK_FILTER_RUNNING_OUT_OF_STOCK => 'expiring',
    ];

    public function stockValue(): void
    {
        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId', 'all'));
        if ($manufacturerId != 'all') {
            $manufacturerId = (int) $manufacturerId;
        }
        $active = h($this->getRequest()->getQuery('active', APP_ON));
        $stockFilter = h($this->getRequest()->getQuery('stockFilter', self::STOCK_FILTER_ALL));
        if (!array_key_exists($stockFilter, self::STOCK_FILTER_MAP)) {
            $stockFilter = self::STOCK_FILTER_ALL;
        }

        $productsForBackendService = new ProductsForBackendService();
        $query = $productsForBackendService->getQuery(
            productIds: '',
            manufacturerId: $manufacturerId,
            active: (string) $active,
        );
        $query->where([
            'Products.is_stock_product' => APP_ON,
            'Manufacturers.stock_management_enabled' => APP_ON,
        ]);
        $query->orderBy([
            'Manufacturers.name' => 'ASC',
            'Products.name' => 'ASC',
        ], true);
        $products = $productsForBackendService->getPreparedProducts($query, true);

        $productsTable = $this->getTableLocator()->get('Products');
        $cartsTable = $this->getTableLocator()->get('Carts');
        $productIds = [];
        foreach ($products as $product) {
            $productIdParts = explode('-', (string) $product->id_product);
            $productIds[] = (int) $productIdParts[0];
        }
        $manufacturerIdsByProductId = [];
        $productNamesByCompositeId = [];
        if (!empty($productIds)) {
            $productsForOrderDetailNames = $productsTable->find('all')
                ->where([
                    'Products.id_product IN' => array_unique($productIds),
                ])
                ->toArray();
            foreach ($productsForOrderDetailNames as $productForOrderDetailName) {
                $manufacturerIdsByProductId[$productForOrderDetailName->id_product] = $productForOrderDetailName->id_manufacturer;
                $productNamesByCompositeId[(string) $productForOrderDetailName->id_product] = $cartsTable->getProductNameWithUnity($productForOrderDetailName->name, $productForOrderDetailName->unity);
            }
        }

        $productStockValueService = new ProductStockValueService();
        $stockValueSum = 0;
        foreach ($products as $index => $product) {
            if ($productsTable->isMainProduct($product) && !empty($product->product_attributes)) {
                unset($products[$index]);
                continue;
            }

            if (!$this->productMatchesStockFilter($product, $stockFilter)) {
                unset($products[$index]);
                continue;
            }

            $products[$index]->price = $productStockValueService->getPrice($product);
            $products[$index]->stock_value = $productStockValueService->getStockValue($product);
            $productIdParts = explode('-', (string) $product->id_product);
            $products[$index]->id_product_for_edit = (int) $productIdParts[0];
            $products[$index]->id_manufacturer_for_edit = $manufacturerIdsByProductId[$products[$index]->id_product_for_edit] ?? null;
            if ($productsTable->isMainProduct($product)) {
                $products[$index]->name = $productNamesByCompositeId[(string) $product->id_product] ?? $product->name;
            } else {
                $products[$index]->name = $cartsTable->getProductNameWithUnity($product->unchanged_name, $this->getAttributeUnity($product));
            }
            $products[$index]->row_class = !$product->active ? 'deactivated' : '';
            $stockValueSum += $products[$index]->stock_value;
        }
        $products = array_values($products);

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $manufacturersForDropdown = ['all' => __('All_manufacturers')];
        $manufacturersForDropdown = array_merge($manufacturersForDropdown, $manufacturersTable->getForDropdown());
        $stockFiltersForDropdown = $this->getStockFiltersForDropdown();

        $this->set('products', $products);
        $this->set('manufacturerId', $manufacturerId);
        $this->set('active', $active);
        $this->set('stockFilter', $stockFilter);
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        $this->set('stockFiltersForDropdown', $stockFiltersForDropdown);
        $this->set('stockValueSum', $stockValueSum);
        $this->set('priceLabel', Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') ? __('Purchase_price') . ' ' . __('net') : __('Price'));
        $this->set('title_for_layout', __('Stock_value'));
    }

    private function getAttributeUnity(stdClass $product): string
    {
        $attributeUnity = trim(strip_tags($product->name));
        $productNamePrefix = $product->unchanged_name . ': ';
        if (str_starts_with($attributeUnity, $productNamePrefix)) {
            $attributeUnity = substr($attributeUnity, strlen($productNamePrefix));
        }
        if ($attributeUnity == $product->unchanged_name) {
            return '';
        }
        return $attributeUnity;
    }

    private function productMatchesStockFilter(stdClass $product, string $stockFilter): bool
    {
        if ($stockFilter == self::STOCK_FILTER_EMPTY) {
            return $product->stock_available->quantity <= 0;
        }
        if ($stockFilter == self::STOCK_FILTER_RUNNING_OUT_OF_STOCK) {
            return $product->stock_available->quantity > 0 && $product->stock_available->sold_out_limit > 0 && $product->stock_available->quantity < $product->stock_available->sold_out_limit;
        }
        return true;
    }

    /**
     * @return array<string, string>
     */
    private function getStockFiltersForDropdown(): array
    {
        $stockFiltersForDropdown = [];
        foreach (self::STOCK_FILTER_MAP as $stockFilter => $label) {
            $stockFiltersForDropdown[$stockFilter] = __('Products') . ': ' . __($label);
        }
        return $stockFiltersForDropdown;
    }

}