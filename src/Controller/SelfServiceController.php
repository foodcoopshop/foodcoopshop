<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use App\Services\CatalogService;
use App\Services\CartService;
use Cake\Routing\Router;
use App\Model\Entity\Customer;
use Cake\Http\Response;

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

    protected CartService $cartService;

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'index',
            'autoLoginAsSelfServiceCustomer',
        ]);
        $this->cartService = new CartService($this);
    }

    public function autoLoginAsSelfServiceCustomer(): ?Response
    {

        $this->disableAutoRender();

        $id = (int) $this->getRequest()->getParam('id');

        $selfServiceLoginCustomer = array_filter(Configure::read('app.selfServiceLoginCustomers'), function($selfServiceLoginCustomer) use ($id) {
            return $selfServiceLoginCustomer['id'] == $id;
        });
        if (empty($selfServiceLoginCustomer)) {
            $this->Flash->error(__('Signing_in_failed.'));
            return $this->redirect(Configure::read('app.slugHelper')->getHome());
        }

        $customerId =   reset($selfServiceLoginCustomer)['customerId'];
        $customerTable = $this->getTableLocator()->get('Customers');
        $customer = $customerTable->find('auth',
            conditions: [
                $customerTable->aliasField('id_customer') => $customerId,
                $customerTable->aliasField('id_default_group') => Customer::GROUP_SELF_SERVICE_CUSTOMER,
            ],
        )->first();
        if (!empty($customer)) {
            $this->Authentication->setIdentity($customer);
            Router::setRequest($this->getRequest());
        } else {
            $this->Flash->error(__('Signing_in_failed.'));
            return $this->redirect(Configure::read('app.slugHelper')->getHome());
        }

        return $this->redirect(Configure::read('app.slugHelper')->getSelfService());
    }

    public function index(): void
    {
        $page = (int) $this->getRequest()->getQuery('page', 1);

        $categoryId = h($this->getRequest()->getQuery('categoryId', 0));
        $this->set('categoryId', $categoryId);

        $keyword = h(trim($this->getRequest()->getQuery('keyword', '')));
        $this->set('keyword', $keyword);

        if (!empty($this->getRequest()->getQuery('productWithError'))) {
            $keyword = h(trim($this->getRequest()->getQuery('productWithError')));
        }

        $categoriesTable = $this->getTableLocator()->get('Categories');
        $categoriesForSelect = $categoriesTable->getForSelect(null, false, false, false);

        $catalogService = new CatalogService();
        $allProductsCount = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), false, '', 0, true, Configure::read('app.selfServiceModeShowOnlyStockProducts'));
        $categoriesForSelect = [
            Configure::read('app.categoryAllProducts') => __('All_products') . ' (' . $allProductsCount . ')',
        ] + $categoriesForSelect;
        $this->set('categoriesForSelect', $categoriesForSelect);

        $categoryIdForSearch = $categoryId;
        if ($categoryId == 0 && $keyword != '') {
            $categoryIdForSearch = Configure::read('app.categoryAllProducts');
        }
        $products = $catalogService->getProducts($categoryIdForSearch, false, $keyword, 0, false, Configure::read('app.selfServiceModeShowOnlyStockProducts'), page: $page);
        $totalProductCount = $catalogService->getProducts($categoryIdForSearch, false, $keyword, 0, true, Configure::read('app.selfServiceModeShowOnlyStockProducts'));
        $pagesCount = $catalogService->getPagesCount($totalProductCount);
        $products = $catalogService->prepareProducts($products);

        $this->set('totalProductCount', $totalProductCount);
        $this->set('products', $products);
        $this->set('pagesCount', $pagesCount);
        $this->set('page', $page);

        $this->set('products', $products);

        $this->viewBuilder()->setLayout('self_service');
        $this->set('title_for_layout', __('Self_service_mode'));

        if (!empty($this->getRequest()->getQuery('keyword')) && count($products) == 1) {

            $hashedProductId = strtolower(substr($keyword, 0, 4));
            $attributeId = (int) substr($keyword, 4, 4);

            $customBarcodeFound = false;
            $customBarcodeWithWeightFound = false;
            if (!empty($products[0]->barcode_product) && $keyword == $products[0]->barcode_product->barcode) {
                $customBarcodeFound = true;
                $attributeId = 0;
            } else {
                if (!empty($products[0]->barcode_product)) {
                    if ($catalogService->hasABarcodeWeightPrefix($keyword)){
                        if ($catalogService->getBarcodeWeightFilledWithNull($keyword) == $products[0]->barcode_product->barcode) {
                            $customBarcodeWithWeightFound = true;
                            $attributeId = 0;
                        }
                    }
                }
            }

            if (!empty($products[0]->product_attributes)) {
                foreach($products[0]->product_attributes as $productAttribute) {
                    if ($productAttribute->barcode_product_attribute) {
                        if ($keyword == $productAttribute->barcode_product_attribute->barcode) {
                            $customBarcodeFound = true;
                            $attributeId = $productAttribute->id_product_attribute;
                            break;
                        } else {
                            if ($catalogService->hasABarcodeWeightPrefix($keyword)){
                                if ($catalogService->getBarcodeWeightFilledWithNull($keyword) == $productAttribute->barcode_product_attribute->barcode) {
                                    $customBarcodeWithWeightFound = true;
                                    $attributeId = $productAttribute->id_product_attribute;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            if ($hashedProductId == $products[0]->system_bar_code || $customBarcodeFound || $customBarcodeWithWeightFound) {
                $cartProductsTable = $this->getTableLocator()->get('CartProducts');
                $quantityInUnits = -1;
                if ($customBarcodeWithWeightFound){
                    $quantityInUnits = $catalogService->getBarcodeWeight($keyword);
                }
                $result = $cartProductsTable->add($products[0]->id_product, $attributeId, 1, $quantityInUnits);

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
            $cart = $this->identity->getCart();
            $this->set('cart', $cart['Cart']);
        }

        if ($this->getRequest()->getEnv('ORIGINAL_REQUEST_METHOD') == 'POST') {

            if ($this->identity->isCartEmpty()) {
                $this->Flash->error(__('Your_shopping_bag_was_empty.'));
                $this->redirect(Configure::read('app.slugHelper')->getSelfService());
                return;
            }

            $cart = $this->cartService->finish();

            if (empty($this->viewBuilder()->getVars()['cartErrors']) && empty($this->viewBuilder()->getVars()['formErrors'])) {

                $redirectUrl = Configure::read('app.slugHelper')->getSelfService();

                if (isset($cart['invoice_id'])) {
                    $invoiceId = $cart['invoice_id'];
                    if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
                        $invoiceRoute = Configure::read('app.slugHelper')->getHelloCashReceipt($invoiceId);
                    } else {
                        $invoicesTable = $this->getTableLocator()->get('Invoices');
                        $invoice = $invoicesTable->find('all', conditions: [
                            'Invoices.id' => $invoiceId,
                        ])->first();
                        if (!empty($invoice)) {
                            $invoiceRoute = Configure::read('app.slugHelper')->getInvoiceDownloadRoute($invoice->filename);
                        }
                    }

                    if (!$this->identity->invoices_per_email_enabled && isset($invoiceRoute)) {
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
