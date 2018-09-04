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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CategoriesTable extends AppTable
{

    public function initialize(array $config)
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

    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('name', __('Please_enter_a_name.'));
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

    public function getForMenu()
    {
        $conditions = [
            $this->getAlias() . '.active' => APP_ON
        ];
        $categories = $this->getThreaded($conditions);
        $categorieForMenu = $this->prepareTreeResultForMenu($categories);
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

    public function getForSelect($excludeCategoryId = null)
    {
        $conditions = [];
        if ($excludeCategoryId) {
            $conditions[] = 'Categories.id_category != ' . $excludeCategoryId;
        }
        $categories = $this->getThreaded($conditions);
        $flattenedCategories = $this->flattenNestedArrayWithChildren($categories);
        return $flattenedCategories;
    }

    /**
     * custom sql for best performance
     * product attributes ARE NOT fetched in this query!
     */
    public function getProductsByCategoryId($categoryId, $filterByNewProducts = false, $keyword = '', $productId = 0, $countMode = false)
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
            $params['keyword'] = '%' . $keyword . '%';
            $sql .= " AND (Products.name LIKE :keyword OR Products.description_short LIKE :keyword) ";
        }

        if ($productId > 0) {
            $params['productId'] = $productId;
            $sql .= " AND Products.id_product = :productId ";
        }

        $sql .= $this->getOrdersForProductListQuery();
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $products = $statement->fetchAll('assoc');
        $products = $this->hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($products);

        if (! $countMode) {
            return $products;
        } else {
            return count($products);
        }

        return $products;
    }

    /**
     *
     * @param array $conditions
     */
    public function prepareTreeResultForMenu($items)
    {
        $itemsForMenu = [];
        foreach ($items as $index => $item) {
            $itemsForMenu[] = $this->buildItemForTree($item, $index);
        }
        return $itemsForMenu;
    }

    private function buildItemForTree($item, $index)
    {
        $productCount = $this->getProductsByCategoryId($item->id_category, false, '', 0, true);

        $tmpMenuItem = [
            'name' => $item->name . ' <span class="additional-info">(' . $productCount . ')</span>',
            'slug' => Configure::read('app.slugHelper')->getCategoryDetail($item->id_category, $item->name),
            'children' => []
        ];
        if (! empty($item->children)) {
            foreach ($item->children as $index => $child) {
                $tmpMenuItem['children'][] = $this->buildItemForTree($child, $index);
            }
        }

        return $tmpMenuItem;
    }
}
