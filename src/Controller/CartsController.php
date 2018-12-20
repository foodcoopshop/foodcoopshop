<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use App\Mailer\AppEmail;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\I18n\FrozenDate;
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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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
                $message = __('You_are_not_signed_in.');
            }
            if ($this->AppAuth->isManufacturer()) {
                $message = __('No_access_for_manufacturers.');
            }
            if ($message != '') {
                $this->log($message);
                die(json_encode([
                    'status' => 0,
                    'msg' => $message
                ]));
            }
        }

        $this->AppAuth->allow('generateRightOfWithdrawalInformationPdf');
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
        $this->set('title_for_layout', __('Your_cart'));
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
            $cart = $this->AppAuth->getCart();
            $this->set('cart', $cart['Cart']);
        }
    }

    /**
     * called from finish context
     * saves pdf as file
     * @param array $cart
     * @param array $orderDetails
     */
    private function generateRightOfWithdrawalInformationAndForm($cart, $products)
    {
        $this->set('cart', $cart);
        $manufacturers = [];
        foreach ($products as $product) {
            $manufacturers[$product->manufacturer->id_manufacturer][] = $product;
        }
        $this->set('manufacturers', $manufacturers);
        $this->set('saveParam', 'I');
        $this->RequestHandler->renderAs($this, 'pdf');
        return $this->render('generateRightOfWithdrawalInformationAndForm');
    }

    /**
     * called from finish context
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
     * @param array $cart
     */
    private function generateOrderConfirmation($cart)
    {
        
        $this->set('cart', $cart);
        $manufacturers = [];

        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $cart['Cart']->id_cart,
            ],
            'contain' => [
                'CartProducts.OrderDetails.OrderDetailTaxes',
                'CartProducts.Products',
                'CartProducts.Products.Manufacturers.AddressManufacturers'
            ]
        ])->first();
        
        foreach ($cart->cart_products as $cartProduct) {
            $manufacturers[$cartProduct->product->id_manufacturer] = [
                'CartProducts' => $cart->cart_products,
                'Manufacturer' => $cartProduct->product->manufacturer
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
    public function generateRightOfWithdrawalInformationPdf()
    {
        $this->set('saveParam', 'I');
        $this->RequestHandler->renderAs($this, 'pdf');
        $this->render('generateRightOfWithdrawalInformationAndForm');
    }

    private function sendStockAvailableLimitReachedEmailToManufacturer($cartId)
    {
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $cartId
            ],
            'contain' => [
                'CartProducts.Products.Manufacturers.AddressManufacturers',
                'CartProducts.Products.Manufacturers.Customers.AddressCustomers',
                'CartProducts.Products.StockAvailables',
                'CartProducts.ProductAttributes.StockAvailables',
                'CartProducts.OrderDetails'
            ]
        ])->first();
        
        foreach($cart->cart_products as $cartProduct) {
            $stockAvailable = $cartProduct->product->stock_available;
            if (!empty($cartProduct->product_attribute)) {
                $stockAvailable = $cartProduct->product_attribute->stock_available;
            }
            if (is_null($stockAvailable->sold_out_limit)) {
                continue;
            }
            $stockAvailableLimitReached = $stockAvailable->quantity <= $stockAvailable->sold_out_limit;
            
            // send email to manufacturer
            if ($stockAvailableLimitReached && $cartProduct->product->manufacturer->stock_management_enabled && $cartProduct->product->is_stock_product && $cartProduct->product->manufacturer->send_product_sold_out_limit_reached_for_manufacturer) {
                $email = new AppEmail();
                $email->viewBuilder()->setTemplate('stock_available_limit_reached_notification');
                $email->setTo($cartProduct->product->manufacturer->address_manufacturer->email)
                ->setSubject(__('Product_{0}:_Only_{1}_units_on_stock', [
                    $cartProduct->order_detail->product_name,
                    $stockAvailable->quantity
                ]))
                ->setViewVars([
                    'appAuth' => $this->AppAuth,
                    'greeting' => __('Hello') . ' ' . $cartProduct->product->manufacturer->address_manufacturer->firstname,
                    'productEditLink' => Configure::read('app.slugHelper')->getProductAdmin(null, $cartProduct->product->id_product),
                    'cartProduct' => $cartProduct,
                    'stockAvailable' => $stockAvailable,
                    'manufacturer' => $cartProduct->product->manufacturer,
                    'showManufacturerUnsubscribeLink' => true
                ]);
                $email->send();
            }
            
            // send email to contact person
            if ($stockAvailableLimitReached && $cartProduct->product->manufacturer->stock_management_enabled && $cartProduct->product->is_stock_product && !empty($cartProduct->product->manufacturer->customer) && $cartProduct->product->manufacturer->send_product_sold_out_limit_reached_for_contact_person) {
                $email = new AppEmail();
                $email->viewBuilder()->setTemplate('stock_available_limit_reached_notification');
                $email->setTo($cartProduct->product->manufacturer->customer->address_customer->email)
                ->setSubject(__('Product_{0}:_Only_{1}_units_on_stock', [
                    $cartProduct->order_detail->product_name,
                    $stockAvailable->quantity
                ]))
                ->setViewVars([
                    'appAuth' => $this->AppAuth,
                    'greeting' => __('Hello') . ' ' . $cartProduct->product->manufacturer->customer->firstname,
                    'productEditLink' => Configure::read('app.slugHelper')->getProductAdmin($cartProduct->product->id_manufacturer, $cartProduct->product->id_product),
                    'cartProduct' => $cartProduct,
                    'stockAvailable' => $stockAvailable,
                    'manufacturer' => $cartProduct->product->manufacturer,
                    'showManufacturerName' => true,
                    'notificationEditLink' => __('You_can_unsubscribe_this_email_<a href="{0}">in_the_settings_of_the_manufacturer</a>.', [Configure::read('app.cakeServerName') . Configure::read('app.slugHelper')->getManufacturerEditOptions($cartProduct->product->id_manufacturer)])
                ]);
                $email->send();
            }
            
        }
        
    }
    
    /**
     * does not send email to inactive users (superadmins can place shop orders for inactive users!)
     * @param array $cart
     * @param array $orders
     * @param array $products
     */
    private function sendConfirmationEmailToCustomer($cart, $products)
    {
        if ($this->AppAuth->user('active')) {
            $email = new AppEmail();
            $email->viewBuilder()->setTemplate('order_successful');
            $email->setTo($this->AppAuth->getEmail())
            ->setSubject(__('Order_confirmation'))
            ->setViewVars([
                'cart' => $this->Cart->getCartGroupedByPickupDay($cart),
                'appAuth' => $this->AppAuth,
                'originalLoggedCustomer' => $this->getRequest()->getSession()->check('Auth.originalLoggedCustomer') ? $this->getRequest()->getSession()->read('Auth.originalLoggedCustomer') : null
            ]);

            if (Configure::read('app.rightOfWithdrawalEnabled')) {
                $email->addAttachments([__('Filename_Right-of-withdrawal-information-and-form').'.pdf' => ['data' => $this->generateRightOfWithdrawalInformationAndForm($cart, $products), 'mimetype' => 'application/pdf']]);
            }
            $email->addAttachments([__('Filename_Order-confirmation').'.pdf' => ['data' => $this->generateOrderConfirmation($cart), 'mimetype' => 'application/pdf']]);
            if (Configure::read('app.generalTermsAndConditionsEnabled')) {
                $generalTermsAndConditionsFiles = [];
                $uniqueManufacturers = $this->AppAuth->Cart->getUniqueManufacturers();
                foreach($uniqueManufacturers as $manufacturerId => $manufacturer) {
                    $src = Configure::read('app.htmlHelper')->getManufacturerTermsOfUseSrc($manufacturerId);
                    if ($src !== false) {
                        $generalTermsAndConditionsFiles[__('Filename_General-terms-and-conditions') . '-' . StringComponent::slugifyAndKeepCase($manufacturer['name']) . '.pdf'] = [
                            'file' => WWW_ROOT . Configure::read('app.htmlHelper')->getManufacturerTermsOfUseSrcTemplate($manufacturerId), // avoid timestamp
                            'mimetype' => 'application/pdf'
                        ];
                    }
                }
                if (count($uniqueManufacturers) > count($generalTermsAndConditionsFiles)) {
                    $generalTermsAndConditionsFiles[__('Filename_General-terms-and-conditions').'.pdf'] = [
                        'data' => $this->generateGeneralTermsAndConditions(),
                        'mimetype' => 'application/pdf'
                    ];
                }
                
                $email->addAttachments($generalTermsAndConditionsFiles);
            }
            
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

    private function saveOrderDetails($orderDetails2save)
    {
        foreach ($orderDetails2save as &$orderDetail) {

            // timebased_currency: ORDER_DETAILS
            if ($this->AppAuth->Cart->isTimebasedCurrencyUsed()) {

                foreach($this->AppAuth->Cart->getProducts() as $cartProduct) {
                    if ($cartProduct['cartProductId'] == $orderDetail['cartProductId']) {

                        if (isset($cartProduct['isTimebasedCurrencyUsed'])) {

                            $orderDetail['timebased_currency_order_detail']['money_excl'] = $cartProduct['timebasedCurrencyMoneyExcl'];
                            $orderDetail['timebased_currency_order_detail']['money_incl'] = $cartProduct['timebasedCurrencyMoneyIncl'];
                            $orderDetail['timebased_currency_order_detail']['seconds'] = $cartProduct['timebasedCurrencySeconds'];
                            $orderDetail['timebased_currency_order_detail']['max_percentage'] = $orderDetail['product']->manufacturer->timebased_currency_max_percentage;
                            $orderDetail['timebased_currency_order_detail']['exchange_rate'] = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE'));

                            // override prices from timebased_currency adapted cart
                            $orderDetail['total_price_tax_excl'] = $cartProduct['priceExcl'];
                            $orderDetail['total_price_tax_incl'] = $cartProduct['price'];
                        }

                        continue;
                    }
                }

            }
        }

        $this->OrderDetail->saveMany(
            $this->OrderDetail->newEntities($orderDetails2save)
        );
    }
    
    public function finish()
    {
        
        if (!$_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->redirect('/');
            return;
        }

        $this->set('title_for_layout', __('Finish_cart'));
        $cart = $this->AppAuth->getCart();
        
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->PickupDay = TableRegistry::getTableLocator()->get('PickupDays');
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
            $this->Flash->error(__('Your_cart_was_empty'));
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
                'contain' => [
                    'Manufacturers',
                    'Manufacturers.AddressManufacturers',
                    'StockAvailables',
                    'ProductAttributes.StockAvailables',
                    'ProductAttributes.ProductAttributeCombinations',
                ]
            ])
            ->select($this->Product)
            ->select($this->Product->StockAvailables)
            ->select($this->Product->Manufacturers)
            ->select($this->Product->Manufacturers->AddressManufacturers)
            ->select($this->Product->ProductAttributes->StockAvailables)
            ->first();
            $products[] = $product;
            $stockAvailableQuantity = $product->stock_available->quantity;
            $stockAvailableAvailableQuantity = $product->stock_available->quantity - $product->stock_available->quantity_limit;

            // stock available check for product (without attributeId)
            if ($ids['attributeId'] == 0 && $stockAvailableAvailableQuantity < $cartProduct['amount']) {
                $message = __('The_desired_amount_{0}_of_the_product_{1}_is_not_available_any_more_available_amount_{2}.', ['<b>' . $cartProduct['amount'] . '</b>', '<b>' . $product->name . '</b>', $stockAvailableAvailableQuantity]);
                $message .= ' ' . __('Please_change_amount_or_delete_product_from_cart_to_place_order.');
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            if ($ids['attributeId'] > 0) {
                $attributeIdFound = false;
                foreach ($product->product_attributes as $attribute) {
                    if ($attribute->id_product_attribute == $ids['attributeId']) {
                        $attributeIdFound = true;
                        $stockAvailableQuantity = $attribute->stock_available->quantity;
                        $stockAvailableAvailableQuantity = $attribute->stock_available->quantity - $attribute->stock_available->quantity_limit;
                        // stock available check for attribute
                        if ($stockAvailableAvailableQuantity < $cartProduct['amount']) {
                            $this->Attribute = TableRegistry::getTableLocator()->get('Attributes');
                            $attribute = $this->Attribute->find('all', [
                                'conditions' => [
                                    'Attributes.id_attribute' => $attribute->product_attribute_combination->id_attribute
                                ]
                            ])->first();
                            $message = __('The_desired_amount_{0}_of_the_attribute_{1}_of_the_product_{2}_is_not_available_any_more_available_amount_{3}.', ['<b>' . $cartProduct['amount'] . '</b>', '<b>' . $attribute->name . '</b> ', '<b>' . $product->name . '</b>', $stockAvailableAvailableQuantity]);
                            $message .= ' ' . __('Please_change_amount_or_delete_product_from_cart_to_place_order.');
                            $cartErrors[$cartProduct['productId']][] = $message;
                        }
                        break;
                    }
                }
                if (! $attributeIdFound) {
                    $message = __('The_attribute_does_not_exist.');
                    $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                    $cartErrors[$cartProduct['productId']][] = $message;
                }
            }

            if (! $product->active) {
                $message = __('The_product_{0}_is_not_activated_any_more.', ['<b>' . $product->name . '</b>']);
                $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            if (! $product->manufacturer->active || $this->Product->deliveryBreakEnabled($product->manufacturer->no_delivery_days, $product->next_delivery_day)) {
                $message = __('The_manufacturer_of_the_product_{0}_has_a_delivery_break_or_product_is_not_activated.', ['<b>' . $product->name . '</b>']);
                $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                $cartErrors[$cartProduct['productId']][] = $message;
            }
            
            if ($product->delivery_rhythm_type == 'individual') {
                if ($product->delivery_rhythm_order_possible_until->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')) < Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                    $message = __('It_is_not_possible_to_order_the_product_{0}_any_more.', ['<b>' . $product->name . '</b>']);
                    $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                    $cartErrors[$cartProduct['productId']][] = $message;
                }
            }

            // prepare data for table order_detail
            $orderDetail2save = [
                'product_id' => $ids['productId'],
                'product_attribute_id' => $ids['attributeId'],
                'product_name' => $this->Cart->getProductNameWithUnity($cartProduct['productName'], $cartProduct['unity']),
                'product_amount' => $cartProduct['amount'],
                'total_price_tax_excl' => $cartProduct['priceExcl'],
                'total_price_tax_incl' => $cartProduct['price'],
                'id_tax' => $product->id_tax,
                'order_state' => ORDER_STATE_ORDER_PLACED,
                'id_customer' => $this->AppAuth->getUserId(),
                'id_cart_product' => $cartProduct['cartProductId'],
                'pickup_day' => $cartProduct['pickupDay'],
                'deposit' => $cartProduct['deposit'],
                'product' => $product,
                'cartProductId' => $cartProduct['cartProductId'],
            ];
            
            if ($this->getRequest()->getSession()->check('Auth.instantOrderCustomer')) {
                $orderDetail2save['pickup_day'] = $cartProduct['pickupDay'];
            }
            
            // prepare data for table order_detail_tax
            $unitPriceExcl = $this->Product->getNetPrice($ids['productId'], $cartProduct['price'] / $cartProduct['amount']);
            $unitTaxAmount = $this->Product->getUnitTax($cartProduct['price'], $unitPriceExcl, $cartProduct['amount']);
            $totalTaxAmount = $unitTaxAmount * $cartProduct['amount'];
            
            $orderDetail2save['order_detail_tax'] = [
                'unit_amount' => $unitTaxAmount,
                'total_amount' => $totalTaxAmount
            ];

            // prepare data for table order_detail_units
            if ($cartProduct['unitName'] != '') {
                $orderDetail2save['order_detail_unit'] = [
                    'unit_name' => $cartProduct['unitName'],
                    'unit_amount' => $cartProduct['unitAmount'],
                    'price_incl_per_unit' => $cartProduct['priceInclPerUnit'],
                    'quantity_in_units' => $cartProduct['quantityInUnits'],
                    'product_quantity_in_units' => $cartProduct['productQuantityInUnits']
                ];
            }

            $orderDetails2save[] = $orderDetail2save;

            $newQuantity = $stockAvailableQuantity - $cartProduct['amount'];
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
        
        if ($this->AppAuth->isTimebasedCurrencyEnabledForCustomer()) {
            $validator = $this->Cart->getValidator('default');
            $validator->notEmpty(
                'timebased_currency_seconds_sum_tmp',
                __('Please_enter_how_much_you_want_to_pay_in_{0}.', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')])
            );
            $validator->numeric('timebased_currency_seconds_sum_tmp',
                __('Please_enter_a_number.')
            );
            $maxValue = $this->AppAuth->Cart->getTimebasedCurrencySecondsSumRoundedUp();
            $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
            $customerCreditBalance = $this->TimebasedCurrencyOrderDetail->getCreditBalance(null, $this->AppAuth->getUserId());
            $maxValueForCustomers = Configure::read('appDb.FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_CUSTOMER') * 3600 + $customerCreditBalance;
            if ($maxValueForCustomers <= $maxValue) {
                $validator = $this->Cart->getNumberRangeValidator(
                    $validator,
                    'timebased_currency_seconds_sum_tmp',
                    0,
                    $maxValueForCustomers,
                    __('Your_overdraft_frame_of_{0}_is_reached.', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_CUSTOMER') . ' ' . Configure::read('appDb.FCS_TIMEBASED_CURRENCY_SHORTCODE')]),
                    false
                );
            } else {
                $validator = $this->Cart->getNumberRangeValidator($validator, 'timebased_currency_seconds_sum_tmp', 0, $maxValue);
            }
            $this->Cart->setValidator('default', $validator);
        }
        
        $options = [];
        if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
            // save pickup day: primary key needs to be changed!
            $this->Cart->PickupDayEntities->setPrimaryKey(['customer_id', 'pickup_day']);
            $options = [
                'associated' => [
                    'PickupDayEntities'
                ]
            ];
            $fixedPickupDayRequest = [];
            $pickupEntities = $this->getRequest()->getData('Carts.pickup_day_entities');
            if (!empty($pickupEntities)) {
                foreach($pickupEntities as $pickupDay) {
                    $pickupDay['pickup_day'] = FrozenDate::createFromFormat(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), $pickupDay['pickup_day']);
                    $fixedPickupDayRequest[] = $pickupDay;
                }
                $this->setRequest($this->getRequest()->withData('Carts.pickup_day_entities', $fixedPickupDayRequest));
            }
        }
        $cart['Cart'] = $this->Cart->patchEntity(
            $cart['Cart'],
            $this->getRequest()->getData(),
            $options
        );
        
        if ($cart['Cart']->hasErrors()) {
            $formErrors = true;
        }
        $this->set('cart', $cart['Cart']); // to show error messages in form (from validation)
        $this->set('formErrors', $formErrors);

        if (!empty($cartErrors) || !empty($formErrors)) {
            $this->Flash->error(__('Errors_occurred.'));
        } else {

            $selectedTimebasedCurrencySeconds = 0;
            $selectedTimeAdaptionFactor = 0;
            if (!empty($this->getRequest()->getData('Carts.timebased_currency_seconds_sum_tmp')) && $this->getRequest()->getData('Carts.timebased_currency_seconds_sum_tmp') > 0) {
                $selectedTimebasedCurrencySeconds = $this->getRequest()->getData('Carts.timebased_currency_seconds_sum_tmp');
                $selectedTimeAdaptionFactor = $selectedTimebasedCurrencySeconds / $this->AppAuth->Cart->getTimebasedCurrencySecondsSum();
            }

            if ($selectedTimeAdaptionFactor > 0) {
                $cart = $this->Cart->adaptCartWithTimebasedCurrency($cart, $selectedTimeAdaptionFactor);
                $this->AppAuth->setCart($cart);
            }

            $this->saveOrderDetails($orderDetails2save);
            $this->saveStockAvailable($stockAvailable2saveData, $stockAvailable2saveConditions);

            $manufacturersThatReceivedInstantOrderNotification = $this->sendInstantOrderNotificationToManufacturers($cart['CartProducts']);
            $this->sendStockAvailableLimitReachedEmailToManufacturer($cart['Cart']->id_cart);
            
            if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
                $this->Cart->PickupDayEntities->saveMany($cart['Cart']->pickup_day_entities);
            }
            
            $cart = $this->AppAuth->getCart(); // to get attached order details
            $this->AppAuth->setCart($cart);
            $cart['Cart'] = $this->AppAuth->Cart->markAsSaved(); // modified timestamp is needed later on!
            
            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            if ($this->getRequest()->getSession()->check('Auth.instantOrderCustomer')) {
                if (empty($manufacturersThatReceivedInstantOrderNotification)) {
                    $message = __('Instant_order_({0})_successfully_placed_for_{1}.', [
                        Configure::read('app.numberHelper')->formatAsCurrency($this->AppAuth->Cart->getProductSum()),
                        '<b>' . $this->request->getSession()->read('Auth.instantOrderCustomer')->name . '</b>'
                    ]);
                } else {
                    $message = __('Instant_order_({0})_successfully_placed_for_{1}._The_following_manufacturers_were_notified:_{2}', [
                        Configure::read('app.numberHelper')->formatAsCurrency($this->AppAuth->Cart->getProductSum()),
                        '<b>' . $this->request->getSession()->read('Auth.instantOrderCustomer')->name . '</b>',
                        '<b>' . join(', ', $manufacturersThatReceivedInstantOrderNotification) . '</b>'
                    ]);
                }
                $message .= '<br />' . __('Pickup_day') . ': <b>' . Configure::read('app.timeHelper')->getDateFormattedWithWeekday(Configure::read('app.timeHelper')->getCurrentDay()).'</b>';
                $this->ActionLog->customSave('instant_order_added', $this->AppAuth->getUserId(), 0, '', $message);
            } else {
                $message = __('Your_order_has_been_placed_succesfully.');
                $messageForActionLog = __('{0}_has_placed_a_new_order_({1}).', [$this->AppAuth->getUsername(), Configure::read('app.numberHelper')->formatAsCurrency($this->AppAuth->Cart->getProductSum())]);
                $this->ActionLog->customSave('customer_order_finished', $this->AppAuth->getUserId(), 0, '', $messageForActionLog);
            }
            $this->Flash->success($message);
            
            $this->sendConfirmationEmailToCustomer($cart, $products);

            // due to redirect, beforeRender() is not called
            $this->resetOriginalLoggedCustomer();

            $this->redirect(Configure::read('app.slugHelper')->getCartFinished($cart['Cart']->id_cart));
        }

        $this->setAction('detail');
    }

    /**
     * @param $cartProducts
     * @return array
     */
    public function sendInstantOrderNotificationToManufacturers($cartProducts)
    {

        if (!$this->getRequest()->getSession()->check('Auth.instantOrderCustomer')) {
            return [];
        }

        $manufacturers = [];
        foreach ($cartProducts as $cartProduct) {
            if ($cartProduct['isStockProduct']) {
                continue;
            }
            $manufacturers[$cartProduct['manufacturerId']][] = $cartProduct;
        }

        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturersThatReceivedInstantOrderNotification = [];
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

            $sendInstantOrderNotification = $this->Manufacturer->getOptionSendInstantOrderNotification($manufacturer->send_instant_order_notification);
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer->bulk_orders_allowed);
            if ($sendInstantOrderNotification && !$bulkOrdersAllowed) {
                $manufacturersThatReceivedInstantOrderNotification[] = $manufacturer->name;
                $email = new AppEmail();
                $email->viewBuilder()->setTemplate('instant_order_notification');
                $email->setTo($manufacturer->address_manufacturer->email)
                ->setSubject(__('Notification_about_instant_order_order'))
                ->setViewVars([
                    'appAuth' => $this->AppAuth,
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
        
        return $manufacturersThatReceivedInstantOrderNotification;
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
        $this->RequestHandler->renderAs($this, 'ajax');

        // ajax calls do not call beforeRender
        $this->resetOriginalLoggedCustomer();
        $this->destroyInstantOrderCustomer();

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    private function doManufacturerCheck($productId)
    {
        if ($this->AppAuth->isManufacturer()) {
            $message = __('No_access_for_manufacturers.');
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
            $message = __('Product_{0}_was_not_available_in_cart.', [$ids['productId']]);
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
        $deliveryDate = $this->getRequest()->getQuery('deliveryDate');
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
