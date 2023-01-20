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
namespace App\Lib\Catalog;

use App\Lib\DeliveryRhythm\DeliveryRhythm;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Query;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\FactoryLocator;
use Cake\Utility\Hash;
use Cake\Utility\Security;

class Catalog {

    protected $Customer;
    protected $Manufacturer;
    protected $Product;
    protected $ProductAttribute;
    protected $OrderDetail;

    public function getProducts($appAuth, $categoryId, $filterByNewProducts = false, $keyword = '', $productId = 0, $countMode = false, $getOnlyStockProducts = false, $manufacturerId = 0)
    {

        $cacheKey = join('_', [
            'Catalog_getProducts',
            'categoryId-' . $categoryId,
            'isLoggedIn-' . (empty($appAuth->user() ? 0 : 1)),
            'forDifferentCustomer-' . ($appAuth->isOrderForDifferentCustomerMode() || $appAuth->isSelfServiceModeByUrl()),
            'filterByNewProducts-' . $filterByNewProducts,
            'keywords-' . substr(md5($keyword), 0, 10),
            'productId-' . $productId,
            'manufacturerId-' . $manufacturerId,
            'getOnlyStockProducts-' . $getOnlyStockProducts,
            'date-' . date('Y-m-d'),
        ]);
        $products = Cache::read($cacheKey);

        if ($products === null) {
            $query = $this->getQuery($appAuth, $categoryId, $filterByNewProducts, $keyword, $productId, $countMode, $getOnlyStockProducts, $manufacturerId);
            $products = $query->toArray();
            $products = $this->hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($appAuth, $products);
            $products = $this->removeProductIfAllAttributesRemovedDueToNoPurchasePrice($products);
            $products = $this->addOrderedProductsTotalAmount($products, $appAuth);
            Cache::write($cacheKey, $products);
        }

        $result = $products;
        if ($countMode) {
            $result = count($products);
        }
        return $result;

    }

    public function getProductsByManufacturerId($appAuth, $manufacturerId, $countMode = false)
    {
        return $this->getProducts($appAuth, '', false, '', 0, $countMode, false, $manufacturerId);
    }

    protected function getQuery($appAuth, $categoryId, $filterByNewProducts = false, $keyword = '', $productId = 0, $countMode = false, $getOnlyStockProducts = false, $manufacturerId = 0)
    {

        $this->Product = FactoryLocator::get('Table')->get('Products');

        $query = $this->Product->find('all');
        $query = $this->addContains($query);
        if ($keyword == '') {
            $query = $this->addOrder($query);
        } else {
            $query = $this->addOrderKeyword($query, $keyword);
        }
        $query = $this->addDefaultConditions($query, $appAuth);
        $query = $this->addSelectFields($query);
        $query = $this->addPurchasePriceIsSetFilter($query);
        $query = $this->addProductIdFilter($query, $productId);
        $query = $this->addCategoryIdFilter($query, $categoryId);
        $query = $this->addManufacturerIdFilter($query, $manufacturerId);

        if (Configure::read('appDb.FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS')) {
            if ($appAuth->isOrderForDifferentCustomerMode()) {
                $getOnlyStockProducts = true;
            }
        }

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

    protected function addDefaultConditions($query, $appAuth)
    {
        if (empty($appAuth->user())) {
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
            $or = [
                $q->newExpr()->like('Products.name', '%'.$keyword.'%'),
                $q->newExpr()->like('Products.description_short', '%'.$keyword.'%'),
                $q->newExpr()->eq('Products.id_product', (int) $keyword),
            ];
            if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                $or = array_merge($or, [
                    $q->newExpr()->like($this->getProductIdentifierField(), strtolower(substr($keyword, 0, 4))),
                    $q->newExpr()->eq('BarcodeProducts.barcode', $keyword),
                    $q->newExpr()->eq('BarcodeProductAttributes.barcode', $keyword),
                ]);
            }
            return $exp->or($or);
        });

        return $query;

    }

    protected function addOrderedProductsTotalAmount($products, $appAuth)
    {

        if (!Configure::read('app.showOrderedProductsTotalAmountInCatalog')) {
            return $products;
        }

        if (!$appAuth->user()) {
            return $products;
        }

        if ($appAuth->isOrderForDifferentCustomerMode() || $appAuth->isSelfServiceModeByUrl()) {
            return $products;
        }

        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');

        $i = -1;
        foreach($products as $product) {
            $i++;
            $pickupDay = DeliveryRhythm::getNextDeliveryDayForProduct($product, $appAuth);
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

    protected function hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($appAuth, $products)
    {

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY') || $appAuth->isOrderForDifferentCustomerMode() || $appAuth->isSelfServiceModeByUrl()) {
            return $products;
        }

        $this->Product = FactoryLocator::get('Table')->get('Products');
        $i = -1;
        foreach($products as $product) {
            $i++;
            $deliveryDate = DeliveryRhythm::getNextPickupDayForProduct($product);

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
        $reindexedArray = [];
        foreach($array as $a) {
            $reindexedArray[] = $a;
        }
        return $reindexedArray;
    }

    public function getProductIdentifierField()
    {
        return 'SUBSTRING(SHA1(CONCAT(Products.id_product, "' .  Security::getSalt() . '", "product")), 1, 4)';
    }

    public function prepareProducts($appAuth, $products)
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
                $appAuth,
                $products[$i]->id_product,
                $products[$i]->price,
                $products[$i]->unit_product->price_incl_per_unit,
                $products[$i]->deposit_product->deposit,
                $taxRate,
            );
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

            $products[$i]->next_delivery_day = DeliveryRhythm::getNextDeliveryDayForProduct($product, $appAuth);

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
                $modifiedAttributePricesByShoppingPrice = $this->Customer->getModifiedAttributePricesByShoppingPrice($appAuth, $attribute->id_product, $attribute->id_product_attribute, $attribute->price, $attribute->unit_product_attribute->price_incl_per_unit, $attribute->deposit_product_attribute->deposit, $taxRate);
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

}
