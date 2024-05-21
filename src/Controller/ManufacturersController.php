<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use App\Services\CatalogService;
use Cake\Event\EventInterface;
use App\Controller\Traits\PaginatedProductsTrait;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ManufacturersController extends FrontendController
{

    use PaginatedProductsTrait;

    protected $Manufacturer;
    protected $BlogPost;

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'index',
            'detail',
        ]);
    }

    public function index()
    {

        $conditions = [
            'Manufacturers.active' => APP_ON,
        ];
        if ($this->identity === null) {
            $conditions['Manufacturers.is_private'] = APP_OFF;
        }

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $manufacturers = $this->Manufacturer->find('all',
        conditions: $conditions,
        order: [
            'Manufacturers.name' => 'ASC'
        ],
        contain: [
            'AddressManufacturers',
            'Customers',
        ]);

        if (empty($manufacturers->toArray())) {
            throw new RecordNotFoundException('no manufacturers available');
        }

        if ($this->identity !== null || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
            $catalogService = new CatalogService();
            foreach ($manufacturers as $manufacturer) {
                $manufacturer->product_count = $catalogService->getProductsByManufacturerId($manufacturer->id_manufacturer, true);
            }
        }

        $this->set('manufacturers', $manufacturers);
        $this->set('title_for_layout', __('Manufacturer'));
    }

    public function detail()
    {
        $manufacturerId = (int) $this->getRequest()->getParam('idAndSlug');
        $this->redirectIfPageIsSetTo1();

        $page = (int) $this->getRequest()->getQuery('page', 1);

        $conditions = [
            'Manufacturers.id_manufacturer' => $manufacturerId,
            'Manufacturers.active' => APP_ON
        ];

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $manufacturer = $this->Manufacturer->find('all',
        conditions: $conditions,
        contain: [
            'AddressManufacturers'
        ])
        ->select($this->Manufacturer)
        ->select($this->Manufacturer->AddressManufacturers)
        ->first();

        if (empty($manufacturer)) {
            throw new RecordNotFoundException('manufacturer not found or not active');
        }

        $correctSlug = StringComponent::slugify($manufacturer->name);
        $givenSlug = StringComponent::removeIdFromSlug($this->getRequest()->getParam('idAndSlug'));
        if ($correctSlug != $givenSlug) {
            $this->redirect(Configure::read('app.slugHelper')->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name));
        }

        if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->identity !== null) {
            $catalogService = new CatalogService();
            $products = $catalogService->getProductsByManufacturerId($manufacturerId, false, $page);
            $totalProductCount = $catalogService->getProductsByManufacturerId($manufacturerId, true);
            $manufacturer['Products'] = $catalogService->prepareProducts($products);
            $pagesCount = $catalogService->getPagesCount($totalProductCount);

            $this->throw404IfNoProductsOnPaginatedPageFound($manufacturer['Products'], $page);
    
            $this->set('totalProductCount', $totalProductCount);
            $this->set('pagesCount', $pagesCount);
            $this->set('page', $page);

        }

        $this->BlogPost = $this->getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($manufacturerId, true);
        $this->set('blogPosts', $blogPosts);

        $this->set('manufacturer', $manufacturer);
        $this->set('title_for_layout', $manufacturer->name);
    }
}
