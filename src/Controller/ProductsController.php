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

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'detail',
        ]);
    }

    public function detail()
    {
        $productId = (int) $this->getRequest()->getParam('idAndSlug');

        $catalogService = new CatalogService();
        $product = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), false, '', $productId);
        $product = $catalogService->prepareProducts($product);

        if (empty($product) || !isset($product[0])) {
            throw new RecordNotFoundException('product not found');
        }

        $this->set('product', $product[0]);

        $correctSlug = StringComponent::slugify($product[0]->name);
        $givenSlug = StringComponent::removeIdFromSlug($this->getRequest()->getParam('idAndSlug'));
        if ($correctSlug != $givenSlug) {
            $this->redirect(Configure::read('app.slugHelper')->getProductDetail($productId, $product[0]->name));
        }

        $this->set('title_for_layout', $product[0]->name);
    }
}
