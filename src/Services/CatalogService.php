<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services;

use Cake\I18n\I18n;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Database\Query;
use Cake\Utility\Security;
use App\Services\DeliveryRhythmService;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Expression\StringExpression;
use Cake\Routing\Router;
use Cake\I18n\Date;
use Cake\ORM\TableRegistry;
use Cake\ORM\Query\SelectQuery;

class CatalogService
{

    protected mixed $identity;
    public bool $showOnlyProductsForNextWeekFilterEnabled = true;

    const MAX_PRODUCTS_PER_PAGE = 200;
    const BARCODE_WITH_WEIGHT_PREFIX = '27';
    const BARCODE_WITH_WEIGHT_PREFIX_INHOUSE = '21';

    public function __construct()
    {
        $this->identity = Router::getRequest()->getAttribute('identity');
    }

    public function showOnlyProductsForNextWeekFilterEnabled(): bool
    {
        return $this->identity !== null
            && Configure::read('appDb.FCS_SHOW_ONLY_PRODUCTS_FOR_NEXT_WEEK_FILTER_ENABLED')
            && !OrderCustomerService::isOrderForDifferentCustomerMode()
            && !Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY');
    }

    public function getPagesCount(int $totalProductCount): int
    {
        return (int) ceil($totalProductCount / self::MAX_PRODUCTS_PER_PAGE);
    }

    /**
     * @return list<\App\Model\Entity\Product>|int
     */
    public function getProducts(
        int|string $categoryId,
        bool $filterByNewProducts = false,
        string $keyword = '',
        int $productId = 0,
        bool $countMode = false,
        bool $getOnlyStockProducts = false,
        int $manufacturerId = 0,
        int $page = 1,
        bool $randomize = false,
        ): array|int
    {

        $cacheKey = join('_', [
            'Catalog_getProducts',
            'categoryId-' . $categoryId,
            'isLoggedIn-' . ((int) ($this->identity !== null)),
            'fdc-' . (OrderCustomerService::isOrderForDifferentCustomerMode() || OrderCustomerService::isSelfServiceModeByUrl()),
            'fbnp-' . $filterByNewProducts,
            'randomize-' . $randomize,
            'sopffdd-' . ($this->identity !== null ? $this->identity->show_only_products_for_next_week : 0),
            'keywords-' . substr(md5($keyword), 0, 10),
            'pId-' . $productId,
            'mId-' . $manufacturerId,
            'gosp-' . $this->getOnlyStockProductsRespectingConfiguration($getOnlyStockProducts),
            'page-' . $page,
            'cm-' . $countMode,
            'date-' . date('Y-m-d'),
        ]);
        $products = Cache::read($cacheKey);

        if ($products === null) {
            $query = $this->getQuery($categoryId, $filterByNewProducts, $keyword, $productId, $getOnlyStockProducts, $manufacturerId, $randomize);
            $products = $query->toArray();
            $products = $this->hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($products);
            $products = $this->removeProductIfAllAttributesRemovedDueToNoPurchasePrice($products);
            $products = $this->removeProductIfShowOnlyProductsForNextWeekEnabled($products);
            $products = $this->addOrderedProductsTotalAmount($products);
            if (!$countMode) {
                $offset = $page * self::MAX_PRODUCTS_PER_PAGE - self::MAX_PRODUCTS_PER_PAGE;
                $products = array_slice($products, $offset, self::MAX_PRODUCTS_PER_PAGE);
            }
            Cache::write($cacheKey, $products);
        }

        $result = $products;
        if ($countMode) {
            $result = count($products);
        }
        return $result;

    }

    /**
     * @return list<\App\Model\Entity\Product>|int
     */
    public function getProductsByManufacturerId(int $manufacturerId, bool $countMode = false, int $page = 1): array|int
    {
        return $this->getProducts(0, false, '', 0, $countMode, false, $manufacturerId, $page);
    }

    public function getOnlyStockProductsRespectingConfiguration(bool $getOnlyStockProducts): bool
    {

        if (Configure::read('appDb.FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS')) {
            if (OrderCustomerService::isOrderForDifferentCustomerMode()) {
                $getOnlyStockProducts = true;
            }
        }

        return $getOnlyStockProducts;

    }

