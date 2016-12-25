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

    public $useTable = 'cms';
    public $primaryKey = 'id_cms';

    public $actsAs = array(
        'Containable',
        'Tree' => array(
            'parent' => 'id_parent'
        )
    );

    public $belongsTo = array(
        'PageLang' => array(
            'foreignKey' => 'id_cms'
        ),
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
        'url' => array(
            'allowEmpty' => true,
            'rule' => array(
                'url',
                true
            ),
            'message' => 'Bitte gibt eine gültige Internet-Adresse an.'
        )
    );

    public function findAllGroupedByMenu($conditions)
    {
        $pages = $this->find('threaded', array(
            'conditions' => $conditions,
            'order' => array(
                'Page.menu_type' => 'DESC',
                'Page.position' => 'ASC',
                'PageLang.meta_title' => 'ASC'
            ),
            'contain' => array(
                'Customer.name',
                'PageLang.meta_title',
                'PageLang.link_rewrite'
            )
        ));
        return $pages;
    }

    public function getPageForFrontend($pageId, $appAuth)
    {
        
        $conditions = array(
            'Page.id_cms' => $pageId,
            'Page.active' => APP_ON,
            'PageLang.id_lang' => Configure::read('app.langId'),
            'PageLang.id_shop' => Configure::read('app.shopId')
        );
        
        if (! $appAuth->loggedIn()) {
            $conditions['Page.is_private'] = APP_OFF;
        }
        
        $page = $this->find('first', array(
            'conditions' => $conditions,
            'contain' => array(
                'PageLang.meta_title',
                'PageLang.link_rewrite',
                'PageLang.content'
            )
        ));
        return $page;
    }

    public function getMainPagesForDropdown($pageIdToExcluce = null)
    {
        $conditions = array(
            'Page.id_parent IS NULL',
            'Page.active > ' . APP_DEL
        );
        if ($pageIdToExcluce > 0) {
            $conditions[] = 'Page.id_cms != ' . $pageIdToExcluce;
        }
        $pages = $this->find('all', array(
            'conditions' => $conditions,
            'order' => array(
                'Page.menu_type' => 'DESC',
                'Page.position' => 'ASC',
                'PageLang.meta_title' => 'ASC'
            ),
            'contain' => array(
                'PageLang.meta_title'
            )
        ));
        
        $preparedPages = array();
        foreach ($pages as $page) {
            $preparedPages[$page['Page']['id_cms']] = $page['PageLang']['meta_title'] . ' - ' . Configure::read('htmlHelper')->getMenuType($page['Page']['menu_type']);
        }
        return $preparedPages;
    }
}

?>