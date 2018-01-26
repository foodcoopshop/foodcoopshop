<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * CartsController
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
class CartsController extends FrontendController
{

    public function beforeFilter(Event $event)
    {

        parent::beforeFilter($event);

        if ($this->request->is('ajax')) {
            $message = '';
            if (! $this->AppAuth->user()) {
                $message = 'Du bist nicht angemeldet.';
            }
            if ($this->AppAuth->isManufacturer()) {
                $message = 'Herstellern steht diese Funktion leider nicht zur Verfügung.';
            }
            if ($message != '') {
                $this->log($message);
                die(json_encode([
                    'status' => 0,
                    'msg' => $message
                ]));
            }
        }

        $this->AppAuth->allow('generateCancellationInformationPdf');
    }

    public function isAuthorized($user)
    {
        return $this->AppAuth->user() && Configure::read('appDb.FCS_CART_ENABLED') && !$this->AppAuth->isManufacturer();
    }

    public function detail()
    {
        $this->set('title_for_layout', 'Dein Warenkorb');
    }

    /**
     * called from finish context
     * saves pdf as file
     * @param array $order
     * @param array $orderDetails
     */
    private function generateCancellationInformationAndForm($order, $products)
    {
        $this->set('order', $order);
        $manufacturers = [];
        foreach ($products as $product) {
            $manufacturers[$product['Manufacturers']['id_manufacturer']][] = $product;
        }
        $this->set('manufacturers', $manufacturers);
        $this->set('saveParam', 'I');
        $this->RequestHandler->renderAs($this, 'pdf');
        return $this->render('generateCancellationInformationAndForm');
    }

    /**
     * called from finish context
     * @param array $order
     * saves pdf as file
     */
    private function generateGeneralTermsAndConditions($order)
    {
        $this->set('order', $order);
        $this->set('saveParam', 'I');
        $this->RequestHandler->renderAs($this, 'pdf');
        return $this->render('generateGeneralTermsAndConditions');
    }

