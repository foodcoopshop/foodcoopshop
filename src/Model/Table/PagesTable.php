<?php

namespace App\Model\Table;
use Cake\Core\Configure;

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

    public $primaryKey = 'id_page';
    
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->addBehavior('Tree', [
            'parent' => 'id_parent'
        ]);
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
    }

    public $validate = [
        'position' => [
            'number' => [
                'allowEmpty' => true,
                'rule' => [
                    'range',
                    - 1,
                    1001
                ],
                'message' => 'Bitte gibt eine Zahl von 0 bis 1000 an'
            ]
        ],
        'extern_url' => [
            'allowEmpty' => true,
            'rule' => [
                'url',
                true
            ],
            'message' => 'Bitte gibt eine gültige Internet-Adresse an.'
        ],
        'title' => [
            'notBlank' => [
                'rule' => [
                    'notBlank'
                ],
                'message' => 'Bitte gib einen Titel an.'
            ],
            'minLength' => [
                'rule' => [
                    'minLength',
                    3
                ],
                'message' => 'Bitte gib mindestens 3 Zeichen ein.'
            ]
        ]
    ];

    public function findAllGroupedByMenu($conditions)
    {

        $pages = $this->find('threaded', [
            'conditions' => $conditions,
            'order' => [
                'Pages.menu_type' => 'DESC',
                'Pages.position' => 'ASC',
                'Pages.title' => 'ASC'
            ],
            'contain' => [
                'Customers.name'
            ]
        ]);
        return $pages;
    }

    public function getMainPagesForDropdown($pageIdToExcluce = null)
    {
        $conditions = [
            'Pages.id_parent IS NULL',
            'Pages.active > ' . APP_DEL
        ];
        if ($pageIdToExcluce > 0) {
            $conditions[] = 'Pages.id_page != ' . $pageIdToExcluce;
        }
        $pages = $this->find('all', [
            'conditions' => $conditions,
            'order' => [
                'Pages.menu_type' => 'DESC',
                'Pages.position' => 'ASC',
                'Pages.title' => 'ASC'
            ]
        ]);

        $preparedPages = [];
        foreach ($pages as $page) {
            $preparedPages[$page['Pages']['id_page']] = $page['Pages']['title'] . ' - ' . Configure::read('AppConfig.htmlHelper')->getMenuType($page['Pages']['menu_type']);
        }
        return $preparedPages;
    }
}
