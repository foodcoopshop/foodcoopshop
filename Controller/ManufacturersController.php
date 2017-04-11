<?php

App::uses('FrontendController', 'Controller');

/**
 * ManufacturersController
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
class ManufacturersController extends FrontendController
{

    public function index()
    {
        $this->Manufacturer->recursive = 1;

        $conditions = array(
            'Manufacturer.active' => APP_ON
        );
        if (! $this->AppAuth->loggedIn()) {
            $conditions['Manufacturer.is_private'] = APP_OFF;
        }

        $manufacturers = $this->Manufacturer->find('all', array(
            'conditions' => $conditions,
            'order' => array(
                'Manufacturer.name' => 'ASC'
            )
        ));

        if (empty($manufacturers)) {
            throw new MissingActionException('no manufacturers available');
        }

        if ($this->AppAuth->loggedIn() || Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
            $productModel = ClassRegistry::init('Product');
            foreach ($manufacturers as &$manufacturer) {
                $manufacturer['product_count'] = $productModel->getCountByManufacturerId($manufacturer['Manufacturer']['id_manufacturer']);
            }
        }

        $this->set('manufacturers', $manufacturers);
        $this->set('title_for_layout', 'Hersteller');
    }

    public function detail()
    {
        $manufacturerId = (int) $this->params['pass'][0];

        $this->Manufacturer->recursive = 1;
        $conditions = array(
            'Manufacturer.id_manufacturer' => $manufacturerId,
            'Manufacturer.active' => APP_ON
        );
        if (! $this->AppAuth->loggedIn()) {
            $conditions['Manufacturer.is_private'] = APP_OFF;
        }
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => $conditions
        ));

        if (empty($manufacturer)) {
            throw new MissingActionException('manufacturer not found or not active');
        }

        $correctSlug = Configure::read('slugHelper')->getManufacturerDetail($manufacturer['Manufacturer']['id_manufacturer'], $manufacturer['Manufacturer']['name']);
        if ($correctSlug != Configure::read('slugHelper')->getManufacturerDetail($manufacturerId, StringComponent::removeIdFromSlug($this->params['pass'][0]))) {
            $this->redirect($correctSlug);
        }

        if (Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->AppAuth->loggedIn()) {
            $products = $this->Manufacturer->getProductsByManufacturerId($manufacturerId);
            $manufacturer['Products'] = $this->perpareProductsForFrontend($products);
        }

        $this->loadModel('BlogPost');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth, null, $manufacturerId);
        $this->set('blogPosts', $blogPosts);

        $this->set('manufacturer', $manufacturer);
        $this->set('title_for_layout', $manufacturer['Manufacturer']['name']);
    }
}
