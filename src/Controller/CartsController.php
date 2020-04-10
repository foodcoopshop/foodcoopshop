<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Exception\Exception;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\ForbiddenException;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CartsController extends FrontendController
{

    public function beforeFilter(EventInterface $event)
    {

        parent::beforeFilter($event);
        if ($this->getRequest()->is('json')) {
            try {
                if (empty($this->AppAuth->user())) {
                    throw new ForbiddenException(__('For_placing_an_order_<a href="{0}">you_need_to_sign_in_or_register</a>.', [
                        Configure::read('app.slugHelper')->getLogin()
                    ]));
                }
                if ($this->AppAuth->isManufacturer()) {
                    throw new ForbiddenException(__('No_access_for_manufacturers.'));
                }
            } catch (Exception $e) {
                $this->sendAjaxError($e);
            }
        }

        $this->AppAuth->allow('generateRightOfWithdrawalInformationPdf');
    }

    public function isAuthorized($user)
    {
        return $this->AppAuth->user() && !$this->AppAuth->isManufacturer();
    }

    public function ajaxGetTimebasedCurrencyHoursDropdown($maxSeconds)
    {
        $this->RequestHandler->renderAs($this, 'json');
        $maxSeconds = (int) $maxSeconds;
        $options = Configure::read('app.timebasedCurrencyHelper')->getTimebasedCurrencyHoursDropdown($maxSeconds, Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE'));
        $this->set([
            'options' => $options,
            'status' => !empty($options)
        ]);
        $this->viewBuilder()->setOption('serialize', ['options', 'status']);
    }

    public function detail()
    {
        $this->set('title_for_layout', __('Your_cart'));
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
            $cart = $this->AppAuth->getCart();
            $this->set('cart', $cart['Cart']);
        }
    }

    /**
     * generates pdf on-the-fly
     */
    public function generateRightOfWithdrawalInformationPdf()
    {
        $this->set('saveParam', 'I');
        $this->RequestHandler->renderAs($this, 'pdf');
        $this->render('generateRightOfWithdrawalInformationAndForm');
    }

    public function finish()
    {
        
        if (!$_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->redirect('/');
            return;
        }

        $this->set('title_for_layout', __('Finish_cart'));
        
        if ($this->AppAuth->Cart->isCartEmpty()) {
            $this->Flash->error(__('Your_cart_was_empty.'));
            $this->redirect(Configure::read('app.slugHelper')->getCartDetail());
            return;
        }
        
        $cart = $this->AppAuth->Cart->finish();
        
        if (empty($this->viewBuilder()->getVars()['cartErrors']) && empty($this->viewBuilder()->getVars()['formErrors'])) {
            $this->resetOriginalLoggedCustomer();
            $this->redirect(Configure::read('app.slugHelper')->getCartFinished($cart['Cart']->id_cart));
            return;
        }
                
        $this->setAction('detail');
    }

    public function orderSuccessful($cartId)
    {
        $cartId = (int) $this->getRequest()->getParam('pass')[0];

        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $cartId,
                'Carts.id_customer' => $this->AppAuth->getUserId()
            ]
        ])->first();
        
        if (empty($cart)) {
            throw new RecordNotFoundException('cart not found');
        }
        $this->set('cart', $cart);

        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', __('Your_order_has_been_placed'));

        $this->resetOriginalLoggedCustomer();
        $this->destroyInstantOrderCustomer();
    }

    public function ajaxDeleteInstantOrderCustomer()
    {
        $this->RequestHandler->renderAs($this, 'json');

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();
        $this->destroyInstantOrderCustomer();

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    private function doManufacturerCheck($productId)
    {
        if ($this->AppAuth->isManufacturer()) {
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

    public function ajaxRemove()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $initialProductId = $this->getRequest()->getData('productId');

        $this->doManufacturerCheck($initialProductId);

        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $ids = $this->Product->getProductIdAndAttributeId($initialProductId);

        $cart = $this->AppAuth->getCart();
        $this->AppAuth->setCart($cart);

        $existingCartProduct = $this->AppAuth->Cart->getProduct($initialProductId);
        if (empty($existingCartProduct)) {
            $message = __('Product_{0}_was_not_available_in_cart.', [$ids['productId']]);
            $this->set([
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'productId']);
            return;
        }

        $cartProductTable = TableRegistry::getTableLocator()->get('CartProducts');
        $cartProductTable->remove($ids['productId'], $ids['attributeId'], $cart['Cart']['id_cart']);

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    public function emptyCart()
    {
        $this->doEmptyCart();
        $message = __('Your_cart_has_been_emptied_you_can_add_new_products_now.');
        $this->Flash->success($message);
        $this->redirect($this->referer());
    }

    private function doEmptyCart()
    {
        $this->CartProduct = TableRegistry::getTableLocator()->get('CartProducts');
        $this->CartProduct->removeAll($this->AppAuth->Cart->getCartId(), $this->AppAuth->getUserId());
        $this->AppAuth->setCart($this->AppAuth->getCart());
    }

    public function addOrderToCart()
    {
        $deliveryDate = h($this->getRequest()->getQuery('deliveryDate'));
        $this->doAddOrderToCart($deliveryDate);
        $this->redirect($this->referer());
    }

    private function doAddOrderToCart($deliveryDate)
    {

        $this->doEmptyCart();
        $this->CartProduct = TableRegistry::getTableLocator()->get('CartProducts');

        $formattedDeliveryDate = strtotime($deliveryDate);

        $dateFrom = strtotime(Configure::read('app.timeHelper')->formatToDbFormatDate(Configure::read('app.timeHelper')->getOrderPeriodFirstDay($formattedDeliveryDate)));
        $dateTo = strtotime(Configure::read('app.timeHelper')->formatToDbFormatDate(Configure::read('app.timeHelper')->getOrderPeriodLastDay($formattedDeliveryDate)));

        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->getOrderDetailQueryForPeriodAndCustomerId($dateFrom, $dateTo, $this->AppAuth->getUserId());

        $errorMessages = [];
        $loadedProducts = count($orderDetails);
        if (count($orderDetails) > 0) {
            foreach($orderDetails as $orderDetail) {
                $result = $this->CartProduct->add($this->AppAuth, $orderDetail->product_id, $orderDetail->product_attribute_id, $orderDetail->product_amount);
                if (is_array($result)) {
                    $errorMessages[] = $result['msg'];
                    $loadedProducts--;
                }
            }
        }

        $message = __('Your_cart_has_been_emptied_and_your_past_order_has_been_loaded_into_the_cart.');
        $message .= '<br />';
        $message .= __('You_can_add_more_products_now.');;

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

    public function addLastOrderToCart()
    {
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->getLastOrderDetailsForDropdown($this->AppAuth->getUserId());
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

    public function ajaxAdd()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $initialProductId = $this->getRequest()->getData('productId');

        $this->doManufacturerCheck($initialProductId);
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $ids = $this->Product->getProductIdAndAttributeId($initialProductId);
        $amount = (int) $this->getRequest()->getData('amount');
        $orderedQuantityInUnits = Configure::read('app.numberHelper')->getStringAsFloat(
            $this->getRequest()->getData('orderedQuantityInUnits')
        );
        
        $this->CartProduct = TableRegistry::getTableLocator()->get('CartProducts');
        $result = $this->CartProduct->add($this->AppAuth, $ids['productId'], $ids['attributeId'], $amount, $orderedQuantityInUnits);

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();

        // result is ok
        if (!is_array($result)) {
            $result = [
                'status' => 1,
                'msg' => 'ok'
            ];
            if ($orderedQuantityInUnits > 0 && $this->AppAuth->isSelfServiceModeByReferer()) {
                $result['callback'] = "foodcoopshop.SelfService.setFocusToSearchInputField();";
            }
        }
        
        $this->set($result);
        $this->viewBuilder()->setOption('serialize', array_keys($result));
    }
}
