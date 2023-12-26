<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Core\Configure;
use App\Services\CatalogService;

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
class ProductsController extends FrontendController
{

    protected $Product;

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
              && !$this->identity->isLoggedIn()
              && (!empty($product->manufacturer) && $product->manufacturer->is_private)
              )
            ) {
                $this->identity->deny($this->getRequest()->getParam('action'));
        }
    }

    public function detail()
    {
        $productId = (int) $this->getRequest()->getParam('pass')[0];

        $catalogService = new CatalogService();
        $product = $catalogService->getProducts($this->identity, Configure::read('app.categoryAllProducts'), false, '', $productId);
        $product = $catalogService->prepareProducts($this->identity, $product);

        if (empty($product) || !isset($product[0])) {
            throw new RecordNotFoundException('product not found');
        }

        $this->set('product', $product[0]);

        $correctSlug = StringComponent::slugify($product[0]->name);
        $givenSlug = StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]);
        if ($correctSlug != $givenSlug) {
            $this->redirect(Configure::read('app.slugHelper')->getProductDetail($productId, $product[0]->name));
        }

        $this->set('title_for_layout', $product[0]->name);
    }
}
