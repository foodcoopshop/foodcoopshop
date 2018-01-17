<?php
/**
 * Category
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class Category extends AppModel
{

    public $actsAs = [
        'Tree' => [
            'left' => 'nleft',
            'right' => 'nright',
            'parent' => 'id_parent',
            'level' => 'level_depth'
        ],
        'Content'
    ];

    public $useTable = 'category';
    public $primaryKey = 'id_category';

    public $validate = [
        'name' => [
            'notBlank' => [
                'rule' => [
                    'notBlank'
                ],
                'message' => 'Bitte gib einen Namen an.'
            ]
        ]
    ];

    private $flattenedArray = [];

    private function flattenNestedArrayWithChildren($array, $separator = '')
    {
        foreach ($array as $item) {
            $statusString = '';
            if (! $item['Category']['active']) {
                $statusString = ' (offline)';
            }
            $this->flattenedArray[$item['Category']['id_category']] = $separator . $item['Category']['name'] . $statusString;
            if (! empty($item['children'])) {
                $this->flattenNestedArrayWithChildren($item['children'], str_repeat('-', $item['Category']['level_depth'] - 1) . ' ');
            }
        }

        return $this->flattenedArray;
    }

    public function getForMenu()
    {
        $conditions = [
            $this->alias . '.active' => APP_ON
        ];
        $categories = $this->getThreaded($conditions);
        $categorieForMenu = $this->prepareTreeResultForMenu($categories);
        return $categorieForMenu;
    }

    public function getExcludeCondition()
    {
        return $this->alias . '.id_category NOT IN(1, 2, ' . Configure::read('AppConfig.categoryAllProducts') . ')';
    }

    public function getThreaded($conditions = [])
    {
        $conditions = array_merge($conditions, [
            $this->getExcludeCondition()
        ]);

        $categories = $this->find('threaded', [
            'conditions' => $conditions,
            'order' => [
                'Category.name' => 'ASC'
            ]
        ]);
        return $categories;
    }

    public function getForSelect($excludeCategoryId = null)
    {
        $conditions = [];
        if ($excludeCategoryId) {
            $conditions[] = 'Category.id_category != ' . $excludeCategoryId;
        }
        $categories = $this->getThreaded($conditions);
        $flattenedCategories = $this->flattenNestedArrayWithChildren($categories);
        return $flattenedCategories;
    }

    /**
     * custom sql for best performance
     */
    public function getProductsByCategoryId($categoryId, $filterByNewProducts = false, $keyword = '', $productId = 0, $countMode = false)
    {
        $params = [
            'active' => APP_ON,
            'langId' => Configure::read('AppConfig.langId'),
            'shopId' => Configure::read('AppConfig.shopId')
        ];
        if (! $this->user()) {
            $params['isPrivate'] = APP_OFF;
        }

        $sql = 'SELECT ';
        if ($countMode) {
            $sql .= 'DISTINCT COUNT(*) as count ';
        } else {
            $sql .= $this->getFieldsForProductListQuery();
        }
        $sql .= "FROM ".$this->tablePrefix."product Product ";

        if (! $filterByNewProducts) {
            $sql .= "LEFT JOIN ".$this->tablePrefix."category_product CategoryProduct ON CategoryProduct.id_product = Product.id_product
                 LEFT JOIN ".$this->tablePrefix."category Category ON CategoryProduct.id_category = Category.id_category ";
        }

        $sql .= $this->getJoinsForProductListQuery();
        $sql .= $this->getConditionsForProductListQuery();

        if (! $filterByNewProducts) {
            $params['categoryId'] = $categoryId;
            $sql .= " AND CategoryProduct.id_category = :categoryId ";
            $sql .= " AND Category.active = :active";
        }

        if ($filterByNewProducts) {
            $params['dateAdd'] = date('Y-m-d', strtotime('-' . Configure::read('AppConfig.db_config_FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' DAYS'));
            $sql .= " AND DATE_FORMAT(ProductShop.date_add, '%Y-%m-%d') > :dateAdd";
        }

        if ($keyword != '') {
            $params['keyword'] = '%' . $keyword . '%';
            $sql .= " AND (ProductLang.name LIKE :keyword OR ProductLang.description_short LIKE :keyword) ";
        }

        if ($productId > 0) {
            $params['productId'] = $productId;
            $sql .= " AND Product.id_product = :productId ";
        }

        $sql .= $this->getOrdersForProductListQuery();
        $products = $this->getDataSource()->fetchAll($sql, $params);

        if (! $countMode) {
            return $products;
        } else {
            return $products[0][0]['count'];
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
        $productCount = $this->getProductsByCategoryId($item['Category']['id_category'], false, '', 0, true);

        $tmpMenuItem = [
            'name' => $item['Category']['name'] . ' <span class="additional-info">(' . $productCount . ')</span>',
            'slug' => Configure::read('AppConfig.slugHelper')->getCategoryDetail($item['Category']['id_category'], $item['Category']['name'])
        ];
        if (! empty($item['children'])) {
            foreach ($item['children'] as $index => $child) {
                $tmpMenuItem['children'][] = $this->buildItemForTree($child, $index);
            }
        }

        return $tmpMenuItem;
    }
}
