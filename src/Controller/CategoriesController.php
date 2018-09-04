<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CategoriesController extends FrontendController
{

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        if (! (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->AppAuth->user())) {
            $this->AppAuth->deny($this->getRequest()->getParam('action'));
        } else {
            $this->AppAuth->allow($this->getRequest()->getParam('action'));
        }
    }

    public function newProducts()
    {
        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $products = $this->Category->getProductsByCategoryId(Configure::read('app.categoryAllProducts'), true);
        $products = $this->prepareProductsForFrontend($products);
        $this->set('products', $products);

        $this->set('title_for_layout', __('New_products'));

        $this->render('detail');
    }

    public function search()
    {
        $keyword = '';
        if (! empty($this->getRequest()->getQuery('keyword'))) {
            $keyword = trim($this->getRequest()->getQuery('keyword'));
        }

        if ($keyword == '') {
            throw new RecordNotFoundException('no keyword');
        }

        $this->set('keyword', $keyword);

        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $products = $this->Category->getProductsByCategoryId(Configure::read('app.categoryAllProducts'), false, $keyword);
        $products = $this->prepareProductsForFrontend($products);
        $this->set('products', $products);

        $this->set('title_for_layout', __('Search') . ' "' . $keyword . '"');

        $this->render('detail');
    }

    public function detail()
    {
        $categoryId = (int) $this->getRequest()->getParam('pass')[0];

        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $category = $this->Category->find('all', [
            'conditions' => [
                'Categories.id_category' => $categoryId,
                'Categories.active' => APP_ON,
            ]
        ])->first();

        if (empty($category)) {
            throw new RecordNotFoundException('category not found');
        }

        $correctSlug = Configure::read('app.slugHelper')->getCategoryDetail($categoryId, $category->name);
        if ($correctSlug != Configure::read('app.slugHelper')->getCategoryDetail($categoryId, StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]))) {
            $this->redirect($correctSlug);
        }

        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $products = $this->Category->getProductsByCategoryId($categoryId);
        $products = $this->prepareProductsForFrontend($products);

        $this->set('products', $products);

        $this->set('category', $category);

        $this->set('title_for_layout', $category->name);
    }
}
