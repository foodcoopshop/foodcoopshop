<?php

App::uses('FrontendController', 'Controller');

/**
 * BlogPostsController
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
class BlogPostsController extends FrontendController
{

    public function detail()
    {
        $blogPostId = (int) $this->params['pass'][0];
        
        $conditions = array(
            'BlogPost.active' => APP_ON,
            'BlogPostLang.id_lang' => Configure::read('app.langId'),
            'BlogPostShop.id_shop' => Configure::read('app.shopId')
        );
        if (! $this->AppAuth->loggedIn()) {
            $conditions['BlogPost.is_private'] = APP_OFF;
        }
        
        $conditions['BlogPost.id_smart_blog_post'] = $blogPostId; // needs to be last element of conditions
        
        $order = array(
            'BlogPost.modified' => 'DESC'
        );
        
        $blogPost = $this->BlogPost->find('first', array(
            'conditions' => $conditions,
            'order' => $order
        ));
        
        if (empty($blogPost)) {
            throw new MissingActionException('blogPost not found');
        }
        
        $correctSlug = Configure::read('slugHelper')->getBlogPostDetail($blogPostId, $blogPost['BlogPostLang']['meta_title']);
        if ($correctSlug != Configure::read('slugHelper')->getBlogPostDetail($blogPostId, StringComponent::removeIdFromSlug($this->params['pass'][0]))) {
            $this->redirect($correctSlug);
        }
        
        $this->set('blogPost', $blogPost);
        
        array_pop($conditions); // do not filter last condition element blogPostId
        $neighbors = $this->BlogPost->find('neighbors', array(
            'field' => 'BlogPost.modified',
            'value' => $blogPost['BlogPost']['modified'],
            'conditions' => $conditions,
            'order' => $order
        ));
        $this->set('neighbors', $neighbors);
        
        $this->set('title_for_layout', $blogPost['BlogPostLang']['meta_title']);
    }

    public function index()
    {
        $conditions = array(
            'BlogPost.active' => APP_ON,
            'BlogPostLang.id_lang' => Configure::read('app.langId'),
            'BlogPostShop.id_shop' => Configure::read('app.shopId')
        );
        
        if (isset($this->params['manufacturerSlug'])) {
            $manufacturerId = (int) $this->params['manufacturerSlug'];
            $this->loadModel('Manufacturer');
            $this->Manufacturer->recursive = 1;
            $manufacturer = $this->Manufacturer->find('first', array(
                'conditions' => array(
                    'Manufacturer.id_manufacturer' => $manufacturerId,
                    'Manufacturer.active' => APP_ON
                )
            ));
            if (empty($manufacturer)) {
                throw new MissingActionException('manufacturer not found or not active');
            }
            $this->set('manufacturer', $manufacturer);
            $conditions['BlogPost.id_manufacturer'] = $manufacturerId;
        }
        
        if (! $this->AppAuth->loggedIn()) {
            $conditions['BlogPost.is_private'] = APP_OFF;
        }
        
        $blogPosts = $this->BlogPost->find('all', array(
            'conditions' => $conditions,
            'order' => array(
                'BlogPost.modified' => 'DESC'
            )
        ));
        
        $this->set('blogPosts', $blogPosts);
        $this->set('title_for_layout', 'Aktuelles');
    }
}

?>