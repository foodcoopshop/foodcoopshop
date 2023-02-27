<?php

namespace App\Controller;

use App\Lib\Catalog\Catalog;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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

        $categoryId = 0;
        if (!empty($this->getRequest()->getQuery('categoryId'))) {
            $categoryId = h($this->getRequest()->getQuery('categoryId'));
        }
        $this->set('categoryId', $categoryId);

        $keyword = '';
        if (!empty($this->getRequest()->getQuery('keyword'))) {
            $keyword = h(trim($this->getRequest()->getQuery('keyword')));
            $this->set('keyword', $keyword);
        }

        if (!empty($this->getRequest()->getQuery('productWithError'))) {
            $keyword = h(trim($this->getRequest()->getQuery('productWithError')));
        }

        $this->Category = $this->getTableLocator()->get('Categories');
        $categoriesForSelect = $this->Category->getForSelect(null, false, false, $this->AppAuth, true);

        $this->Catalog = new Catalog();
        $allProductsCount = $this->Catalog->getProducts($this->AppAuth, Configure::read('app.categoryAllProducts'), false, '', 0, true, Configure::read('app.selfServiceModeShowOnlyStockProducts'));
        $categoriesForSelect = [
            Configure::read('app.categoryAllProducts') => __('All_products') . ' (' . $allProductsCount . ')',
        ] + $categoriesForSelect;
        $this->set('categoriesForSelect', $categoriesForSelect);

        $categoryIdForSearch = $categoryId;
        if ($categoryId == 0 && $keyword != '') {
            $categoryIdForSearch = Configure::read('app.categoryAllProducts');
        }
        $products = $this->Catalog->getProducts($this->AppAuth, $categoryIdForSearch, false, $keyword, 0, false, Configure::read('app.selfServiceModeShowOnlyStockProducts'));
        $products = $this->Catalog->prepareProducts($this->AppAuth, $products);

        $this->set('products', $products);

        $this->viewBuilder()->setLayout('self_service');
        $this->set('title_for_layout', __('Self_service_mode'));

        if (!empty($this->getRequest()->getQuery('keyword')) && count($products) == 1) {

            $hashedProductId = strtolower(substr($keyword, 0, 4));
            $attributeId = (int) substr($keyword, 4, 4);

            $customBarcodeFound = false;
            if (!empty($products[0]->barcode_product) && $keyword == $products[0]->barcode_product->barcode) {
                $customBarcodeFound = true;
                $attributeId = 0;
            }

            if (!empty($products[0]->product_attributes)) {
                foreach($products[0]->product_attributes as $productAttribute) {
                    if ($productAttribute->barcode_product_attribute) {
                        if ($keyword == $productAttribute->barcode_product_attribute->barcode) {
                            $customBarcodeFound = true;
                            $attributeId = $productAttribute->id_product_attribute;
                            break;
                        }
                    }
                }
            }

            if ($hashedProductId == $products[0]->system_bar_code || $customBarcodeFound) {
                $this->CartProduct = $this->getTableLocator()->get('CartProducts');
                $result = $this->CartProduct->add($this->AppAuth, $products[0]->id_product, $attributeId, 1);
                if (!empty($result['msg'])) {
                    $this->Flash->error($result['msg']);
                    $this->request->getSession()->write('highlightedProductId', $products[0]->id_product); // sic! no attributeId needed!
                    $redirectUrl = Configure::read('app.slugHelper')->getSelfService('', $keyword);
                } else {
                    $imgString = '';
                    $imageId = !empty($products[0]->Image) ? $products[0]->Image->id_image : 0;
                    $imgSrc = Configure::read('app.htmlHelper')->getProductImageSrc($imageId, 'home');
                    if ($imgSrc != '') {
                        $imgString .= '<br /><img src="'.$imgSrc.'" />';
                    }
                    $this->Flash->success(__('The_product_{0}_was_added_to_your_cart.', [
                        '<b>' . $products[0]->name . '</b>'
                    ]) . $imgString);
                    $redirectUrl = Configure::read('app.slugHelper')->getSelfService();
                }
                $this->redirect($redirectUrl);
                return;
            }
        }

        if ($this->getRequest()->getEnv('ORIGINAL_REQUEST_METHOD') == 'GET') {
            $cart = $this->AppAuth->getCart();
            $this->set('cart', $cart['Cart']);
        }

        if ($this->getRequest()->getEnv('ORIGINAL_REQUEST_METHOD') == 'POST') {

            if ($this->AppAuth->Cart->isCartEmpty()) {
                $this->Flash->error(__('Your_shopping_bag_was_empty.'));
                $this->redirect(Configure::read('app.slugHelper')->getSelfService());
                return;
            }

            $cart = $this->AppAuth->Cart->finish();

            if (empty($this->viewBuilder()->getVars()['cartErrors']) && empty($this->viewBuilder()->getVars()['formErrors'])) {

                $redirectUrl = Configure::read('app.slugHelper')->getSelfService();

                if (isset($cart['invoice_id'])) {
                    $invoiceId = $cart['invoice_id'];
                    if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
                        $invoiceRoute = Configure::read('app.slugHelper')->getHelloCashReceipt($invoiceId);
                    } else {
                        $this->Invoice = $this->getTableLocator()->get('Invoices');
                        $invoice = $this->Invoice->find('all', [
                            'conditions' => [
                                'Invoices.id' => $invoiceId,
                            ],
                        ])->first();
                        if (!empty($invoice)) {
                            $invoiceRoute = Configure::read('app.slugHelper')->getInvoiceDownloadRoute($invoice->filename);
                        }
                    }
                    if (!$this->AppAuth->user('invoices_per_email_enabled')) {
                        $this->request->getSession()->write('invoiceRouteForAutoPrint', $invoiceRoute);
                    }
                }

                $this->resetOriginalLoggedCustomer();
                $this->destroyOrderCustomer();

                $this->redirect($redirectUrl);
                return;

            }

        }

    }

}
