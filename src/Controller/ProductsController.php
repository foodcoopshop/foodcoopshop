<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductsController extends FrontendController
{

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $productId = (int) $this->getRequest()->getParam('pass')[0];

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId,
                'Products.active' => APP_ON
            ],
            'contain' => [
                'Manufacturers'
            ]
        ])->first();

        if (! Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || (
              !empty($product)
              && !$this->AppAuth->user()
              && (!empty($product->manufacturer) && $product->manufacturer->is_private)
              )
            ) {
                $this->AppAuth->deny($this->getRequest()->getParam('action'));
        }
    }

    public function detail()
    {
        $productId = (int) $this->getRequest()->getParam('pass')[0];

        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $product = $this->Category->getProductsByCategoryId(Configure::read('app.categoryAllProducts'), false, '', $productId);
        $product = $this->prepareProductsForFrontend($product);

        if (empty($product) || !isset($product[0])) {
            throw new RecordNotFoundException('product not found');
        }

        $this->set('product', $product[0]);

        $correctSlug = Configure::read('app.slugHelper')->getProductDetail($productId, $product[0]['name']);
        if ($correctSlug != Configure::read('app.slugHelper')->getProductDetail($productId, StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]))) {
            $this->redirect($correctSlug);
        }

        $this->set('title_for_layout', $product[0]['name']);
    }
}
