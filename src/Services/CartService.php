<?php
declare(strict_types=1);

namespace App\Services;

use App\Services\DeliveryRhythmService;
use App\Services\HelloCash\HelloCashService;
use App\Services\Invoice\GenerateInvoiceToCustomerService;
use App\Services\PdfWriter\GeneralTermsAndConditionsPdfWriterService;
use App\Services\PdfWriter\InformationAboutRightOfWithdrawalPdfWriterService;
use App\Services\PdfWriter\OrderConfirmationPdfWriterService;
use App\Mailer\AppMailer;
use App\Model\Traits\CartValidatorTrait;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use App\Controller\Component\StringComponent;
use App\Model\Table\ActionLogsTable;
use App\Model\Table\AttributesTable;
use App\Model\Table\CartsTable;
use App\Model\Table\InvoicesTable;
use App\Model\Table\ManufacturersTable;
use App\Model\Table\OrderDetailsTable;
use App\Model\Table\PickupDaysTable;
use App\Model\Table\ProductsTable;
use Cake\Routing\Router;
use Cake\I18n\Date;
use App\Model\Entity\Customer;
use App\Model\Entity\Cart;
use App\Model\Entity\OrderDetail;

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

class CartService
{

    use CartValidatorTrait;

    protected ActionLogsTable $ActionLog;
    protected AttributesTable $Attribute;
    protected CartsTable $Cart;
    protected InvoicesTable $Invoice;
    protected ManufacturersTable $Manufacturer;
    protected ProductsTable $Product;
    protected PickupDaysTable $PickupDay;
    protected OrderDetailsTable $OrderDetail;

    private $identity;
    private $request;
    private $controller;

    public $cart = null;

    public function __construct($controller)
    {
        $this->identity = Router::getRequest()->getAttribute('identity');
        $this->request  = Router::getRequest();
        $this->controller = $controller;
    }

    public function getRequest()
    {
        return $this->request;
    }

