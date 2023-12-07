<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
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
class FrontendController extends AppController
{

    public $protectEmailAddresses = true;
    protected $Category;
    protected $OrderDetail;
    protected $Page;

    public function isAuthorized($user)
    {
        return true;
    }

    protected function resetOriginalLoggedCustomer()
    {
        if ($this->getRequest()->getSession()->read('Auth.originalLoggedCustomer')) {
            $this->AppAuth->setUser($this->getRequest()->getSession()->read('Auth.originalLoggedCustomer'));
        }
    }

    protected function destroyOrderCustomer()
    {
        $this->getRequest()->getSession()->delete('Auth.orderCustomer');
        $this->getRequest()->getSession()->delete('Auth.originalLoggedCustomer');
    }

    // is not called on ajax actions!
    public function beforeRender(EventInterface $event)
    {

        parent::beforeRender($event);

        // when an instant order was placed, the pdfs that are rendered for the order confirmation email
        // called this method and therefore called resetOriginalLoggedCustomer() => email was sent t
        // the user who placed the order for a member and not to the member
        if ($this->getResponse()->getType() != 'text/html') {
            return;
        }

        $this->resetOriginalLoggedCustomer();

        $categoriesForMenu = [];
        if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->isLoggedIn()) {
            $this->Category = $this->getTableLocator()->get('Categories');
            $catalogService = new CatalogService();
            $catalogService->setRequest($this->request);
            $allProductsCount = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), false, '', 0, true);
            $newProductsCount = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), true, '', 0, true);
            $this->Category->setRequest($this->request);
            $categoriesForMenu = $this->Category->getForMenu();
            array_unshift($categoriesForMenu, [
                'slug' => Configure::read('app.slugHelper')->getNewProducts(),
                'name' => __('New_products') . ' <span class="additional-info"> (' . $newProductsCount . ')</span>',
                'options' => [
                    'fa-icon' => 'fa-star' . ($newProductsCount > 0 ? ' gold' : '')
                ]
            ]);
            array_unshift($categoriesForMenu, [
                'slug' => Configure::read('app.slugHelper')->getAllProducts(),
                'name' => __('All_products') . ' <span class="additional-info"> (' . $allProductsCount . ')</span>',
                'options' => [
                    'fa-icon' => 'fa-tags'
                ]
            ]);
        }
        $this->set('categoriesForMenu', $categoriesForMenu);

        $manufacturersForMenu = [];
        if (Configure::read('app.showManufacturerListAndDetailPage')) {
            $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
            $this->Manufacturer->setRequest($this->request);
            $manufacturersForMenu = $this->Manufacturer->getForMenu();
            $this->set('manufacturersForMenu', $manufacturersForMenu);
        }

        $this->Page = $this->getTableLocator()->get('Pages');
        $conditions = [];
        $conditions['Pages.active'] = APP_ON;
        $conditions[] = 'Pages.position > 0';
        if (!$this->isLoggedIn()) {
            $conditions['Pages.is_private'] = APP_OFF;
        }

        $pages = $this->Page->getThreaded($conditions);
        $pagesForHeader = [];
        $pagesForFooter = [];
        foreach ($pages as $page) {
            if ($page->menu_type == 'header') {
                $pagesForHeader[] = $page;
            }
            if ($page->menu_type == 'footer') {
                $pagesForFooter[] = $page;
            }
        }
        $this->set('pagesForHeader', $pagesForHeader);
        $this->set('pagesForFooter', $pagesForFooter);
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        if (($this->name == 'Categories' && $this->getRequest()->getParam('action') == 'detail') || $this->name == 'Carts') {
            // do not allow but call isAuthorized
        } else {
            //$this->AppAuth->allow();
        }

        /*
         * changed the acutally logged in customer to the desired orderCustomer
         * but only in controller beforeFilter(), beforeRender() sets the customer back to the original one
         * this means, in views $appAuth ALWAYS returns the original customer, in controllers ALWAYS the desired orderCustomer
         */
        if (0 && $this->AppAuth->isOrderForDifferentCustomerMode()) {
            $this->getRequest()->getSession()->write('Auth.originalLoggedCustomer', $this->AppAuth->user());
            $this->AppAuth->setUser($this->getRequest()->getSession()->read('Auth.orderCustomer'));
        }
        if (0 && $this->isLoggedIn()) {

            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $creditBalance = $this->AppAuth->getCreditBalance();
                $this->set('creditBalance', $creditBalance);
            }

            $this->set('shoppingPrice', $this->AppAuth->user('shopping_price'));

            $cartsTable = $this->getTableLocator()->get('Carts');
            $this->set('paymentType', $this->AppAuth->isSelfServiceCustomer() ? $cartsTable::CART_SELF_SERVICE_PAYMENT_TYPE_CASH : $cartsTable::CART_SELF_SERVICE_PAYMENT_TYPE_CREDIT);

            $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
            $futureOrderDetails = $this->OrderDetail->getGroupedFutureOrdersByCustomerId($this->AppAuth->getUserId());
            $this->set('futureOrderDetails', $futureOrderDetails);
        }
        //$this->AppAuth->setCart($this->AppAuth->getCart());
    }
}
