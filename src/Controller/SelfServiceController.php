<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SelfServiceController extends FrontendController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        if (!(Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && $this->AppAuth->user())) {
            $this->AppAuth->deny($this->getRequest()->getParam('action'));
        }
    }

    public function index()
    {

        $categoryId = Configure::read('app.categoryAllProducts');
        if (!empty($this->getRequest()->getQuery('categoryId'))) {
            $categoryId = h($this->getRequest()->getQuery('categoryId'));
        }
        $this->set('categoryId', $categoryId);

        $keyword = '';
        if (!empty($this->getRequest()->getQuery('keyword'))) {
            $keyword = h(trim($this->getRequest()->getQuery('keyword')));
            $this->set('keyword', $keyword);
        }

        $this->Category = $this->getTableLocator()->get('Categories');
        $this->set('categoriesForSelect', $this->Category->getForSelect(null, false));

        $products = $this->Category->getProductsByCategoryId($this->AppAuth, $categoryId, false, $keyword, 0, false, true);
        $products = $this->prepareProductsForFrontend($products);
        $this->set('products', $products);

        $this->viewBuilder()->setLayout('self_service');
        $this->set('title_for_layout', __('Self_service_for_stock_products'));

        if (!empty($this->getRequest()->getQuery('keyword')) && count($products) == 1) {
            $hashedProductId = strtolower(substr($keyword, 0, 4));
            $attributeId = (int) substr($keyword, 4, 4);
            if ($hashedProductId == $products[0]['ProductIdentifier']) {
                $this->CartProduct = $this->getTableLocator()->get('CartProducts');
                $result = $this->CartProduct->add($this->AppAuth, $products[0]['id_product'], $attributeId, 1);
                if (!empty($result['msg'])) {
                    $this->Flash->error($result['msg']);
                    $this->request->getSession()->write('highlightedProductId', $products[0]['id_product']); // sic! no attributeId needed!
                }
                $this->redirect(Configure::read('app.slugHelper')->getSelfService());
                return;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $cart = $this->AppAuth->getCart();
            $this->set('cart', $cart['Cart']);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if ($this->AppAuth->Cart->isCartEmpty()) {
                $this->Flash->error(__('Your_shopping_bag_was_empty.'));
                $this->redirect(Configure::read('app.slugHelper')->getSelfService());
                return;
            }

            $this->AppAuth->Cart->finish();

            if (empty($this->viewBuilder()->getVars()['cartErrors']) && empty($this->viewBuilder()->getVars()['formErrors'])) {
                $this->redirect(Configure::read('app.slugHelper')->getSelfService());
                return;
            }

        }

    }

}
