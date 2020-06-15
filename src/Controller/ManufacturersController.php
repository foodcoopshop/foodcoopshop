<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
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
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ManufacturersController extends FrontendController
{

    public function beforeFilter(EventInterface $event)
    {

        parent::beforeFilter($event);

        if (!Configure::read('app.showManufacturerListAndDetailPage')) {
            throw new NotFoundException();
        }

        switch ($this->getRequest()->getParam('action')) {
            case 'detail':
                $manufacturerId = (int) $this->getRequest()->getParam('pass')[0];
                $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
                $manufacturer = $this->Manufacturer->find('all', [
                    'conditions' => [
                        'Manufacturers.id_manufacturer' => $manufacturerId,
                        'Manufacturers.active' => APP_ON
                    ]
                ])->first();
                if (!empty($manufacturer) && !$this->AppAuth->user() && $manufacturer->is_private) {
                    $this->AppAuth->deny($this->getRequest()->getParam('action'));
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

        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturers = $this->Manufacturer->find('all', [
            'conditions' => $conditions,
            'order' => [
                'Manufacturers.name' => 'ASC'
            ],
            'contain' => [
                'AddressManufacturers'
            ]
        ])->select($this->Manufacturer);

        if (empty($manufacturers)) {
            throw new RecordNotFoundException('no manufacturers available');
        }

        if ($this->AppAuth->user() || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
            foreach ($manufacturers as $manufacturer) {
                $manufacturer->product_count = $this->Manufacturer->getProductsByManufacturerId($this->AppAuth, $manufacturer->id_manufacturer, true);
            }
        }

        $this->set('manufacturers', $manufacturers);
        $this->set('title_for_layout', __('Manufacturer'));
    }

    public function detail()
    {
        $manufacturerId = (int) $this->getRequest()->getParam('pass')[0];

        $conditions = [
            'Manufacturers.id_manufacturer' => $manufacturerId,
            'Manufacturers.active' => APP_ON
        ];

        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'AddressManufacturers'
            ]
        ])
        ->select($this->Manufacturer)
        ->select($this->Manufacturers->AddressManufacturers)
        ->first();

        if (empty($manufacturer)) {
            throw new RecordNotFoundException('manufacturer not found or not active');
        }

        $correctSlug = StringComponent::slugify($manufacturer->name);
        $givenSlug = StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]);
        if ($correctSlug != $givenSlug) {
            $this->redirect(Configure::read('app.slugHelper')->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name));
        }

        if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->AppAuth->user()) {
            $products = $this->Manufacturer->getProductsByManufacturerId($this->AppAuth, $manufacturerId);
            $manufacturer['Products'] = $this->prepareProductsForFrontend($products);
        }

        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth, null, $manufacturerId);
        $this->set('blogPosts', $blogPosts);

        $this->set('manufacturer', $manufacturer);
        $this->set('title_for_layout', $manufacturer->name);
    }
}
