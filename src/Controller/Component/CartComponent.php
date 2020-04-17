<?php

namespace App\Controller\Component;

use App\Lib\PdfWriter\GeneralTermsAndConditionsPdfWriter;
use App\Lib\PdfWriter\InformationAboutRightOfWithdrawalPdfWriter;
use App\Lib\PdfWriter\OrderConfirmationPdfWriter;
use App\Mailer\AppMailer;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

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

class CartComponent extends Component
{

    public $components = [
        'AppAuth',
        'RequestHandler'
    ];
    
    public $cart = null;

    public function getProducts()
    {
        if ($this->cart !== null) {
            return $this->cart['CartProducts'];
        }
        return null;
    }

    public function getProductsWithUnitCount()
    {
        if ($this->cart !== null) {
            return $this->cart['ProductsWithUnitCount'];
        }
        return 0;
    }

    public function getProductAndDepositSum()
    {
        return $this->getProductSum() + $this->getDepositSum();
    }

    public function getTimebasedCurrencyMoneyInclSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartTimebasedCurrencyMoneyInclSum'];
        }
        return 0;
    }

    public function isTimebasedCurrencyUsed()
    {
        return isset($this->cart['CartTimebasedCurrencyUsed']) && $this->cart['CartTimebasedCurrencyUsed'];
    }

    public function getTimebasedCurrencyMoneyExclSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartTimebasedCurrencyMoneyExclSum'];
        }
        return 0;
    }

    /**
     * avoids rounding errors
     * @return number
     */
    public function getTimebasedCurrencySecondsSumRoundedUp()
    {
        return round($this->getTimebasedCurrencySecondsSum() * 1.05, 0);
    }

    public function getTimebasedCurrencySecondsSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartTimebasedCurrencySecondsSum'];
        }
        return 0;
    }

    public function getTaxSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartTaxSum'];
        }
        return 0;
    }

    public function getDepositSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartDepositSum'];
        }
        return 0;
    }

    public function getProductSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartProductSum'];
        }
        return 0;
    }

    public function getProductSumExcl()
    {
        if ($this->cart !== null) {
            return $this->cart['CartProductSumExcl'];
        }
        return 0;
    }

    public function getCartId()
    {
        return $this->cart['Cart']->id_cart;
    }

    public function markAsSaved()
    {
        if ($this->cart === null) {
            return false;
        }
        $cc = TableRegistry::getTableLocator()->get('Carts');
        $patchedEntity = $cc->patchEntity(
            $cc->get($this->getCartId()), [
                'status' => APP_OFF
            ]
        );
        $cc->save($patchedEntity);
        return $patchedEntity;
    }
    
    public function getUniqueManufacturers()
    {
        $manufactures = [];
        foreach ($this->getProducts() as $product) {
            $manufactures[$product['manufacturerId']] = [
                'name' => $product['manufacturerName']
            ];
        }
        return $manufactures;
    }

    /**
     *
     * @param string $productId
     *            - possible value: 34-423 (productId, attributeId)
     */
    public function getProduct($productId)
    {
        foreach ($this->getProducts() as $product) {
            if ($product['productId'] == $productId) {
                return $product;
                break;
            }
        }
        return false;
    }
    
    public function isCartEmpty()
    {
        $isEmpty = false;
        if (empty($this->getProducts())) {
            $isEmpty = true;
        }
        return $isEmpty;
    }
    
    public function finish()
    {
        
        $cart = $this->AppAuth->getCart();
        
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->PickupDay = TableRegistry::getTableLocator()->get('PickupDays');
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        
        // START check if no amount is 0
        $productWithAmount0Found = false;
        foreach ($this->getProducts() as $cartProduct) {
            $ids = $this->Product->getProductIdAndAttributeId($cartProduct['productId']);
            if ($cartProduct['amount'] == 0) {
                Log::error('amount of cart productId ' . $ids['productId'] . ' (attributeId : ' . $ids['attributeId'] . ') was 0 and therefore removed from cart');
                $cartProductTable = TableRegistry::getTableLocator()->get('CartProducts');
                $cartProductTable->remove($ids['productId'], $ids['attributeId'], $this->getCartId());
                $productWithAmount0Found = true;
            }
        }
        
        if ($productWithAmount0Found) {
            $cart = $this->AppAuth->getCart();
            $this->AppAuth->setCart($cart);
        }
        // END check if no amount is 0
        
        $cartErrors = [];
        $orderDetails2save = [];
        $products = [];
        $stockAvailable2saveData = [];
        $stockAvailable2saveConditions = [];
        
        foreach ($this->getProducts() as $cartProduct) {
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
            ])->first();
            $products[] = $product;
            
            $stockAvailableQuantity = $product->stock_available->quantity;
            $stockAvailableAvailableQuantity = $stockAvailableQuantity;
            if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
                $stockAvailableAvailableQuantity = $product->stock_available->quantity - $product->stock_available->quantity_limit;
            }
            // stock available check for product (without attributeId)
            if ((($product->is_stock_product && $product->manufacturer->stock_management_enabled) || !$product->stock_available->always_available) && $ids['attributeId'] == 0 && $stockAvailableAvailableQuantity < $cartProduct['amount']) {
                $message = __('The_desired_amount_{0}_of_the_product_{1}_is_not_available_any_more_available_amount_{2}.', ['<b>' . $cartProduct['amount'] . '</b>', '<b>' . $product->name . '</b>', $stockAvailableAvailableQuantity]);
                $message .= ' ' . __('Please_change_amount_or_delete_product_from_cart_to_place_order.');
                $cartErrors[$cartProduct['productId']][] = $message;
            }
            
            $attribute = null;
            if ($ids['attributeId'] > 0) {
                $attributeIdFound = false;
                foreach ($product->product_attributes as $attribute) {
                    if ($attribute->id_product_attribute == $ids['attributeId']) {
                        
                        $attributeIdFound = true;
                        $stockAvailableQuantity = $attribute->stock_available->quantity;
                        $stockAvailableAvailableQuantity = $stockAvailableQuantity;
                        if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
                            $stockAvailableAvailableQuantity = $attribute->stock_available->quantity - $attribute->stock_available->quantity_limit;
                        }
                        
                        // stock available check for attribute
                        if ((($product->is_stock_product && $product->manufacturer->stock_management_enabled) || !$attribute->stock_available->always_available) && $stockAvailableAvailableQuantity < $cartProduct['amount']) {
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
            
            if (! $product->manufacturer->active || (!$this->AppAuth->isInstantOrderMode() && !$this->AppAuth->isSelfServiceModeByUrl() && $this->Product->deliveryBreakEnabled($product->manufacturer->no_delivery_days, $product->next_delivery_day))) {
                $message = __('The_manufacturer_of_the_product_{0}_has_a_delivery_break_or_product_is_not_activated.', ['<b>' . $product->name . '</b>']);
                $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                $cartErrors[$cartProduct['productId']][] = $message;
            }
            
            if (!$this->AppAuth->isInstantOrderMode()) {
                if ( !($product->manufacturer->stock_management_enabled && $product->is_stock_product) && $product->delivery_rhythm_type == 'individual') {
                    if ($product->delivery_rhythm_order_possible_until->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')) < Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                        $message = __('It_is_not_possible_to_order_the_product_{0}_any_more.', ['<b>' . $product->name . '</b>']);
                        $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                        $cartErrors[$cartProduct['productId']][] = $message;
                    }
                }
            }
            
            if (!$this->AppAuth->isInstantOrderMode() && !$this->AppAuth->isSelfServiceModeByUrl() && $this->Product->deliveryBreakEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $product->next_delivery_day)) {
                $message = __('{0}_has_activated_the_delivery_break_and_product_{1}_cannot_be_ordered.',
                    [
                        Configure::read('appDb.FCS_APP_NAME'),
                        '<b>' . $product->name . '</b>'
                    ]
                );
                $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                $cartErrors[$cartProduct['productId']][] = $message;
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
            
            if ($this->AppAuth->isInstantOrderMode() || $this->AppAuth->isSelfServiceModeByUrl()) {
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
            if (isset($cartProduct['quantityInUnits'])) {
                $orderDetail2save['order_detail_unit'] = [
                    'unit_name' => $cartProduct['unitName'],
                    'unit_amount' => $cartProduct['unitAmount'],
                    'price_incl_per_unit' => $cartProduct['priceInclPerUnit'],
                    'quantity_in_units' => $cartProduct['quantityInUnits'],
                    'product_quantity_in_units' => $cartProduct['productQuantityInUnits']
                ];
            }
            
            $orderDetails2save[] = $orderDetail2save;
            
            $decreaseQuantity = ($product->is_stock_product && $product->manufacturer->stock_management_enabled) || !$product->stock_available->always_available;
            if (isset($attribute->stock_available)) {
                $decreaseQuantity = ($product->is_stock_product && $product->manufacturer->stock_management_enabled) || !$attribute->stock_available->always_available;
            }
            
            if ($decreaseQuantity) {
                $newQuantity = $stockAvailableQuantity - $cartProduct['amount'];
                $stockAvailable2saveData[] = [
                    'quantity' => $newQuantity
                ];
                $stockAvailable2saveConditions[] = [
                    'id_product' => $ids['productId'],
                    'id_product_attribute' => $ids['attributeId']
                ];
            }
        }
        
        $this->getController()->set('cartErrors', $cartErrors);
        
        if ($this->AppAuth->isTimebasedCurrencyEnabledForCustomer()) {
            $validator = $this->Cart->getValidator('default');
            $validator->notEmptyString(
                'timebased_currency_seconds_sum_tmp',
                __('Please_enter_how_much_you_want_to_pay_in_{0}.', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')])
                );
            $validator->numeric('timebased_currency_seconds_sum_tmp',
                __('Please_enter_a_number.')
                );
            $maxValue = $this->getTimebasedCurrencySecondsSumRoundedUp();
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
            $pickupEntities = $this->getController()->getRequest()->getData('Carts.pickup_day_entities');
            if (!empty($pickupEntities)) {
                foreach($pickupEntities as $pickupDay) {
                    $pickupDay['pickup_day'] = FrozenDate::createFromFormat(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), $pickupDay['pickup_day']);
                    $fixedPickupDayRequest[] = $pickupDay;
                }
                $this->getController()->setRequest($this->getController()->getRequest()->withData('Carts.pickup_day_entities', $fixedPickupDayRequest));
            }
        }
        $cart['Cart'] = $this->Cart->patchEntity(
            $cart['Cart'],
            $this->getController()->getRequest()->getData(),
            $options
        );
        
        $formErrors = false;
        if ($cart['Cart']->hasErrors()) {
            $formErrors = true;
        }
        $this->getController()->set('cart', $cart['Cart']); // to show error messages in form (from validation)
        $this->getController()->set('formErrors', $formErrors);
        
        if (!empty($cartErrors) || !empty($formErrors)) {
            $this->getController()->Flash->error(__('Errors_occurred.'));
        } else {
            
            $selectedTimebasedCurrencySeconds = 0;
            $selectedTimeAdaptionFactor = 0;
            if (!empty($this->getController()->getRequest()->getData('Carts.timebased_currency_seconds_sum_tmp')) && $this->getController()->getRequest()->getData('Carts.timebased_currency_seconds_sum_tmp') > 0) {
                $selectedTimebasedCurrencySeconds = $this->getController()->getRequest()->getData('Carts.timebased_currency_seconds_sum_tmp');
                $selectedTimeAdaptionFactor = $selectedTimebasedCurrencySeconds / $this->getTimebasedCurrencySecondsSum();
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
            $cart['Cart'] = $this->markAsSaved(); // modified timestamp is needed later on!
            
            $cartType = $this->AppAuth->getCartType();
            $userIdForActionLog = $this->AppAuth->getUserId();
            
            switch($cartType) {
                case $this->Cart::CART_TYPE_WEEKLY_RHYTHM;
                    $actionLogType = 'customer_order_finished';
                    $message = __('Your_order_has_been_placed_succesfully.');
                    $messageForActionLog = __('{0}_has_placed_a_new_order_({1}).', [$this->AppAuth->getUsername(), Configure::read('app.numberHelper')->formatAsCurrency($this->getProductSum())]);
                    $this->sendConfirmationEmailToCustomer($cart, $products);
                    break;
                case $this->Cart::CART_TYPE_INSTANT_ORDER;
                    $actionLogType = 'instant_order_added';
                    $userIdForActionLog = $this->getController()->getRequest()->getSession()->read('Auth.originalLoggedCustomer')['id_customer'];
                    if (empty($manufacturersThatReceivedInstantOrderNotification)) {
                        $message = __('Instant_order_({0})_successfully_placed_for_{1}.', [
                            Configure::read('app.numberHelper')->formatAsCurrency($this->getProductSum()),
                            '<b>' . $this->getController()->getRequest()->getSession()->read('Auth.instantOrderCustomer')->name . '</b>'
                        ]);
                    } else {
                        $message = __('Instant_order_({0})_successfully_placed_for_{1}._The_following_manufacturers_were_notified:_{2}', [
                            Configure::read('app.numberHelper')->formatAsCurrency($this->getProductSum()),
                            '<b>' . $this->getController()->getRequest()->getSession()->read('Auth.instantOrderCustomer')->name . '</b>',
                            '<b>' . join(', ', $manufacturersThatReceivedInstantOrderNotification) . '</b>'
                        ]);
                    }
                    $message .= '<br />' . __('Pickup_day') . ': <b>' . Configure::read('app.timeHelper')->getDateFormattedWithWeekday(Configure::read('app.timeHelper')->getCurrentDay()).'</b>';
                    $messageForActionLog = $message;
                    $this->sendConfirmationEmailToCustomer($cart, $products);
                    break;
                case $this->Cart::CART_TYPE_SELF_SERVICE;
                    $actionLogType = 'self_service_order_added';
                    $message = __('Thank_you_for_your_purchase!');
                    $message .= '<br />';
                    $message .= '<a class="btn-flash-message btn-flash-message-logout btn btn-outline-light" href="'.Configure::read('app.slugHelper')->getLogout().'">'.__('Sign_out').'?</a>';
                    $message .= '<a class="btn-flash-message btn-flash-message-continue btn btn-outline-light" href="'.Configure::read('app.slugHelper')->getSelfService().'">'.__('Continue_shopping?').'</a>';
                    $messageForActionLog = __('{0}_has_placed_a_new_order_({1}).', [$this->AppAuth->getUsername(), Configure::read('app.numberHelper')->formatAsCurrency($this->getProductSum())]);
                    $this->sendConfirmationEmailToCustomerSelfService($cart, $products);
                    break;
            }
            
            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave($actionLogType, $userIdForActionLog, $cart['Cart']->id_cart, 'carts', $messageForActionLog);
            $this->getController()->Flash->success($message);

        }
        
        return $cart;
    
    }
    
    private function saveOrderDetails($orderDetails2save)
    {
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        foreach ($orderDetails2save as &$orderDetail) {
            
            // timebased_currency: ORDER_DETAILS
            if ($this->isTimebasedCurrencyUsed()) {
                
                foreach($this->getProducts() as $cartProduct) {
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
    
    private function saveStockAvailable($stockAvailable2saveData, $stockAvailable2saveConditions)
    {
        $this->Product = TableRegistry::getTableLocator()->get('Products');
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
    
    /**
     * @param $cartProducts
     * @return array
     */
    private function sendInstantOrderNotificationToManufacturers($cartProducts)
    {
        
        if (!$this->AppAuth->isInstantOrderMode()) {
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
            if ($sendInstantOrderNotification) {
                $manufacturersThatReceivedInstantOrderNotification[] = $manufacturer->name;
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('instant_order_notification');
                $email->setTo($manufacturer->address_manufacturer->email)
                ->setSubject(__('Notification_about_instant_order_order'))
                ->setViewVars([
                    'appAuth' => $this->AppAuth,
                    'cart' => ['CartProducts' => $cartProducts],
                    'originalLoggedCustomer' => $this->getController()->getRequest()->getSession()->read('Auth.originalLoggedCustomer'),
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
    
    private function sendStockAvailableLimitReachedEmailToManufacturer($cartId)
    {
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
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
                $email = new AppMailer();
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
                $email = new AppMailer();
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
    
    private function sendConfirmationEmailToCustomerSelfService($cart, $products)
    {
        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('order_successful_self_service');
        $email->setTo($this->AppAuth->getEmail())
        ->setSubject(__('Your_purchase'))
        ->setViewVars([
            'cart' => $this->Cart->getCartGroupedByPickupDay($cart),
            'appAuth' => $this->AppAuth
        ]);
        $email->send();
    }
    
    /**
     * does not send email to inactive users (superadmins can place shop orders for inactive users!)
     * @param array $cart
     * @param array $products
     */
    private function sendConfirmationEmailToCustomer($cart, $products)
    {
        
        if (!$this->AppAuth->user('active')) {
            return false;
        }
        
        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('order_successful');
        $email->setTo($this->AppAuth->getEmail())
        ->setSubject(__('Order_confirmation'))
        ->setViewVars([
            'cart' => $this->Cart->getCartGroupedByPickupDay($cart),
            'appAuth' => $this->AppAuth,
            'originalLoggedCustomer' => $this->getController()->getRequest()->getSession()->check('Auth.originalLoggedCustomer') ? $this->getController()->getRequest()->getSession()->read('Auth.originalLoggedCustomer') : null
        ]);
        
        if (Configure::read('app.rightOfWithdrawalEnabled')) {
            $email->addAttachments([__('Filename_Right-of-withdrawal-information-and-form').'.pdf' => ['data' => $this->generateRightOfWithdrawalInformationAndForm($cart, $products), 'mimetype' => 'application/pdf']]);
        }

        $email->addAttachments([__('Filename_Order-confirmation').'.pdf' => ['data' => $this->generateOrderConfirmation($cart), 'mimetype' => 'application/pdf']]);
        if (Configure::read('app.generalTermsAndConditionsEnabled')) {
            $generalTermsAndConditionsFiles = [];
            $uniqueManufacturers = $this->getUniqueManufacturers();
            foreach($uniqueManufacturers as $manufacturerId => $manufacturer) {
                $src = Configure::read('app.htmlHelper')->getManufacturerTermsOfUseSrc($manufacturerId);
                if ($src !== false) {
                    $generalTermsAndConditionsFiles[__('Filename_General-terms-and-conditions') . '-' . StringComponent::slugify($manufacturer['name']) . '.pdf'] = [
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
    
    /**
     * called from finish context
     * saves pdf as file
     * @param array $cart
     * @param array $orderDetails
     */
    private function generateRightOfWithdrawalInformationAndForm($cart, $products)
    {
        $manufacturers = [];
        foreach ($products as $product) {
            $manufacturers[$product->manufacturer->id_manufacturer][] = $product;
        }
        
        $pdfWriter = new InformationAboutRightOfWithdrawalPdfWriter();
        $pdfWriter->setData([
            'products' => $products,
            'appAuth' => $this->AppAuth,
            'cart' => $cart,
            'manufacturers' => $manufacturers,
        ]);
        return $pdfWriter->writeAttachment();
    }
    
    /**
     * called from finish context
     * saves pdf as file
     */
    private function generateGeneralTermsAndConditions()
    {
        $pdfWriter = new GeneralTermsAndConditionsPdfWriter();
        return $pdfWriter->writeAttachment();
    }
    
    /**
     * called from finish context
     * saves pdf as file
     * @param array $cart
     */
    private function generateOrderConfirmation($cart)
    {
        
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
        
        $pdfWriter = new OrderConfirmationPdfWriter();
        $pdfWriter->setData([
            'appAuth' => $this->AppAuth,
            'cart' => $cart,
            'manufacturers' => $manufacturers,
        ]);
        return $pdfWriter->writeAttachment();
    }
    
}
