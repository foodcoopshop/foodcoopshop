<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use App\Services\CatalogService;
use App\Services\OrderCustomerService;
use Cake\Routing\Router;
use Cake\Cache\Cache;

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

    protected function resetOriginalLoggedCustomer()
    {
        $OriginalIdentity = $this->getRequest()->getSession()->read('OriginalIdentity');
        if ($OriginalIdentity) {
            $this->Authentication->setIdentity($OriginalIdentity);
            Router::setRequest($this->getRequest());
        }
    }

    protected function destroyOrderCustomer()
    {
        $this->getRequest()->getSession()->delete('OrderIdentity');
        $this->getRequest()->getSession()->delete('OriginalIdentity');
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

        $catalogService = new CatalogService();
        $orderCustomerService = new OrderCustomerService();

        $cacheKey = join('_', [
            'categoriesForMenu',
            'date-' . date('Y-m-d'),
            'isLoggedIn-' . ((int) ($this->identity !== null)),
            'forDifferentCustomer-' . ($orderCustomerService->isOrderForDifferentCustomerMode() || $orderCustomerService->isSelfServiceModeByUrl()),
            'getOnlyStockProducts-' . $catalogService->getOnlyStockProductsRespectingConfiguration(false),
        ]);

        $categoriesForMenu = Cache::read($cacheKey);
        if ($categoriesForMenu === null) {
            if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->identity !== null) {
                $this->Category = $this->getTableLocator()->get('Categories');
                $allProductsCount = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), false, '', 0, true);
                $newProductsCount = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), true, '', 0, true);
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
            Cache::write($cacheKey, $categoriesForMenu);
        }
        $categoriesForMenu = $categoriesForMenu ?? [];
        $this->set('categoriesForMenu', $categoriesForMenu);


        if (Configure::read('app.showManufacturerListAndDetailPage')) {
            $cacheKey = join('_', [
                'manufacturersForMenu',
                'date-' . date('Y-m-d'),
                'isLoggedIn-' . ((int) ($this->identity !== null)),
                'forDifferentCustomer-' . ($orderCustomerService->isOrderForDifferentCustomerMode() || $orderCustomerService->isSelfServiceModeByUrl()),
                'getOnlyStockProducts-' . $catalogService->getOnlyStockProductsRespectingConfiguration(false),
            ]);
            $manufacturersForMenu = Cache::read($cacheKey);
            if ($manufacturersForMenu === null) {
                $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
                $manufacturersForMenu = $this->Manufacturer->getForMenu();
                Cache::write($cacheKey, $manufacturersForMenu);
            }
            $manufacturersForMenu = $manufacturersForMenu ?? [];
            $this->set('manufacturersForMenu', $manufacturersForMenu);
        }

        $this->Page = $this->getTableLocator()->get('Pages');
        $conditions = [];
        $conditions['Pages.active'] = APP_ON;
        $conditions[] = 'Pages.position > 0';
        if ($this->identity === null) {
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

        /*
         * changes the identity to the desired orderCustomer
         * but only in controller beforeFilter()
         * beforeRender() sets the customer back to the original one
         * this means, in views $identity ALWAYS returns the original customer, in controllers ALWAYS the desired orderCustomer
         */
        $orderCustomerService = new OrderCustomerService();
        if ($orderCustomerService->isOrderForDifferentCustomerMode()) {
            $this->getRequest()->getSession()->write('OriginalIdentity', $this->identity);
            $newIdentity = $this->getRequest()->getSession()->read('OrderIdentity');
            $this->Authentication->setIdentity($newIdentity);
            Router::setRequest($this->getRequest());
            $this->identity = $newIdentity;
            $this->set('identity', $newIdentity);
        }

        if ($this->identity !== null) {

            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $creditBalance = $this->identity->getCreditBalance();
                $this->set('creditBalance', $creditBalance);
            }

            $this->set('shoppingPrice', $this->identity->shopping_price);

            $cartsTable = $this->getTableLocator()->get('Carts');
            $this->set('paymentType', $this->identity->isSelfServiceCustomer() ? $cartsTable::CART_SELF_SERVICE_PAYMENT_TYPE_CASH : $cartsTable::CART_SELF_SERVICE_PAYMENT_TYPE_CREDIT);

            $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
            $futureOrderDetails = $this->OrderDetail->getGroupedFutureOrdersByCustomerId($this->identity->getId());
            $this->set('futureOrderDetails', $futureOrderDetails);

            $this->identity->setCart($this->identity->getCart());

        }

    }
}
