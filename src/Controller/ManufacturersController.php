<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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
        switch ($this->request->action) {
            case 'detail':
                $manufacturerId = (int) $this->request->getParam('pass')[0];
                $manufacturer = $this->Manufacturer->find('all', [
                    'conditions' => [
                        'Manufacturers.id_manufacturer' => $manufacturerId,
                        'Manufacturers.active' => APP_ON
                    ]
                ])->first();
                if (!empty($manufacturer) && !$this->AppAuth->user() && $manufacturer->is_private) {
                    $this->AppAuth->deny($this->request->action);
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

        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $manufacturers = $this->Manufacturer->find('all', [
            'conditions' => $conditions,
            'order' => [
                'Manufacturers.name' => 'ASC'
            ],
            'fields' => ['is_holiday_active' => '!'.$this->Manufacturer->getManufacturerHolidayConditions()],
            'contain' => [
                'AddressManufacturers'
            ]
        ])->select($this->Manufacturer); // select all fields from table
        
        if (empty($manufacturers)) {
            throw new RecordNotFoundException('no manufacturers available');
        }

        if ($this->AppAuth->user() || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
            $productModel = TableRegistry::get('Products');
            foreach ($manufacturers as $manufacturer) {
                $manufacturer->product_count = $productModel->getCountByManufacturerId($manufacturer->id_manufacturer, true);
            }
        }

        $this->set('manufacturers', $manufacturers);
        $this->set('title_for_layout', 'Hersteller');
    }

    public function detail()
    {
        $manufacturerId = (int) $this->request->getParam('pass')[0];

        $conditions = [
            'Manufacturers.id_manufacturer' => $manufacturerId,
            'Manufacturers.active' => APP_ON
        ];
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => $conditions,
            'fields' => ['Manufacturers.*', 'Addresses.*', 'is_holiday_active' => '!'.$this->Manufacturer->getManufacturerHolidayConditions()]
        ])->first();

        if (empty($manufacturer)) {
            throw new RecordNotFoundException('manufacturer not found or not active');
        }

        $correctSlug = Configure::read('app.slugHelper')->getManufacturerDetail($manufacturer['Manufacturers']['id_manufacturer'], $manufacturer['Manufacturers']['name']);
        if ($correctSlug != Configure::read('app.slugHelper')->getManufacturerDetail($manufacturerId, StringComponent::removeIdFromSlug($this->request->getParam('pass')[0]))) {
            $this->redirect($correctSlug);
        }

        if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->AppAuth->user()) {
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
