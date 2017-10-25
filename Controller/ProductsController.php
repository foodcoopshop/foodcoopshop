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

        $this->loadModel('Product');
        $productId = (int) $this->params['pass'][0];

        $product = $this->Product->find('first', array(
            'conditions' => array(
                'Product.id_product' => $productId,
                'Product.active' => APP_ON
            )
        ));
        if ((
              !empty($product)
              && !$this->AppAuth->loggedIn()
              && (isset($product['Manufacturer']) && $product['Manufacturer']['is_private'])
              )
            ) {
            $this->AppAuth->deny($this->action);
        }
    }

    public function detail()
    {
        $productId = (int) $this->params['pass'][0];

        $this->loadModel('BlogPost');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->loadModel('Category');
        $product = $this->Category->getProductsByCategoryId(Configure::read('app.categoryAllProducts'), false, '', $productId);
        $product = $this->prepareProductsForFrontend($product);

        if (empty($product) || !isset($product[0])) {
            throw new MissingActionException('product not found');
        }

        $this->set('product', $product[0]);

        $correctSlug = Configure::read('slugHelper')->getProductDetail($productId, $product[0]['ProductLang']['name']);
        if ($correctSlug != Configure::read('slugHelper')->getProductDetail($productId, StringComponent::removeIdFromSlug($this->params['pass'][0]))) {
            $this->redirect($correctSlug);
        }

        $this->set('title_for_layout', $product[0]['ProductLang']['name']);
    }
}
