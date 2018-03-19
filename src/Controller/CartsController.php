<?php

namespace App\Controller;

use App\Mailer\AppEmail;
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
            if (empty($this->AppAuth->user())) {
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
        if (!$this->request->is('post')) {
            $this->Order = TableRegistry::get('Orders');
            $this->set('order', $this->Order->newEntity());
        }
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
            $manufacturers[$product->manufacturer->id_manufacturer][] = $product;
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
    private function generateGeneralTermsAndConditions()
    {
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
    private function generateOrderConfirmation($order)
    {

        $this->OrderDetail = TableRegistry::get('OrderDetails');

        $this->set('order', $order);
        $manufacturers = [];
        $i = 0;

        $orderDetails = $this->Order->OrderDetails->find('all', [
            'conditions' => [
                'OrderDetails.id_order' => $order->id_order
            ],
            'contain' => [
                'OrderDetailTaxes',
                'Products',
                'Products.Manufacturers.AddressManufacturers'
            ]
        ]);

        foreach ($orderDetails as $orderDetail) {
            $manufacturers[$orderDetail->product->id_manufacturer] = [
                'OrderDetails' => $orderDetails,
                'Manufacturer' => $orderDetail->product->manufacturer
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
        $stockAvailable2saveData = [];

        foreach ($this->AppAuth->Cart->getProducts() as $ccp) {
            $ids = $this->Product->getProductIdAndAttributeId($ccp['productId']);

            $product = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $ids['productId']
                ],
                'fields' => ['is_holiday_active' => '!'.$this->Product->getManufacturerHolidayConditions()],
                'contain' => [
                    'ProductLangs',
                    'Manufacturers',
                    'Manufacturers.AddressManufacturers',
                    'StockAvailables',
                    'ProductAttributes.StockAvailables',
                    'ProductAttributes.ProductAttributeCombinations',
                ]
            ])
            ->select($this->Product)
            ->select($this->Product->ProductLangs)
            ->select($this->Product->StockAvailables)
            ->select($this->Product->Manufacturers)
            ->select($this->Product->Manufacturers->AddressManufacturers)
            ->select($this->Product->ProductAttributes->StockAvailables)
            ->first();
            $products[] = $product;
            $stockAvailableQuantity = $product->stock_available->quantity;

            // stock available check for product (without attributeId)
            if ($ids['attributeId'] == 0 && $stockAvailableQuantity < $ccp['amount']) {
                $message = 'Die gewünschte Anzahl (' . $ccp['amount'] . ') des Produktes "' . $product->product_lang->name . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $stockAvailableQuantity . ' Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um die Bestellung abzuschließen.';
                $cartErrors[$ccp['productId']][] = $message;
            }

            if ($ids['attributeId'] > 0) {
                $attributeIdFound = false;
                foreach ($product->product_attributes as $attribute) {
                    if ($attribute->id_product_attribute == $ids['attributeId']) {
                        $attributeIdFound = true;
                        $stockAvailableQuantity = $attribute->stock_available->quantity;
                        // stock available check for attribute
                        if ($stockAvailableQuantity < $ccp['amount']) {
                            $this->Attribute = TableRegistry::get('Attributes');
                            $attribute = $this->Attribute->find('all', [
                                'conditions' => [
                                    'Attributes.id_attribute' => $attribute->product_attribute_combination->id_attribute
                                ]
                            ])->first();
                            $message = 'Die gewünschte Anzahl (' . $ccp['amount'] . ') der Variante "' . $attribute->name . '" des Produktes "' . $product->product_lang->name . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $stockAvailableQuantity . '. Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um die Bestellung abzuschließen.';
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

            if (! $product->active) {
                $message = 'Das Produkt "' . $product->product_lang->name . '" ist leider nicht mehr aktiviert und somit nicht mehr bestellbar. Um deine Bestellung abzuschließen, lösche bitte das Produkt aus deinem Warenkorb.';
                $cartErrors[$ccp['productId']][] = $message;
            }

            if (! $product->manufacturer->active || $product->is_holiday_active) {
                $message = 'Der Hersteller des Produktes "' . $product->product_lang->name . '" hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar. Um deine Bestellung abzuschließen, lösche bitte das Produkt aus deinem Warenkorb.';
                $cartErrors[$ccp['productId']][] = $message;
            }

            $orderDetails2save[] = [
                'product_id' => $ids['productId'],
                'product_attribute_id' => $ids['attributeId'],
                'product_name' => $this->Cart->getProductNameWithUnity($ccp['productName'], $ccp['unity']),
                'product_quantity' => $ccp['amount'],
                'product_price' => $ccp['priceExcl'],
                'total_price_tax_excl' => $ccp['priceExcl'],
                'total_price_tax_incl' => $ccp['price'],
                'id_tax' => $product->id_tax,
                'deposit' => $ccp['deposit'],
                'product' => $product, // will be removed!
                'ccp' => $ccp // will be removed!
            ];
            
            $newQuantity = $stockAvailableQuantity - $ccp['amount'];
            if ($newQuantity < 0) {
                $message = 'attention, this should never happen! stock available would have been negative: productId: ' . $ids['productId'] . ', attributeId: ' . $ids['attributeId'] . '; changed it manually to 0 to avoid negative stock available value.';
                $newQuantity = 0; // never ever allow negative stock available
            }
            $stockAvailable2saveData[] = [
                'quantity' => $newQuantity
            ];
            $stockAvailable2saveConditions[] = [
                'id_product' => $ids['productId'],
                'id_product_attribute' => $ids['attributeId']
            ];
        }

        $this->set('cartErrors', $cartErrors);

        $formErrors = false;
        $this->Order = TableRegistry::get('Orders');
        
        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $this->AppAuth->user('timebased_currency_enabled')) {
            $validator = $this->Order->TimebasedCurrencyOrders->validator('default');
            $validator = $this->Order->TimebasedCurrencyOrders->getNumberRangeValidator($validator, 'time_sum', 0, $this->AppAuth->Cart->getTimebasedCurrencyPartTime());
        }
        $order = $this->Order->newEntity(
            $this->request->getData(),
            [
                'validate' => 'cart'
            ]
        );
        if (!empty($order->getErrors())) {
            $formErrors = true;
        }
        $this->set('order', $order); // to show error messages in form (from validation)
        $this->set('formErrors', $formErrors);

        if (!empty($cartErrors) || !empty($formErrors)) {
            $this->Flash->error('Es sind Fehler aufgetreten.');
        } else {
            // START save order
            $order2save = [
                 'Orders' => [
                    'id_customer' => $this->AppAuth->getUserId(),
                    'id_cart' => $this->AppAuth->Cart->getCartId(),
                    'current_state' => ORDER_STATE_OPEN,
                    'total_paid' => $this->AppAuth->Cart->getProductSum(),
                    'total_paid_tax_incl' => $this->AppAuth->Cart->getProductSum(),
                    'total_paid_tax_excl' => $this->AppAuth->Cart->getProductSumExcl(),
                    'total_deposit' => $this->AppAuth->Cart->getDepositSum()
                 ]
            ];
            if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $this->AppAuth->user('timebased_currency_enabled')) {
                $order2save['timebased_currency_order']['money_sum'] = $this->AppAuth->Cart->getTimebasedCurrencyPartMoney();
                $order2save['timebased_currency_order']['time_sum'] = $this->AppAuth->Cart->getTimebasedCurrencyPartTime();
            }
            $patchedOrder = $this->Order->patchEntity($order, $order2save);
            $order = $this->Order->save($patchedOrder);
            if (!$order) {
                $message = 'Bei der Erstellung der Bestellung ist ein Fehler aufgetreten.';
                $this->Flash->error($message);
                $this->log($message);
                $this->redirect(Configure::read('app.slugHelper')->getCartFinish());
            }
            $orderId = $order->id_order;

            // get order again to have field date_add available as a datetime-object
            $order = $this->Order->find('all', [
                'conditions' => [
                    'Orders.id_order' => $orderId
                ]
            ])->first();
            // END save order

            // START update order_id in orderDetails2save
            foreach ($orderDetails2save as &$orderDetail) {
                $orderDetail['id_order'] = $orderId;
                
                // recalculate prices
                if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $this->AppAuth->user('timebased_currency_enabled')) {
                    if (isset($orderDetail['ccp']['timebasedCurrencyPartMoney'])) {
                        $orderDetail['timebased_currency_order_detail']['money'] = $orderDetail['ccp']['timebasedCurrencyPartMoney'];
                    }
                    if (isset($orderDetail['ccp']['timebasedCurrencyPartTime'])) {
                        $orderDetail['timebased_currency_order_detail']['time'] = $orderDetail['ccp']['timebasedCurrencyPartTime'];
                    }
                    $timebasedCurrencyPartMoneyExcl = $this->Product->Manufacturers->getTimebasedCurrencyPartMoney($ccp['priceExcl'], $orderDetail['product']->manufacturer->timebased_currency_max_percentage);
                    $orderDetail['product_price'] = $orderDetail['ccp']['priceExcl'] - $timebasedCurrencyPartMoneyExcl;
                    $orderDetail['total_price_tax_excl'] = $orderDetail['ccp']['priceExcl'] - $timebasedCurrencyPartMoneyExcl;
                    $orderDetail['total_price_tax_incl'] = $orderDetail['ccp']['price'] - $this->Product->Manufacturers->getTimebasedCurrencyPartMoney($orderDetail['ccp']['price'], $orderDetail['product']->manufacturer->timebased_currency_max_percentage);
                }
                
                unset($orderDetail['product']);
                unset($orderDetail['ccp']);
                
            }

            $this->Order->OrderDetails->saveMany(
                $this->Order->OrderDetails->newEntities($orderDetails2save)
            );
            // END update order_id in orderDetails2save

            // START save order_detail_tax
            $orderDetails = $this->Order->OrderDetails->find('all', [
                'conditions' => [
                    'OrderDetails.id_order' => $orderId
                ],
                'contain' => [
                    'OrderDetailTaxes'
                ]
            ]);

            if (empty($orderDetails)) {
                $message = 'Beim Speichern der bestellten Produkte ist ein Fehler aufgetreten.';
                $this->Flash->error($message);
                $this->log($message);
                $this->redirect(Configure::read('app.slugHelper')->getCartFinish());
            }

            $orderDetailTax2save = [];
            $this->OrderDetailTax = TableRegistry::get('OrderDetailTaxes');
            foreach ($orderDetails as $orderDetail) {
                // should not be necessary but a user somehow managed to set product_quantity as 0
                $quantity = $orderDetail->product_quantity;
                if ($quantity == 0) {
                    $this->log('product_quantity was 0, would have resulted in division by zero error');
                    continue;
                }

                $productId = $orderDetail->product_id;
                $price = $orderDetail->total_price_tax_incl;

                $unitPriceExcl = $this->Product->getNetPrice($productId, $price / $quantity);
                $unitTaxAmount = $this->Product->getUnitTax($price, $unitPriceExcl, $quantity);
                $totalTaxAmount = $unitTaxAmount * $quantity;

                $orderDetailTax2save[] = [
                    'id_order_detail' => $orderDetail->id_order_detail,
                    'id_tax' => 0, // do not use the field id_tax in order_details_tax but id_tax in order_details!
                    'unit_amount' => $unitTaxAmount,
                    'total_amount' => $totalTaxAmount
                ];
            }

            $this->OrderDetailTax->saveMany(
                $this->OrderDetailTax->newEntities($orderDetailTax2save)
            );
            // END save order_detail_tax

            $this->sendShopOrderNotificationToManufacturers($cart['CartProducts'], $order);

            // START update stock available
            $i = 0;
            foreach ($stockAvailable2saveData as &$data) {
                $this->Product->StockAvailables->updateAll(
                    $stockAvailable2saveData[$i],
                    $stockAvailable2saveConditions[$i]
                );
                $this->Product->StockAvailables->updateQuantityForMainProduct($stockAvailable2saveConditions[$i]['id_product']);
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
                $email->setTemplate('customer_order_successful')
                    ->setTo($this->AppAuth->getEmail())
                    ->setSubject('Bestellbestätigung')
                    ->setViewVars([
                    'cart' => $cart,
                    'appAuth' => $this->AppAuth,
                    'originalLoggedCustomer' => $this->request->getSession()->check('Auth.originalLoggedCustomer') ? $this->request->getSession()->read('Auth.originalLoggedCustomer') : null,
                    'order' => $order,
                    'depositSum' => $this->AppAuth->Cart->getDepositSum(),
                    'productSum' => $this->AppAuth->Cart->getProductSum(),
                    'productAndDepositSum' => $this->AppAuth->Cart->getProductAndDepositSum()
                    ]);

                $email->addAttachments(['Informationen-ueber-Ruecktrittsrecht-und-Ruecktrittsformular.pdf' => ['data' => $this->generateCancellationInformationAndForm($order, $products), 'mimetype' => 'application/pdf']]);
                $email->addAttachments(['Bestelluebersicht.pdf' => ['data' => $this->generateOrderConfirmation($order), 'mimetype' => 'application/pdf']]);
                $email->addAttachments(['Allgemeine-Geschaeftsbedingungen.pdf' => ['data' => $this->generateGeneralTermsAndConditions(), 'mimetype' => 'application/pdf']]);

                $email->send();
            }
            //END send confirmation email to customer

            // due to redirect, beforeRender() is not called
            $this->resetOriginalLoggedCustomer();

            $this->redirect(Configure::read('app.slugHelper')->getCartFinished($orderId));
        }

        $this->setAction('detail');
    }

    public function sendShopOrderNotificationToManufacturers($cartProducts, $order)
    {

        if (!$this->request->getSession()->check('Auth.shopOrderCustomer')) {
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
                ],
                'contain' => [
                    'AddressManufacturers'
                ]
            ])->first();

            $depositSum = 0;
            $productSum = 0;
            foreach ($cartProducts as $cartProduct) {
                $depositSum += $cartProduct['deposit'];
                $productSum += $cartProduct['price'];
            }

            $sendShopOrderNotification = $this->Manufacturer->getOptionSendShopOrderNotification($manufacturer->send_shop_order_notification);
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer->bulk_orders_allowed);
            if ($sendShopOrderNotification && !$bulkOrdersAllowed) {
                $email = new AppEmail();
                $email->setTemplate('shop_order_notification')
                ->setTo($manufacturer->address_manufacturer->email)
                ->setSubject('Benachrichtigung über Sofort-Bestellung Nr. ' . $order->id_order)
                ->setViewVars([
                    'appAuth' => $this->AppAuth,
                    'order' => $order,
                    'cart' => ['CartProducts' => $cartProducts],
                    'originalLoggedCustomer' => $this->request->getSession()->read('Auth.originalLoggedCustomer'),
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
            ],
            'contain' => [
                'Customers'
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

        $initialProductId = $this->request->getData('productId');

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

        $initialProductId = $this->request->getData('productId');

        $this->doManufacturerCheck($initialProductId);

        $attributeId = 0;
        $productId = $initialProductId;
        $explodedProductId = explode('-', $initialProductId);
        if (count($explodedProductId) == 2) {
            $productId = $explodedProductId[0];
            $attributeId = (int) $explodedProductId[1];
        }

        $amount = (int) $this->request->getData('amount');
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
                'ProductLangs',
                'StockAvailables',
                'ProductAttributes',
                'ProductAttributes.StockAvailables',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
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
        if ($attributeId == 0 && $product->stock_available->quantity < $combinedAmount && $amount > 0) {
            $message = 'Die gewünschte Anzahl (' . $combinedAmount . ') des Produktes "' . $product->product_lang->name . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $product->stock_available->quantity;
            die(json_encode([
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ]));
        }

        // check if passed optional product/attribute relation exists
        if ($attributeId > 0) {
            $attributeIdFound = false;
            foreach ($product->product_attributes as $attribute) {
                if ($attribute->id_product_attribute == $attributeId) {
                    $attributeIdFound = true;
                    // stock available check for attribute
                    if ($attribute->stock_available->quantity < $combinedAmount && $amount > 0) {
                        $message = 'Die gewünschte Anzahl (' . $combinedAmount . ') der Variante "' . $attribute->product_attribute_combination->attribute->name . '" des Produktes "' . $product->product_lang->name . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $attribute->stock_available->quantity;
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

        $cartProduct2save = [
            'id_product' => $productId,
            'amount' => $combinedAmount,
            'id_product_attribute' => $attributeId,
            'id_cart' => $cart['Cart']['id_cart']
        ];
        if ($existingCartProduct) {
            $oldEntity = $ccp->get($existingCartProduct['cartProductId']);
            $entity = $ccp->patchEntity($oldEntity, $cartProduct2save);
        } else {
            $entity = $ccp->newEntity($cartProduct2save);
        }
        $ccp->save($entity);

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }
}
