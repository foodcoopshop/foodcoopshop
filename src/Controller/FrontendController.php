<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use App\Services\CatalogService;
use App\Services\OrderCustomerService;
use Cake\Routing\Router;
use Cake\Cache\Cache;
use App\Model\Entity\Cart;

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

    public bool $protectEmailAddresses = true;

    protected function resetOriginalLoggedCustomer(): void
    {
        $OriginalIdentity = $this->getRequest()->getSession()->read('OriginalIdentity');
        if ($OriginalIdentity) {
            $this->Authentication->setIdentity($OriginalIdentity);
            Router::setRequest($this->getRequest());
        }
    }

    protected function destroyOrderCustomer(): void
    {
        $this->getRequest()->getSession()->delete('OrderIdentity');
        $this->getRequest()->getSession()->delete('OriginalIdentity');
    }

    // is not called on ajax actions!
    public function beforeRender(EventInterface $event): void
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
                $categoriesTable = $this->getTableLocator()->get('Categories');
                $allProductsCount = (int) $catalogService->getProducts(Configure::read('app.categoryAllProducts'), false, '', 0, true);
                $newProductsCount = (int) $catalogService->getProducts(Configure::read('app.categoryAllProducts'), true, '', 0, true);
                $categoriesForMenu = $categoriesTable->getForMenu();
                array_unshift($categoriesForMenu, [
                    'slug' => Configure::read('app.slugHelper')->getRandomProducts(),
                    'name' => __('Random_products'),
                ]);
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

        $filtersForMenu = [];
        if ($catalogService->showOnlyProductsForNextWeekFilterEnabled()) {
            $name = __('Show_all_products');
            $options = [
                'fa-icon' => 'far fa-square',
            ];
            if ($this->identity !== null) {
                $name = __('Show_only_products_for_next_week');
                if ($this->identity->show_only_products_for_next_week) {
                    $options = [
                        'fa-icon' => 'fa-square-check',
                    ];
                }
            }
            $filtersForMenu[] = [
                'slug' => Configure::read('app.slugHelper')->getChangeShowOnlyProductsForNextWeek(),
                'name' => $name,
                'options' => $options,
            ];
        }
        $this->set('filtersForMenu', $filtersForMenu);

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
                $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
                $manufacturersForMenu = $manufacturersTable->getForMenu();
                Cache::write($cacheKey, $manufacturersForMenu);
            }
            $this->set('manufacturersForMenu', $manufacturersForMenu);
        }

        $pagesTable = $this->getTableLocator()->get('Pages');
        $conditions = [];
        $conditions['Pages.active'] = APP_ON;
        $conditions[] = 'Pages.position > 0';
        if ($this->identity === null) {
            $conditions['Pages.is_private'] = APP_OFF;
        }

        $pages = $pagesTable->getThreaded($conditions);
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

    public function beforeFilter(EventInterface $event): void
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
            $this->set('paymentType', $this->identity->isSelfServiceCustomer() ? Cart::SELF_SERVICE_PAYMENT_TYPE_CASH : Cart::SELF_SERVICE_PAYMENT_TYPE_CREDIT);

            $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
            $futureOrderDetails = $orderDetailsTable->getGroupedFutureOrdersByCustomerId($this->identity->getId());
            $this->set('futureOrderDetails', $futureOrderDetails);

            $this->identity->setCart($this->identity->getCart());

        }

    }
}
