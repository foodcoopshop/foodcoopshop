<?php

use App\Controller\Component\StringComponent;
use Cake\Controller\Exception\MissingActionException;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Product = TableRegistry::get('Products');
        $productId = (int) $this->params['pass'][0];

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId,
                'Products.active' => APP_ON
            ]
        ])->first();
        if (! Configure::read('AppConfigDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || (
              !empty($product)
              && !$this->AppAuth->user()
              && (isset($product['Manufacturers']) && $product['Manufacturers']['is_private'])
              )
            ) {
                $this->AppAuth->deny($this->request->action);
        }
    }

    public function detail()
    {
        $productId = (int) $this->params['pass'][0];

        $this->BlogPost = TableRegistry::get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->Category = TableRegistry::get('Categories');
        $product = $this->Category->getProductsByCategoryId(Configure::read('AppConfig.categoryAllProducts'), false, '', $productId);
        $product = $this->prepareProductsForFrontend($product);

        if (empty($product) || !isset($product[0])) {
            throw new MissingActionException('product not found');
        }

        $this->set('product', $product[0]);

        $correctSlug = Configure::read('AppConfig.slugHelper')->getProductDetail($productId, $product[0]['ProductLangs']['name']);
        if ($correctSlug != Configure::read('AppConfig.slugHelper')->getProductDetail($productId, StringComponent::removeIdFromSlug($this->params['pass'][0]))) {
            $this->redirect($correctSlug);
        }

        $this->set('title_for_layout', $product[0]['ProductLangs']['name']);
    }
}