    /**
     * called from finish context
     * saves pdf as file
     * @param array $order
     * @param array $orderDetails
     * @param array $orderDetailsTax
     */
    private function generateOrderConfirmation($order, $orderDetails, $orderDetailsTax)
    {

        $this->Product = TableRegistry::get('Products');
        $this->set('order', $order);
        $manufacturers = [];
        foreach ($orderDetails as $orderDetail) {
            $product = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $orderDetail['OrderDetails']['product_id']
                ]
            ])->first();
            // avoid extra db request and attach taxes manually to order details
            foreach ($orderDetailsTax as $tax) {
                if ($tax['id_order_detail'] == $orderDetail['OrderDetails']['id_order_detail']) {
                    $orderDetail['OrderDetails']['OrderDetailTaxes'] = $tax;
                }
            }
            $manufacturers[$product['Products']['id_manufacturer']][] = [
                'OrderDetails' => $orderDetail['OrderDetails'],
                'Manufacturers' => $product['Manufacturers']
            ];
        }

        $this->set('manufacturers', $manufacturers);
        $this->set('saveParam', 'I');
        $this->RequestHandler->renderAs($this, 'pdf');
        return $this->render('generateOrderConfirmation');
    }

    /**
     * generates pdf on-the-fly
     */
    public function generateCancellationInformationPdf()
    {
        $this->set('saveParam', 'I');
        $this->RequestHandler->renderAs($this, 'pdf');
        $this->render('generateCancellationInformationAndForm');
    }

    public function finish()
    {

        if (!$this->request->is('post')) {
            $this->redirect('/');
        }

        $this->set('title_for_layout', 'Warenkorb abschließen');
        $cart = $this->AppAuth->getCart();

        $this->Cart = TableRegistry::get('Carts');
        $this->Product = TableRegistry::get('Products');

        // START check if no amount is 0
        $productWithAmount0Found = false;
        foreach ($this->AppAuth->Cart->getProducts() as $ccp) {
            $ids = $this->Product->getProductIdAndAttributeId($ccp['productId']);
            if ($ccp['amount'] == 0) {
                $this->log('amount of cart productId ' . $ids['productId'] . ' (attributeId : ' . $ids['attributeId'] . ') was 0 and therefore removed from cart');
                $ccp = TableRegistry::get('CartProducts');
                $ccp->remove($ids['productId'], $ids['attributeId'], $this->AppAuth->Cart->getCartId());
                $productWithAmount0Found = true;
            }
        }

        if ($productWithAmount0Found) {
            $cart = $this->AppAuth->getCart();
            $this->AppAuth->setCart($cart);
        }
        // END check if no amount is 0

        if (empty($cart) || empty($this->AppAuth->Cart->getProducts())) {
            $this->Flash->error('Dein Warenkorb war leer.');
            $this->redirect(Configure::read('app.slugHelper')->getCartDetail());
        }

        $cartErrors = [];
        $orderDetails2save = [];
        $products = [];

        foreach ($this->AppAuth->Cart->getProducts() as $ccp) {
            $ids = $this->Product->getProductIdAndAttributeId($ccp['productId']);

            $product = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $ids['productId']
                ],
                'fields' => ['Products.*', '!'.$this->Product->getManufacturerHolidayConditions().' as IsHolidayActive']
            ])->first();
            $products[] = $product;

            $stockAvailableQuantity = $product['StockAvailables']['quantity'];

            // stock available check for product (without attributeId)
            if ($ids['attributeId'] == 0 && $stockAvailableQuantity < $ccp['amount']) {
                $message = 'Die gewünschte Anzahl (' . $ccp['amount'] . ') des Produktes "' . $product['ProductLangs']['name'] . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $stockAvailableQuantity . ' Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um die Bestellung abzuschließen.';
                $cartErrors[$ccp['productId']][] = $message;
            }

            if ($ids['attributeId'] > 0) {
                $attributeIdFound = false;
                foreach ($product['ProductAttributes'] as $attribute) {
                    if ($attribute['id_product_attribute'] == $ids['attributeId']) {
                        $attributeIdFound = true;
                        $stockAvailableQuantity = $attribute['StockAvailables']['quantity'];
                        // stock available check for attribute
                        if ($stockAvailableQuantity < $ccp['amount']) {
                            $this->Attribute = TableRegistry::get('Attributes');
                            $attribute = $this->Attribute->find('all', [
                                'conditions' => [
                                    'Attributes.id_attribute' => $attribute['ProductAttributeCombinations']['id_attribute']
                                ]
                            ])->first();
                            $message = 'Die gewünschte Anzahl (' . $ccp['amount'] . ') der Variante "' . $attribute['Attributes']['name'] . '" des Produktes "' . $product['ProductLangs']['name'] . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $stockAvailableQuantity . '. Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um die Bestellung abzuschließen.';
                            $cartErrors[$ccp['productId']][] = $message;
                        }
                        break;
                    }
                }
                if (! $attributeIdFound) {
                    $message = 'Die Variante existiert nicht. Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um deine Bestellung abzuschließen.';
                    $cartErrors[$ccp['productId']][] = $message;
                }
            }

            if (! $product['Products']['active']) {
                $message = 'Das Produkt "' . $product['ProductLangs']['name'] . '" ist leider nicht mehr aktiviert und somit nicht mehr bestellbar. Um deine Bestellung abzuschließen, lösche bitte das Produkt aus deinem Warenkorb.';
                $cartErrors[$ccp['productId']][] = $message;
            }

            if (! $product['Manufacturers']['active'] || $product[0]['IsHolidayActive']) {
                $message = 'Der Hersteller des Produktes "' . $product['ProductLangs']['name'] . '" hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar. Um deine Bestellung abzuschließen, lösche bitte das Produkt aus deinem Warenkorb.';
                $cartErrors[$ccp['productId']][] = $message;
            }

            // build orderDetails2save
            $orderDetails2save[] = [
                'product_id' => $ids['productId'],
                'product_attribute_id' => $ids['attributeId'],
                'product_name' => $this->Cart->getProductNameWithUnity($ccp['productName'], $ccp['unity']),
                'product_quantity' => $ccp['amount'],
                'product_price' => $ccp['priceExcl'],
                'total_price_tax_excl' => $ccp['priceExcl'],
                'total_price_tax_incl' => $ccp['price'],
                'id_tax' => $product['Products']['id_tax'],
                'deposit' => $ccp['deposit']
            ];

            $newQuantity = $stockAvailableQuantity - $ccp['amount'];
            if ($newQuantity < 0) {
                $message = 'attention, this should never happen! stock available would have been negative: productId: ' . $ids['productId'] . ', attributeId: ' . $ids['attributeId'] . '; changed it manually to 0 to avoid negative stock available value.';
                $newQuantity = 0; // never ever allow negative stock available
            }
            $stockAvailable2saveData[] = [
                'StockAvailables.quantity' => $newQuantity
            ];
            $stockAvailable2saveConditions[] = [
                'StockAvailables.id_product' => $ids['productId'],
                'StockAvailables.id_product_attribute' => $ids['attributeId']
            ];
        }


        $this->set('cartErrors', $cartErrors);


        $this->Order = TableRegistry::get('Orders');
        $formErrors = false;
        if (!isset($this->request->data['Orders']['general_terms_and_conditions_accepted']) || $this->request->data['Orders']['general_terms_and_conditions_accepted'] != 1) {
            $this->Order->invalidate('general_terms_and_conditions_accepted', 'Bitte akzeptiere die AGB.');
            $formErrors = true;
        }
        if (!isset($this->request->data['Orders']['cancellation_terms_accepted']) || $this->request->data['Orders']['cancellation_terms_accepted'] != 1) {
            $this->Order->invalidate('cancellation_terms_accepted', 'Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.');
            $formErrors = true;
        }
        if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
            $orderComment = strip_tags(trim($this->request->data['Orders']['comment']), '<strong><b>');
            $maxOrderCommentCount = 500;
            if (strlen($orderComment) > $maxOrderCommentCount) {
                $this->Order->invalidate('comment', 'Bitte gib maximal '.$maxOrderCommentCount.' Zeichen ein.');
                $formErrors = true;
            }
        }

        $this->set('formErrors', $formErrors);

        if (!empty($cartErrors) || $formErrors) {
            $this->Flash->error('Es sind Fehler aufgetreten.');
        } else {
            // START save order
            $this->Order->id = null;
            $order2save = [
                'id_customer' => $this->AppAuth->getUserId(),
                'id_cart' => $this->AppAuth->Cart->getCartId(),
                'id_currency' => 1,
                'current_state' => ORDER_STATE_OPEN,
                'total_paid' => $this->AppAuth->Cart->getProductSum(),
                'total_paid_tax_incl' => $this->AppAuth->Cart->getProductSum(),
                'total_paid_tax_excl' => $this->AppAuth->Cart->getProductSumExcl(),
                'total_deposit' => $this->AppAuth->Cart->getDepositSum(),
                'general_terms_and_conditions_accepted' => $this->request->data['Orders']['general_terms_and_conditions_accepted'],
                'cancellation_terms_accepted' => $this->request->data['Orders']['cancellation_terms_accepted']
            ];
            if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
                $order2save['comment'] = $orderComment;
            }
            $order = $this->Order->save($order2save, [
                'validate' => false
            ]);

            if (empty($order)) {
                $message = 'Bei der Erstellung der Bestellung ist ein Fehler aufgetreten.';
                $this->Flash->error($message);
                $this->log($message);
                $this->redirect(Configure::read('app.slugHelper')->getCartFinish());
            }

            $orderId = $order['Orders']['id_order'];
            // END save order

            // START update order_id in orderDetails2save
            foreach ($orderDetails2save as &$orderDetail) {
                $orderDetail['id_order'] = $orderId;
            }
            $this->Order->OrderDetails->saveAll($orderDetails2save);
            // END update order_id in orderDetails2save

            // START save order_detail_tax
            $orderDetails = $this->Order->OrderDetails->find('all', [
                'conditions' => [
                    'OrderDetails.id_order' => $orderId
                ]
            ]);

            if (empty($orderDetails)) {
                $message = 'Beim Speichern der bestellten Produkte ist ein Fehler aufgetreten.';
                $this->Flash->error($message);
                $this->log($message);
                $this->redirect(Configure::read('app.slugHelper')->getCartFinish());
            }

            $orderDetailTax2save = [];
            $this->OrderDetailTax = TableRegistry::get('OrderDetailTaxs');
            foreach ($orderDetails as $orderDetail) {
                // should not be necessary but a user somehow managed to set product_quantity as 0
                $quantity = $orderDetail['OrderDetails']['product_quantity'];
                if ($quantity == 0) {
                    $this->log('product_quantity was 0, would have resulted in division by zero error');
                    continue;
                }

                $productId = $orderDetail['OrderDetails']['product_id'];
                $price = $orderDetail['OrderDetails']['total_price_tax_incl'];

                $unitPriceExcl = $this->Product->getNetPrice($productId, $price / $quantity);
                $unitTaxAmount = $this->Product->getUnitTax($price, $unitPriceExcl, $quantity);
                $totalTaxAmount = $unitTaxAmount * $quantity;

                $orderDetailTax2save[] = [
                    'id_order_detail' => $orderDetail['OrderDetails']['id_order_detail'],
                    'id_tax' => 0, // do not use the field id_tax in order_details_tax but id_tax in order_details!
                    'unit_amount' => $unitTaxAmount,
                    'total_amount' => $totalTaxAmount
                ];
            }

            $this->OrderDetailTax->saveAll($orderDetailTax2save);
            // END save order_detail_tax

            $this->sendShopOrderNotificationToManufacturers($cart['CartProducts'], $order);

            // START update stock available
            $i = 0;
            foreach ($stockAvailable2saveData as &$data) {
                $this->Product->StockAvailable->updateAll($stockAvailable2saveData[$i], $stockAvailable2saveConditions[$i]);
                $this->Product->StockAvailable->updateQuantityForMainProduct($stockAvailable2saveConditions[$i]['StockAvailables.id_product']);
                $i ++;
            }
            // END update stock available

            $this->AppAuth->Cart->markAsSaved();

            $this->Flash->success('Deine Bestellung wurde erfolgreich abgeschlossen.');
            $this->ActionLog = TableRegistry::get('ActionLogs');
            $this->ActionLog->customSave('customer_order_finished', $this->AppAuth->getUserId(), $orderId, 'orders', $this->AppAuth->getUsername() . ' hat eine neue Bestellung getätigt (' . Configure::read('app.htmlHelper')->formatAsEuro($this->AppAuth->Cart->getProductSum()) . ').');

            // START send confirmation email to customer
            // do not send email to inactive users (superadmins can place shop orders for inactive users!)
            if ($this->AppAuth->user('active')) {
                $email = new AppEmail();
                $email->template('customer_order_successful')
                    ->emailFormat('html')
                    ->to($this->AppAuth->getEmail())
                    ->subject('Bestellbestätigung')
                    ->viewVars([
                    'cart' => $cart,
                    'appAuth' => $this->AppAuth,
                        'originalLoggedCustomer' => $this->request->session()->check('Auth.originalLoggedCustomer') ? $this->request->session()->read('Auth.originalLoggedCustomer') : null,
                    'order' => $order,
                    'depositSum' => $this->AppAuth->Cart->getDepositSum(),
                    'productSum' => $this->AppAuth->Cart->getProductSum(),
                    'productAndDepositSum' => $this->AppAuth->Cart->getProductAndDepositSum()
                    ]);


                $email->addAttachments(['Informationen-ueber-Ruecktrittsrecht-und-Ruecktrittsformular.pdf' => ['data' => $this->generateCancellationInformationAndForm($order, $products), 'mimetype' => 'application/pdf']]);
                $email->addAttachments(['Bestelluebersicht.pdf' => ['data' => $this->generateOrderConfirmation($order, $orderDetails, $orderDetailTax2save), 'mimetype' => 'application/pdf']]);
                $email->addAttachments(['Allgemeine-Geschaeftsbedingungen.pdf' => ['data' => $this->generateGeneralTermsAndConditions($order), 'mimetype' => 'application/pdf']]);

                $email->send();
            }
            //END send confirmation email to customer

            // due to redirect, beforeRender() is not called
            $this->resetOriginalLoggedCustomer();

            $this->redirect(Configure::read('app.slugHelper')->getCartFinished($orderId));
        }

        $this->request->action = 'detail';
        $this->render('detail');
    }

    public function sendShopOrderNotificationToManufacturers($cartProducts, $order)
    {

        if (!$this->request->session()->check('Auth.shopOrderCustomer')) {
            return false;
        }

        $manufacturers = [];
        foreach ($cartProducts as $cartProduct) {
            $manufacturers[$cartProduct['manufacturerId']][] = $cartProduct;
        }

        $this->Manufacturer = TableRegistry::get('Manufacturers');

        foreach ($manufacturers as $manufacturerId => $cartProducts) {
            $manufacturer = $this->Manufacturer->find('all', [
                'conditions' => [
                    'Manufacturers.id_manufacturer' => $manufacturerId
                ]
            ])->first();

            $depositSum = 0;
            $productSum = 0;
            foreach ($cartProducts as $cartProduct) {
                $depositSum += $cartProduct['deposit'];
                $productSum += $cartProduct['price'];
            }

            $sendShopOrderNotification = $this->Manufacturer->getOptionSendShopOrderNotification($manufacturer['Manufacturers']['send_shop_order_notification']);
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer['Manufacturers']['bulk_orders_allowed']);
            if ($sendShopOrderNotification && !$bulkOrdersAllowed) {
                $email = new AppEmail();
                $email->template('shop_order_notification')
                ->emailFormat('html')
                ->to($manufacturer['Addresses']['email'])
                ->subject('Benachrichtigung über Sofort-Bestellung Nr. ' . $order['Orders']['id_order'])
                ->viewVars([
                    'appAuth' => $this->AppAuth,
                    'order' => $order,
                    'cart' => ['CartProducts' => $cartProducts],
                    'originalLoggedCustomer' => $this->request->session()->read('Auth.originalLoggedCustomer'),
                    'manufacturer' => $manufacturer,
                    'depositSum' => $depositSum,
                    'productSum' => $productSum,
                    'productAndDepositSum' => $depositSum + $productSum,
                    'showManufacturerUnsubscribeLink' => true
                ]);
                $email->send();
            }
        }
    }

    public function orderSuccessful($orderId)
    {
        $orderId = (int) $this->request->getParam('pass')[0];

        $this->Order = TableRegistry::get('Orders');
        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId,
                'Orders.id_customer' => $this->AppAuth->getUserId()
            ]
        ])->first();
        if (empty($order)) {
            throw new RecordNotFoundException('order not found');
        }

        $this->set('order', $order);

        $this->BlogPost = TableRegistry::get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', 'Deine Bestellung ist abgeschlossen');

        $this->resetOriginalLoggedCustomer();
        $this->destroyShopOrderCustomer();
    }

    public function ajaxDeleteShopOrderCustomer()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();
        $this->destroyShopOrderCustomer();

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    private function doManufacturerCheck($productId)
    {
        if ($this->AppAuth->isManufacturer()) {
            $message = 'Herstellern steht diese Funktion leider nicht zur Verfügung.';
            $this->log($message);
            die(json_encode([
                'status' => 0,
                'msg' => $message,
                'productId' => $productId
            ]));
        }
    }

    public function ajaxRemove()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $initialProductId = $this->params['data']['productId'];

        $this->doManufacturerCheck($initialProductId);

        $attributeId = 0;
        $productId = $initialProductId;
        $explodedProductId = explode('-', $initialProductId);
        if (count($explodedProductId) == 2) {
            $productId = $explodedProductId[0];
            $attributeId = (int) $explodedProductId[1];
        }

        $cart = $this->AppAuth->getCart();
        $this->AppAuth->setCart($cart);

        $existingCartProduct = $this->AppAuth->Cart->getProduct($initialProductId);
        if (empty($existingCartProduct)) {
            $message = 'Produkt ' . $productId . ' war nicht in Warenkorb vorhanden.';
            die(json_encode([
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ]));
        }

        $ccp = TableRegistry::get('CartProducts');
        $ccp->remove($productId, $attributeId, $cart['Cart']['id_cart']);

        // update cart to update field date_upd
        $cc = TableRegistry::get('Cart');
        $cc->id = $cart['Cart']['id_cart'];
        $cc->updateDateUpd();

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    public function ajaxAdd()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $initialProductId = $this->params['data']['productId'];

        $this->doManufacturerCheck($initialProductId);

        $attributeId = 0;
        $productId = $initialProductId;
        $explodedProductId = explode('-', $initialProductId);
        if (count($explodedProductId) == 2) {
            $productId = $explodedProductId[0];
            $attributeId = (int) $explodedProductId[1];
        }

        $amount = (int) $this->params['data']['amount'];
        // allow -1 and 1 - 99
        if ($amount == 0 || $amount < - 1 || $amount > 99) {
            $message = 'Die gewünschte Anzahl "' . $amount . '" ist nicht gültig.';
            die(json_encode([
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ]));
        }

        // get product data from database
        $this->Product = TableRegistry::get('Products');
        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'ProductLangs', 'StockAvailables', 'ProductAttributes', 'ProductAttributes.StockAvailable', 'ProductAttributes.ProductAttributeCombination.Attribute'
            ]
        ])->first();

        $existingCartProduct = $this->AppAuth->Cart->getProduct($initialProductId);
        $combinedAmount = $amount;
        if ($existingCartProduct) {
            $combinedAmount = $existingCartProduct['amount'] + $amount;
        }
        // check if passed product exists
        if (empty($product)) {
            $message = 'Das Produkt mit der ID ' . $productId . ' ist nicht vorhanden.';
            $this->log($message);
            die(json_encode([
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ]));
        }

        // stock available check for product
        if ($attributeId == 0 && $product['StockAvailables']['quantity'] < $combinedAmount && $amount > 0) {
            $message = 'Die gewünschte Anzahl (' . $combinedAmount . ') des Produktes "' . $product['ProductLangs']['name'] . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $product['StockAvailables']['quantity'];
            die(json_encode([
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ]));
        }

        // check if passed optional product/attribute relation exists
        if ($attributeId > 0) {
            $attributeIdFound = false;
            foreach ($product['ProductAttributes'] as $attribute) {
                if ($attribute['id_product_attribute'] == $attributeId) {
                    $attributeIdFound = true;
                    // stock available check for attribute
                    if ($attribute['StockAvailables']['quantity'] < $combinedAmount && $amount > 0) {
                        $message = 'Die gewünschte Anzahl (' . $combinedAmount . ') der Variante "' . $attribute['ProductAttributeCombinations']['Attributes']['name'] . '" des Produktes "' . $product['ProductLangs']['name'] . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $attribute['StockAvailables']['quantity'];
                        die(json_encode([
                            'status' => 0,
                            'msg' => $message,
                            'productId' => $initialProductId
                        ]));
                    }
                    break;
                }
            }
            if (! $attributeIdFound) {
                $message = 'Die Variante existiert nicht: ' . $initialProductId;
                die(json_encode([
                    'status' => 0,
                    'msg' => $message,
                    'productId' => $initialProductId
                ]));
            }
        }

        // update amount if cart product already exists
        $cart = $this->AppAuth->getCart();
        $this->AppAuth->setCart($cart);
        $ccp = TableRegistry::get('CartProducts');
        $ccp->id = null;
        if ($existingCartProduct) {
            $ccp->id = $existingCartProduct['cartProductId'];
        }

        $cartProduct2save = [
            'id_product' => $productId,
            'amount' => $combinedAmount,
            'id_product_attribute' => $attributeId,
            'id_cart' => $cart['Cart']['id_cart']
        ];
        $ccp->save($cartProduct2save);

        // update cart to update field date_upd
        $cc = TableRegistry::get('Cart');
        $cc->id = $cart['Cart']['id_cart'];
        $cc->updateDateUpd();

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }
}
