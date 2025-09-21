<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use App\Services\CatalogService;
use Cake\ORM\Query\SelectQuery;
use App\Model\Entity\Category;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 * 
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 */
class CategoriesTable extends AppTable
{

    use ProductCacheClearAfterSaveAndDeleteTrait;

    /**
     * @var array<int, string>
     */
    private array $flattenedArray = [];

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

    private function getChildrenIds(Category $category): array
    {
        $childrenIds = [];
        if (!empty($category->children)) {
            foreach($category->children as $child) {
                $childrenIds[] = $child->id_category;
                if (!empty($child->children)) {
                    $childrenIds = array_merge($childrenIds, $this->getChildrenIds($child));
                }
            }
        }
        return $childrenIds;

    }

    /**
     * @param \App\Model\Entity\Category[] $categories
     */
    private function flattenNestedArrayWithChildren(array $categories, bool $renderParentIdAndChildrenIdContainers, string $separator = ''): array
    {
        foreach ($categories as $category) {
            $statusString = '';
            if (! $category->active) {
                $statusString = ' ('.__('offline').')';
            }

            $parentIdString = '';
            $childrenIdsString = '';
            if ($renderParentIdAndChildrenIdContainers) {
                if ($category->id_parent > 0) {
                    $parentIdString = '<span class="parent-id hide">' . $category->id_parent . '</span>';
                }
                $childrenIds = $this->getChildrenIds($category);
                if (count($childrenIds) > 0) {
                    $childrenIdsString = '<span class="children-ids hide">' . join(',', $childrenIds) . '</span>';
                }
            }
            $this->flattenedArray[$category->id_category] = $separator . $category->name . $statusString . $parentIdString . $childrenIdsString;
            if (! empty($category['children'])) {
                $this->flattenNestedArrayWithChildren($category->children, $renderParentIdAndChildrenIdContainers, str_repeat('-',
                $this->getLevel($category) + 1) . ' ');
            }
        }

        return $this->flattenedArray;
    }

    public function getForMenu(): array
    {
        $conditions = [
            $this->aliasField('active') => APP_ON,
        ];
        $categories = $this->getThreaded($conditions);
        $categorieForMenu = $this->prepareTreeResultForMenu($categories);
        return $categorieForMenu;
    }

    public function getExcludeCondition(): string
    {
        return $this->aliasField('id_category') . ' NOT IN(1, 2, ' . Configure::read('app.categoryAllProducts') . ')';
    }

    /**
     * @param array<int|string, mixed> $conditions
     * @return SelectQuery<\App\Model\Entity\Category>
     */
    public function getThreaded(array $conditions = []): SelectQuery
    {
        $conditions = array_merge($conditions, [
            $this->getExcludeCondition()
        ]);

        $categories = $this->find('threaded',
            parentField: 'id_parent',
            conditions: $conditions,
            order: [
                $this->aliasField('name') => 'ASC',
            ],
        );
        return $categories;
    }

    public function getForSelect(
        ?int $excludeCategoryId=null,
        bool $showOfflineCategories=true,
        bool $renderParentIdAndChildrenIdContainers=false,
        bool $showProductCount=false,
        ): array
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
            $catalogService = new CatalogService();
            foreach($flattenedCategories as $categoryId => $category) {
                $productCount = $catalogService->getProducts($categoryId, false, '', 0, true, Configure::read('app.selfServiceModeShowOnlyStockProducts'));	
                $flattenedCategories[$categoryId] .= ' (' . $productCount . ')';
            }
        }

        return $flattenedCategories;
    }

    public function prepareTreeResultForMenu(SelectQuery $items): array
    {
        $itemsForMenu = [];
        foreach ($items as $index => $item) {
            $itemsForMenu[] = $this->buildItemForTree($item, $index);
        }
        return $itemsForMenu;
    }

    private function buildItemForTree(Category $category, int $index): array
    {
        $tmpMenuItem = [
            'name' => $category->name,
            'slug' => Configure::read('app.slugHelper')->getCategoryDetail($category->id_category, $category->name),
            'children' => []
        ];
        if (! empty($category->children)) {
            foreach ($category->children as $index => $child) {
                $tmpMenuItem['children'][] = $this->buildItemForTree($child, $index);
            }
        }

        return $tmpMenuItem;
    }
}
