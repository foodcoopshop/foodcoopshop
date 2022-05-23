<?php

namespace App\Model\Table;

use App\Lib\Catalog\Catalog;
use App\Model\Traits\ProductCacheClearAfterDeleteTrait;
use App\Model\Traits\ProductCacheClearAfterSaveTrait;
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
            $this->Catalog = new Catalog();
            foreach($flattenedCategories as $categoryId => $category) {
                $productCount = $this->Catalog->getProducts($appAuth, $categoryId, false, '', 0, true, Configure::read('app.selfServiceModeShowOnlyStockProducts'));	
                $flattenedCategories[$categoryId] .= ' (' . $productCount . ')';
            }
        }

        return $flattenedCategories;
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
        $this->Catalog = new Catalog();
        $productCount = $this->Catalog->getProducts($appAuth, $item->id_category, false, '', 0, true);

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
