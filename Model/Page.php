<?php
/**
 * Page
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
class Page extends AppModel
{

    public $useTable = 'pages';
    public $primaryKey = 'id_page';

    public $actsAs = array(
        'Containable',
        'Tree' => array(
            'parent' => 'id_parent'
        )
    );

    public $belongsTo = array(
        'Customer' => array(
            'foreignKey' => 'id_customer'
        )
    );

    public $validate = array(
        'position' => array(
            'number' => array(
                'allowEmpty' => true,
                'rule' => array(
                    'range',
                    - 1,
                    1001
                ),
                'message' => 'Bitte gibt eine Zahl von 0 bis 1000 an'
            )
        ),
        'extern_url' => array(
            'allowEmpty' => true,
            'rule' => array(
                'url',
                true
            ),
            'message' => 'Bitte gibt eine gültige Internet-Adresse an.'
        ),
        'title' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib einen Titel an.'
            ),
            'minLength' => array(
                'rule' => array(
                    'minLength',
                    3
                ),
                'message' => 'Bitte gib mindestens 3 Zeichen ein.'
            )
        )
    );

    public function findAllGroupedByMenu($conditions)
    {

        $pages = $this->find('threaded', array(
            'conditions' => $conditions,
            'order' => array(
                'Page.menu_type' => 'DESC',
                'Page.position' => 'ASC',
                'Page.title' => 'ASC'
            ),
            'contain' => array(
                'Customer.name'
            )
        ));
        return $pages;
    }

    public function getMainPagesForDropdown($pageIdToExcluce = null)
    {
        $conditions = array(
            'Page.id_parent IS NULL',
            'Page.active > ' . APP_DEL
        );
        if ($pageIdToExcluce > 0) {
            $conditions[] = 'Page.id_page != ' . $pageIdToExcluce;
        }
        $pages = $this->find('all', array(
            'conditions' => $conditions,
            'order' => array(
                'Page.menu_type' => 'DESC',
                'Page.position' => 'ASC',
                'Page.title' => 'ASC'
            )
        ));

        $preparedPages = array();
        foreach ($pages as $page) {
            $preparedPages[$page['Page']['id_page']] = $page['Page']['title'] . ' - ' . Configure::read('htmlHelper')->getMenuType($page['Page']['menu_type']);
        }
        return $preparedPages;
    }
}
