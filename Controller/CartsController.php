<?php

App::uses('FrontendController', 'Controller');

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

    public function beforeFilter()
    {

        parent::beforeFilter();

        if ($this->request->is('ajax')) {
            $message = '';
            if (! $this->AppAuth->loggedIn()) {
                $message = 'Du bist nicht angemeldet.';
            }
            if ($this->AppAuth->isManufacturer()) {
                $message = 'Herstellern steht diese Funktion leider nicht zur Verfügung.';
            }
            if ($message != '') {
                $this->log($message);
                die(json_encode(array(
                    'status' => 0,
                    'msg' => $message
                )));
            }
        }

        $this->AppAuth->allow('generateCancellationInformationPdf');
    }

    public function isAuthorized($user)
    {
        return $this->AppAuth->loggedIn() && Configure::read('app.db_config_FCS_CART_ENABLED') && !$this->AppAuth->isManufacturer();
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
        $manufacturers = array();
        foreach ($products as $product) {
            $manufacturers[$product['Manufacturer']['id_manufacturer']][] = $product;
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

        $this->loadModel('Product');
        $this->set('order', $order);
        $manufacturers = array();
        foreach ($orderDetails as $orderDetail) {
            $this->Product->recursive = 2;
            $product = $this->Product->find('first', array(
                'conditions' => array(
                    'Product.id_product' => $orderDetail['OrderDetails']['product_id']
                )
            ));
            // avoid extra db request and attach taxes manually to order details
            foreach ($orderDetailsTax as $tax) {
                if ($tax['id_order_detail'] == $orderDetail['OrderDetails']['id_order_detail']) {
                    $orderDetail['OrderDetails']['OrderDetailTax'] = $tax;
                }
            }
            $manufacturers[$product['Product']['id_manufacturer']][] = array(
                'OrderDetail' => $orderDetail['OrderDetails'],
                'Manufacturer' => $product['Manufacturer']
            );
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

        $this->loadModel('Cart');
        $this->loadModel('Product');

        // START check if no amount is 0
        $productWithAmount0Found = false;
        foreach ($this->AppAuth->Cart->getProducts() as $ccp) {
            $ids = $this->Product->getProductIdAndAttributeId($ccp['productId']);
            if ($ccp['amount'] == 0) {
                $this->log('amount of cart productId ' . $ids['productId'] . ' (attributeId : ' . $ids['attributeId'] . ') was 0 and therefore removed from cart');
                $ccp = ClassRegistry::init('CartProduct');
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
            $this->redirect(Configure::read('slugHelper')->getCartDetail());
        }

        $cartErrors = array();
        $orderDetails2save = array();
        $products = array();

        foreach ($this->AppAuth->Cart->getProducts() as $ccp) {
            $ids = $this->Product->getProductIdAndAttributeId($ccp['productId']);

            $this->Product->recursive = 2;
            $product = $this->Product->find('first', array(
                'conditions' => array(
                    'Product.id_product' => $ids['productId']
                ),
                'fields' => array('Product.*', '!'.$this->Product->getManufacturerHolidayConditions().' as IsHolidayActive')
            ));
            $products[] = $product;

            $stockAvailableQuantity = $product['StockAvailable']['quantity'];

            // stock available check for product (without attributeId)
            if ($ids['attributeId'] == 0 && $stockAvailableQuantity < $ccp['amount']) {
                $message = 'Die gewünschte Anzahl (' . $ccp['amount'] . ') des Produktes "' . $product['ProductLang']['name'] . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $stockAvailableQuantity . ' Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um die Bestellung abzuschließen.';
                $cartErrors[$ccp['productId']][] = $message;
            }

            if ($ids['attributeId'] > 0) {
                $attributeIdFound = false;
                foreach ($product['ProductAttributes'] as $attribute) {
                    if ($attribute['id_product_attribute'] == $ids['attributeId']) {
                        $attributeIdFound = true;
                        $stockAvailableQuantity = $attribute['StockAvailable']['quantity'];
                        // stock available check for attribute
                        if ($stockAvailableQuantity < $ccp['amount']) {
                            $this->loadModel('Attribute');
                            $attribute = $this->Attribute->find('first', array(
                                'conditions' => array(
                                    'Attribute.id_attribute' => $attribute['ProductAttributeCombination']['id_attribute']
                                )
                            ));
                            $message = 'Die gewünschte Anzahl (' . $ccp['amount'] . ') der Variante "' . $attribute['Attribute']['name'] . '" des Produktes "' . $product['ProductLang']['name'] . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $stockAvailableQuantity . '. Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um die Bestellung abzuschließen.';
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

            if (! $product['Product']['active']) {
                $message = 'Das Produkt "' . $product['ProductLang']['name'] . '" ist leider nicht mehr aktiviert und somit nicht mehr bestellbar. Um deine Bestellung abzuschließen, lösche bitte das Produkt aus deinem Warenkorb.';
                $cartErrors[$ccp['productId']][] = $message;
            }

            if (! $product['Manufacturer']['active'] || $product[0]['IsHolidayActive']) {
                $message = 'Der Hersteller des Produktes "' . $product['ProductLang']['name'] . '" hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar. Um deine Bestellung abzuschließen, lösche bitte das Produkt aus deinem Warenkorb.';
                $cartErrors[$ccp['productId']][] = $message;
            }

            // build orderDetails2save
            $orderDetails2save[] = array(
                'product_id' => $ids['productId'],
                'product_attribute_id' => $ids['attributeId'],
                'product_name' => $this->Cart->getProductNameWithUnity($ccp['productName'], $ccp['unity']),
                'product_quantity' => $ccp['amount'],
                'product_price' => $ccp['priceExcl'],
                'total_price_tax_excl' => $ccp['priceExcl'],
                'total_price_tax_incl' => $ccp['price'],
                'id_tax' => $product['Product']['id_tax'],
                'deposit' => $ccp['deposit']
            );

            $newQuantity = $stockAvailableQuantity - $ccp['amount'];
            if ($newQuantity < 0) {
                $message = 'attention, this should never happen! stock available would have been negative: productId: ' . $ids['productId'] . ', attributeId: ' . $ids['attributeId'] . '; changed it manually to 0 to avoid negative stock available value.';
                $newQuantity = 0; // never ever allow negative stock available
            }
            $stockAvailable2saveData[] = array(
                'StockAvailable.quantity' => $newQuantity
            );
            $stockAvailable2saveConditions[] = array(
                'StockAvailable.id_product' => $ids['productId'],
                'StockAvailable.id_product_attribute' => $ids['attributeId']
            );
        }


        $this->set('cartErrors', $cartErrors);


        $this->loadModel('Order');
        $formErrors = false;
        if (!isset($this->request->data['Order']['general_terms_and_conditions_accepted']) || $this->request->data['Order']['general_terms_and_conditions_accepted'] != 1) {
            $this->Order->invalidate('general_terms_and_conditions_accepted', 'Bitte akzeptiere die AGB.');
            $formErrors = true;
        }
        if (!isset($this->request->data['Order']['cancellation_terms_accepted']) || $this->request->data['Order']['cancellation_terms_accepted'] != 1) {
            $this->Order->invalidate('cancellation_terms_accepted', 'Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.');
            $formErrors = true;
        }
        if (Configure::read('app.db_config_FCS_ORDER_COMMENT_ENABLED')) {
            $orderComment = strip_tags(trim($this->request->data['Order']['comment']), '<strong><b>');
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
            $order2save = array(
                'id_shop' => Configure::read('app.shopId'),
                'id_lang' => Configure::read('app.langId'),
                'id_customer' => $this->AppAuth->getUserId(),
                'id_cart' => $this->AppAuth->Cart->getCartId(),
                'id_currency' => 1,
                'current_state' => ORDER_STATE_OPEN,
                'id_lang' => Configure::read('app.langId'),
                'total_paid' => $this->AppAuth->Cart->getProductSum(),
                'total_paid_tax_incl' => $this->AppAuth->Cart->getProductSum(),
                'total_paid_tax_excl' => $this->AppAuth->Cart->getProductSumExcl(),
                'total_deposit' => $this->AppAuth->Cart->getDepositSum(),
                'general_terms_and_conditions_accepted' => $this->request->data['Order']['general_terms_and_conditions_accepted'],
                'cancellation_terms_accepted' => $this->request->data['Order']['cancellation_terms_accepted']
            );
            if (Configure::read('app.db_config_FCS_ORDER_COMMENT_ENABLED')) {
                $order2save['comment'] = $orderComment;
            }
            $order = $this->Order->save($order2save, array(
                'validate' => false
            ));

            if (empty($order)) {
                $message = 'Bei der Erstellung der Bestellung ist ein Fehler aufgetreten.';
                $this->Flash->error($message);
                $this->log($message);
                $this->redirect(Configure::read('slugHelper')->getCartFinish());
            }

            $orderId = $order['Order']['id_order'];
            // END save order

            // START update order_id in orderDetails2save
            foreach ($orderDetails2save as &$orderDetail) {
                $orderDetail['id_order'] = $orderId;
            }
            $this->Order->OrderDetails->saveAll($orderDetails2save);
            // END update order_id in orderDetails2save

            // START save order_detail_tax
            $orderDetails = $this->Order->OrderDetails->find('all', array(
                'conditions' => array(
                    'OrderDetails.id_order' => $orderId
                )
            ));

            if (empty($orderDetails)) {
                $message = 'Beim Speichern der bestellten Produkte ist ein Fehler aufgetreten.';
                $this->Flash->error($message);
                $this->log($message);
                $this->redirect(Configure::read('slugHelper')->getCartFinish());
            }

            $orderDetailTax2save = array();
            $this->loadModel('OrderDetailTax');
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

                $orderDetailTax2save[] = array(
                    'id_order_detail' => $orderDetail['OrderDetails']['id_order_detail'],
                    'id_tax' => 0, // do not use the field id_tax in order_details_tax but id_tax in order_details!
                    'unit_amount' => $unitTaxAmount,
                    'total_amount' => $totalTaxAmount
                );
            }

            $this->OrderDetailTax->saveAll($orderDetailTax2save);
            // END save order_detail_tax

            $this->sendShopOrderNotificationToManufacturers($cart['CartProducts'], $order);

            // START update stock available
            $i = 0;
            foreach ($stockAvailable2saveData as &$data) {
                $this->Product->StockAvailable->updateAll($stockAvailable2saveData[$i], $stockAvailable2saveConditions[$i]);
                $this->Product->StockAvailable->updateQuantityForMainProduct($stockAvailable2saveConditions[$i]['StockAvailable.id_product']);
                $i ++;
            }
            // END update stock available

            $this->AppAuth->Cart->markAsSaved();

            $this->Flash->success('Deine Bestellung wurde erfolgreich abgeschlossen.');
            $this->loadModel('ActionLog');
            $this->ActionLog->customSave('customer_order_finished', $this->AppAuth->getUserId(), $orderId, 'orders', $this->AppAuth->getUsername() . ' hat eine neue Bestellung getätigt (' . Configure::read('htmlHelper')->formatAsEuro($this->AppAuth->Cart->getProductSum()) . ').');

            // START send confirmation email to customer
            // do not send email to inactive users (superadmins can place shop orders for inactive users!)
            if ($this->AppAuth->user('active')) {
                $email = new AppEmail();
                $email->template('customer_order_successful')
                    ->emailFormat('html')
                    ->to($this->AppAuth->getEmail())
                    ->subject('Bestellbestätigung')
                    ->viewVars(array(
                    'cart' => $cart,
                    'appAuth' => $this->AppAuth,
                    'originalLoggedCustomer' => $this->Session->check('Auth.originalLoggedCustomer') ? $this->Session->read('Auth.originalLoggedCustomer') : null,
                    'order' => $order,
                    'depositSum' => $this->AppAuth->Cart->getDepositSum(),
                    'productSum' => $this->AppAuth->Cart->getProductSum(),
                    'productAndDepositSum' => $this->AppAuth->Cart->getProductAndDepositSum()
                    ));


                $email->addAttachments(array('Informationen-ueber-Ruecktrittsrecht-und-Ruecktrittsformular.pdf' => array('data' => $this->generateCancellationInformationAndForm($order, $products), 'mimetype' => 'application/pdf')));
                $email->addAttachments(array('Bestelluebersicht.pdf' => array('data' => $this->generateOrderConfirmation($order, $orderDetails, $orderDetailTax2save), 'mimetype' => 'application/pdf')));
                $email->addAttachments(array('Allgemeine-Geschaeftsbedingungen.pdf' => array('data' => $this->generateGeneralTermsAndConditions($order), 'mimetype' => 'application/pdf')));

                $email->send();
            }
            //END send confirmation email to customer

            // due to redirect, beforeRender() is not called
            $this->resetOriginalLoggedCustomer();

            $this->redirect(Configure::read('slugHelper')->getCartFinished($orderId));
        }

        $this->action = 'detail';
        $this->render('detail');
    }

    public function sendShopOrderNotificationToManufacturers($cartProducts, $order)
    {

        if (!$this->Session->check('Auth.shopOrderCustomer')) {
            return false;
        }

        $manufacturers = array();
        foreach ($cartProducts as $cartProduct) {
            $manufacturers[$cartProduct['manufacturerId']][] = $cartProduct;
        }

        $this->loadModel('Manufacturer');
        $this->Manufacturer->recursive = 1;

        foreach ($manufacturers as $manufacturerId => $cartProducts) {
            $manufacturer = $this->Manufacturer->find('first', array(
                'conditions' => array(
                    'Manufacturer.id_manufacturer' => $manufacturerId
                )
            ));

            $depositSum = 0;
            $productSum = 0;
            foreach ($cartProducts as $cartProduct) {
                $depositSum += $cartProduct['deposit'];
                $productSum += $cartProduct['price'];
            }

            $sendShopOrderNotification = $this->Manufacturer->getOptionSendShopOrderNotification($manufacturer['Manufacturer']['send_shop_order_notification']);
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer['Manufacturer']['bulk_orders_allowed']);
            if ($sendShopOrderNotification && !$bulkOrdersAllowed) {
                $email = new AppEmail();
                $email->template('shop_order_notification')
                ->emailFormat('html')
                ->to($manufacturer['Address']['email'])
                ->subject('Benachrichtigung über Sofort-Bestellung Nr. ' . $order['Order']['id_order'])
                ->viewVars(array(
                    'appAuth' => $this->AppAuth,
                    'order' => $order,
                    'cart' => array('CartProducts' => $cartProducts),
                    'originalLoggedCustomer' => $this->Session->read('Auth.originalLoggedCustomer'),
                    'manufacturer' => $manufacturer,
                    'depositSum' => $depositSum,
                    'productSum' => $productSum,
                    'productAndDepositSum' => $depositSum + $productSum,
                    'showManufacturerUnsubscribeLink' => true
                ));
                $email->send();
            }
        }
    }

    public function orderSuccessful($orderId)
    {
        $orderId = (int) $this->params['pass'][0];

        $this->loadModel('Order');
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'Order.id_order' => $orderId,
                'Order.id_customer' => $this->AppAuth->getUserId()
            )
        ));
        if (empty($order)) {
            throw new MissingActionException('order not found');
        }

        $this->set('order', $order);

        $this->loadModel('BlogPost');
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

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }

    private function doManufacturerCheck($productId)
    {
        if ($this->AppAuth->isManufacturer()) {
            $message = 'Herstellern steht diese Funktion leider nicht zur Verfügung.';
            $this->log($message);
            die(json_encode(array(
                'status' => 0,
                'msg' => $message,
                'productId' => $productId
            )));
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
            die(json_encode(array(
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            )));
        }

        $ccp = ClassRegistry::init('CartProduct');
        $ccp->remove($productId, $attributeId, $cart['Cart']['id_cart']);

        // update cart to update field date_upd
        $cc = ClassRegistry::init('Cart');
        $cc->id = $cart['Cart']['id_cart'];
        $cc->updateDateUpd();

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
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
            die(json_encode(array(
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            )));
        }

        // get product data from database
        $this->loadModel('Product');
        $this->Product->recursive = 3;
        $this->Product->Behaviors->load('Containable');
        $product = $this->Product->find('first', array(
            'conditions' => array(
                'Product.id_product' => $productId
            ),
            'contain' => array(
                'ProductLang', 'StockAvailable', 'ProductAttributes', 'ProductAttributes.StockAvailable', 'ProductAttributes.ProductAttributeCombination.Attribute'
            )
        ));

        $existingCartProduct = $this->AppAuth->Cart->getProduct($initialProductId);
        $combinedAmount = $amount;
        if ($existingCartProduct) {
            $combinedAmount = $existingCartProduct['amount'] + $amount;
        }
        // check if passed product exists
        if (empty($product)) {
            $message = 'Das Produkt mit der ID ' . $productId . ' ist nicht vorhanden.';
            $this->log($message);
            die(json_encode(array(
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            )));
        }

        // stock available check for product
        if ($attributeId == 0 && $product['StockAvailable']['quantity'] < $combinedAmount && $amount > 0) {
            $message = 'Die gewünschte Anzahl (' . $combinedAmount . ') des Produktes "' . $product['ProductLang']['name'] . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $product['StockAvailable']['quantity'];
            die(json_encode(array(
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            )));
        }

        // check if passed optional product/attribute relation exists
        if ($attributeId > 0) {
            $attributeIdFound = false;
            foreach ($product['ProductAttributes'] as $attribute) {
                if ($attribute['id_product_attribute'] == $attributeId) {
                    $attributeIdFound = true;
                    // stock available check for attribute
                    if ($attribute['StockAvailable']['quantity'] < $combinedAmount && $amount > 0) {
                        $message = 'Die gewünschte Anzahl (' . $combinedAmount . ') der Variante "' . $attribute['ProductAttributeCombination']['Attribute']['name'] . '" des Produktes "' . $product['ProductLang']['name'] . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $attribute['StockAvailable']['quantity'];
                        die(json_encode(array(
                            'status' => 0,
                            'msg' => $message,
                            'productId' => $initialProductId
                        )));
                    }
                    break;
                }
            }
            if (! $attributeIdFound) {
                $message = 'Die Variante existiert nicht: ' . $initialProductId;
                die(json_encode(array(
                    'status' => 0,
                    'msg' => $message,
                    'productId' => $initialProductId
                )));
            }
        }

        // update amount if cart product already exists
        $cart = $this->AppAuth->getCart();
        $this->AppAuth->setCart($cart);
        $ccp = ClassRegistry::init('CartProduct');
        $ccp->id = null;
        if ($existingCartProduct) {
            $ccp->id = $existingCartProduct['cartProductId'];
        }

        $cartProduct2save = array(
            'id_product' => $productId,
            'amount' => $combinedAmount,
            'id_product_attribute' => $attributeId,
            'id_cart' => $cart['Cart']['id_cart']
        );
        $ccp->save($cartProduct2save);

        // update cart to update field date_upd
        $cc = ClassRegistry::init('Cart');
        $cc->id = $cart['Cart']['id_cart'];
        $cc->updateDateUpd();

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }
}
