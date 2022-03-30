<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\Catalog;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Query;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\FrozenDate;
use Cake\Utility\Security;

class Catalog {

    public function getProductsByManufacturerId($appAuth, $manufacturerId, $countMode = false)
    {

        $cacheKey = join('_', [
            'ManufacturersController_getProductsByManufacturerId',
            'manufacturerId-' . $manufacturerId,
            'isLoggedIn-' . empty($appAuth->user()),
            'forDifferentCustomer-' . ($appAuth->isOrderForDifferentCustomerMode() || $appAuth->isSelfServiceModeByUrl()),
            'date-' . date('Y-m-d'),
        ]);
        $products = Cache::read($cacheKey);

        if ($products === null) {
            $query = $this->getQuery($appAuth, null, false, '', 0, $countMode, false);
            $query->where([
                'Manufacturers.id_manufacturer' => $manufacturerId,
                'Manufacturers.active' => APP_ON,
            ]);
            if (empty($appAuth->user())) {
                $query->where([
                    'Manufacturers.is_private' => APP_OFF,
                ]);
            }
            $products = $query->toArray();
            $products = $this->hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($appAuth, $products);
            Cache::write($cacheKey, $products);
        }

        if (! $countMode) {
            return $products;
        } else {
            return count($products);
        }

    }

    public function getProducts($appAuth, $categoryId, $filterByNewProducts = false, $keyword = '', $productId = 0, $countMode = false, $getOnlyStockProducts = false)
    {

        $cacheKey = join('_', [
            'Catalog_getProducts',
            'categoryId-' . $categoryId,
            'isLoggedIn-' . (empty($appAuth->user() ? 0 : 1)),
            'forDifferentCustomer-' . ($appAuth->isOrderForDifferentCustomerMode() || $appAuth->isSelfServiceModeByUrl()),
            'filterByNewProducts-' . $filterByNewProducts,
            'keywords-' . substr(md5($keyword), 0, 10),
            'productId-' . $productId,
            'getOnlyStockProducts-' . $getOnlyStockProducts,
            'date-' . date('Y-m-d'),
        ]);
        $products = Cache::read($cacheKey);

        if ($products === null) {
            $query = $this->getQuery($appAuth, $categoryId, $filterByNewProducts, $keyword, $productId, $countMode, $getOnlyStockProducts);
            $products = $query->toArray();
            $products = $this->hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($appAuth, $products);
            Cache::write($cacheKey, $products);
        }

        if (! $countMode) {
            return $products;
        } else {
            return count($products);
        }

    }

