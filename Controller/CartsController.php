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

    public function isAuthorized($user)
    {
        return $this->AppAuth->loggedIn() && Configure::read('app.db_config_FCS_CART_ENABLED');
    }

    public function detail()
    {
        $this->set('title_for_layout', 'Dein Warenkorb');
    }

    public function finish()
    {
        
        $this->set('title_for_layout', 'Warenkorb abschließen');
        $cart = $this->AppAuth->getCakeCart();
        
        $this->loadModel('CakeCart');
        $this->loadModel('Product');
        
        // START check if no amount is 0
        $productWithAmount0Found = false;
        foreach ($this->AppAuth->Cart->getProducts() as $ccp) {
            $ids = $this->Product->getProductIdAndAttributeId($ccp['productId']);
            if ($ccp['amount'] == 0) {
                $this->log('amount of cart productId ' . $ids['productId'] . ' (attributeId : ' . $ids['attributeId'] . ') was 0 and therefore removed from cart');
                $ccp = ClassRegistry::init('CakeCartProduct');
                $ccp->remove($ids['productId'], $ids['attributeId'], $this->AppAuth->Cart->getCartId());
                $productWithAmount0Found = true;
            }
        }
        
        if ($productWithAmount0Found) {
            $cart = $this->AppAuth->getCakeCart();
            $this->AppAuth->setCakeCart($cart);
        }
        // END check if no amount is 0
        
        if (empty($cart) || empty($this->AppAuth->Cart->getProducts())) {
            $this->AppSession->setFlashError('Dein Warenkorb war leer.');
            $this->redirect(Configure::read('slugHelper')->getCartDetail());
        }
        
        $cartErrors = array();
        $orderDetails2save = array();
        
        foreach ($this->AppAuth->Cart->getProducts() as $ccp) {
            
            $ids = $this->Product->getProductIdAndAttributeId($ccp['productId']);
            
            $this->Product->recursive = 2;
            $product = $this->Product->find('first', array(
                'conditions' => array(
                    'Product.id_product' => $ids['productId']
                )
            ));
            
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
                            $this->loadModel('AttributeLang');
                            $attributeLang = $this->AttributeLang->find('first', array(
                                'conditions' => array(
                                    'AttributeLang.id_attribute' => $attribute['ProductAttributeCombination']['id_attribute']
                                )
                            ));
                            $message = 'Die gewünschte Anzahl (' . $ccp['amount'] . ') der Variante "' . $attributeLang['AttributeLang']['name'] . '" des Produktes "' . $product['ProductLang']['name'] . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $stockAvailableQuantity . '. Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um die Bestellung abzuschließen.';
                            $cartErrors[$ccp['productId']][] = $message;
                        }
                        break;
                    }
                }
                if (! $attributeIdFound) {
                    $message = 'Die Variante existiert nicht. Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um deine Bestellung abzuschließen.';
                    $this->log($message);
                    $cartErrors[$ccp['productId']][] = $message;
                }
            }
            
            if (! $product['Product']['active']) {
                $message = 'Das Produkt "' . $product['ProductLang']['name'] . '" ist leider nicht mehr aktiviert und somit nicht mehr bestellbar. Um deine Bestellung abzuschließen, lösche bitte das Produkt aus deinem Warenkorb.';
                $cartErrors[$ccp['productId']][] = $message;
            }
            
            if (! $product['Manufacturer']['active'] || $product['Manufacturer']['holiday']) {
                $message = 'Der Hersteller des Produkts "' . $product['ProductLang']['name'] . '" ist entweder im Urlaub oder nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar. Um deine Bestellung abzuschließen, lösche bitte das Produkt aus deinem Warenkorb.';
                $this->log($message);
                $cartErrors[$ccp['productId']][] = $message;
            }
            
            // build orderDetails2save
            $orderDetails2save[] = array(
                'product_id' => $ids['productId'],
                'product_attribute_id' => $ids['attributeId'],
                'product_name' => $this->CakeCart->getProductNameWithUnity($ccp['productName'], $ccp['unity']),
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
        $checkboxErrors = false;
        if ($this->request->data['Order']['general_terms_and_conditions_accepted'] != 1) {
            $this->Order->invalidate('general_terms_and_conditions_accepted', 'Bitte akzeptiere die AGB.');
            $checkboxErrors = true;
        }
        if ($this->request->data['Order']['cancellation_terms_accepted'] != 1) {
            $this->Order->invalidate('cancellation_terms_accepted', 'Bitte akzeptiere den Ausschluss des Rücktrittsrechts.');
            $checkboxErrors = true;
        }
        
        if (!empty($cartErrors) || $checkboxErrors) {
            $this->AppSession->setFlashError('Es sind Fehler aufgetreten.');
        } else {
            
            // START save order
            $this->Order->id = null;
            $order2save = array(
                'reference' => strtoupper(StringComponent::createRandomString(9)),
                'id_shop' => Configure::read('app.shopId'),
                'id_lang' => Configure::read('app.langId'),
                'id_customer' => $this->AppAuth->getUserId(),
                'id_cart' => 0,
                'id_cake_cart' => $this->AppAuth->Cart->getCartId(),
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
            $order = $this->Order->save($order2save);
            
            if (empty($order)) {
                $message = 'Bei der Erstellung der Bestellung ist ein Fehler aufgetreten.';
                $this->AppSession->setFlashError($message);
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
                $message = 'Bei der Erstellung der bestellten Artikel ist ein Fehler aufgetreten.';
                $this->AppSession->setFlashError($message);
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
            
            // START update stock available
            $i = 0;
            foreach ($stockAvailable2saveData as &$data) {
                $this->Product->StockAvailable->updateAll($stockAvailable2saveData[$i], $stockAvailable2saveConditions[$i]);
                $this->Product->StockAvailable->updateQuantityForMainProduct($stockAvailable2saveConditions[$i]['StockAvailable.id_product']);
                $i ++;
            }
            // END update stock available
            
            $this->AppAuth->Cart->markAsSaved();
            
            $this->AppSession->setFlashMessage('Deine Bestellung wurde erfolgreich abgeschlossen.');
            $this->loadModel('CakeActionLog');
            $this->CakeActionLog->customSave('customer_order_finished', $this->AppAuth->getUserId(), $orderId, 'orders', $this->AppAuth->getUsername() . ' hat eine neue Bestellung getätigt (' . Configure::read('htmlHelper')->formatAsEuro($this->AppAuth->Cart->getProductSum()) . ').');
            
            // send confirmation email to customer
            $email = new AppEmail();
            $email->template('customer_order_successful')
                ->emailFormat('html')
                ->to($this->AppAuth->getEmail())
                ->subject('Bestellbestätigung')
                ->viewVars(array(
                'cart' => $cart,
                'appAuth' => $this->AppAuth,
                'order' => $order
            ));
            
            if (Configure::read('app.db_config_FCS_ORDER_CONFIRMATION_MAIL_BCC') != '') {
                $email->bcc(Configure::read('app.db_config_FCS_ORDER_CONFIRMATION_MAIL_BCC'));
            }
            
            $email->send();
            
            // due to redirect, before render is not called
            $this->resetOriginalLoggedCustomer();
            
            $this->redirect(Configure::read('slugHelper')->getCartFinished($orderId));
        }
        
        $this->action = 'detail';
        $this->render('detail');
    }

    public function order_successful($orderId)
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
        $blogPosts = $this->BlogPost->findBlogPosts(null, $this->AppAuth);
        $this->set('blogPosts', $blogPosts);
        
        $this->set('title_for_layout', 'Deine Bestellung ist abgeschlossen');
        
        $this->resetOriginalLoggedCustomer();
        $this->destroyShopOrderCustomer();
    }

    public function beforeFilter()
    {
        if ($this->request->is('post')) {
            $message = '';
            if (! $this->AppAuth->loggedIn()) {
                $message = 'Du bist nicht angemeldet.';
            }
            if ($this->AppAuth->isManufacturer()) {
                $message = 'Herstellern steht diese Funktion leider nicht zur Verfügung.';
            }
            if ($message != '') {
                die(json_encode(array(
                    'status' => 0,
                    'msg' => $message
                )));
            }
        }
        
        parent::beforeFilter();
    }

    public function ajaxDeleteShopOrderCustomer()
    {
        $this->autoRender = false;
        
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
        $this->autoRender = false;
        
        $initialProductId = $this->params['data']['productId'];
        
        $this->doManufacturerCheck($initialProductId);
        
        $attributeId = 0;
        $productId = $initialProductId;
        $explodedProductId = explode('-', $initialProductId);
        if (count($explodedProductId) == 2) {
            $productId = $explodedProductId[0];
            $attributeId = (int) $explodedProductId[1];
        }
        
        $cakeCart = $this->AppAuth->getCakeCart();
        $this->AppAuth->setCakeCart($cakeCart);
        
        $existingCartProduct = $this->AppAuth->Cart->getProduct($initialProductId);
        if (empty($existingCartProduct)) {
            $message = 'Produkt ' . $productId . ' war nicht in Warenkorb vorhanden.';
            $this->log($message);
            die(json_encode(array(
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            )));
        }
        
        $ccp = ClassRegistry::init('CakeCartProduct');
        $ccp->remove($productId, $attributeId, $cakeCart['CakeCart']['id_cart']);
        
        // update cart to update field date_upd
        $cc = ClassRegistry::init('CakeCart');
        $cc->id = $cakeCart['CakeCart']['id_cart'];
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
        $this->autoRender = false;
        
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
            $this->log($message);
            die(json_encode(array(
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            )));
        }
        
        // get product data from database
        $this->loadModel('Product');
        $this->Product->recursive = 2;
        $product = $this->Product->find('first', array(
            'conditions' => array(
                'Product.id_product' => $productId
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
                        $this->loadModel('AttributeLang');
                        $attributeLang = $this->AttributeLang->find('first', array(
                            'conditions' => array(
                                'AttributeLang.id_attribute' => $attribute['ProductAttributeCombination']['id_attribute']
                            )
                        ));
                        $message = 'Die gewünschte Anzahl (' . $combinedAmount . ') der Variante "' . $attributeLang['AttributeLang']['name'] . '" des Produktes "' . $product['ProductLang']['name'] . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $attribute['StockAvailable']['quantity'];
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
                $this->log($message);
                die(json_encode(array(
                    'status' => 0,
                    'msg' => $message,
                    'productId' => $initialProductId
                )));
            }
        }
        
        // update amount if cart product already exists
        $cakeCart = $this->AppAuth->getCakeCart();
        $this->AppAuth->setCakeCart($cakeCart);
        $ccp = ClassRegistry::init('CakeCartProduct');
        $ccp->id = null;
        if ($existingCartProduct) {
            $ccp->id = $existingCartProduct['cakeCartProductId'];
        }
        
        $cartProduct2save = array(
            'id_product' => $productId,
            'amount' => $combinedAmount,
            'id_product_attribute' => $attributeId,
            'id_cart' => $cakeCart['CakeCart']['id_cart']
        );
        $ccp->save($cartProduct2save);
        
        // update cart to update field date_upd
        $cc = ClassRegistry::init('CakeCart');
        $cc->id = $cakeCart['CakeCart']['id_cart'];
        $cc->updateDateUpd();
        
        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();
        
        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }
}

?>