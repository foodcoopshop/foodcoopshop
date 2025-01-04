<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;
use Cake\ORM\Query\SelectQuery;

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
 */
class PagesTable extends AppTable
{

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

    private function flattenNestedArrayWithChildren($array, $separator = ''): array
    {
        foreach ($array as $item) {
            $statusString = '';
            if (! $item->active) {
                $statusString = ' ('.__('offline').')';
            }
            $this->flattenedArray[$item->id_page] = $separator . $item->title . $statusString;
            if (! empty($item['children'])) {
                $this->flattenNestedArrayWithChildren($item->children, str_repeat('-',
                /** @phpstan-ignore-next-line */
                $this->getLevel($item) + 1) . ' ');
            }
        }

        return $this->flattenedArray;
    }

    public function getThreaded($conditions = []): SelectQuery
    {
        $pages = $this->find('threaded',
        parentField: 'id_parent',
        conditions: $conditions,
        order: [
            'Pages.menu_type' => 'DESC',
            'Pages.position' => 'ASC',
            'Pages.title' => 'ASC'
        ],
        contain: [
            'Customers'
        ]);
        return $pages;
    }

    public function getForSelect($excludePageId = null): array
    {
        $conditions = [];
        if ($excludePageId) {
            $conditions[] = 'Pages.active > ' . APP_DEL;
        }
        $pages = $this->getThreaded($conditions);
        $flattenedPages = $this->flattenNestedArrayWithChildren($pages);
        return $flattenedPages;
    }
}