    protected function getProductContain(): array
    {
        $contain = [
            'Manufacturers',
            'Manufacturers.AddressManufacturers',
            'StockAvailables',
            'ProductAttributes.StockAvailables',
            'ProductAttributes.ProductAttributeCombinations',
            'ProductAttributes.UnitProductAttributes',
            'Taxes',
            'UnitProducts'
        ];
        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $contain[] = 'PurchasePriceProducts.Taxes';
            $contain[] = 'ProductAttributes.PurchasePriceProductAttributes';
        }
        return $contain;
    }

    protected function saveCart($cart, $orderDetails2save, $stockAvailable2saveData, $stockAvailable2saveConditions, $customerSelectedPickupDay, $products)
    {

        $this->saveOrderDetails($orderDetails2save);
        $stockAvailablesTable = FactoryLocator::get('Table')->get('StockAvailables');
        $stockAvailablesTable->saveStockAvailable($stockAvailable2saveData, $stockAvailable2saveConditions);

        $manufacturersThatReceivedInstantOrderNotification = $this->sendInstantOrderNotificationToManufacturers($cart['CartProducts']);
        $this->sendStockAvailableLimitReachedEmailToManufacturer($cart['Cart']->id_cart);

        $pickupDayEntities = null;
        if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
            $pickupDayEntities = $cart['Cart']->pickup_day_entities;
            $this->Cart->PickupDayEntities->saveMany($pickupDayEntities);
        }

        $cart = $this->identity->getCart(); // to get attached order details
        $this->identity->setCart($cart);
        $cart['Cart'] = $this->identity->markCartAsSaved(); // modified timestamp is needed later on!

        $cartType = $this->identity->getCartType();
        $userIdForActionLog = $this->identity->getId();

        $orderCustomerService = new OrderCustomerService();
        switch($cartType) {
            case Cart::TYPE_WEEKLY_RHYTHM;
                $actionLogType = 'customer_order_finished';
                $message = __('Your_order_has_been_placed_succesfully.');
                $messageForActionLog = __('{0}_has_placed_a_new_order_({1}).', [$this->identity->name, Configure::read('app.numberHelper')->formatAsCurrency($this->identity->getProductSum())]);
                $cartGroupedByPickupDay = $this->Cart->getCartGroupedByPickupDay($cart, $customerSelectedPickupDay);
                $this->sendConfirmationEmailToCustomer($cart, $cartGroupedByPickupDay, $products, $pickupDayEntities);
                break;
            case Cart::TYPE_INSTANT_ORDER;
                $actionLogType = 'instant_order_added';
                $userIdForActionLog = $this->request->getSession()->read('OriginalIdentity')['id_customer'];
                if (empty($manufacturersThatReceivedInstantOrderNotification)) {
                    $message = __('Instant_order_({0})_successfully_placed_for_{1}.', [
                        Configure::read('app.numberHelper')->formatAsCurrency($this->identity->getProductSum()),
                        '<b>' . $this->identity->name . '</b>'
                    ]);
                } else {
                    $message = __('Instant_order_({0})_successfully_placed_for_{1}._The_following_manufacturers_were_notified:_{2}', [
                        Configure::read('app.numberHelper')->formatAsCurrency($this->identity->getProductSum()),
                        '<b>' . $this->identity->name . '</b>',
                        '<b>' . join(', ', $manufacturersThatReceivedInstantOrderNotification) . '</b>'
                    ]);
                }
                $message .= '<br />' . __('Pickup_day') . ': <b>' . Configure::read('app.timeHelper')->getDateFormattedWithWeekday(Configure::read('app.timeHelper')->getCurrentDay()).'</b>';
                $messageForActionLog = $message;
                $cartGroupedByPickupDay = $this->Cart->getCartGroupedByPickupDay($cart);
                if (!($orderCustomerService->isOrderForDifferentCustomerMode() && Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS'))) {
                    $this->sendConfirmationEmailToCustomer($cart, $cartGroupedByPickupDay, $products, []);
                }
                break;
            case Cart::TYPE_SELF_SERVICE;

                if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && Configure::read('app.selfServiceModeAutoGenerateInvoice')) {
                    $this->Invoice = FactoryLocator::get('Table')->get('Invoices');
                    $currentDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
                    $invoiceData = $this->Invoice->getDataForCustomerInvoice($this->identity->getId(), $currentDay);

                    if (!$orderCustomerService->isOrderForDifferentCustomerMode()) {
                        $paidInCash = 0;
                        if ($this->identity->isSelfServiceCustomer()) {
                            $paidInCash = 1;
                        }
                        if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
                            $helloCashService = new HelloCashService();
                            $responseObject = $helloCashService->generateInvoice($invoiceData, $currentDay, $paidInCash, false);
                            $invoiceId = $responseObject->invoice_id;
                            $invoiceRoute = Configure::read('app.slugHelper')->getHelloCashReceipt($invoiceId);
                        } else {
                            $invoiceToCustomerService = new GenerateInvoiceToCustomerService();
                            $newInvoice = $invoiceToCustomerService->run($invoiceData, $currentDay, $paidInCash);
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
                $messageForActionLog = __('{0}_has_placed_a_new_order_({1}).', [$this->identity->name, Configure::read('app.numberHelper')->formatAsCurrency($this->identity->getProductSum())]);

                if ($orderCustomerService->isOrderForDifferentCustomerMode()) {
                    $userIdForActionLog = $this->request->getSession()->read('OriginalIdentity')['id_customer'];
                    $messageForActionLog = __('{0}_has_placed_a_new_order_for_{1}_({2}).', [
                        $this->request->getSession()->read('OriginalIdentity')['name'],
                        '<b>' . $this->identity->name . '</b>',
                        Configure::read('app.numberHelper')->formatAsCurrency($this->identity->getProductSum()),
                    ]);
                } else {
                    $this->sendConfirmationEmailToCustomerSelfService($cart);
                }
                break;
        }

        if (isset($actionLogType) && isset($messageForActionLog) && isset($message)) {
            $this->ActionLog = FactoryLocator::get('Table')->get('ActionLogs');
            $this->ActionLog->customSave($actionLogType, $userIdForActionLog, $cart['Cart']->id_cart, 'carts', $messageForActionLog);
            $this->controller->Flash->success($message);
        }

        return $cart;
    }

    public function finish()
    {

        $cart = $this->identity->getCart();

        $this->Cart = FactoryLocator::get('Table')->get('Carts');
        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');
        $this->PickupDay = FactoryLocator::get('Table')->get('PickupDays');
        $this->Product = FactoryLocator::get('Table')->get('Products');

        // START check if no amount is 0
        $productWithAmount0Found = false;
        foreach ($this->identity->getProducts() as $cartProduct) {
            $ids = $this->Product->getProductIdAndAttributeId($cartProduct['productId']);
            if ($cartProduct['amount'] == 0) {
                $cartProductTable = FactoryLocator::get('Table')->get('CartProducts');
                $cartProductTable->remove($ids['productId'], $ids['attributeId'], $this->identity->getCartId());
                $productWithAmount0Found = true;
            }
        }

        if ($productWithAmount0Found) {
            $cart = $this->identity->getCart();
            $this->identity->setCart($cart);
        }
        // END check if no amount is 0

        $cartErrors = [];

        $orderCustomerService = new OrderCustomerService();
        if (Configure::read('app.htmlHelper')->paymentIsCashless() && !$orderCustomerService->isOrderForDifferentCustomerMode()) {
            if ($this->identity->getCreditBalanceMinusCurrentCartSum() < Configure::read('appDb.FCS_MINIMAL_CREDIT_BALANCE')) {
                $message = __('Please_add_credit_({0})_(minimal_credit_is_{1}).', [
                    '<b>'.Configure::read('app.numberHelper')->formatAsCurrency($this->identity->getCreditBalanceMinusCurrentCartSum()).'</b>',
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
        $productQuantityService = new ProductQuantityService();

        foreach ($this->identity->getProducts() as $cartProduct) {

            $ids = $this->Product->getProductIdAndAttributeId($cartProduct['productId']);
            $product = $this->Product->find('all',
                conditions: [
                    'Products.id_product' => $ids['productId']
                ],
                contain: $contain,
            )->first();

            $product->next_delivery_day = (new DeliveryRhythmService())->getNextDeliveryDayForProduct($product, $orderCustomerService);
            $products[] = $product;

            $stockAvailableQuantity = $product->stock_available->quantity;
            $stockAvailableAvailableQuantity = $stockAvailableQuantity;
            if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
                $stockAvailableAvailableQuantity = $product->stock_available->quantity - $product->stock_available->quantity_limit;
            }
    
            $orderedQuantityInUnits = $cartProduct['orderedQuantityInUnits'] ?? -1;
            $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($product, $product->unit_product);
            if ($isAmountBasedOnQuantityInUnits) {
                if ($orderedQuantityInUnits == -1 && !$orderCustomerService->isSelfServiceMode()) {
                    $orderedQuantityInUnits = $product->unit_product->quantity_in_units * $cartProduct['amount'];
                }
            }

            $message = $this->isAmountAvailableProduct(
                $product->is_stock_product,
                $product->manufacturer->stock_management_enabled,
                $product->stock_available->always_available,
                $ids['attributeId'],
                $stockAvailableAvailableQuantity,
                $isAmountBasedOnQuantityInUnits ? $orderedQuantityInUnits : $cartProduct['amount'],
                $product->name,
                $isAmountBasedOnQuantityInUnits ? $product->unit_product->name : '',
            );
            if ($message !== true) {
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
                $this->Attribute = FactoryLocator::get('Table')->get('Attributes');

                foreach ($product->product_attributes as $attribute) {
                    if ($attribute->id_product_attribute == $ids['attributeId']) {

                        $attributeIdFound = true;
                        $stockAvailableQuantity = $attribute->stock_available->quantity;
                        $stockAvailableAvailableQuantity = $stockAvailableQuantity;
                        if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
                            $stockAvailableAvailableQuantity = $attribute->stock_available->quantity - $attribute->stock_available->quantity_limit;
                        }

                        $attributeEntity = $this->Attribute->find('all',
                            conditions: [
                                'Attributes.id_attribute' => $attribute->product_attribute_combination->id_attribute,
                            ]
                        )->first();

                        $errorMessage = $this->isAmountAvailableAttribute(
                            $product->is_stock_product,
                            $product->manufacturer->stock_management_enabled,
                            $attribute->stock_available->always_available,
                            $stockAvailableAvailableQuantity,
                            $cartProduct['amount'],
                            $attributeEntity->name,
                            $product->name,
                        );
                        if ($errorMessage !== true) {
                            $message .= $errorMessage;
                            $message .= ' ' . __('Please_change_amount_or_delete_product_from_cart_to_place_order.');
                            $cartErrors[$cartProduct['productId']][] = $message;
                        }

                        // purchase price check for attribute
                        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                            if (!$this->Product->ProductAttributes->PurchasePriceProductAttributes->isPurchasePriceSet($attribute)) {
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

            $message = $this->isProductActive($product->active, $product->name);
            if ($message !== true) {
                $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            $message = $this->hasProductDeliveryRhythmTriggeredDeliveryBreak($orderCustomerService, $product->next_delivery_day, $product->name);
            if ($message !== true) {
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            $message = $this->isManufacturerActiveOrManufacturerHasDeliveryBreak(
                $orderCustomerService,
                $this->Product,
                $product->manufacturer->active,
                $product->manufacturer->no_delivery_days,
                $product->next_delivery_day,
                $product->manufacturer->stock_management_enabled,
                $product->is_stock_product,
                $product->name,
            );
            if ($message !== true) {
                $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            $message = $this->isProductBulkOrderStillPossible(
                $orderCustomerService,
                $product->manufacturer->stock_management_enabled,
                $product->is_stock_product,
                $product->delivery_rhythm_type,
                $product->delivery_rhythm_order_possible_until,
                $product->name,
            );
            if ($message !== true) {
                $message .= ' ' . __('Please_delete_product_from_cart_to_place_order.');
                $cartErrors[$cartProduct['productId']][] = $message;
            }

            $message = $this->isGlobalDeliveryBreakEnabled($orderCustomerService, $this->Product, $product->next_delivery_day, $product->name);
            if ($message !== true) {
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
                'order_state' => OrderDetail::STATE_OPEN,
                'id_customer' => $this->identity->getId(),
                'id_cart_product' => $cartProduct['cartProductId'],
                'pickup_day' => $cartProduct['pickupDay'],
                'deposit' => $cartProduct['deposit'],
                'product' => $product,
                'cartProductId' => $cartProduct['cartProductId'],
            ];

            $customerSelectedPickupDay = null;
            if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
                $customerSelectedPickupDay = h($this->request->getData('Carts.pickup_day'));
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
                    && in_array($this->identity->shopping_price, [Customer::PURCHASE_PRICE, Customer::SELLING_PRICE])
                    && isset($cartProduct['purchasePriceInclPerUnit'])
                    ) {
                    $orderDetail2save['order_detail_unit']['purchase_price_incl_per_unit'] = $cartProduct['purchasePriceInclPerUnit'];
                }
            }

            if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')
                && in_array($this->identity->shopping_price, [Customer::PURCHASE_PRICE, Customer::SELLING_PRICE])
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
                $productQuantityService = new ProductQuantityService();

                $unitObject = $product->unit_product;
                if ($attribute !== null) {
                    $unitObject = $attribute->unit_product_attribute;
                }
                if ($productQuantityService->isAmountBasedOnQuantityInUnits($product, $unitObject)) {
                    $newQuantity = $stockAvailableQuantity - ($unitObject->quantity_in_units * $cartProduct['amount']);
                }

                if ($productQuantityService->isAmountBasedOnQuantityInUnitsIncludingSelfServiceCheck($product, $unitObject) && isset($cartProduct['productQuantityInUnits']) && $cartProduct['productQuantityInUnits'] > 0) {
                    $newQuantity = $stockAvailableQuantity - $cartProduct['productQuantityInUnits'];
                }

                $stockAvailable2saveData[] = [
                    'quantity' => $newQuantity,
                ];
                $stockAvailable2saveConditions[] = [
                    'id_product' => $ids['productId'],
                    'id_product_attribute' => $ids['attributeId'],
                ];
            }

        }

        $this->controller->set('cartErrors', $cartErrors);
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
            $pickupEntities = $this->request->getData('Carts.pickup_day_entities');
            if (!empty($pickupEntities)) {
                foreach($pickupEntities as $pickupDay) {
                    $pickupDay['pickup_day'] = Date::createFromFormat(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), $pickupDay['pickup_day']);
                    $fixedPickupDayRequest[] = $pickupDay;
                }
                $this->controller->setRequest($this->request->withData('Carts.pickup_day_entities', $fixedPickupDayRequest));
                $this->sendOrderCommentNotificationToPlatformOwner($pickupEntities);
            }
        }

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $options['validate'] = 'customerCanSelectPickupDay';
        }

        if ($this->identity->getCartType() == Cart::TYPE_SELF_SERVICE
            && $orderCustomerService->isOrderForDifferentCustomerMode()) {
            $options['validate'] = 'selfServiceForDifferentCustomer';
        }

        $cart['Cart'] = $this->Cart->patchEntity(
            $cart['Cart'],
            $this->request->getData(),
            $options
        );

        $formErrors = false;
        if ($cart['Cart']->hasErrors()) {
            $formErrors = true;
        }
        $this->controller->set('cart', $cart['Cart']); // to show error messages in form (from validation)
        $this->controller->set('formErrors', $formErrors);

        if (!empty($cartErrors) || !empty($formErrors)) {
            $this->controller->Flash->error(__('Errors_occurred.'));
            return $cart;
        }

        $cart = $this->saveCart($cart, $orderDetails2save, $stockAvailable2saveData, $stockAvailable2saveConditions, $customerSelectedPickupDay ?? null, $products);
        return $cart;

    }

    private function prepareOrderDetailPurchasePrices($ids, $product, $cartProduct): array
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

    private function saveOrderDetails($orderDetails2save): void
    {
        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');
        $this->OrderDetail->saveMany(
            $this->OrderDetail->newEntities($orderDetails2save)
        );
    }

    private function sendInstantOrderNotificationToManufacturers($cartProducts): array
    {

        $orderCustomerService = new OrderCustomerService();

        if (!$orderCustomerService->isOrderForDifferentCustomerMode() || Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
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

            $manufacturer = $this->Manufacturer->find('all',
                conditions: [
                    'Manufacturers.id_manufacturer' => $manufacturerId,
                ],
                contain: [
                    'AddressManufacturers',
                ]
            )->first();

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
                    'identity' => $this->identity,
                    'cart' => ['CartProducts' => $cartProducts],
                    'originalLoggedCustomer' => $this->request->getSession()->read('OriginalIdentity'),
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
        $cart = $this->Cart->find('all',
            conditions: [
                'Carts.id_cart' => $cartId,
            ],
            contain: [
                'CartProducts.Products.Manufacturers.AddressManufacturers',
                'CartProducts.Products.Manufacturers.Customers.AddressCustomers',
                'CartProducts.Products.StockAvailables',
                'CartProducts.ProductAttributes.StockAvailables',
                'CartProducts.OrderDetails',
            ]
        )->first();

        foreach($cart->cart_products as $cartProduct) {
            $stockAvailable = $cartProduct->product->stock_available;
            if (!empty($cartProduct->product_attribute)) {
                $stockAvailable = $cartProduct->product_attribute->stock_available;
            }
            if (is_null($stockAvailable->sold_out_limit)) {
                continue;
            }

            $stockAvailableLimitReached = $stockAvailable->quantity <= $stockAvailable->sold_out_limit;

            $productQuantityService = new ProductQuantityService();
            $unitsTable = FactoryLocator::get('Table')->get('Units');
            $unitObject = $unitsTable->getUnitsObject($cartProduct->id_product, $cartProduct->id_product_attribute);
            $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($cartProduct->product, $unitObject);
            $unitName = !empty($unitObject) ? $unitObject->name : '';
            $formattedQuantity = $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $stockAvailable->quantity, $unitName);
            $formattedQuantityLimit = $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $stockAvailable->quantity_limit, $unitName);
            $formattedSoldOutLimit = $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $stockAvailable->sold_out_limit, $unitName);

            // send email to manufacturer
            if ($stockAvailableLimitReached && $cartProduct->product->manufacturer->stock_management_enabled && $cartProduct->product->is_stock_product && $cartProduct->product->manufacturer->send_product_sold_out_limit_reached_for_manufacturer) {
    
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('stock_available_limit_reached_notification');
                $email->setTo($cartProduct->product->manufacturer->address_manufacturer->email)
                ->setSubject(__('Product_{0}:_Only_{1}_units_on_stock', [
                    $cartProduct->order_detail->product_name,
                    $formattedQuantity,
                ]))
                ->setViewVars([
                    'identity' => $this->identity,
                    'greeting' => __('Hello') . ' ' . $cartProduct->product->manufacturer->address_manufacturer->firstname,
                    'productEditLink' => Configure::read('app.slugHelper')->getProductAdmin(null, $cartProduct->product->id_product),
                    'cartProduct' => $cartProduct,
                    'stockAvailable' => $stockAvailable,
                    'formattedQuantity' => $formattedQuantity,
                    'formattedQuantityLimit' => $formattedQuantityLimit,
                    'formattedSoldOutLimit' => $formattedSoldOutLimit,
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
                    $formattedQuantity,
                ]))
                ->setViewVars([
                    'identity' => $this->identity,
                    'greeting' => __('Hello') . ' ' . $cartProduct->product->manufacturer->customer->firstname,
                    'productEditLink' => Configure::read('app.slugHelper')->getProductAdmin($cartProduct->product->id_manufacturer, $cartProduct->product->id_product),
                    'cartProduct' => $cartProduct,
                    'stockAvailable' => $stockAvailable,
                    'formattedQuantity' => $formattedQuantity,
                    'formattedQuantityLimit' => $formattedQuantityLimit,
                    'formattedSoldOutLimit' => $formattedSoldOutLimit,
                    'manufacturer' => $cartProduct->product->manufacturer,
                    'showManufacturerName' => true,
                    'notificationEditLink' => __('You_can_unsubscribe_this_email_<a href="{0}">in_the_settings_of_the_manufacturer</a>.', [Configure::read('App.fullBaseUrl') . Configure::read('app.slugHelper')->getManufacturerEditOptions($cartProduct->product->id_manufacturer)])
                ]);
                $email->addToQueue();
            }

        }

    }

    private function sendConfirmationEmailToCustomerSelfService($cart)
    {
        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('order_successful_self_service');
        $email->setTo($this->identity->email)
        ->setSubject(__('Your_purchase'))
        ->setViewVars([
            'cart' => $this->Cart->getCartGroupedByPickupDay($cart),
            'identity' => $this->identity,
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
            $formattedPickupDay = Date::createFromFormat(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), $pickupDay['pickup_day']);
            $formattedPickupDay = $formattedPickupDay->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('order_comment_notification');
            $email->setTo(Configure::read('appDb.FCS_APP_EMAIL'))
            ->setSubject(__('New_order_comment__was_written_by_{0}_for_{1}', [
                $this->identity->name,
                $formattedPickupDay,
            ]))
            ->setViewVars([
                'comment' => $pickupDay['comment'],
                'formattedPickupDay' => $formattedPickupDay,
                'identity' => $this->identity,
            ]);
            $email->addToQueue();
        }
    }

    /**
     * does not send email to inactive users (superadmins can place instant orders for inactive users!)
     */
    private function sendConfirmationEmailToCustomer($cart, $cartGroupedByPickupDay, $products, $pickupDayEntities)
    {

        if (!$this->identity->active) {
            return false;
        }

        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('order_successful');
        $email->setTo($this->identity->email)
        ->setSubject(__('Order_confirmation'))
        ->setViewVars([
            'cart' => $cartGroupedByPickupDay,
            'pickupDayEntities' => $pickupDayEntities,
            'identity' => $this->identity,
            'originalLoggedCustomer' => $this->request->getSession()->check('OriginalIdentity') ? $this->request->getSession()->read('OriginalIdentity') : null
        ]);

        if (Configure::read('app.rightOfWithdrawalEnabled')) {
            $email->addAttachments([__('Filename_Right-of-withdrawal-information-and-form').'.pdf' => ['data' => $this->generateRightOfWithdrawalInformationAndForm($cart, $products), 'mimetype' => 'application/pdf']]);
        }

        if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            $email->addAttachments([__('Filename_Order-confirmation').'.pdf' => ['data' => $this->generateOrderConfirmation($cart), 'mimetype' => 'application/pdf']]);
        }
        if (Configure::read('app.generalTermsAndConditionsEnabled')) {
            $generalTermsAndConditionsFiles = [];
            $uniqueManufacturers = $this->identity->getUniqueManufacturers();
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

        $pdfWriter = new InformationAboutRightOfWithdrawalPdfWriterService();
        $pdfWriter->setData([
            'products' => $products,
            'identity' => $this->identity,
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
        $pdfWriter = new GeneralTermsAndConditionsPdfWriterService();
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
        $cart = $this->Cart->find('all',
            conditions: [
                'Carts.id_cart' => $cart['Cart']->id_cart,
            ],
            contain: [
                'CartProducts.OrderDetails',
                'CartProducts.Products',
                'CartProducts.Products.Manufacturers.AddressManufacturers',
            ],
        )->first();

        foreach ($cart->cart_products as $cartProduct) {
            $manufacturers[$cartProduct->product->id_manufacturer] = [
                'CartProducts' => $cart->cart_products,
                'Manufacturer' => $cartProduct->product->manufacturer
            ];
        }

        $pdfWriter = new OrderConfirmationPdfWriterService();
        $pdfWriter->setData([
            'identity' => $this->identity,
            'cart' => $cart,
            'manufacturers' => $manufacturers,
        ]);
        return $pdfWriter->writeAttachment();
    }

}
