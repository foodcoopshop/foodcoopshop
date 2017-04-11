<?php
/**
 * BlogPost
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
class BlogPost extends AppModel
{

    public $useTable = 'smart_blog_post';
    public $primaryKey = 'id_smart_blog_post';

    public $hasOne = array(
        'BlogPostLang' => array(
            'foreignKey' => 'id_smart_blog_post'
        ),
        'BlogPostShop' => array(
            'foreignKey' => 'id_smart_blog_post'
        )
    );

    public $belongsTo = array(
        'Customer' => array(
            'foreignKey' => 'id_customer'
        ),
        'Manufacturer' => array(
            'foreignKey' => 'id_manufacturer'
        )
    );

    public function findFeatured($appAuth)
    {
        return $this->findBlogPosts(null, $appAuth, null, true);
    }

    public function findBlogPosts($limit = null, $appAuth, $manufacturerId = null, $isFeatured = null)
    {
        $conditions = array(
            'BlogPost.active' => APP_ON,
            'BlogPostLang.id_lang' => Configure::read('app.langId'),
            'BlogPostShop.id_shop' => Configure::read('app.shopId')
        );
        if (! $appAuth->loggedIn()) {
            $conditions['BlogPost.is_private'] = APP_OFF;
        }
        if ($manufacturerId) {
            $conditions['BlogPost.id_manufacturer'] = $manufacturerId;
        }
        if ($isFeatured) {
            $conditions['BlogPost.is_featured'] = true;
        }

        $blogPosts = $this->find('all', array(
            'conditions' => $conditions,
            'order' => array(
                'BlogPost.modified' => 'DESC'
            ),
            'limit' => $limit
        ));

        return $blogPosts;
    }
}
