<?php

use App\Controller\Component\StringComponent;
use Cake\Controller\Exception\MissingActionException;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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

    public function beforeFilter(Event $event)
    {

        parent::beforeFilter($event);
        switch ($this->action) {
            case 'detail':
                $manufacturerId = (int) $this->params['pass'][0];
                $manufacturer = $this->Manufacturer->find('all', [
                    'conditions' => [
                        'Manufacturers.id_manufacturer' => $manufacturerId,
                        'Manufacturers.active' => APP_ON
                    ]
                ])->first();
                if (!empty($manufacturer) && !$this->AppAuth->user() && $manufacturer['Manufacturers']['is_private']) {
                    $this->AppAuth->deny($this->action);
                }
                break;
        }
    }

    public function index()
    {

        $conditions = [
            'Manufacturers.active' => APP_ON
        ];
        if (! $this->AppAuth->user()) {
            $conditions['Manufacturers.is_private'] = APP_OFF;
        }

        $manufacturers = $this->Manufacturer->find('all', [
            'conditions' => $conditions,
            'order' => [
                'Manufacturers.name' => 'ASC'
            ],
            'fields' => ['Manufacturers.*', 'Addresses.*', '!'.$this->Manufacturer->getManufacturerHolidayConditions().' as IsHolidayActive']
        ]);

        if (empty($manufacturers)) {
            throw new MissingActionException('no manufacturers available');
        }

        if ($this->AppAuth->user() || Configure::read('AppConfigDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
            $productModel = TableRegistry::get('Products');
            foreach ($manufacturers as &$manufacturer) {
                $manufacturer['product_count'] = $productModel->getCountByManufacturerId($manufacturer['Manufacturers']['id_manufacturer'], true);
            }
        }

        $this->set('manufacturers', $manufacturers);
        $this->set('title_for_layout', 'Hersteller');
    }

    public function detail()
    {
        $manufacturerId = (int) $this->params['pass'][0];

        $conditions = [
            'Manufacturers.id_manufacturer' => $manufacturerId,
            'Manufacturers.active' => APP_ON
        ];
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => $conditions,
            'fields' => ['Manufacturers.*', 'Addresses.*', '!'.$this->Manufacturer->getManufacturerHolidayConditions().' as IsHolidayActive']
        ])->first();

        if (empty($manufacturer)) {
            throw new MissingActionException('manufacturer not found or not active');
        }

        $correctSlug = Configure::read('AppConfig.slugHelper')->getManufacturerDetail($manufacturer['Manufacturers']['id_manufacturer'], $manufacturer['Manufacturers']['name']);
        if ($correctSlug != Configure::read('AppConfig.slugHelper')->getManufacturerDetail($manufacturerId, StringComponent::removeIdFromSlug($this->params['pass'][0]))) {
            $this->redirect($correctSlug);
        }

        if (Configure::read('AppConfigDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->AppAuth->user()) {
            $products = $this->Manufacturer->getProductsByManufacturerId($manufacturerId);
            $manufacturer['Products'] = $this->prepareProductsForFrontend($products);
        }

        $this->BlogPost = TableRegistry::get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth, null, $manufacturerId);
        $this->set('blogPosts', $blogPosts);

        $this->set('manufacturer', $manufacturer);
        $this->set('title_for_layout', $manufacturer['Manufacturers']['name']);
    }
}
