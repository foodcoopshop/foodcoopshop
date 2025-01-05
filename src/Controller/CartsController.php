<?php
declare(strict_types=1);

namespace App\Controller;

use App\Services\PdfWriter\InformationAboutRightOfWithdrawalPdfWriterService;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use App\Services\DeliveryRhythmService;
use App\Services\OrderCustomerService;
use App\Services\CartService;
use Cake\View\JsonView;
use Cake\ORM\TableRegistry;
use Cake\Http\Response;
use Cake\Log\Log;

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
class CartsController extends FrontendController
{

    protected CartService $cartService;

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    /**
     * allowing ajaxActions is ok as they are separately checked in ajaxIsAuthorized
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'generateRightOfWithdrawalInformationPdf',
            'ajaxAdd',
            'ajaxRemove',
            'ajaxDeleteOrderForDifferentCustomer',
        ]);

        $this->cartService = new CartService($this);

    }

    /**
     * ajax methods need to be checked separately due to individual error message handling
     */
    private function ajaxIsAuthorized(): void
    {
        if (!($this->identity !== null)) {
            throw new ForbiddenException(__('For_placing_an_order_<a href="{0}">you_need_to_sign_in_or_register</a>.', [
                Configure::read('app.slugHelper')->getLogin()
            ]));
        }
        if ($this->identity->isManufacturer()) {
            throw new ForbiddenException(__('No_access_for_manufacturers.'));
        }
    }

