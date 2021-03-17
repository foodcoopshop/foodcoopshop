<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductsController extends FrontendController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->Product = $this->getTableLocator()->get('Products');
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

        $this->Category = $this->getTableLocator()->get('Categories');
        $product = $this->Category->getProductsByCategoryId($this->AppAuth, Configure::read('app.categoryAllProducts'), false, '', $productId);
        $product = $this->prepareProductsForFrontend($product);

        if (empty($product) || !isset($product[0])) {
            throw new RecordNotFoundException('product not found');
        }

        $this->set('product', $product[0]);

        $correctSlug = StringComponent::slugify($product[0]['name']);
        $givenSlug = StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]);
        if ($correctSlug != $givenSlug) {
            $this->redirect(Configure::read('app.slugHelper')->getProductDetail($productId, $product[0]['name']));
        }


        $this->set('title_for_layout', $product[0]['name']);
    }
}