    /**
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function getQuery(
        int|string $categoryId,
        bool $filterByNewProducts,
        string $keyword,
        int $productId,
        bool $getOnlyStockProducts,
        int $manufacturerId,
        bool $randomize=false,
        ): SelectQuery
    {

        $productsTable = TableRegistry::getTableLocator()->get('Products');

        $query = $productsTable->find('all');
        $query = $this->addContains($query);
        if ($keyword == '') {
            if ($randomize) {
                $query = $this->addOrderByRand($query);
            } else {
                $query = $this->addOrder($query);
            }
        }
        if ($keyword != '') {
            $query = $this->addOrderKeyword($query, $keyword);
        }
        $query = $this->addDefaultConditions($query);
        $query = $this->addSelectFields($query);
        $query = $this->addPurchasePriceIsSetFilter($query);
        $query = $this->addProductIdFilter($query, $productId);
        $query = $this->addCategoryIdFilter($query, $categoryId);
        $query = $this->addManufacturerIdFilter($query, $manufacturerId);

        $getOnlyStockProducts = $this->getOnlyStockProductsRespectingConfiguration($getOnlyStockProducts);
        $query = $this->addGetOnlyStockProductsFilter($query, $getOnlyStockProducts);
        $query = $this->addNewProductsFilter($query, $filterByNewProducts);
        $query = $this->addKeywordFilter($query, $keyword);

        return $query;

    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addOrderKeyword(SelectQuery $query, string $keyword): SelectQuery
    {

        $query->orderByDesc(function (QueryExpression $exp, Query $query) use ($keyword) {
            return $exp->case()
                ->when($query->expr()->like('Products.name', $keyword.'%'))->then('20') //list product "Birnensaft" before "Apfel-Birnesaft" for keyword "Birnensaft"
                ->when($query->expr()->like('Products.name', '%'.$keyword.'%'))->then('10')
                ->else('0');
            }
        );

        $query->orderBy([
            'Products.name' => 'ASC',
        ]);

        return $query;

    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addOrder(SelectQuery $query): SelectQuery
    {
        $query->orderBy([
            'Products.name' => 'ASC',
            'Images.id_image' => 'DESC',
        ]);
        return $query;
    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addOrderByRand(SelectQuery $query): SelectQuery
    {
        $query->orderBy(['RAND()']);
        return $query;
    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addSelectFields(SelectQuery $query): SelectQuery
    {
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $depositProductsTable = TableRegistry::getTableLocator()->get('DepositProducts');
        $taxesTable = TableRegistry::getTableLocator()->get('Taxes');
        $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
        $unitProductsTable = TableRegistry::getTableLocator()->get('UnitProducts');
        $stockAvailablesTable = TableRegistry::getTableLocator()->get('StockAvailables');

        $query
            ->select('Products.id_product')->distinct()
            ->select($productsTable)
            ->select($depositProductsTable)
            ->select('Images.id_image')
            ->select($taxesTable)
            ->select($manufacturersTable)
            ->select($unitProductsTable)
            ->select($stockAvailablesTable);

        return $query;
    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addContains(SelectQuery $query): SelectQuery
    {
        $query->contain([
            'Images',
            'DepositProducts',
            'Manufacturers',
            'StockAvailables' => [
                'conditions' => [
                    'StockAvailables.id_product_attribute' => 0
                ]
            ],
            'CategoryProducts',
            'UnitProducts',
            'Taxes',
            'ProductAttributes',
            'ProductAttributes.StockAvailables' => [
                'conditions' => [
                    'StockAvailables.id_product_attribute > 0'
                ]
            ],
            'ProductAttributes.DepositProductAttributes',
            'ProductAttributes.UnitProductAttributes',
            'ProductAttributes.ProductAttributeCombinations.Attributes',
        ]);
        return $query;
    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addDefaultConditions(SelectQuery $query): SelectQuery
    {
        if ($this->identity === null) {
            $query->where([
                'Manufacturers.is_private' => APP_OFF,
            ]);
        }

        $query->where([
            'Products.active' => APP_ON,
            'Manufacturers.active' => APP_ON,
        ]);

        return $query;
    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addManufacturerIdFilter(SelectQuery $query, int $manufacturerId): SelectQuery
    {
        if ($manufacturerId == 0) {
            return $query;
        }

        $query->where([
            'Manufacturers.id_manufacturer' => $manufacturerId,
        ]);

        return $query;

    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addProductIdFilter(SelectQuery $query, int $productId): SelectQuery
    {
        if ($productId == 0) {
            return $query;
        }

        $query->where([
            'Products.id_product' => $productId,
        ]);

        return $query;

    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addCategoryIdFilter(SelectQuery $query, int|string $categoryId): SelectQuery
    {
        if ($categoryId == 0) {
            return $query;
        }

        $query->innerJoinWith('CategoryProducts', function ($q) use ($categoryId) {
            return $q->where(['CategoryProducts.id_category' => $categoryId]);
        });

        return $query;

    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addGetOnlyStockProductsFilter(SelectQuery $query, bool $getOnlyStockProducts): SelectQuery
    {
        if (!$getOnlyStockProducts) {
            return $query;
        }

        $query->where(function (QueryExpression $exp, Query $q) {
            return $exp->and([
                $q->expr()->eq('Manufacturers.stock_management_enabled', APP_ON),
                $q->expr()->eq('Products.is_stock_product', APP_ON),
            ]);
        });

        return $query;

    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addNewProductsFilter(SelectQuery $query, bool $filterByNewProducts): SelectQuery
    {
        if (!$filterByNewProducts) {
            return $query;
        }

        $query->where(function (QueryExpression $exp) {
            return $exp->gt('Products.new', Date::now()->subDays((int) Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW'))->format('Y-m-d'));
        });

        return $query;
    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addPurchasePriceIsSetFilter(SelectQuery $query): SelectQuery
    {
        if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            return $query;
        }

        $purchasePriceProductsTable = TableRegistry::getTableLocator()->get('PurchasePriceProducts');
        $query->select(['system_bar_code' => $this->getProductIdentifierField()]);
        $query->select($purchasePriceProductsTable);
        $query->contain([
            'PurchasePriceProducts',
            'ProductAttribute',
            'ProductAttributes.PurchasePriceProductAttributes',
        ]);

        $query->where(function (QueryExpression $exp, Query $q) {
            return $exp->or([
                $q->expr()->isNotNull('ProductAttribute.id_product'),
                $exp->or([
                    $exp->and([
                        $q->expr()->eq('UnitProducts.price_per_unit_enabled', APP_ON),
                        $q->expr()->isNotNull('UnitProducts.purchase_price_incl_per_unit'),
                    ]),
                    $q->expr()->isNotNull('PurchasePriceProducts.price'),
                ]),
            ]);
        });

        $query->contain([
            'ProductAttributes' => [
                'conditions' => function(QueryExpression $exp, Query $q) {
                    return $exp->or([
                        $exp->and([
                            $q->expr()->eq('UnitProductAttributes.price_per_unit_enabled', APP_ON),
                            $q->expr()->isNotNull('UnitProductAttributes.purchase_price_incl_per_unit'),
                        ]),
                        $exp->and([
                            $q->expr()->isNotNull('PurchasePriceProductAttributes.price'),
                        ]),
                    ]);
                }
            ],
        ]);

        return $query;

    }

    /**
     * @param SelectQuery<\App\Model\Entity\Product> $query
     * @return SelectQuery<\App\Model\Entity\Product>
     */
    protected function addKeywordFilter(SelectQuery $query, string $keyword): SelectQuery
    {
        if ($keyword == '') {
            return $query;
        }

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $barcodeProductsTable = TableRegistry::getTableLocator()->get('BarcodeProducts');
            $query->select(['system_bar_code' => $this->getProductIdentifierField()]);
            $query->select($barcodeProductsTable);
            $query->contain([
                'BarcodeProducts',
                'ProductAttributes.BarcodeProductAttributes',
            ]);
            $query->leftJoinWith('ProductAttributes.BarcodeProductAttributes');
        }

