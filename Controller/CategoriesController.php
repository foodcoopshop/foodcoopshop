<?php

App::uses('FrontendController', 'Controller');

/**
 * CategoriesController
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
class CategoriesController extends FrontendController
{

    public function beforeFilter()
    {
        parent::beforeFilter();
        if (! (Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->AppAuth->loggedIn())) {
            $this->AppAuth->deny($this->action);
        } else {
            $this->AppAuth->allow($this->action);
        }
    }

    public function new_products()
    {
        $this->loadModel('BlogPost');
        $blogPosts = $this->BlogPost->findBlogPosts(null, $this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $products = $this->Category->getProductsByCategoryId(Configure::read('app.categoryAllProducts'), true);
        $products = $this->perpareProductsForFrontend($products);
        $this->set('products', $products);

        $this->set('title_for_layout', 'Neue Produkte');

        $this->render('detail');
    }

    public function search()
    {
        $keyword = '';
        if (! empty($this->params->query['keyword'])) {
            $keyword = trim($this->params->query['keyword']);
        }

        if ($keyword == '') {
            throw new MissingActionException('no keyword');
        }

        $this->set('keyword', $keyword);

        $this->loadModel('BlogPost');
        $blogPosts = $this->BlogPost->findBlogPosts(null, $this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $products = $this->Category->getProductsByCategoryId(Configure::read('app.categoryAllProducts'), false, $keyword);
        $products = $this->perpareProductsForFrontend($products);
        $this->set('products', $products);

        $this->set('title_for_layout', 'Suche "' . $keyword . '"');

        $this->render('detail');
    }

    public function detail()
    {
        $categoryId = (int) $this->params['pass'][0];

        $category = $this->Category->find('first', array(
            'conditions' => array(
                'Category.id_category' => $categoryId,
                'Category.active' => APP_ON,
                'CategoryLang.id_shop' => Configure::read('app.shopId')
            )
        ));

        if (empty($category)) {
            throw new MissingActionException('category not found');
        }

        $correctSlug = Configure::read('slugHelper')->getCategoryDetail($categoryId, $category['CategoryLang']['name']);
        if ($correctSlug != Configure::read('slugHelper')->getCategoryDetail($categoryId, StringComponent::removeIdFromSlug($this->params['pass'][0]))) {
            $this->redirect($correctSlug);
        }

        $this->loadModel('BlogPost');
        $blogPosts = $this->BlogPost->findBlogPosts(null, $this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $products = $this->Category->getProductsByCategoryId($categoryId);
        $products = $this->perpareProductsForFrontend($products);

        $this->set('products', $products);

        $this->set('category', $category);

        $this->set('title_for_layout', $category['CategoryLang']['name']);
    }
}