    public function detail(): void
    {
        $this->set('title_for_layout', __('Your_cart'));

        if ($this->getRequest()->getEnv('ORIGINAL_REQUEST_METHOD') == 'GET') {

            $cart = $this->identity->getCart();

            if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY') && $cart['Cart']->pickup_day_entities) {
                $cart['Cart']->pickup_day_entities[0]->comment = '';
            }

            $this->set('cart', $cart['Cart']);

        }
    }

    public function generateRightOfWithdrawalInformationPdf(): void
    {
        $pdfWriter = new InformationAboutRightOfWithdrawalPdfWriterService();
        $pdfWriter->setData([
            'identity' => $this->identity
        ]);
        die($pdfWriter->writeInline());
    }

    public function finish(): void
    {

        if (!$this->getRequest()->getEnv('ORIGINAL_REQUEST_METHOD') == 'POST') {
            $this->redirect('/');
            return;
        }

        if ($this->getRequest()->getEnv('ORIGINAL_REQUEST_METHOD') == 'POST') {
            // no spam protected email output in input field when email address is used in comment text
            $this->protectEmailAddresses = false; 
        }

        $this->set('title_for_layout', __('Finish_cart'));

        if ($this->identity->isCartEmpty()) {
            $this->Flash->error(__('Your_cart_was_empty.'));
            $this->redirect(Configure::read('app.slugHelper')->getCartDetail());
            return;
        }

        $cart = $this->cartService->finish();

        if (empty($this->viewBuilder()->getVars()['cartErrors']) && empty($this->viewBuilder()->getVars()['formErrors'])) {
            $this->resetOriginalLoggedCustomer();
            $this->redirect(Configure::read('app.slugHelper')->getCartFinished($cart['Cart']->id_cart));
            return;
        }

        $this->detail();
        $this->render('detail');
    }

    public function orderSuccessful($cartId): void
    {
        $cartId = (int) $this->getRequest()->getParam('pass')[0];

        $cartsTable = $this->getTableLocator()->get('Carts');
        $cart = $cartsTable->find('all', conditions: [
            'Carts.id_cart' => $cartId,
            'Carts.id_customer' => $this->identity->getId()
        ])->first();

        if (empty($cart)) {
            throw new RecordNotFoundException('cart not found');
        }
        $this->set('cart', $cart);

        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPosts = $blogPostsTable->findBlogPosts(null, true);
        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', __('Your_order_has_been_placed'));

        $this->resetOriginalLoggedCustomer();
        $this->destroyOrderCustomer();
    }

    public function ajaxDeleteOrderForDifferentCustomer(): ?Response
    {
        try {
            $this->ajaxIsAuthorized();
        } catch(ForbiddenException $e) {
            return $this->sendAjaxError($e);
        }

        $this->request = $this->request->withParam('_ext', 'json');

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();
        $this->destroyOrderCustomer();

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
        return null;
    }

    private function doManufacturerCheck($productId): void
    {
        if ($this->identity->isManufacturer()) {
            $message = __('No_access_for_manufacturers.');
            $this->log($message);
            $this->set([
                'status' => 0,
                'msg' => $message,
                'productId' => $productId
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'productId']);
            return;
        }
    }

    public function ajaxRemove(): ?Response
    {
        try {
            $this->ajaxIsAuthorized();
        } catch(ForbiddenException $e) {
            return $this->sendAjaxError($e);
        }

        $this->request = $this->request->withParam('_ext', 'json');

        $initialProductId = $this->getRequest()->getData('productId');

        $this->doManufacturerCheck($initialProductId);

        $productsTable = $this->getTableLocator()->get('Products');
        $ids = $productsTable->getProductIdAndAttributeId($initialProductId);

        $cart = $this->identity->getCart();
        $this->identity->setCart($cart);

        $existingCartProduct = $this->identity->getProduct($initialProductId);
        if (empty($existingCartProduct)) {
            $message = __('Product_{0}_was_not_available_in_cart.', [$ids['productId']]);
            $this->set([
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'productId']);
            return null;
        }

        $cartProductTable = $this->getTableLocator()->get('CartProducts');
        $cartProductTable->remove($ids['productId'], $ids['attributeId'], $cart['Cart']['id_cart']);

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();

        $result = [
            'status' => 1,
            'msg' => 'ok'
        ];
        if ((new OrderCustomerService())->isSelfServiceModeByReferer()) {
            $result['callback'] = "foodcoopshop.SelfService.setFocusToSearchInputField();";
        }
        $this->set($result);
        $this->viewBuilder()->setOption('serialize', array_keys($result));
        return null;
    }

    public function emptyCart(): void
    {
        $this->doEmptyCart();
        $message = __('Your_cart_has_been_emptied_you_can_add_new_products_now.');
        $this->Flash->success($message);
        $this->redirect($this->referer());
    }

    private function doEmptyCart(): void
    {
        $cartProductsTable = TableRegistry::getTableLocator()->get('CartProducts');
        $cartProductsTable = $this->getTableLocator()->get('CartProducts');
        $cartProductsTable->removeAll($this->identity->getCartId(), $this->identity->getId());
        $this->identity->setCart($this->identity->getCart());
    }

    public function addOrderToCart(): void
    {
        $deliveryDate = h($this->getRequest()->getQuery('deliveryDate'));
        $this->doAddOrderToCart($deliveryDate);
        $this->redirect($this->referer());
    }

    private function doAddOrderToCart($deliveryDate): void
    {

        $this->doEmptyCart();
        $cartProductsTable = TableRegistry::getTableLocator()->get('CartProducts');

        $formattedDeliveryDate = strtotime($deliveryDate);

        $dateFrom = strtotime(Configure::read('app.timeHelper')->formatToDbFormatDate((new DeliveryRhythmService())->getOrderPeriodFirstDayByDeliveryDay($formattedDeliveryDate)));
        $dateTo = strtotime(Configure::read('app.timeHelper')->formatToDbFormatDate((new DeliveryRhythmService())->getOrderPeriodLastDayByDeliveryDay($formattedDeliveryDate)));

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $orderDetailsTable->getOrderDetailQueryForPeriodAndCustomerId($dateFrom, $dateTo, $this->identity->getId());

        $errorMessages = [];
        $loadedProducts = count($orderDetails);
        if (count($orderDetails) > 0) {
            foreach($orderDetails as $orderDetail) {
                $result = $cartProductsTable->add($orderDetail->product_id, $orderDetail->product_attribute_id, $orderDetail->product_amount);
                if (is_array($result)) {
                    $errorMessages[] = $result['msg'];
                    $loadedProducts--;
                }
            }
        }

        $message = __('Your_cart_has_been_emptied_and_your_past_order_has_been_loaded_into_the_cart.');
        $message .= '<br />';
        $message .= __('You_can_add_more_products_now.');

        if (!empty($errorMessages)) {
            $message .= '<div class="error">';
                $removedProducts = count($orderDetails) - $loadedProducts;
                $message .= '<b>';
                if ($removedProducts == 1) {
                    $message .= __('1_product_is_not_available_any_more.');
                } else {
                    $message .= __('{0}_products_are_not_available_any_more.', [$removedProducts]);
                }
                $message .= ' </b>';
                $message .= '<ul><li>' . join('</li><li>', $errorMessages) . '</li></ul>';
            $message .= '</div>';
        }

        $this->Flash->success($message);

    }

    public function addLastOrderToCart(): void
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $orderDetailsTable->getLastOrderDetailsForDropdown($this->identity->getId());
        if (empty($orderDetails)) {
            $message = __('There_are_no_orders_available.');
            $this->Flash->error($message);
        } else {
            reset($orderDetails);
            $lastOrderDate = key($orderDetails);
            $this->doAddOrderToCart($lastOrderDate);
        }
        $this->redirect(Configure::read('app.slugHelper')->getCartDetail());
    }

    public function ajaxAdd(): ?Response
    {
        try {
            $this->ajaxIsAuthorized();
        } catch(ForbiddenException $e) {
            return $this->sendAjaxError($e);
        }

        $this->request = $this->request->withParam('_ext', 'json');

        $initialProductId = $this->getRequest()->getData('productId');

        $this->doManufacturerCheck($initialProductId);
        $productsTable = $this->getTableLocator()->get('Products');
        $ids = $productsTable->getProductIdAndAttributeId($initialProductId);
        $amount = (int) $this->getRequest()->getData('amount');
        $orderedQuantityInUnits = Configure::read('app.numberHelper')->getStringAsFloat(
            (string) $this->getRequest()->getData('orderedQuantityInUnits')
        );

        $cartProductTable = TableRegistry::getTableLocator()->get('CartProducts');
        $cartProductTable = $this->getTableLocator()->get('CartProducts');
        $result = $cartProductTable->add($ids['productId'], $ids['attributeId'], $amount, $orderedQuantityInUnits);

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();

        // result is ok
        if (!is_array($result)) {
            $result = [
                'status' => 1,
                'msg' => 'ok'
            ];
        }

        if ((new OrderCustomerService())->isSelfServiceModeByReferer()) {
            $result['callback'] = "foodcoopshop.SelfService.setFocusToSearchInputField();";
        }

        $this->set($result);
        $this->viewBuilder()->setOption('serialize', array_keys($result));
        return null;
    }
}
