<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Lib\DeliveryRhythm\DeliveryRhythm;
use App\Lib\HelloCash\HelloCash;
use App\Lib\Invoice\GenerateInvoiceToCustomer;
use App\Lib\PdfWriter\GeneralTermsAndConditionsPdfWriter;
use App\Lib\PdfWriter\InformationAboutRightOfWithdrawalPdfWriter;
use App\Lib\PdfWriter\OrderConfirmationPdfWriter;
use App\Mailer\AppMailer;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\Datasource\FactoryLocator;

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
        $cc = FactoryLocator::get('Table')->get('Carts');
        $patchedEntity = $cc->patchEntity(
            $cc->get($this->getCartId()), [
                'status' => APP_OFF,
            ],
            ['validate' => false],
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

    protected function getProductContain(): array
    {
        $contain = [
            'Manufacturers',
            'Manufacturers.AddressManufacturers',
            'StockAvailables',
            'ProductAttributes.StockAvailables',
            'ProductAttributes.ProductAttributeCombinations',
            'Taxes',
        ];
        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $contain[] = 'UnitProducts';
            $contain[] = 'PurchasePriceProducts.Taxes';
            $contain[] = 'ProductAttributes.PurchasePriceProductAttributes';
            $contain[] = 'ProductAttributes.UnitProductAttributes';
        }
        return $contain;
    }

    protected function saveCart($cart, $orderDetails2save, $stockAvailable2saveData, $stockAvailable2saveConditions, $customerSelectedPickupDay, $products)
    {

        $this->saveOrderDetails($orderDetails2save);
        $this->saveStockAvailable($stockAvailable2saveData, $stockAvailable2saveConditions);

        $manufacturersThatReceivedInstantOrderNotification = $this->sendInstantOrderNotificationToManufacturers($cart['CartProducts']);
        $this->sendStockAvailableLimitReachedEmailToManufacturer($cart['Cart']->id_cart);

        $pickupDayEntities = null;
        if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
            $pickupDayEntities = $cart['Cart']->pickup_day_entities;
            $this->Cart->PickupDayEntities->saveMany($pickupDayEntities);
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
                $cartGroupedByPickupDay = $this->Cart->getCartGroupedByPickupDay($cart, $customerSelectedPickupDay);
                $this->sendConfirmationEmailToCustomer($cart, $cartGroupedByPickupDay, $products, $pickupDayEntities);
                break;
            case $this->Cart::CART_TYPE_INSTANT_ORDER;
                $actionLogType = 'instant_order_added';
                $userIdForActionLog = $this->getController()->getRequest()->getSession()->read('Auth.originalLoggedCustomer')['id_customer'];
                if (empty($manufacturersThatReceivedInstantOrderNotification)) {
                    $message = __('Instant_order_({0})_successfully_placed_for_{1}.', [
                        Configure::read('app.numberHelper')->formatAsCurrency($this->getProductSum()),
                        '<b>' . $this->getController()->getRequest()->getSession()->read('Auth.orderCustomer')->name . '</b>'
                    ]);
                } else {
                    $message = __('Instant_order_({0})_successfully_placed_for_{1}._The_following_manufacturers_were_notified:_{2}', [
                        Configure::read('app.numberHelper')->formatAsCurrency($this->getProductSum()),
                        '<b>' . $this->getController()->getRequest()->getSession()->read('Auth.orderCustomer')->name . '</b>',
                        '<b>' . join(', ', $manufacturersThatReceivedInstantOrderNotification) . '</b>'
                    ]);
                }
                $message .= '<br />' . __('Pickup_day') . ': <b>' . Configure::read('app.timeHelper')->getDateFormattedWithWeekday(Configure::read('app.timeHelper')->getCurrentDay()).'</b>';
                $messageForActionLog = $message;
                $cartGroupedByPickupDay = $this->Cart->getCartGroupedByPickupDay($cart);
                if (!($this->AppAuth->isOrderForDifferentCustomerMode() && Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS'))) {
                    $this->sendConfirmationEmailToCustomer($cart, $cartGroupedByPickupDay, $products, []);
                }
                break;
            case $this->Cart::CART_TYPE_SELF_SERVICE;

                if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && Configure::read('app.selfServiceModeAutoGenerateInvoice')) {
                    $this->Invoice = FactoryLocator::get('Table')->get('Invoices');
                    $currentDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
                    $invoiceData = $this->Invoice->getDataForCustomerInvoice($this->AppAuth->getUserId(), $currentDay);

                    if (!$this->AppAuth->isOrderForDifferentCustomerMode()) {
                        $paidInCash = 0;
                        if ($this->AppAuth->isSelfServiceCustomer()) {
                            $paidInCash = 1;
                        }
                        if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
                            $helloCash = new HelloCash();
                            $responseObject = $helloCash->generateInvoice($invoiceData, $currentDay, $paidInCash, false);
                            $invoiceId = $responseObject->invoice_id;
                            $invoiceRoute = Configure::read('app.slugHelper')->getHelloCashReceipt($invoiceId);
                        } else {
                            $invoiceToCustomer = new GenerateInvoiceToCustomer();
                            $newInvoice = $invoiceToCustomer->run($invoiceData, $currentDay, $paidInCash);
                            $invoiceId = $newInvoice->id;
                            $invoiceRoute = Configure::read('app.slugHelper')->getInvoiceDownloadRoute($newInvoice->filename);
                        }
                        $cart['invoice_id'] = $invoiceId;
                    }
                }

                $actionLogType = 'self_service_order_added';
                $message = __('Thank_you_for_your_purchase!');
                $message .= '<br />';
                $message .= '<a class="btn-flash-message btn-flash-message-logout btn btn-outline-light" href="'.Configure::read('app.slugHelper')->getLogout(Configure::read('app.slugHelper')->getSelfService()) . '"><i class="fas fa-sign-out-alt ok"></i> '.__('Sign_out').'</a>';
                $message .= '<a class="btn-flash-message btn-flash-message-continue btn btn-outline-light" href="'.Configure::read('app.slugHelper')->getSelfService().'"><i class="fas fa-shopping-bag ok"></i> '.__('Continue_shopping').'</a>';
                if (isset($invoiceRoute)) {
                    $message .= '<a onclick="'.h(Configure::read('app.jsNamespace') . '.Helper.openPrintDialogForFile("'.Configure::read('App.fullBaseUrl') . $invoiceRoute. '");'). '" class="btn-flash-message btn-flash-message-print-invoice btn btn-outline-light" href="javascript:void(0);"><i class="fas ok fa-print"></i> '.__('Print_receipt').'</a>';
                }
                $messageForActionLog = __('{0}_has_placed_a_new_order_({1}).', [$this->AppAuth->getUsername(), Configure::read('app.numberHelper')->formatAsCurrency($this->getProductSum())]);

                if ($this->AppAuth->isOrderForDifferentCustomerMode()) {
                    $userIdForActionLog = $this->getController()->getRequest()->getSession()->read('Auth.originalLoggedCustomer')['id_customer'];
                    $messageForActionLog = __('{0}_has_placed_a_new_order_for_{1}_({2}).', [
                        $this->getController()->getRequest()->getSession()->read('Auth.originalLoggedCustomer')['name'],
                        '<b>' . $this->getController()->getRequest()->getSession()->read('Auth.orderCustomer')->name . '</b>',
                        Configure::read('app.numberHelper')->formatAsCurrency($this->getProductSum()),
                    ]);
                } else {
                    $this->sendConfirmationEmailToCustomerSelfService($cart, $products);
                }
                break;
        }

        $this->ActionLog = FactoryLocator::get('Table')->get('ActionLogs');
        $this->ActionLog->customSave($actionLogType, $userIdForActionLog, $cart['Cart']->id_cart, 'carts', $messageForActionLog);
        $this->getController()->Flash->success($message);

        return $cart;
    }

    public function finish()
    {

        $cart = $this->AppAuth->getCart();

        $this->Cart = FactoryLocator::get('Table')->get('Carts');
        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');
        $this->PickupDay = FactoryLocator::get('Table')->get('PickupDays');
        $this->Product = FactoryLocator::get('Table')->get('Products');

        // START check if no amount is 0
        $productWithAmount0Found = false;
        foreach ($this->getProducts() as $cartProduct) {
            $ids = $this->Product->getProductIdAndAttributeId($cartProduct['productId']);
            if ($cartProduct['amount'] == 0) {
                $cartProductTable = FactoryLocator::get('Table')->get('CartProducts');
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

        if (Configure::read('app.htmlHelper')->paymentIsCashless() && !$this->AppAuth->isOrderForDifferentCustomerMode()) {
            if ($this->AppAuth->getCreditBalanceMinusCurrentCartSum() < Configure::read('appDb.FCS_MINIMAL_CREDIT_BALANCE')) {
                $message = __('Please_add_credit_({0})_(minimal_credit_is_{1}).', [
                    '<b>'.Configure::read('app.numberHelper')->formatAsCurrency($this->AppAuth->getCreditBalanceMinusCurrentCartSum()).'</b>',
                    '<b>'.Configure::read('app.numberHelper')->formatAsCurrency(Configure::read('appDb.FCS_MINIMAL_CREDIT_BALANCE')).'</b>',
                ]);
                $cartErrors['global'][] = $message;
            }
        }

        $orderDetails2save = [];
        $products = [];
        $stockAvailable2saveData = [];
        $stockAvailable2saveConditions = [];

        $contain = $this->getProductContain();

        foreach ($this->getProducts() as $cartProduct) {

            $ids = $this->Product->getProductIdAndAttributeId($cartProduct['productId']);
            $product = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $ids['productId']
                ],
                'contain' => $contain,
            ])->first();

            $product->next_delivery_day = DeliveryRhythm::getNextDeliveryDayForProduct($product, $this->AppAuth);
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

            // purchase price check for product
            if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                if ($ids['attributeId'] == 0 && !$this->Product->PurchasePriceProducts->isPurchasePriceSet($product)) {
                    $message = __('The_product_{0}_cannot_be_ordered_any_more_due_to_interal_reasons.', ['<b>' . $product->name . '</b>']);
                    $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                    $cartErrors[$cartProduct['productId']][] = $message;
                }
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
                            $this->Attribute = FactoryLocator::get('Table')->get('Attributes');
                            $attributeEntity = $this->Attribute->find('all', [
                                'conditions' => [
                                    'Attributes.id_attribute' => $attribute->product_attribute_combination->id_attribute
                                ]
                            ])->first();
                            $message = __('The_desired_amount_{0}_of_the_attribute_{1}_of_the_product_{2}_is_not_available_any_more_available_amount_{3}.', ['<b>' . $cartProduct['amount'] . '</b>', '<b>' . $attributeEntity->name . '</b> ', '<b>' . $product->name . '</b>', $stockAvailableAvailableQuantity]);
                            $message .= ' ' . __('Please_change_amount_or_delete_product_from_cart_to_place_order.');
                            $cartErrors[$cartProduct['productId']][] = $message;
                        }

                        // purchase price check for attribute
                        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                            if (!$this->Product->ProductAttributes->PurchasePriceProductAttributes->isPurchasePriceSet($attribute)) {
                                $this->Attribute = FactoryLocator::get('Table')->get('Attributes');
                                $attributeEntity = $this->Attribute->find('all', [
                                    'conditions' => [
                                        'Attributes.id_attribute' => $attribute->product_attribute_combination->id_attribute
                                    ]
                                ])->first();
                                $message = __('The_attribute_{0}_of_the_product_{1}_cannot_be_ordered_any_more_due_to_interal_reasons.', ['<b>' . $attributeEntity->name . '</b> ', '<b>' . $product->name . '</b>']);
                                $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                                $cartErrors[$cartProduct['productId']][] = $message;
                            }
                        }

                        break;
                    }
                }
                if (! $attributeIdFound) {
                    $message = __('The_attribute_does_not_exist.');
                    $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                    $cartErrors[$cartProduct['productId']][] = $message;
                }
            } else {

                if (!empty($product->product_attributes)) {
                    $message = __('The_product_now_contains_attributes.');
                    $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                    $message .= ' ' . __('You_can_add_it_again_after_having_it_deleted.');
                    $cartErrors[$cartProduct['productId']][] = $message;
                }
            }

            if (! $product->active) {
                $message = __('The_product_{0}_is_not_activated_any_more.', ['<b>' . $product->name . '</b>']);
                $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            if (!$this->AppAuth->isOrderForDifferentCustomerMode() && !$this->AppAuth->isSelfServiceModeByUrl() && !$this->AppAuth->isSelfServiceModeByReferer() && $product->next_delivery_day == 'delivery-rhythm-triggered-delivery-break') {
                $message = __('{0}_can_be_ordered_next_week.',
                    [
                        '<b>' . $product->name . '</b>'
                    ]
                );
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            if (! $product->manufacturer->active || (!$this->AppAuth->isOrderForDifferentCustomerMode()
                && !$this->AppAuth->isSelfServiceModeByUrl()
                && $this->Product->deliveryBreakManufacturerEnabled(
                    $product->manufacturer->no_delivery_days,
                    $product->next_delivery_day,
                    $product->manufacturer->stock_management_enabled,
                    $product->is_stock_product))) {
                        $message = __('The_manufacturer_of_the_product_{0}_has_a_delivery_break_or_product_is_not_activated.', ['<b>' . $product->name . '</b>']);
                        $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                        $cartErrors[$cartProduct['productId']][] = $message;
            }

            if (!$this->AppAuth->isOrderForDifferentCustomerMode()) {
                if ( !($product->manufacturer->stock_management_enabled && $product->is_stock_product) && $product->delivery_rhythm_type == 'individual') {
                    if ($product->delivery_rhythm_order_possible_until->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')) < Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                        $message = __('It_is_not_possible_to_order_the_product_{0}_any_more.', ['<b>' . $product->name . '</b>']);
                        $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                        $cartErrors[$cartProduct['productId']][] = $message;
                    }
                }
            }

            if (!$this->AppAuth->isOrderForDifferentCustomerMode() && !$this->AppAuth->isSelfServiceModeByUrl() && $this->Product->deliveryBreakGlobalEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $product->next_delivery_day)) {
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
                'tax_unit_amount' => $cartProduct['taxPerPiece'],
                'tax_total_amount' => $cartProduct['tax'],
                'tax_rate' => $product->tax->rate ?? 0,
                'order_state' => ORDER_STATE_ORDER_PLACED,
                'id_customer' => $this->AppAuth->getUserId(),
                'id_cart_product' => $cartProduct['cartProductId'],
                'pickup_day' => $cartProduct['pickupDay'],
                'deposit' => $cartProduct['deposit'],
                'product' => $product,
                'cartProductId' => $cartProduct['cartProductId'],
            ];

            $customerSelectedPickupDay = null;
            if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
                $customerSelectedPickupDay = h($this->getController()->getRequest()->getData('Carts.pickup_day'));
                $orderDetail2save['pickup_day'] = $customerSelectedPickupDay;
            }

            // prepare data for table order_detail_units
            if (isset($cartProduct['quantityInUnits'])) {
                $orderDetail2save['order_detail_unit'] = [
                    'unit_name' => $cartProduct['unitName'],
                    'unit_amount' => $cartProduct['unitAmount'],
                    'mark_as_saved' => $cartProduct['markAsSaved'],
                    'price_incl_per_unit' => $cartProduct['priceInclPerUnit'],
                    'quantity_in_units' => $cartProduct['quantityInUnits'],
                    'product_quantity_in_units' => $cartProduct['productQuantityInUnits']
                ];
                if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')
                    && in_array($this->AppAuth->user('shopping_price'), ['PP', 'SP'])
                    && isset($cartProduct['purchasePriceInclPerUnit'])
                    ) {
                    $orderDetail2save['order_detail_unit']['purchase_price_incl_per_unit'] = $cartProduct['purchasePriceInclPerUnit'];
                }
            }

            if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')
                && in_array($this->AppAuth->user('shopping_price'), ['PP', 'SP'])
                ) {
                $orderDetailPurchasePrices = $this->prepareOrderDetailPurchasePrices($ids, $product, $cartProduct);
                $orderDetail2save['order_detail_purchase_price'] = $orderDetailPurchasePrices;
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
                $this->sendOrderCommentNotificationToPlatformOwner($pickupEntities);
            }
        }

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $options['validate'] = 'customerCanSelectPickupDay';
        }

        if ($this->AppAuth->getCartType() == $this->Cart::CART_TYPE_SELF_SERVICE
            && $this->AppAuth->isOrderForDifferentCustomerMode()) {
            $options['validate'] = 'selfServiceForDifferentCustomer';
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
            return $cart;
        }

        $cart = $this->saveCart($cart, $orderDetails2save, $stockAvailable2saveData, $stockAvailable2saveConditions, $customerSelectedPickupDay, $products);
        return $cart;

    }

    private function prepareOrderDetailPurchasePrices($ids, $product, $cartProduct)
    {

        $amount = $cartProduct['amount'];

        $purchasePriceTaxRate = $product->purchase_price_product->tax->rate ?? 0;
        $totalPurchasePriceTaxIncl = 0;
        $totalPurchasePriceTaxExcl = 0;
        $unitPurchasePriceTaxAmount = 0;
        $totalPurchasePriceTaxAmount = 0;

        if ($ids['attributeId'] > 0) {
            // attribute
            foreach ($product->product_attributes as $attribute) {
                if ($attribute->id_product_attribute == $ids['attributeId']) {
                    if (!empty($attribute->unit_product_attribute) && $attribute->unit_product_attribute->price_per_unit_enabled) {
                        $totalPurchasePriceTaxIncl = $attribute->unit_product_attribute->purchase_price_incl_per_unit ?? 0;
                        $totalPurchasePriceTaxIncl = round((float) $totalPurchasePriceTaxIncl * $cartProduct['productQuantityInUnits'] / $attribute->unit_product_attribute->amount, 2);
                        $totalPurchasePriceTaxExcl = $this->Product->getNetPrice($totalPurchasePriceTaxIncl, $purchasePriceTaxRate);
                        $totalPurchasePriceTaxExcl = round($totalPurchasePriceTaxExcl, 2);
                    } else {
                        $totalPurchasePriceTaxExcl = $attribute->purchase_price_product_attribute->price ?? 0;
                        $totalPurchasePriceTaxExcl = round((float) $totalPurchasePriceTaxExcl, 2);
                        $totalPurchasePriceTaxIncl = $this->Product->getGrossPrice($totalPurchasePriceTaxExcl, $purchasePriceTaxRate);
                        $totalPurchasePriceTaxIncl *= $amount;
                        $totalPurchasePriceTaxExcl *= $amount;
                    }
                    continue;
                }
            }
        } else {
            // main product
            if (!empty($product->unit_product) && $product->unit_product->price_per_unit_enabled) {
                $totalPurchasePriceTaxIncl = $product->unit_product->purchase_price_incl_per_unit ?? 0;
                $totalPurchasePriceTaxIncl = round((float) $totalPurchasePriceTaxIncl * $cartProduct['productQuantityInUnits'] / $product->unit_product->amount, 2);
                $totalPurchasePriceTaxExcl = $this->Product->getNetPrice($totalPurchasePriceTaxIncl, $purchasePriceTaxRate);
                $totalPurchasePriceTaxExcl = round($totalPurchasePriceTaxExcl, 2);
            } else {
                $totalPurchasePriceTaxExcl = $product->purchase_price_product->price ?? 0;
                $totalPurchasePriceTaxExcl = round((float) $totalPurchasePriceTaxExcl, 2);
                $totalPurchasePriceTaxIncl = $this->Product->getGrossPrice($totalPurchasePriceTaxExcl, $purchasePriceTaxRate);
                $totalPurchasePriceTaxIncl *= $amount;
                $totalPurchasePriceTaxExcl *= $amount;
            }
        }

        $unitPurchasePriceExcl = $this->Product->getNetPrice($totalPurchasePriceTaxIncl / $amount, $purchasePriceTaxRate);
        $unitPurchasePriceTaxAmount = $this->Product->getUnitTax($totalPurchasePriceTaxIncl, $unitPurchasePriceExcl, $amount);
        $totalPurchasePriceTaxAmount = $unitPurchasePriceTaxAmount * $amount;

        $result = [
            'tax_rate' => $purchasePriceTaxRate,
            'total_price_tax_incl' => $totalPurchasePriceTaxIncl,
            'total_price_tax_excl' => $totalPurchasePriceTaxExcl,
            'tax_unit_amount' => $unitPurchasePriceTaxAmount,
            'tax_total_amount' => $totalPurchasePriceTaxAmount,
        ];

        return $result;

    }

    private function saveOrderDetails($orderDetails2save)
    {
        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');
        $this->OrderDetail->saveMany(
            $this->OrderDetail->newEntities($orderDetails2save)
        );
    }

    private function saveStockAvailable($stockAvailable2saveData, $stockAvailable2saveConditions)
    {
        $this->Product = FactoryLocator::get('Table')->get('Products');
        $i = 0;
        foreach($stockAvailable2saveConditions as $condition) {
            $stockAvailableEntity = $this->Product->StockAvailables->find('all', [
                'conditions' => $condition,
            ])->first();
            $stockAvailableEntity->quantity = $stockAvailable2saveData[$i]['quantity'];
            $originalPrimaryKey = $this->Product->StockAvailables->getPrimaryKey();
            if ($condition['id_product_attribute'] > 0) {
                $this->Product->StockAvailables->setPrimaryKey('id_product_attribute');
            }
            $this->Product->StockAvailables->save($stockAvailableEntity);
            $this->Product->StockAvailables->setPrimaryKey($originalPrimaryKey);
            $this->Product->StockAvailables->updateQuantityForMainProduct($condition['id_product']);
            $i++;
        }
    }

    /**
     * @param $cartProducts
     * @return array
     */
    private function sendInstantOrderNotificationToManufacturers($cartProducts)
    {

        if (!$this->AppAuth->isOrderForDifferentCustomerMode() || Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            return [];
        }

        $manufacturers = [];
        foreach ($cartProducts as $cartProduct) {
            if ($cartProduct['isStockProduct']) {
                continue;
            }
            $manufacturers[$cartProduct['manufacturerId']][] = $cartProduct;
        }

        $this->Manufacturer = FactoryLocator::get('Table')->get('Manufacturers');
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
                $email->addToQueue();
            }
        }

        return $manufacturersThatReceivedInstantOrderNotification;
    }

    private function sendStockAvailableLimitReachedEmailToManufacturer($cartId)
    {
        $this->Cart = FactoryLocator::get('Table')->get('Carts');
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
                $email->addToQueue();
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
                    'notificationEditLink' => __('You_can_unsubscribe_this_email_<a href="{0}">in_the_settings_of_the_manufacturer</a>.', [Configure::read('App.fullBaseUrl') . Configure::read('app.slugHelper')->getManufacturerEditOptions($cartProduct->product->id_manufacturer)])
                ]);
                $email->addToQueue();
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
            'appAuth' => $this->AppAuth,
        ]);
        $email->addToQueue();
    }

    private function sendOrderCommentNotificationToPlatformOwner($pickupDayEntities)
    {
        if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') || Configure::read('appDb.FCS_APP_EMAIL') == '') {
            return false;
        }
        foreach($pickupDayEntities as $pickupDay) {
            if ($pickupDay['comment'] == '') {
                continue;
            }
            $formattedPickupDay = FrozenDate::createFromFormat(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), $pickupDay['pickup_day']);
            $formattedPickupDay = $formattedPickupDay->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('order_comment_notification');
            $email->setTo(Configure::read('appDb.FCS_APP_EMAIL'))
            ->setSubject(__('New_order_comment__was_written_by_{0}_for_{1}', [
                $this->AppAuth->getUsername(),
                $formattedPickupDay,
            ]))
            ->setViewVars([
                'comment' => $pickupDay['comment'],
                'formattedPickupDay' => $formattedPickupDay,
                'appAuth' => $this->AppAuth,
            ]);
            $email->addToQueue();
        }
    }

    /**
     * does not send email to inactive users (superadmins can place instant orders for inactive users!)
     */
    private function sendConfirmationEmailToCustomer($cart, $cartGroupedByPickupDay, $products, $pickupDayEntities)
    {

        if (!$this->AppAuth->user('active')) {
            return false;
        }

        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('order_successful');
        $email->setTo($this->AppAuth->getEmail())
        ->setSubject(__('Order_confirmation'))
        ->setViewVars([
            'cart' => $cartGroupedByPickupDay,
            'pickupDayEntities' => $pickupDayEntities,
            'appAuth' => $this->AppAuth,
            'originalLoggedCustomer' => $this->getController()->getRequest()->getSession()->check('Auth.originalLoggedCustomer') ? $this->getController()->getRequest()->getSession()->read('Auth.originalLoggedCustomer') : null
        ]);

        if (Configure::read('app.rightOfWithdrawalEnabled')) {
            $email->addAttachments([__('Filename_Right-of-withdrawal-information-and-form').'.pdf' => ['data' => $this->generateRightOfWithdrawalInformationAndForm($cart, $products), 'mimetype' => 'application/pdf']]);
        }

        if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            $email->addAttachments([__('Filename_Order-confirmation').'.pdf' => ['data' => $this->generateOrderConfirmation($cart), 'mimetype' => 'application/pdf']]);
        }
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

        $email->addToQueue();
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
        $this->Cart = FactoryLocator::get('Table')->get('Carts');
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $cart['Cart']->id_cart,
            ],
            'contain' => [
                'CartProducts.OrderDetails',
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