    protected function getQuery($appAuth, $categoryId, $filterByNewProducts = false, $keyword = '', $productId = 0, $countMode = false, $getOnlyStockProducts = false)
    {

        $this->Product = FactoryLocator::get('Table')->get('Products');

        $query = $this->Product->find('all', [
            'order' => [
                'Products.name' => 'ASC',
                'Images.id_image' => 'DESC',
            ],
        ]);

        $query->where([
            'Products.active' => APP_ON,
            'Manufacturers.active' => APP_ON,
        ]);

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

        if (empty($appAuth->user())) {
            $query->where([
                'Manufacturers.is_private' => APP_OFF,
            ]);
        }

        if ($productId > 0) {
            $query->where([
                'Products.id_product' => $productId,
            ]);
        }

        $query
        ->select('Products.id_product')->distinct()
        ->select($this->Product) // Products
        ->select($this->Product->DepositProducts)
        ->select('Images.id_image')
        ->select($this->Product->Taxes)
        ->select($this->Product->Manufacturers)
        ->select($this->Product->UnitProducts)
        ->select($this->Product->StockAvailables);

        // TODO: only add contains if called from self service controller
        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $query->select(['system_bar_code' => $this->getProductIdentifierField()]);
            $query->select($this->Product->BarcodeProducts);
            $query->contain([
                'BarcodeProducts',
                'ProductAttributes.BarcodeProductAttributes',
            ]);
        }

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
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

        }

        if (!$filterByNewProducts && $categoryId != '') {
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
        }

        if (Configure::read('appDb.FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS')) {
            if ($appAuth->isOrderForDifferentCustomerMode()) {
                $getOnlyStockProducts = true;
            }
        }

        if ($getOnlyStockProducts) {
            $query->where(function (QueryExpression $exp, Query $q) {
                return $exp->and([
                    $q->newExpr()->eq('Manufacturers.stock_management_enabled', APP_ON),
                    $q->newExpr()->eq('Products.is_stock_product', APP_ON),
                ]);
            });
        }

        if ($filterByNewProducts) {
            $dateAdd = date('Y-m-d', strtotime('-' . Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' DAYS'));
            $query->where(function (QueryExpression $exp) use ($dateAdd) {
                return $exp->gt('DATE_FORMAT(Products.created, \'%Y-%m-%d\')', $dateAdd);
            });
        }

        if ($keyword != '') {

            $query->where(function (QueryExpression $exp, Query $q) use($keyword) {
                $or = [
                    $q->newExpr()->like('Products.name', '%'.$keyword.'%'),
                    $q->newExpr()->like('Products.description_short', '%'.$keyword.'%'),
                    $q->newExpr()->eq('Products.id_product', (int) $keyword),
                ];
                if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                    $or[] = $q->newExpr()->like($this->getProductIdentifierField(), strtolower(substr($keyword, 0, 4)));
                    $or[] = $q->newExpr()->eq('BarcodeProducts.barcode', $keyword);
                }
                return $exp->and([
                    $exp->or($or),
                ]);
            });

                /*
                 * only works if above where is commented
                 if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                 $query->contain([
                 'ProductAttributes' => [
                 'conditions' => function(QueryExpression $exp, Query $q) use($keyword) {
                 return $exp->and([
                 $q->newExpr()->eq('BarcodeProductAttributes.barcode', $keyword),
                 ]);
                 }
                 ],
                 ]);
                 }
                 */
        }

        return $query;

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
            $deliveryDate = $this->Product->calculatePickupDayRespectingDeliveryRhythm($product);

            // deactivates the product if it can not be ordered this week
            if ($deliveryDate == 'delivery-rhythm-triggered-delivery-break') {
                $products[$i]->delivery_break_enabled = true;
            }

            // deactivates the product if manufacturer based delivery break is enabled
            if ($this->Product->deliveryBreakEnabled($product->manufacturer->no_delivery_days, $deliveryDate)) {
                $products[$i]->delivery_break_enabled = true;
            }

            // deactivates the product if global delivery break is enabled
            if ($this->Product->deliveryBreakEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $deliveryDate)) {
                $products[$i]->delivery_break_enabled = true;
            }

            // hides products when order_possible_until is reached (do not apply if product is stock product)
            if (!($product->is_stock_product && $product->stock_management_enabled) &&
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

            $productCacheKey = join('_', [
                'Catalog_prepareProducts',
                'productId' => $products[$i]['id_product'],
                $appAuth->isOrderForDifferentCustomerMode() || $appAuth->isSelfServiceModeByUrl(),
                $appAuth->user('shopping_price'),
                $appAuth->isTimebasedCurrencyEnabledForCustomer(),
                'date-' . date('Y-m-d'),
            ]);
            $cachedProduct = Cache::read($productCacheKey);

            if ($cachedProduct === null) {

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
                    $products[$i]['deposit'] = 0;
                }

                if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
                    $products[$i]->next_delivery_day = new FrozenDate('1970-01-01');
                } elseif ($appAuth->isOrderForDifferentCustomerMode() || $appAuth->isSelfServiceModeByUrl()) {
                    $products[$i]->next_delivery_day = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
                } else {
                    $products[$i]->next_delivery_day = $this->Product->calculatePickupDayRespectingDeliveryRhythm($products[$i]);
                }

                if ($appAuth->isTimebasedCurrencyEnabledForCustomer()) {
                    if ($this->Manufacturer->getOptionTimebasedCurrencyEnabled($products[$i]['timebased_currency_enabled'])) {
                        $products[$i]['timebased_currency_money_incl'] = $this->Manufacturer->getTimebasedCurrencyMoney($products[$i]['gross_price'], $products[$i]['timebased_currency_max_percentage']);
                        $products[$i]['timebased_currency_money_excl'] = $this->Manufacturer->getTimebasedCurrencyMoney($products[$i]['price'], $products[$i]['timebased_currency_max_percentage']);
                        $products[$i]['timebased_currency_seconds'] = $this->Manufacturer->getCartTimebasedCurrencySeconds($products[$i]['gross_price'], $products[$i]['timebased_currency_max_percentage']);
                        $products[$i]['timebased_currency_manufacturer_limit_reached'] = $this->Manufacturer->hasManufacturerReachedTimebasedCurrencyLimit($products[$i]['id_manufacturer']);
                    }

                }

                foreach ($product->product_attributes as &$attribute) {

                    $attributePricePerUnit = !empty($attribute->unit_product_attribute) ? $attribute->unit_product_attribute->price_incl_per_unit : 0;
                    $attributeDeposit = !empty($attribute->deposit_product_attribute) ? $attribute->deposit_product_attribute->deposit : 0;

                    $attribute->unit_product_attribute = $attribute->unit_product_attribute ?? (object) [
                        'price_per_unit_enabled' => 0,
                        'price_incl_per_unit' => 0,
                        'quantity_in_units' => 0,
                        'name' => '',
                    ];

                    // START: override shopping with purchase prices / zero prices
                    $modifiedAttributePricesByShoppingPrice = $this->Customer->getModifiedAttributePricesByShoppingPrice($appAuth, $attribute->id_product, $attribute->id_product_attribute, $attribute->price, $attributePricePerUnit, $attributeDeposit, $taxRate);
                    $attribute->price = $modifiedAttributePricesByShoppingPrice['price'];
                    $attribute->unit_product_attribute->price_incl_per_unit = $modifiedAttributePricesByShoppingPrice['price_incl_per_unit'];
                    if (!empty($attribute->deposit_product_attribute)) {
                        $attribute->deposit_product_attribute->deposit = $modifiedAttributePricesByShoppingPrice['deposit'];
                    }
                    // END: override shopping with purchase prices / zero prices

                    $grossPrice = $this->Product->getGrossPrice($attribute->price, $taxRate);

                    $attribute->gross_price = $grossPrice;
                    $attribute->calculated_tax = $grossPrice - $attribute->price;

                    /*
                     if ($appAuth->isTimebasedCurrencyEnabledForCustomer()) {
                     if ($this->Manufacturer->getOptionTimebasedCurrencyEnabled($products[$i]['timebased_currency_enabled'])) {
                     $preparedAttributes['timebased_currency_money_incl'] = $this->Manufacturer->getTimebasedCurrencyMoney($grossPrice, $products[$i]['timebased_currency_max_percentage']);
                     $preparedAttributes['timebased_currency_money_excl'] = $this->Manufacturer->getTimebasedCurrencyMoney($attribute->price, $products[$i]['timebased_currency_max_percentage']);
                     $preparedAttributes['timebased_currency_seconds'] = $this->Manufacturer->getCartTimebasedCurrencySeconds($grossPrice, $products[$i]['timebased_currency_max_percentage']);
                     $preparedAttributes['timebased_currency_manufacturer_limit_reached'] = $this->Manufacturer->hasManufacturerReachedTimebasedCurrencyLimit($products[$i]['id_manufacturer']);
                     }
                     }
                     */
                }
            } else {
                $products[$i] = $cachedProduct;
            }

            Cache::write($productCacheKey, $products[$i]);
            $i++;
        }

        return $products;

    }

}
