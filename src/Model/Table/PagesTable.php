<?php

namespace App\Model\Table;

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PagesTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_page');
        $this->addBehavior('Tree', [
            'parent' => 'id_parent'
        ]);
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
    }

    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('title', 'Bitte gib einen Titel an.');
        $validator->minLength('title', 2, 'Bitte gib mindestens 3 Zeichen ein.');
        $validator->range('position', [-1, 1001], 'Bitte gibt eine Zahl von 0 bis 1000 an.');
        $validator->urlWithProtocol('extern_url', 'Bitte gibt eine gültige Internet-Adresse an.');
        $validator->allowEmpty('extern_url');
        return $validator;
    }

    private $flattenedArray = [];

    private function flattenNestedArrayWithChildren($array, $separator = '')
    {
        foreach ($array as $item) {
            $statusString = '';
            if (! $item->active) {
                $statusString = ' (offline)';
            }
            $this->flattenedArray[$item->id_page] = $separator . $item->title . $statusString;
            if (! empty($item['children'])) {
                $this->flattenNestedArrayWithChildren($item->children, str_repeat('-', $this->getLevel($item) + 1) . ' ');
            }
        }

        return $this->flattenedArray;
    }

    public function getThreaded($conditions = [])
    {
        $pages = $this->find('threaded', [
            'parentField' => 'id_parent',
            'conditions' => $conditions,
            'order' => [
                'Pages.menu_type' => 'DESC',
                'Pages.position' => 'ASC',
                'Pages.title' => 'ASC'
            ],
            'contain' => [
                'Customers'
            ]
        ]);
        return $pages;
    }

    public function getForSelect($excludePageId = null)
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
