<?php

namespace App\Controller;

use App\Mailer\AppEmail;
use App\Model\Table\OrdersTable;
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

        if ($this->getRequest()->is('ajax')) {
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
    
    public function ajaxGetTimebasedCurrencyHoursDropdown($maxSeconds)
    {
        $this->RequestHandler->renderAs($this, 'json');
        $maxSeconds = (int) $maxSeconds;
        $options = Configure::read('app.timebasedCurrencyHelper')->getTimebasedCurrencyHoursDropdown($maxSeconds, Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE'));
        $this->set('data', [
            'options' => $options,
            'status' => !empty($options)
        ]);
        $this->set('_serialize', 'data');
    }

    public function detail()
    {
        $this->set('title_for_layout', 'Dein Warenkorb');
        if (!$this->getRequest()->is('post')) {
            $this->Order = TableRegistry::getTableLocator()->get('Orders');
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

        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');

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
    
    /**
     * does not send email to inactive users (superadmins can place shop orders for inactive users!)
     * @param array $cart
     * @param array $orders
     * @param array $products
     */
    private function sendConfirmationEmailToCustomer($cart, $order, $products)
    {
        if ($this->AppAuth->user('active')) {
            $email = new AppEmail();
            $email->setTemplate('customer_order_successful')
            ->setTo($this->AppAuth->getEmail())
            ->setSubject('Bestellbestätigung')
            ->setViewVars([
                'cart' => $cart,
                'appAuth' => $this->AppAuth,
                'originalLoggedCustomer' => $this->getRequest()->getSession()->check('Auth.originalLoggedCustomer') ? $this->getRequest()->getSession()->read('Auth.originalLoggedCustomer') : null,
                'order' => $order
            ]);
            
            $email->addAttachments(['Informationen-ueber-Ruecktrittsrecht-und-Ruecktrittsformular.pdf' => ['data' => $this->generateCancellationInformationAndForm($order, $products), 'mimetype' => 'application/pdf']]);
            $email->addAttachments(['Bestelluebersicht.pdf' => ['data' => $this->generateOrderConfirmation($order), 'mimetype' => 'application/pdf']]);
            $email->addAttachments(['Allgemeine-Geschaeftsbedingungen.pdf' => ['data' => $this->generateGeneralTermsAndConditions(), 'mimetype' => 'application/pdf']]);
            
            $email->send();
        }
    }
    
    private function saveStockAvailable($stockAvailable2saveData, $stockAvailable2saveConditions)
    {
        $i = 0;
        foreach ($stockAvailable2saveData as &$data) {
            $this->Product->StockAvailables->updateAll(
                $stockAvailable2saveData[$i],
                $stockAvailable2saveConditions[$i]
            );
            $this->Product->StockAvailables->updateQuantityForMainProduct($stockAvailable2saveConditions[$i]['id_product']);
            $i++;
        }
    }
    
    private function saveOrderDetails($orderDetails2save, $order)
    {
        foreach ($orderDetails2save as &$orderDetail) {
            
            $orderDetail['id_order'] = $order->id_order;
            
            // timebased_currency: ORDER_DETAILS
            if ($this->AppAuth->Cart->isTimebasedCurrencyUsed()) {
                
                foreach($this->AppAuth->Cart->getProducts() as $cartProduct) {
                    if ($cartProduct['cartProductId'] == $orderDetail['cartProductId']) {
                        
                        if (isset($cartProduct['isTimebasedCurrencyUsed'])) {
                        
                            $orderDetail['timebased_currency_order_detail']['money_excl'] = $cartProduct['timebasedCurrencyMoneyExcl'];
                            $orderDetail['timebased_currency_order_detail']['money_incl'] = $cartProduct['timebasedCurrencyMoneyIncl'];
                            $orderDetail['timebased_currency_order_detail']['seconds'] = $cartProduct['timebasedCurrencySeconds'];
                            $orderDetail['timebased_currency_order_detail']['max_percentage'] = $orderDetail['product']->manufacturer->timebased_currency_max_percentage;
                            $orderDetail['timebased_currency_order_detail']['exchange_rate'] = Configure::read('app.numberHelper')->replaceCommaWithDot(Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE'));
                            
                            // override prices from timebased_currency adapted cart
                            $orderDetail['total_price_tax_excl'] = $cartProduct['priceExcl'];
                            $orderDetail['total_price_tax_incl'] = $cartProduct['price'];
                        }
                        
                        continue;
                    }
                }
                
            }
        }
        
        $this->Order->OrderDetails->saveMany(
            $this->Order->OrderDetails->newEntities($orderDetails2save)
        );
    }
    
    /**
     * @param array $order
     * @return OrdersTable $order
     */
    private function saveOrder($orderEntity)
    {
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
        
        // timebased_currency: ORDERS
        if ($this->AppAuth->Cart->isTimebasedCurrencyUsed()) {
            $order2save['timebased_currency_order']['money_excl_sum'] = $this->AppAuth->Cart->getTimebasedCurrencyMoneyExclSum();
            $order2save['timebased_currency_order']['money_incl_sum'] = $this->AppAuth->Cart->getTimebasedCurrencyMoneyInclSum();
            $order2save['timebased_currency_order']['seconds_sum'] = $this->AppAuth->Cart->getTimebasedCurrencySecondsSum();
        }
        
        // avoid saving empty record for timebased_currency_order
        if (!$this->AppAuth->Cart->isTimebasedCurrencyUsed()) {
            unset($orderEntity->timebased_currency_order);
        }
        
        $patchedOrder = $this->Order->patchEntity($orderEntity, $order2save);
        $order = $this->Order->save($patchedOrder);
        
        if (!$order) {
            $message = 'Bei der Erstellung der Bestellung ist ein Fehler aufgetreten.';
            $this->Flash->error($message);
            $this->log($message);
            $this->redirect(Configure::read('app.slugHelper')->getCartFinish());
        }
        
        // get order again to have field date_add available as a datetime-object
        $order = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $order->id_order
            ]
        ])->first();
        
        return $order;
    }
    
    private function saveOrderDetailTax($orderId)
    {
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
        $this->OrderDetailTax = TableRegistry::getTableLocator()->get('OrderDetailTaxes');
        foreach ($orderDetails as $orderDetail) {
            // should not be necessary but a user somehow managed to set product_amount as 0
            $amount = $orderDetail->product_amount;
            if ($amount == 0) {
                $this->log('product_amount was 0, would have resulted in division by zero error');
                continue;
            }
            
            $productId = $orderDetail->product_id;
            $price = $orderDetail->total_price_tax_incl;
            
            $unitPriceExcl = $this->Product->getNetPrice($productId, $price / $amount);
            $unitTaxAmount = $this->Product->getUnitTax($price, $unitPriceExcl, $amount);
            $totalTaxAmount = $unitTaxAmount * $amount;
            
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
        
    }
    
    public function finish()
    {

        if (!$this->getRequest()->is('post')) {
            $this->redirect('/');
            return;
        }
        
        $this->set('title_for_layout', 'Warenkorb abschließen');
        $cart = $this->AppAuth->getCart();

        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $this->Product = TableRegistry::getTableLocator()->get('Products');

        // START check if no amount is 0
        $productWithAmount0Found = false;
        foreach ($this->AppAuth->Cart->getProducts() as $cartProduct) {
            $ids = $this->Product->getProductIdAndAttributeId($cartProduct['productId']);
            if ($cartProduct['amount'] == 0) {
                $this->log('amount of cart productId ' . $ids['productId'] . ' (attributeId : ' . $ids['attributeId'] . ') was 0 and therefore removed from cart');
                $cartProductTable = TableRegistry::getTableLocator()->get('CartProducts');
                $cartProductTable->remove($ids['productId'], $ids['attributeId'], $this->AppAuth->Cart->getCartId());
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
        $stockAvailable2saveConditions = [];

        foreach ($this->AppAuth->Cart->getProducts() as $cartProduct) {
            $ids = $this->Product->getProductIdAndAttributeId($cartProduct['productId']);

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
            if ($ids['attributeId'] == 0 && $stockAvailableQuantity < $cartProduct['amount']) {
                $message = 'Die gewünschte Anzahl <b>' . $cartProduct['amount'] . '</b> des Produktes <b>' . $product->product_lang->name . '</b> ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $stockAvailableQuantity . ' Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um die Bestellung abzuschließen.';
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            if ($ids['attributeId'] > 0) {
                $attributeIdFound = false;
                foreach ($product->product_attributes as $attribute) {
                    if ($attribute->id_product_attribute == $ids['attributeId']) {
                        $attributeIdFound = true;
                        $stockAvailableQuantity = $attribute->stock_available->quantity;
                        // stock available check for attribute
                        if ($stockAvailableQuantity < $cartProduct['amount']) {
                            $this->Attribute = TableRegistry::getTableLocator()->get('Attributes');
                            $attribute = $this->Attribute->find('all', [
                                'conditions' => [
                                    'Attributes.id_attribute' => $attribute->product_attribute_combination->id_attribute
                                ]
                            ])->first();
                            $message = 'Die gewünschte Anzahl <b>' . $cartProduct['amount'] . '</b> der Variante <b>' . $attribute->name . '</b> des Produktes <b>' . $product->product_lang->name . '<b> ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $stockAvailableQuantity . '. Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um die Bestellung abzuschließen.';
                            $cartErrors[$cartProduct['productId']][] = $message;
                        }
                        break;
                    }
                }
                if (! $attributeIdFound) {
                    $message = 'Die Variante existiert nicht. Bitte ändere die Anzahl oder lösche das Produkt aus deinem Warenkorb um deine Bestellung abzuschließen.';
                    $cartErrors[$cartProduct['productId']][] = $message;
                }
            }

            if (! $product->active) {
                $message = 'Das Produkt <b>' . $product->product_lang->name . '</b> ist leider nicht mehr aktiviert und somit nicht mehr bestellbar. Um deine Bestellung abzuschließen, lösche bitte das Produkt aus deinem Warenkorb.';
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            if (! $product->manufacturer->active || $product->is_holiday_active) {
                $message = 'Der Hersteller des Produktes <b>' . $product->product_lang->name . '</b> hat entweder Lieferpause oder er ist nicht mehr aktiviert und das Produkt ist somit nicht mehr bestellbar. Um deine Bestellung abzuschließen, lösche bitte das Produkt aus deinem Warenkorb.';
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            $orderDetails2save[] = [
                'product_id' => $ids['productId'],
                'product_attribute_id' => $ids['attributeId'],
                'product_name' => $this->Cart->getProductNameWithUnity($cartProduct['productName'], $cartProduct['unity']),
                'product_amount' => $cartProduct['amount'],
                'total_price_tax_excl' => $cartProduct['priceExcl'],
                'total_price_tax_incl' => $cartProduct['price'],
                'id_tax' => $product->id_tax,
                'deposit' => $cartProduct['deposit'],
                'product' => $product,
                'cartProductId' => $cartProduct['cartProductId'],
                'unit_name' => $cartProduct['unitName'],
                'price_incl_per_unit' => $cartProduct['priceInclPerUnit'],
                'quantity_in_units' => $cartProduct['quantityInUnits'],
            ];
            
            $newQuantity = $stockAvailableQuantity - $cartProduct['amount'];
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
        $this->Order = TableRegistry::getTableLocator()->get('Orders');
        
        if ($this->AppAuth->isTimebasedCurrencyEnabledForCustomer()) {
            $validator = $this->Order->TimebasedCurrencyOrders->getValidator('default');
            $maxValue = $this->AppAuth->Cart->getTimebasedCurrencySecondsSumRoundedUp();
            $validator = $this->Order->TimebasedCurrencyOrders->getNumberRangeValidator($validator, 'seconds_sum_tmp', 0, $maxValue);
        }
        $order = $this->Order->newEntity(
            $this->getRequest()->getData(),
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
            
            $selectedTimebasedCurrencySeconds = 0;
            $selectedTimeAdaptionFactor = 0;
            if (!empty($this->getRequest()->getData('timebased_currency_order.seconds_sum_tmp')) && $this->getRequest()->getData('timebased_currency_order.seconds_sum_tmp') > 0) {
                $selectedTimebasedCurrencySeconds = $this->getRequest()->getData('timebased_currency_order.seconds_sum_tmp');
                $selectedTimeAdaptionFactor = $selectedTimebasedCurrencySeconds / $this->AppAuth->Cart->getTimebasedCurrencySecondsSum();
            }
            
            if ($selectedTimeAdaptionFactor > 0) {
                $cart = $this->Cart->adaptCartWithTimebasedCurrency($cart, $selectedTimeAdaptionFactor);
                $this->AppAuth->setCart($cart);
            }
            
            $order = $this->saveOrder($order);
            
            $this->saveOrderDetails($orderDetails2save, $order);
            $this->saveOrderDetailTax($order->id_order);
            $this->saveStockAvailable($stockAvailable2saveData, $stockAvailable2saveConditions);
            
            $this->sendShopOrderNotificationToManufacturers($cart['CartProducts'], $order);
            
            $this->AppAuth->Cart->markAsSaved();

            $this->Flash->success('Deine Bestellung wurde erfolgreich abgeschlossen.');
            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('customer_order_finished', $this->AppAuth->getUserId(), $order->id_order, 'orders', $this->AppAuth->getUsername() . ' hat eine neue Bestellung getätigt (' . Configure::read('app.htmlHelper')->formatAsEuro($this->AppAuth->Cart->getProductSum()) . ').');

            $this->sendConfirmationEmailToCustomer($cart, $order, $products);

            // due to redirect, beforeRender() is not called
            $this->resetOriginalLoggedCustomer();

            $this->redirect(Configure::read('app.slugHelper')->getCartFinished($order->id_order));
        }

        $this->setAction('detail');
    }

    public function sendShopOrderNotificationToManufacturers($cartProducts, $order)
    {

        if (!$this->getRequest()->getSession()->check('Auth.shopOrderCustomer')) {
            return false;
        }

        $manufacturers = [];
        foreach ($cartProducts as $cartProduct) {
            $manufacturers[$cartProduct['manufacturerId']][] = $cartProduct;
        }

        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
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
                    'originalLoggedCustomer' => $this->getRequest()->getSession()->read('Auth.originalLoggedCustomer'),
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
        $orderId = (int) $this->getRequest()->getParam('pass')[0];

        $this->Order = TableRegistry::getTableLocator()->get('Orders');
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

        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
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

        $initialProductId = $this->getRequest()->getData('productId');

        $this->doManufacturerCheck($initialProductId);

        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $ids = $this->Product->getProductIdAndAttributeId($initialProductId);

        $cart = $this->AppAuth->getCart();
        $this->AppAuth->setCart($cart);

        $existingCartProduct = $this->AppAuth->Cart->getProduct($initialProductId);
        if (empty($existingCartProduct)) {
            $message = 'Produkt ' . $ids['productId'] . ' war nicht in Warenkorb vorhanden.';
            die(json_encode([
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ]));
        }

        $cartProductTable = TableRegistry::getTableLocator()->get('CartProducts');
        $cartProductTable->remove($ids['productId'], $ids['attributeId'], $cart['Cart']['id_cart']);

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    public function emptyCart()
    {
        $this->doEmptyCart();
        $message = 'Dein Warenkorb wurde geleert, du kannst jetzt neue Produkte hinzufügen.';
        $this->Flash->success($message);
        $this->redirect($this->referer());
    }
    
    private function doEmptyCart()
    {
        $this->CartProduct = TableRegistry::getTableLocator()->get('CartProducts');
        $this->CartProduct->removeAll($this->AppAuth->Cart->getCartId(), $this->AppAuth->getUserId());
        $this->AppAuth->setCart($this->AppAuth->getCart());
    }
    
    public function addOrderToCart($deliveryDate)
    {
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
            $newCartProductsData = [];
            foreach($orderDetails as $orderDetail) {
                $result = $this->CartProduct->add($this->AppAuth, $orderDetail->product_id, $orderDetail->product_attribute_id, $orderDetail->product_amount);
                if (is_array($result)) {
                    $errorMessages[] = $result['msg'];
                    $loadedProducts--;
                }
            }
        }
        
        $message = 'Dein Warenkorb wurde geleert und deine vergangene Bestellung in den Warenkorb geladen.';
        $message .= '<br />Du kannst jetzt weitere Produkte hinzufügen.';

        if (!empty($errorMessages)) {
            $message .= '<div class="error">';
                $removedProducts = count($orderDetails) - $loadedProducts;
                $message .= '<b>';
                if ($removedProducts == 1) {
                    $message .= $removedProducts . ' Produkt ist';
                } else {
                    $message .= $removedProducts . ' Produkte sind';
                }
                $message .= ' nicht mehr verfügbar</b>';
                $message .= '<ul><li>' . join('</li><li>', $errorMessages) . '</li></ul>';
            $message .= '</div>';
        }
        
        $this->Flash->success($message);
        
        $this->log($message);
        
    }
    
    public function addLastOrderToCart()
    {
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->getLastOrderDetailsForDropdown($this->AppAuth->getUserId());
        if (empty($orderDetails)) {
            $message = 'Es sind keine Bestellungen vorhanden.';
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
        $this->RequestHandler->renderAs($this, 'ajax');

        $initialProductId = $this->getRequest()->getData('productId');

        $this->doManufacturerCheck($initialProductId);
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $ids = $this->Product->getProductIdAndAttributeId($initialProductId);
        $amount = (int) $this->getRequest()->getData('amount');
        
        $this->CartProduct = TableRegistry::getTableLocator()->get('CartProducts');
        $result = $this->CartProduct->add($this->AppAuth, $ids['productId'], $ids['attributeId'], $amount);

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();
        
        if (is_array($result)) {
            die(json_encode($result));
        }

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
        
    }
}
