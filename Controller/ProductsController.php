<?php

App::uses('FrontendController', 'Controller');

/**
 * ProductsController
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
class ProductsController extends FrontendController
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

    public function detail()
    {
        $productId = (int) $this->params['pass'][0];

        $this->loadModel('BlogPost');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->loadModel('Category');
        $products = $this->Category->getProductsByCategoryId(Configure::read('app.categoryAllProducts'), false, '', $productId);
        $products = $this->perpareProductsForFrontend($products, true);

        if (empty($products)) {
            throw new MissingActionException('product not found');
        }

        $this->set('products', $products);

        $correctSlug = Configure::read('slugHelper')->getProductDetail($productId, $products[0]['ProductLang']['name']);
        if ($correctSlug != Configure::read('slugHelper')->getProductDetail($productId, StringComponent::removeIdFromSlug($this->params['pass'][0]))) {
            $this->redirect($correctSlug);
        }

        $this->set('title_for_layout', $products[0]['ProductLang']['name']);
    }
}
