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

use App\Model\Table\CustomersTable;
use App\Model\Table\ManufacturersTable;
use App\Model\Table\OrderDetailsTable;
use App\Model\Table\ProductAttributesTable;
use App\Model\Table\ProductsTable;
use Cake\I18n\I18n;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Database\Query;
use Cake\Utility\Security;
use Cake\Datasource\FactoryLocator;
use App\Services\DeliveryRhythmService;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Expression\StringExpression;
use Cake\Routing\Router;

class CatalogService
{

    protected CustomersTable $Customer;
    protected ManufacturersTable $Manufacturer;
    protected ProductsTable $Product;
    protected ProductAttributesTable $ProductAttribute;
    protected OrderDetailsTable $OrderDetail;
    protected $identity;

    const MAX_PRODUCTS_PER_PAGE = 100;
    const BARCODE_WITH_WEIGHT_PREFIX = '27';
    const BARCODE_WITH_WEIGHT_PREFIX_INHOUSE = '21';

    public function __construct()
    {
        $this->identity = Router::getRequest()->getAttribute('identity');
    }

    public function getPagesCount($totalProductCount)
    {
        return ceil($totalProductCount / self::MAX_PRODUCTS_PER_PAGE);
    }

    public function getProducts($categoryId, $filterByNewProducts = false, $keyword = '', $productId = 0, $countMode = false, $getOnlyStockProducts = false, $manufacturerId = 0, $page = 1)
    {

        $orderCustomerService = new OrderCustomerService();
        $cacheKey = join('_', [
            'Catalog_getProducts',
            'categoryId-' . $categoryId,
            'isLoggedIn-' . ((int) ($this->identity !== null)),
            'forDifferentCustomer-' . ($orderCustomerService->isOrderForDifferentCustomerMode() || $orderCustomerService->isSelfServiceModeByUrl()),
            'filterByNewProducts-' . $filterByNewProducts,
            'keywords-' . substr(md5($keyword), 0, 10),
            'productId-' . $productId,
            'manufacturerId-' . $manufacturerId,
            'getOnlyStockProducts-' . $this->getOnlyStockProductsRespectingConfiguration($getOnlyStockProducts),
            'page-' . $page,
            'countMode-' . $countMode,
            'date-' . date('Y-m-d'),
        ]);
        $products = Cache::read($cacheKey);

        if ($products === null) {
            $query = $this->getQuery($categoryId, $filterByNewProducts, $keyword, $productId, $getOnlyStockProducts, $manufacturerId);
            $products = $query->toArray();
            $products = $this->hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($products);
            $products = $this->removeProductIfAllAttributesRemovedDueToNoPurchasePrice($products);
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

    public function getProductsByManufacturerId($manufacturerId, $countMode = false, $page = 1)
    {
        return $this->getProducts('', false, '', 0, $countMode, false, $manufacturerId, $page);
    }

    public function getOnlyStockProductsRespectingConfiguration($getOnlyStockProducts)
    {

        if (Configure::read('appDb.FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS')) {
            $orderCustomerService = new OrderCustomerService();
            if ($orderCustomerService->isOrderForDifferentCustomerMode()) {
                $getOnlyStockProducts = true;
            }
        }

        return $getOnlyStockProducts;

    }

    protected function getQuery($categoryId, $filterByNewProducts, $keyword, $productId, $getOnlyStockProducts, $manufacturerId)
    {

        $this->Product = FactoryLocator::get('Table')->get('Products');

        $query = $this->Product->find('all');
        $query = $this->addContains($query);
        if ($keyword == '') {
            $query = $this->addOrder($query);
        } else {
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

    protected function addOrderKeyword($query, $keyword)
    {

        $query->orderDesc(function (QueryExpression $exp, Query $query) use ($keyword) {
            return $exp->case()
                ->when($query->newExpr()->like('Products.name', $keyword.'%'))->then('20') //list product "Birnensaft" before "Apfel-Birnesaft" for keyword "Birnensaft"
                ->when($query->newExpr()->like('Products.name', '%'.$keyword.'%'))->then('10')
                ->else('0');
            }
        );

        $query->order([
            'Products.name' => 'ASC',
        ]);

        return $query;

    }

    protected function addOrder($query)
    {
        $query->order([
            'Products.name' => 'ASC',
            'Images.id_image' => 'DESC',
        ]);
        return $query;
    }

    protected function addSelectFields($query)
    {
        $query
            ->select('Products.id_product')->distinct()
            ->select($this->Product)
            ->select($this->Product->DepositProducts)
            ->select('Images.id_image')
            ->select($this->Product->Taxes)
            ->select($this->Product->Manufacturers)
            ->select($this->Product->UnitProducts)
            ->select($this->Product->StockAvailables);

        return $query;
    }

    protected function addContains($query)
    {
        $query->contain([
            'Images',
            'CategoryProducts',
            'DepositProducts',
            'Manufacturers',
            'StockAvailables' => [
                'conditions' => [
                    'StockAvailables.id_product_attribute' => 0
                ]
            ],
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

    protected function addDefaultConditions($query)
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

    protected function addManufacturerIdFilter($query, $manufacturerId)
    {
        if ($manufacturerId == 0) {
            return $query;
        }

        $query->where([
            'Manufacturers.id_manufacturer' => $manufacturerId,
        ]);

        return $query;

    }

    protected function addProductIdFilter($query, $productId)
    {
        if ($productId == 0) {
            return $query;
        }

        $query->where([
            'Products.id_product' => $productId,
        ]);

        return $query;

    }

    protected function addCategoryIdFilter($query, $categoryId)
    {
        if ($categoryId == '') {
            return $query;
        }

        $query->contain([
            'CategoryProducts' => [
                'Categories' => [
                    'conditions' => [
                        'Categories.active' => APP_ON,
                    ],
                ],
            ],
        ]);
        $query->matching('CategoryProducts', function ($q) use ($categoryId) {
            return $q->where(['CategoryProducts.id_category IN' => $categoryId]);
        });

        return $query;

    }

    protected function addGetOnlyStockProductsFilter($query, $getOnlyStockProducts)
    {
        if (!$getOnlyStockProducts) {
            return $query;
        }

        $query->where(function (QueryExpression $exp, Query $q) {
            return $exp->and([
                $q->newExpr()->eq('Manufacturers.stock_management_enabled', APP_ON),
                $q->newExpr()->eq('Products.is_stock_product', APP_ON),
            ]);
        });

        return $query;

    }

    protected function addNewProductsFilter($query, $filterByNewProducts)
    {
        if (!$filterByNewProducts) {
            return $query;
        }

        $dateAdd = date('Y-m-d', strtotime('-' . Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' DAYS'));
        $query->where(function (QueryExpression $exp) use ($dateAdd) {
            return $exp->gt('DATE_FORMAT(Products.created, \'%Y-%m-%d\')', $dateAdd);
        });

        return $query;
    }

    protected function addPurchasePriceIsSetFilter($query)
    {
        if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            return $query;
        }

        $query->select(['system_bar_code' => $this->getProductIdentifierField()]);
        $query->select($this->Product->PurchasePriceProducts);
        $query->contain([
            'PurchasePriceProducts',
            'ProductAttribute',
            'ProductAttributes.PurchasePriceProductAttributes',
        ]);

        $query->where(function (QueryExpression $exp, Query $q) {
            return $exp->or([
                $q->newExpr()->isNotNull('ProductAttribute.id_product'),
                $exp->or([
                    $exp->and([
                        $q->newExpr()->eq('UnitProducts.price_per_unit_enabled', APP_ON),
                        $q->newExpr()->isNotNull('UnitProducts.purchase_price_incl_per_unit'),
                    ]),
                    $q->newExpr()->isNotNull('PurchasePriceProducts.price'),
                ]),
            ]);
        });

        $query->contain([
            'ProductAttributes' => [
                'conditions' => function(QueryExpression $exp, Query $q) {
                    return $exp->or([
                        $exp->and([
                            $q->newExpr()->eq('UnitProductAttributes.price_per_unit_enabled', APP_ON),
                            $q->newExpr()->isNotNull('UnitProductAttributes.purchase_price_incl_per_unit'),
                        ]),
                        $exp->and([
                            $q->newExpr()->isNotNull('PurchasePriceProductAttributes.price'),
                        ]),
                    ]);
                }
            ],
        ]);

        return $query;

    }

    protected function addKeywordFilter($query, $keyword)
    {
        if ($keyword == '') {
            return $query;
        }

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $query->select(['system_bar_code' => $this->getProductIdentifierField()]);
            $query->select($this->Product->BarcodeProducts);
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
                $q->newExpr()->like('Products.name', $searchValue),
                $q->newExpr()->like('Products.description_short', $searchValue),
                $q->newExpr()->eq('Products.id_product', (int) $keyword),
            ];
            if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                $or = array_merge($or, [
                    $q->newExpr()->eq('BarcodeProducts.barcode', $keyword),
                    $q->newExpr()->eq('BarcodeProductAttributes.barcode', $keyword),
                ]);
                // fixes https://github.com/foodcoopshop/foodcoopshop/issues/938
                if (strlen($keyword) == 8) {
                    $or = array_merge($or, [
                        $q->newExpr()->like($this->getProductIdentifierField(), strtolower(substr($keyword, 0, 4))),
                    ]);
                }
                if ($this->hasABarcodeWeightPrefix($keyword)){
                    $productBarcodeWithoutWeight = $this->getBarcodeWeightFilledWithNull($keyword);
                    $or = array_merge($or, [
                        $q->newExpr()->eq('BarcodeProducts.barcode', $productBarcodeWithoutWeight),
                        $q->newExpr()->eq('BarcodeProductAttributes.barcode', $productBarcodeWithoutWeight),
                    ]);
				}
            }
            return $exp->or($or);
        });

        return $query;

    }

    protected function addOrderedProductsTotalAmount($products)
    {

        if (!Configure::read('app.showOrderedProductsTotalAmountInCatalog')) {
            return $products;
        }

        if ($this->identity === null) {
            return $products;
        }

        $orderCustomerService = new OrderCustomerService();
        if ($orderCustomerService->isOrderForDifferentCustomerMode() || $orderCustomerService->isSelfServiceModeByUrl()) {
            return $products;
        }

        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');

        $deliveryRhytmService = new DeliveryRhythmService();
        $i = -1;
        foreach($products as $product) {
            $i++;
            $pickupDay = $deliveryRhytmService->getNextDeliveryDayForProduct($product, $orderCustomerService);
            if (empty($product->product_attributes)) {
                $product->ordered_total_amount = $this->OrderDetail->getTotalOrderDetails($pickupDay, $product->id_product, 0);
            } else {
                foreach($product->product_attributes as &$attribute) {
                    $attribute->ordered_total_amount = $this->OrderDetail->getTotalOrderDetails($pickupDay, $product->id_product, $attribute->id_product_attribute);
                }
            }
        }

        return $products;

    }


    protected function removeProductIfAllAttributesRemovedDueToNoPurchasePrice($products)
    {
        if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            return $products;
        }

        $this->ProductAttribute = FactoryLocator::get('Table')->get('ProductAttributes');
        $productAttributes = Cache::remember('productAttributes', function() {
            return $this->ProductAttribute->find('all')
                ->select(['ProductAttributes.id_product'])
                ->group('ProductAttributes.id_product')
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
        $products = $this->reindexArray($products);
        return $products;
    }

    protected function hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($products)
    {

        $orderCustomerService = new OrderCustomerService();
        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY') || $orderCustomerService->isOrderForDifferentCustomerMode() || $orderCustomerService->isSelfServiceModeByUrl()) {
            return $products;
        }

        $this->Product = FactoryLocator::get('Table')->get('Products');
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
            if ($this->Product->deliveryBreakManufacturerEnabled(
                $product->manufacturer->no_delivery_days,
                $deliveryDate,
                $product->manufacturer->stock_management_enabled,
                $product->is_stock_product)) {
                    $products[$i]->delivery_break_enabled = true;
            }

            // deactivates the product if global delivery break is enabled
            if ($this->Product->deliveryBreakGlobalEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $deliveryDate)) {
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

        $products = $this->reindexArray($products);
        return $products;

    }

    protected function reindexArray($array)
    {
        return array_values($array);
    }

    public function getProductIdentifierField()
    {
        return 'SUBSTRING(SHA1(CONCAT(Products.id_product, "' .  Security::getSalt() . '", "product")), 1, 4)';
    }

    public function prepareProducts($products)
    {
        $this->Product = FactoryLocator::get('Table')->get('Products');
        $this->Manufacturer = FactoryLocator::get('Table')->get('Manufacturers');
        $this->ProductAttribute = FactoryLocator::get('Table')->get('ProductAttributes');
        $this->Customer = FactoryLocator::get('Table')->get('Customers');

        $i = 0;
        foreach ($products as $product) {

            $taxRate = $products[$i]->tax->rate ?? 0;

            $products[$i]->deposit_product = $products[$i]->deposit_product ?? (object) ['deposit' => 0];
            $products[$i]->tax = $products[$i]->tax ?? (object) ['rate' => 0];
            $products[$i]->unit_product = $products[$i]->unit_product ?? (object) [
                'price_per_unit_enabled' => 0,
                'price_incl_per_unit' => 0,
                'quantity_in_units' => 0,
                'name' => '',
            ];

            // START: override shopping with purchase prices / zero prices
            $modifiedProductPricesByShoppingPrice = $this->Customer->getModifiedProductPricesByShoppingPrice(
                $products[$i]->id_product,
                $products[$i]->price,
                $products[$i]->unit_product->price_incl_per_unit,
                $products[$i]->deposit_product->deposit,
                $taxRate,
            );

            $products[$i]->selling_prices = [
                'gross_price' => $this->Product->getGrossPrice($products[$i]->price, $taxRate),
                'price_incl_per_unit' => $products[$i]->unit_product->price_incl_per_unit,
            ];

            $products[$i]->price = $modifiedProductPricesByShoppingPrice['price'];
            $products[$i]->unit_product->price_incl_per_unit = $modifiedProductPricesByShoppingPrice['price_incl_per_unit'];
            $products[$i]->deposit_product->deposit = $modifiedProductPricesByShoppingPrice['deposit'];
            // END: override shopping with purchase prices / zero prices

            $grossPrice = $this->Product->getGrossPrice($products[$i]->price, $taxRate);

            $products[$i]->gross_price = $grossPrice;
            $products[$i]->calculated_tax = $grossPrice - $products[$i]->price;
            $products[$i]->tax->rate = $taxRate;
            $products[$i]->is_new = $this->Product->isNew($products[$i]->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')));

            if (!Configure::read('app.isDepositEnabled')) {
                $products[$i]->deposit_product->deposit = 0;
            }

            $products[$i]->next_delivery_day = (new DeliveryRhythmService())->getNextDeliveryDayForProduct($product, new OrderCustomerService());

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
                $modifiedAttributePricesByShoppingPrice = $this->Customer->getModifiedAttributePricesByShoppingPrice($attribute->id_product, $attribute->id_product_attribute, $attribute->price, $attribute->unit_product_attribute->price_incl_per_unit, $attribute->deposit_product_attribute->deposit, $taxRate);

                $attribute->selling_prices = [
                    'gross_price' => $this->Product->getGrossPrice($attribute->price, $taxRate),
                    'price_incl_per_unit' => $attribute->unit_product_attribute->price_incl_per_unit,
                ];

                $attribute->price = $modifiedAttributePricesByShoppingPrice['price'];
                $attribute->unit_product_attribute->price_incl_per_unit = $modifiedAttributePricesByShoppingPrice['price_incl_per_unit'];
                $attribute->deposit_product_attribute->deposit = $modifiedAttributePricesByShoppingPrice['deposit'];
                // END: override shopping with purchase prices / zero prices

                $grossPrice = $this->Product->getGrossPrice($attribute->price, $taxRate);

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

}
