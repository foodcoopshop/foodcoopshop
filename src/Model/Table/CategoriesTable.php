<?php

namespace App\Model\Table;

use App\Model\Traits\ProductCacheClearAfterDeleteTrait;
use App\Model\Traits\ProductCacheClearAfterSaveTrait;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Query;
use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CategoriesTable extends AppTable
{

    use ProductCacheClearAfterDeleteTrait;
    use ProductCacheClearAfterSaveTrait;

    public function initialize(array $config): void
    {
        $this->setTable('category');
        $this->addBehavior('Tree', [
            'left' => 'nleft',
            'right' => 'nright',
            'parent' => 'id_parent'
        ]);
        parent::initialize($config);
        $this->setPrimaryKey('id_category');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('name', __('Please_enter_a_name.'));
        return $validator;
    }

    private $flattenedArray = [];

    private function getChildrenIds($item) {
        $childrenIds = [];
        if (!empty($item->children)) {
            foreach($item->children as $child) {
                $childrenIds[] = $child->id_category;
                if (!empty($child->children)) {
                    $childrenIds = array_merge($childrenIds, $this->getChildrenIds($child));
                }
            }
        }
        return $childrenIds;

    }

    private function flattenNestedArrayWithChildren($array, $renderParentIdAndChildrenIdContainers, $separator = '')
    {
        foreach ($array as $item) {
            $statusString = '';
            if (! $item->active) {
                $statusString = ' ('.__('offline').')';
            }

            $parentIdString = '';
            $childrenIdsString = '';
            if ($renderParentIdAndChildrenIdContainers) {
                if ($item->id_parent > 0) {
                    $parentIdString = '<span class="parent-id hide">' . $item->id_parent . '</span>';
                }
                $childrenIds = $this->getChildrenIds($item);
                if (count($childrenIds) > 0) {
                    $childrenIdsString = '<span class="children-ids hide">' . join(',', $childrenIds) . '</span>';
                }
            }
            $this->flattenedArray[$item->id_category] = $separator . $item->name . $statusString . $parentIdString . $childrenIdsString;
            if (! empty($item['children'])) {
                $this->flattenNestedArrayWithChildren($item->children, $renderParentIdAndChildrenIdContainers, str_repeat('-', $this->getLevel($item) + 1) . ' ');
            }
        }

        return $this->flattenedArray;
    }

    public function getForMenu($appAuth)
    {
        $conditions = [
            $this->getAlias() . '.active' => APP_ON
        ];
        $categories = $this->getThreaded($conditions);
        $categorieForMenu = $this->prepareTreeResultForMenu($appAuth, $categories);
        return $categorieForMenu;
    }

    public function getExcludeCondition()
    {
        return $this->getAlias() . '.id_category NOT IN(1, 2, ' . Configure::read('app.categoryAllProducts') . ')';
    }

    public function getThreaded($conditions = [])
    {
        $conditions = array_merge($conditions, [
            $this->getExcludeCondition()
        ]);

        $categories = $this->find('threaded', [
            'parentField' => 'id_parent',
            'conditions' => $conditions,
            'order' => [
                'Categories.name' => 'ASC'
            ]
        ]);
        return $categories;
    }

    public function getForSelect($excludeCategoryId=null, $showOfflineCategories=true, $renderParentIdAndChildrenIdContainers=false, $appAuth=null, $showProductCount=false)
    {
        $conditions = [];
        if ($excludeCategoryId) {
            $conditions[] = 'Categories.id_category != ' . $excludeCategoryId;
        }
        if (!$showOfflineCategories) {
            $conditions['Categories.active'] = APP_ON;
        }
        $categories = $this->getThreaded($conditions)->toArray();

        $flattenedCategories = $this->flattenNestedArrayWithChildren($categories, $renderParentIdAndChildrenIdContainers, '');

        $flattenedCategories = array_map(function($category) {
            return html_entity_decode($category);
        }, $flattenedCategories);

        if ($showProductCount) {
            foreach($flattenedCategories as $categoryId => $category) {
                $productCount = $this->getProductsByCategoryId($appAuth, $categoryId, false, '', 0, true, true);
                $flattenedCategories[$categoryId] .= ' (' . $productCount . ')';
            }
        }

        return $flattenedCategories;
    }

    /**
     * custom sql for best performance
     * product attributes ARE NOT fetched in this query!
     */
    public function getProductsByCategoryId($appAuth, $categoryId, $filterByNewProducts = false, $keyword = '', $productId = 0, $countMode = false, $getOnlyStockProducts = false)
    {
        $cacheKey = join('_', [
            'CategoriesTable_getProductsByCategoryId',
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

            $this->Product = FactoryLocator::get('Table')->get('Products');

            $query = $this->Product->find('all', [
                'order' => $this->getOrdersForProductListQuery(),
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

            /*
            if ($keyword != '') {

                $params['keywordLike'] = '%' . $keyword . '%';
                $params['keyword'] = $keyword;

                // use id_product LIKE and not = because barcode search "SELECT * FROM fcs_product WHERE id_product LIKE '1a1b0000'" would find product with ID 1
                $sql .= " AND (Products.name LIKE :keywordLike OR Products.description_short LIKE :keywordLike OR Products.id_product LIKE :keyword ";

                if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                    $params['barcodeIdentifier'] = strtolower(substr($keyword, 0, 4));
                    $sql .= " OR " . $this->getProductIdentifierField() . " = :barcodeIdentifier";
                    $sql .= " OR ProductBarcodes.barcode = :keyword";
                    $sql .= " OR ProductAttributeBarcodes.barcode = :keyword";
                }

                $sql .= ")";

            }
            */

            $products = $query->toArray();

            //$products = $this->hideMultipleAttributes($products);
            // implement in SQL
            $products = $this->hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($appAuth, $products);

            Cache::write($cacheKey, $products);
        }

        if (! $countMode) {
            return $products;
        } else {
            return count($products);
        }

    }

    /**
     *
     * @param array $conditions
     */
    public function prepareTreeResultForMenu($appAuth, $items)
    {
        $itemsForMenu = [];
        foreach ($items as $index => $item) {
            $itemsForMenu[] = $this->buildItemForTree($appAuth, $item, $index);
        }
        return $itemsForMenu;
    }

    private function buildItemForTree($appAuth, $item, $index)
    {
        $productCount = $this->getProductsByCategoryId($appAuth, $item->id_category, false, '', 0, true);

        $tmpMenuItem = [
            'name' => $item->name . ' <span class="additional-info">(' . $productCount . ')</span>',
            'slug' => Configure::read('app.slugHelper')->getCategoryDetail($item->id_category, $item->name),
            'children' => []
        ];
        if (! empty($item->children)) {
            foreach ($item->children as $index => $child) {
                $tmpMenuItem['children'][] = $this->buildItemForTree($appAuth, $child, $index);
            }
        }

        return $tmpMenuItem;
    }
}
