<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;

/**
 * FrontendController
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
class FrontendController extends AppController
{

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @return
     */
    /*
    public function __construct(ServerRequest $request = null, Response $response = null, $name = null, $eventManager = null, $components = null)
    {
        $defaultUrl = Configure::read('AppConfig.slugHelper')->getAllProducts();
        $redirectUrl = ! empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $defaultUrl;
        $this->components['AppAuth']['logoutRedirect'] = $redirectUrl;
        return parent::__construct($request, $response, $name, $eventManager, $components);
    }
    */

    public function isAuthorized($user)
    {
        return true;
    }

    /**
     * should be moved into component
     * adds product attributes and deposit
     *
     * @param array $products
     */
    protected function prepareProductsForFrontend($products)
    {
        $this->loadModel('Product');
        $this->loadModel('ProductAttribute');
        $this->ProductAttribute->recursive = 2; // for attribute lang

        foreach ($products as &$product) {
            $grossPrice = $this->Product->getGrossPrice($product['Product']['id_product'], $product['ProductShop']['price']);
            $product['Product']['gross_price'] = $grossPrice;
            $product['Product']['tax'] = $grossPrice - $product['ProductShop']['price'];
            $product['Product']['is_new'] = $this->Product->isNew($product['ProductShop']['date_add']);

            $product['attributes'] = $this->ProductAttribute->find('all', [
                'conditions' => [
                    'ProductAttribute.id_product' => $product['Product']['id_product']
                ]
            ]);
            foreach ($product['attributes'] as &$attribute) {
                $grossPrice = $this->Product->getGrossPrice($attribute['ProductAttributeShop']['id_product'], $attribute['ProductAttributeShop']['price']);
                $attribute['ProductAttributeShop']['gross_price'] = $grossPrice;
                $attribute['ProductAttributeShop']['tax'] = $grossPrice - $attribute['ProductAttributeShop']['price'];
            }
        }

        return $products;
    }

    protected function resetOriginalLoggedCustomer()
    {
        if ($this->Session->read('Auth.originalLoggedCustomer')) {
            $this->AppAuth->login($this->Session->read('Auth.originalLoggedCustomer'));
        }
    }

    protected function destroyShopOrderCustomer()
    {
        $this->Session->delete('Auth.shopOrderCustomer');
        $this->Session->delete('Auth.originalLoggedCustomer');
    }

    // is not called on ajax actions!
    public function beforeRender(Event $event)
    {

        parent::beforeRender($event);

        // when a shop order was placed, the pdfs that are rendered for the order confirmation email
        // called this method and therefore called resetOriginalLoggedCustomer() => email was sent t
        // the user who placed the order for a member and not to the member
        if ($this->response->type() != 'text/html') {
            return;
        }

        $this->resetOriginalLoggedCustomer();

        $categoriesForMenu = [];
        if (Configure::read('AppConfig.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->AppAuth->user()) {
            $this->loadModel('Category');
            $allProductsCount = $this->Category->getProductsByCategoryId(Configure::read('AppConfig.categoryAllProducts'), false, '', 0, true);
            $newProductsCount = $this->Category->getProductsByCategoryId(Configure::read('AppConfig.categoryAllProducts'), true, '', 0, true);
            $categoriesForMenu = $this->Category->getForMenu();
            array_unshift($categoriesForMenu, [
                'slug' => '/neue-produkte',
                'name' => 'Neue Produkte <span class="additional-info"> (' . $newProductsCount . ')</span>',
                'options' => [
                    'fa-icon' => 'fa-star' . ($newProductsCount > 0 ? ' gold' : '')
                ]
            ]);
            array_unshift($categoriesForMenu, [
                'slug' => Configure::read('AppConfig.slugHelper')->getAllProducts(),
                'name' => 'Alle Produkte <span class="additional-info"> (' . $allProductsCount . ')</span>',
                'options' => [
                    'fa-icon' => 'fa-tags'
                ]
            ]);
        }
        $this->set('categoriesForMenu', $categoriesForMenu);

        $this->loadModel('Manufacturer');
        $manufacturersForMenu = $this->Manufacturer->getForMenu($this->AppAuth);
        $this->set('manufacturersForMenu', $manufacturersForMenu);

        $this->loadModel('Page');
        $conditions = [];
        $conditions['Page.active'] = APP_ON;
        $conditions[] = 'Page.position > 0';
        if (! $this->AppAuth->user()) {
            $conditions['Page.is_private'] = APP_OFF;
        }

        $pages = $this->Page->findAllGroupedByMenu($conditions);
        $pagesForHeader = [];
        $pagesForFooter = [];
        foreach ($pages as $page) {
            if ($page['Page']['menu_type'] == 'header') {
                $pagesForHeader[] = $page;
            }
            if ($page['Page']['menu_type'] == 'footer') {
                $pagesForFooter[] = $page;
            }
        }
        $this->set('pagesForHeader', $pagesForHeader);
        $this->set('pagesForFooter', $pagesForFooter);
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        if (($this->name == 'Categories' && $this->action == 'detail') || $this->name == 'Carts') {
            // do not allow but call isAuthorized
        } else {
            $this->AppAuth->allow();
        }

        /*
         * changed the acutally logged in customer to the desired shopOrderCustomer
         * but only in controller beforeFilter(), beforeRender() sets the customer back to the original one
         * this means, in views $appAuth ALWAYS returns the original customer, in controllers ALWAYS the desired shopOrderCustomer
         */
        if ($this->Session->read('Auth.shopOrderCustomer')) {
            $this->Session->write('Auth.originalLoggedCustomer', $this->AppAuth->user());
            $this->AppAuth->login($this->Session->read('Auth.shopOrderCustomer')['Customer']);
        }

        if ($this->AppAuth->user() && Configure::read('AppConfig.htmlHelper')->paymentIsCashless()) {
            $creditBalance = $this->AppAuth->getCreditBalance();
            $this->set('creditBalance', $creditBalance);

            $shoppingLimitReached = Configure::read('AppConfig.db_config_FCS_MINIMAL_CREDIT_BALANCE') != - 1 && $creditBalance < Configure::read('AppConfig.db_config_FCS_MINIMAL_CREDIT_BALANCE') * - 1;
            $this->set('shoppingLimitReached', $shoppingLimitReached);
        }

        $this->AppAuth->setCart($this->AppAuth->getCart());
    }
}