        $query->where(function (QueryExpression $exp, Query $q) use($keyword) {
            $searchValue = '%' . $keyword . '%';
            if (I18n::getLocale() == 'de_DE') {
                $searchValue = new StringExpression('%'.$keyword.'%', 'utf8mb4_german2_ci');
            }
            $or = [
                $q->expr()->like('Products.name', $searchValue),
                $q->expr()->like('Products.description_short', $searchValue),
                $q->expr()->eq('Products.id_product', (int) $keyword),
            ];
            if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                $or = array_merge($or, [
                    $q->expr()->eq('BarcodeProducts.barcode', $keyword),
                    $q->expr()->eq('BarcodeProductAttributes.barcode', $keyword),
                ]);
                // fixes https://github.com/foodcoopshop/foodcoopshop/issues/938
                if (strlen($keyword) == 8) {
                    $or = array_merge($or, [
                        $q->expr()->like($this->getProductIdentifierField(), strtolower(substr($keyword, 0, 4))),
                    ]);
                }
                if ($this->hasABarcodeWeightPrefix($keyword)) {
                    $productBarcodeWithoutWeight = $this->getBarcodeWeightFilledWithNull($keyword);
                    $or = array_merge($or, [
                        $q->expr()->eq('BarcodeProducts.barcode', $productBarcodeWithoutWeight),
                        $q->expr()->eq('BarcodeProductAttributes.barcode', $productBarcodeWithoutWeight),
                    ]);
				}
            }
            return $exp->or($or);
        });

        return $query;

    }

    /**
     * @param \App\Model\Entity\Product[] $products
     */
    /**
     * @param list<\App\Model\Entity\Product> $products
     * @return list<\App\Model\Entity\Product>
     */
    protected function addOrderedProductsTotalAmount(array $products): array
    {

        if (!Configure::read('app.showOrderedProductsTotalAmountInCatalog')) {
            return $products;
        }

        if ($this->identity === null) {
            return $products;
        }

        if (OrderCustomerService::isOrderForDifferentCustomerMode() || OrderCustomerService::isSelfServiceModeByUrl()) {
            return $products;
        }

        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');

        $deliveryRhytmService = new DeliveryRhythmService();
        $i = -1;
        foreach($products as $product) {
            $i++;
            $pickupDay = $deliveryRhytmService->getNextDeliveryDayForProduct($product);
            if (empty($product->product_attributes)) {
                $product->ordered_total_amount = $orderDetailsTable->getTotalOrderDetails($pickupDay, $product->id_product, 0);
            } else {
                foreach($product->product_attributes as &$attribute) {
                    $attribute->ordered_total_amount = $orderDetailsTable->getTotalOrderDetails($pickupDay, $product->id_product, $attribute->id_product_attribute);
                }
            }
        }

        return $products;

    }

    /**
     * @param \App\Model\Entity\Product[] $products
     */
    /**
     * @param list<\App\Model\Entity\Product> $products
     * @return list<\App\Model\Entity\Product>
     */
    protected function removeProductIfShowOnlyProductsForNextWeekEnabled(array $products): array
    {
        if ($this->identity === null ||
            !$this->showOnlyProductsForNextWeekFilterEnabled ||
            Configure::read('appDb.FCS_SHOW_ONLY_PRODUCTS_FOR_NEXT_WEEK_FILTER_ENABLED') == 0 ||
            OrderCustomerService::isOrderForDifferentCustomerMode() ||
            !$this->identity->show_only_products_for_next_week) {
            return $products;
        }

        $i = -1;
        foreach($products as $product) {
            $i++;
            $nextDeliveryDayOfProduct = (new DeliveryRhythmService())->getNextDeliveryDayForProduct($product);
            $nextDeliveryDayGlobal = date(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), (new DeliveryRhythmService())->getDeliveryDayByCurrentDay());
            if ($nextDeliveryDayOfProduct != $nextDeliveryDayGlobal) {
                unset($products[$i]);
            }
        }
        $products = array_values($products);
        return $products;
    }

    /**
     * @param \App\Model\Entity\Product[] $products
     */
    /**
     * @param list<\App\Model\Entity\Product> $products
     * @return list<\App\Model\Entity\Product>
     */
    protected function removeProductIfAllAttributesRemovedDueToNoPurchasePrice(array $products): array
    {
        if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            return $products;
        }

        $productAttributes = Cache::remember('productAttributes', function() {
            $productAttributesTable = TableRegistry::getTableLocator()->get('ProductAttributes');
            return $productAttributesTable->find('all')
                ->select(['ProductAttributes.id_product'])
                ->groupBy('ProductAttributes.id_product')
                ->toArray();
        });
        $productIdsWithAttributes = Hash::extract($productAttributes, '{n}.id_product');
        $i = -1;
        foreach($products as $product) {
            $i++;
            if (empty($product->product_attributes) && in_array($product->id_product, $productIdsWithAttributes)) {
                unset($products[$i]);
            }
        }
        $products = array_values($products);
        return $products;
    }

    /**
     * @param \App\Model\Entity\Product[] $products
     */
    /**
     * @param list<\App\Model\Entity\Product> $products
     * @return list<\App\Model\Entity\Product>
     */
    protected function hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak(array $products): array
    {

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY') || OrderCustomerService::isOrderForDifferentCustomerMode() || OrderCustomerService::isSelfServiceModeByUrl()) {
            return $products;
        }

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $deliveryRhythmService = new DeliveryRhythmService();
        $i = -1;
        foreach($products as $product) {
            $i++;
            $deliveryDate = $deliveryRhythmService->getNextPickupDayForProduct($product);

            // deactivates the product if it can not be ordered this week
            if ($deliveryDate == 'delivery-rhythm-triggered-delivery-break') {
                $products[$i]->delivery_break_enabled = true;
            }

            // deactivates the product if manufacturer based delivery break is enabled
            if ($productsTable->deliveryBreakManufacturerEnabled(
                $product->manufacturer->no_delivery_days,
                $deliveryDate,
                $product->manufacturer->stock_management_enabled,
                $product->is_stock_product)) {
                    $products[$i]->delivery_break_enabled = true;
            }

            // deactivates the product if global delivery break is enabled
            if ($productsTable->deliveryBreakGlobalEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $deliveryDate)) {
                $products[$i]->delivery_break_enabled = true;
            }

            // hides products when order_possible_until is reached (do not apply if product is stock product)
            if (!($product->is_stock_product && $product->manufacturer->stock_management_enabled) &&
                $product->delivery_rhythm_type == 'individual' &&
                !is_null($product->delivery_rhythm_order_possible_until) &&
                $product->delivery_rhythm_order_possible_until->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')) < Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                    unset($products[$i]);
                }

        }

        $products = array_values($products);
        return $products;

    }

    public function getProductIdentifierField(): string
    {
        return 'SUBSTRING(SHA1(CONCAT(Products.id_product, "' .  Security::getSalt() . '", "product")), 1, 4)';
    }

    /**
     * @param \App\Model\Entity\Product[] $products
     */
    /**
     * @param list<\App\Model\Entity\Product> $products
     * @return list<\App\Model\Entity\Product>
     */
    public function prepareProducts(array $products): array
    {
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $customersTable = TableRegistry::getTableLocator()->get('Customers');

        $i = 0;
        foreach ($products as $product) {

            $taxRate = $products[$i]->tax_rate;

            $products[$i]->deposit_product = $products[$i]->deposit_product ?? (object) ['deposit' => 0];
            $products[$i]->tax = $products[$i]->tax ?? (object) ['rate' => 0];
            $products[$i]->unit_product = $products[$i]->unit_product ?? (object) [
                'price_per_unit_enabled' => 0,
                'price_incl_per_unit' => 0,
                'quantity_in_units' => 0,
                'name' => '',
            ];

            // START: override shopping with purchase prices / zero prices
            $modifiedProductPricesByShoppingPrice = $customersTable->getModifiedProductPricesByShoppingPrice(
                $products[$i]->id_product,
                $products[$i]->price,
                $products[$i]->unit_product->price_incl_per_unit,
                $products[$i]->deposit_product->deposit,
                $taxRate,
            );

            $products[$i]->selling_prices = [
                'gross_price' => $productsTable->getGrossPrice($products[$i]->price, $taxRate),
                'price_incl_per_unit' => $products[$i]->unit_product->price_incl_per_unit,
            ];

            $products[$i]->price = $modifiedProductPricesByShoppingPrice['price'];
            $products[$i]->unit_product->price_incl_per_unit = $modifiedProductPricesByShoppingPrice['price_incl_per_unit'];
            $products[$i]->deposit_product->deposit = $modifiedProductPricesByShoppingPrice['deposit'];
            // END: override shopping with purchase prices / zero prices

            $grossPrice = $productsTable->getGrossPrice($products[$i]->price, $taxRate);

            $products[$i]->gross_price = $grossPrice;
            $products[$i]->calculated_tax = $grossPrice - $products[$i]->price;
            $products[$i]->tax->rate = $taxRate;

            if (!Configure::read('app.isDepositEnabled')) {
                $products[$i]->deposit_product->deposit = 0;
            }

            $products[$i]->next_delivery_day = (new DeliveryRhythmService())->getNextDeliveryDayForProduct($product);

            foreach ($product->product_attributes as &$attribute) {

                $attribute->unit_product_attribute = $attribute->unit_product_attribute ?? (object) [
                    'price_per_unit_enabled' => 0,
                    'price_incl_per_unit' => 0,
                    'quantity_in_units' => 0,
                    'name' => '',
                ];

                $attribute->deposit_product_attribute = $attribute->deposit_product_attribute ?? (object) [
                    'deposit' => 0,
                ];

                if (!Configure::read('app.isDepositEnabled')) {
                    $attribute->deposit_product_attribute->deposit = 0;
                }

                // START: override shopping with purchase prices / zero prices
                $modifiedAttributePricesByShoppingPrice = $customersTable->getModifiedAttributePricesByShoppingPrice($attribute->id_product, $attribute->id_product_attribute, $attribute->price, $attribute->unit_product_attribute->price_incl_per_unit, $attribute->deposit_product_attribute->deposit, $taxRate);

                $attribute->selling_prices = [
                    'gross_price' => $productsTable->getGrossPrice($attribute->price, $taxRate),
                    'price_incl_per_unit' => $attribute->unit_product_attribute->price_incl_per_unit,
                ];

                $attribute->price = $modifiedAttributePricesByShoppingPrice['price'];
                $attribute->unit_product_attribute->price_incl_per_unit = $modifiedAttributePricesByShoppingPrice['price_incl_per_unit'];
                $attribute->deposit_product_attribute->deposit = $modifiedAttributePricesByShoppingPrice['deposit'];
                // END: override shopping with purchase prices / zero prices

                $grossPrice = $productsTable->getGrossPrice($attribute->price, $taxRate);

                $attribute->gross_price = $grossPrice;
                $attribute->calculated_tax = $grossPrice - $attribute->price;

            }

            $i++;
        }

        return $products;

    }

    public function hasABarcodeWeightPrefix(string $barcode): bool
    {
        return strpos($barcode, self::BARCODE_WITH_WEIGHT_PREFIX) === 0 || strpos($barcode, self::BARCODE_WITH_WEIGHT_PREFIX_INHOUSE) === 0;
    }

    public function getBarcodeWeightFilledWithNull(string $barcode): string
    {
        $productBarcodeWithoutWeight = substr($barcode, 0, 7);
        $productBarcodeWithoutWeight .= "000000";
        return $productBarcodeWithoutWeight;
    }

    public function getBarcodeWeight(string $barcode): float
    {
        $productBarcodeWeight = substr($barcode, 7, 5);
        $leadingDecimalPlaces = substr($productBarcodeWeight, 0, 2);
        $trailingDecimalPlaces = substr($productBarcodeWeight, 2, 3);
        $productBarcodeWeight = $leadingDecimalPlaces;
        $productBarcodeWeight .= ".";
        $productBarcodeWeight .= $trailingDecimalPlaces;
        $productBarcodeWeight = floatval($productBarcodeWeight);
        return $productBarcodeWeight;
    }

}
