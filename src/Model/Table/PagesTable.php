<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;
use Cake\ORM\Query\SelectQuery;
/**
 * FoodCoopShop - The open source software for your foodcoop
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 */
class PagesTable extends AppTable
{

    /**
     * @var array<int, string>
     */
    private array $flattenedArray = [];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_page');
        $this->addBehavior('Tree', [
            'parent' => 'id_parent'
        ]);
        $this->addBehavior('Timestamp');
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('title', __('Please_enter_a_title.'));
        $validator->minLength('title', 2, __('Please_enter_at_least_{0}_characters.', [2]));
        $validator->range('position', [-1, 1001], __('Please_enter_a_number_between_{0}_and_{1}.', [0, 1000]));
        $validator->urlWithProtocol('extern_url', __('Please_enter_a_valid_internet_address.'));
        $validator->allowEmptyString('extern_url');
        return $validator;
    }

    /**
     * @param list<\App\Model\Entity\Page> $pages
     * @return array<int, string>
     */
    private function flattenNestedArrayWithChildren(array $pages, string $separator = ''): array
    {
        foreach ($pages as $page) {
            $statusString = '';
            if (!$page->active) {
                $statusString = ' ('.__('offline').')';
            }
            $this->flattenedArray[$page->id_page] = $separator . $page->title . $statusString;
            if (!empty($page->children)) {
                $this->flattenNestedArrayWithChildren($page->children, str_repeat('-',
                $this->getLevel($page) + 1) . ' ');
            }
        }

        return $this->flattenedArray;
    }

    /**
     * @param array<int|string, mixed> $conditions
     * @return SelectQuery<\App\Model\Entity\Page>
     */
    public function getThreaded(array $conditions = []): SelectQuery
    {
        $pages = $this->find('threaded',
        parentField: 'id_parent',
        conditions: $conditions,
        order: [
            'Pages.menu_type' => 'DESC',
            'Pages.position' => 'ASC',
            'Pages.title' => 'ASC',
        ],
        contain: [
            'Customers',
        ]);
        return $pages;
    }

    /**
     * @return array<int, string>
     */
    public function getForSelect(?int $excludePageId = null): array
    {
        $conditions = [];
        if ($excludePageId) {
            $conditions[] = 'Pages.active > ' . APP_DEL;
        }
        $pages = $this->getThreaded($conditions);
        $flattenedPages = $this->flattenNestedArrayWithChildren($pages->toArray());
        return $flattenedPages;
    }
}
