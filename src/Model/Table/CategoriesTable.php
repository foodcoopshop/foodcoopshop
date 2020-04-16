<?php

namespace App\Model\Table;

use Cake\Core\Configure;
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

    private function flattenNestedArrayWithChildren($array, $separator = '')
    {
        foreach ($array as $item) {
            $statusString = '';
            if (! $item->active) {
                $statusString = ' ('.__('offline').')';
            }
            $this->flattenedArray[$item->id_category] = $separator . $item->name . $statusString;
            if (! empty($item['children'])) {
                $this->flattenNestedArrayWithChildren($item->children, str_repeat('-', $this->getLevel($item) + 1) . ' ');
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

    public function getForSelect($excludeCategoryId = null, $showOfflineCategories=true)
    {
        $conditions = [];
        if ($excludeCategoryId) {
            $conditions[] = 'Categories.id_category != ' . $excludeCategoryId;
        }
        if (!$showOfflineCategories) {
            $conditions['Categories.active'] = true;
        }
        $categories = $this->getThreaded($conditions);
        $flattenedCategories = $this->flattenNestedArrayWithChildren($categories);
        $flattenedCategories = array_map(function($category) {
            return html_entity_decode($category);
        }, $flattenedCategories);
        return $flattenedCategories;
    }

    /**
     * custom sql for best performance
     * product attributes ARE NOT fetched in this query!
     */
    public function getProductsByCategoryId($appAuth, $categoryId, $filterByNewProducts = false, $keyword = '', $productId = 0, $countMode = false, $getOnlyStockProducts = false)
    {
        $params = [
            'active' => APP_ON
        ];
        if (! $this->getLoggedUser()) {
            $params['isPrivate'] = APP_OFF;
        }

        $sql = 'SELECT ';
        $sql .= $this->getFieldsForProductListQuery();
        $sql .= "FROM ".$this->tablePrefix."product Products ";

        if (! $filterByNewProducts) {
            $sql .= "LEFT JOIN ".$this->tablePrefix."category_product CategoryProducts ON CategoryProducts.id_product = Products.id_product
                 LEFT JOIN ".$this->tablePrefix."category Categories ON CategoryProducts.id_category = Categories.id_category ";
        }

        $sql .= $this->getJoinsForProductListQuery();
        $sql .= $this->getConditionsForProductListQuery();

        if (! $filterByNewProducts) {
            $params['categoryId'] = $categoryId;
            $sql .= " AND CategoryProducts.id_category = :categoryId ";
            $sql .= " AND Categories.active = :active";
        }

        if ($filterByNewProducts) {
            $params['dateAdd'] = date('Y-m-d', strtotime('-' . Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' DAYS'));
            $sql .= " AND DATE_FORMAT(Products.created, '%Y-%m-%d') > :dateAdd";
        }

        if ($keyword != '') {
            
            $params['keywordLike'] = '%' . $keyword . '%';
            $params['keyword'] = $keyword;
            $sql .= " AND (Products.name LIKE :keywordLike OR Products.description_short LIKE :keywordLike OR Products.id_product = :keyword ";
            
            if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                $params['barcodeIdentifier'] = strtolower(substr($keyword, 0, 4));
                $sql .= " OR " . $this->getProductIdentifierField() . " = :barcodeIdentifier";
            }
                
            $sql .= ")";
            
        }

        if ($productId > 0) {
            $params['productId'] = $productId;
            $sql .= " AND Products.id_product = :productId ";
        }
        
        if ($getOnlyStockProducts) {
            $sql .= " AND (Products.is_stock_product = 1 AND Manufacturers.stock_management_enabled = 1) ";
        }

        $sql .= $this->getOrdersForProductListQuery();
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $products = $statement->fetchAll('assoc');
        $products = $this->hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($appAuth, $products);
        
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
