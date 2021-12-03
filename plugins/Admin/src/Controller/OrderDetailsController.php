<?php

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use App\Lib\Error\Exception\InvalidParameterException;
use App\Lib\PdfWriter\OrderDetailsPdfWriter;
use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Utility\Hash;
use App\Model\Table\OrderDetailsTable;

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
class OrderDetailsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'profit';
            case 'editPurchasePrice';
                return Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') && $this->AppAuth->isSuperadmin();
                break;
            case 'changeTaxOfInvoicedOrderDetail';
                return $this->AppAuth->isSuperadmin();
                break;
            case 'addFeedback';
                if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                    return true;
                }
                if ($this->AppAuth->isCustomer()) {
                    $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
                    $orderDetail = $this->OrderDetail->find('all', [
                        'conditions' => [
                            'OrderDetails.id_order_detail' => $this->getRequest()->getData('orderDetailId')
                        ]
                    ])->first();
                    if (!empty($orderDetail)) {
                        if ($orderDetail->id_customer == $this->AppAuth->getUserId()) {
                            return true;
                        }
                    }
                }
                $this->sendAjaxError(new ForbiddenException(ACCESS_DENIED_MESSAGE));
                return false;
                break;
            case 'delete':
            case 'editProductPrice':
            case 'editProductAmount':
            case 'editProductQuantity':
            case 'editCustomer':
                if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                    return true;
                }
                if ($this->getRequest()->getParam('action') == 'editCustomer' && ($this->AppAuth->isManufacturer() || $this->AppAuth->isCustomer())) {
                    return false;
                }
                /*
                 * START customer/manufacturer OWNER check
                 * param orderDetailId / orderDetailIds is passed via ajaxCall
                 */
                if (!empty($this->getRequest()->getData('orderDetailIds'))) {
                    $accessAllowed = false;
                    foreach ($this->getRequest()->getData('orderDetailIds') as $orderDetailId) {
                        $accessAllowed |= $this->checkOrderDetailIdAccess($orderDetailId);
                    }
                    return $accessAllowed;
                }
                if (!empty($this->getRequest()->getData('orderDetailId'))) {
                    return $this->checkOrderDetailIdAccess($this->getRequest()->getData('orderDetailId'));
                }
                return false;
            default:
                return parent::isAuthorized($user);
                break;
        }
    }

    /**
     * @param int $orderDetailId
     * @return boolean
     */
    private function checkOrderDetailIdAccess($orderDetailId)
    {
        if ($this->AppAuth->isCustomer() || $this->AppAuth->isManufacturer()) {
            $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
            $orderDetail = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail' => $orderDetailId
                ],
                'contain' => [
                    'Products'
                ]
            ])->first();
            if (!empty($orderDetail)) {
                if ($this->AppAuth->isManufacturer() && $orderDetail->product->id_manufacturer == $this->AppAuth->getManufacturerId()) {
                    return true;
                }
                if ($this->AppAuth->isCustomer() && !Configure::read('isCustomerAllowedToModifyOwnOrders') && $orderDetail->id_customer == $this->AppAuth->getUserId()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string $manufacturerNameField
     * @return string
     */
    private function getSortFieldForGroupedOrderDetails($manufacturerNameField)
    {
        $sortField = 'name';
        $sortMatches = [
            'Manufacturers.name' => $manufacturerNameField,
            'sum_price' => 'sum_price',
            'sum_amount' => 'sum_amount',
            'sum_deposit' => 'sum_deposit'
        ];
        if (!empty($this->getRequest()->getQuery('sort')) && isset($sortMatches[$this->getRequest()->getQuery('sort')])) {
            $sortField = $sortMatches[$this->getRequest()->getQuery('sort')];
        }
        return $sortField;
    }

    /**
     * @return string
     */
    private function getSortDirectionForGroupedOrderDetails()
    {
        $sortDirection = 'ASC';
        if (!empty($this->getRequest()->getQuery('direction') && in_array($this->getRequest()->getQuery('direction'), ['asc', 'desc']))) {
            $sortDirection = h($this->getRequest()->getQuery('direction'));
        }
        return $sortDirection;
    }

    /**
     * Helper method if invoices was already generated but tax was wrong
     *
     * 1) re-open all order details of the wrong invoice using config/sql/_helper/change-order-state-of-order-details.sql
     * 2) run this script by calling it via url (as superadmin)
     * 3) remove appropriate record of manufacturer in fcs_invoices
     * 4) re-send invoice using `bin/cake SendInvoices yyyy-mm-dd` (month is one month later than order details)
     *
     * @param int $orderDetailId
     * @param int $newTaxRate
     */
    public function changeTaxOfInvoicedOrderDetail($orderDetailId, $newTaxRate)
    {

        $this->RequestHandler->renderAs($this, 'json');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Customers',
                'Products.Manufacturers',
                'OrderDetailUnits'
            ]
        ])->first();

        $patchedEntity = $this->OrderDetail->patchEntity(
            $oldOrderDetail,
            ['tax_rate' => $newTaxRate]
        );
        $orderDetailWithNewTax = $this->OrderDetail->save($patchedEntity);

        $orderDetailWithChangedPrice = $this->changeOrderDetailPriceDepositTax($orderDetailWithNewTax, $orderDetailWithNewTax->total_price_tax_incl, $orderDetailWithNewTax->product_amount);

        $this->set([
            'status' => 0,
            'orderDetailWithChangedPrice' => $orderDetailWithChangedPrice,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'orderDetailWithChangedPrice']);

    }

    protected function initOrderForDifferentCustomer($customerId)
    {
        if (! $customerId) {
            throw new RecordNotFoundException('customerId not passed');
        }

        $this->Customer = $this->getTableLocator()->get('Customers');
        $orderCustomer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
            'contain' => [
                'AddressCustomers'
            ]
        ])->first();

        if (! empty($orderCustomer)) {
            $this->getRequest()->getSession()->write('Auth.orderCustomer', $orderCustomer);
        } else {
            $this->Flash->error(__d('admin', 'No_member_found_with_id_{0}.', [$customerId]));
        }
    }

    public function initInstantOrder($customerId)
    {
        $this->initOrderForDifferentCustomer($customerId);
        $this->redirect('/');
    }

    public function initSelfServiceOrder($customerId)
    {
        $this->initOrderForDifferentCustomer($customerId);
        $this->redirect(Configure::read('app.slugHelper')->getSelfService());
    }

    public function iframeInstantOrder()
    {
        $this->set('title_for_layout', __d('admin', 'Instant_order'));
    }

    public function iframeSelfServiceOrder()
    {
        $this->set('title_for_layout', __d('admin', 'Self_service_order'));
    }

    public function orderDetailsAsPdf()
    {

        $pickupDay = [$this->getRequest()->getQuery('pickupDay')];
        $order = $this->getRequest()->getQuery('order') ?? null;

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $odParams = $this->OrderDetail->getOrderDetailParams($this->AppAuth, '', '', '', $pickupDay, '', '');
        $this->OrderDetail->getAssociation('PickupDayEntities')->setConditions([
            'PickupDayEntities.pickup_day' => Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[0])
        ]);
        $orderDetails = $this->OrderDetail->find('all', [
            'conditions' => $odParams['conditions'],
            'contain' => $odParams['contain'],
        ])->toArray();

        $storageLocation = [];
        $customerName = [];
        $manufacturerName = [];
        $productName = [];
        foreach($orderDetails as $orderDetail) {
            $storageLocationValue = 0;
            if (!is_null($order)) {
                $storageLocationValue = $orderDetail->product->storage_location->rank;
            }
            $storageLocation[] = $storageLocationValue;
            $customerName[] = StringComponent::slugify($orderDetail->customer->name);
            $manufacturerName[] = StringComponent::slugify($orderDetail->product->manufacturer->name);
            $productName[] = StringComponent::slugify($orderDetail->product_name);
        }
        array_multisort(
            $storageLocation, SORT_ASC,
            $customerName, SORT_ASC,
            $manufacturerName, SORT_ASC,
            $productName, SORT_ASC,
            $orderDetails,
        );

        $preparedOrderDetails = [];
        foreach($orderDetails as $orderDetail) {
            if (!isset($preparedOrderDetails[$orderDetail->id_customer])) {
                $preparedOrderDetails[$orderDetail->id_customer] = [];
            }
            $preparedOrderDetails[$orderDetail->id_customer][] = $orderDetail;
        }

        if (Configure::read('app.showTaxSumTableOnOrderDetailPdf')) {

            $taxRates = [];

            $depositVatRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'));
            $depositVatRate = Configure::read('app.numberHelper')->formatTaxRate($depositVatRate);

            foreach($preparedOrderDetails as $customerId => $orderDetails) {

                $taxRates[$customerId] = $this->OrderDetail->getTaxSums($orderDetails);
                $defaultArray = [
                    'sum_price_excl' => 0,
                    'sum_tax' => 0,
                    'sum_price_incl' => 0,
                ];

                foreach($orderDetails as $orderDetail) {
                    if (!isset($taxRates[$customerId][$depositVatRate])) {
                        $taxRates[$customerId][$depositVatRate] = $defaultArray;
                    }
                    $taxRates[$customerId][$depositVatRate]['sum_price_excl'] += $this->OrderDetail->getDepositNet($orderDetail->deposit, $orderDetail->product_amount);
                    $taxRates[$customerId][$depositVatRate]['sum_tax'] += $this->OrderDetail->getDepositTax($orderDetail->deposit, $orderDetail->product_amount);
                    $taxRates[$customerId][$depositVatRate]['sum_price_incl'] += $orderDetail->deposit;
                }

                $taxRates[$customerId] = $this->OrderDetail->clearZeroArray($taxRates[$customerId]);
                ksort($taxRates[$customerId]);

            }

        }

        $pdfWriter = new OrderDetailsPdfWriter();
        $pdfWriter->setData([
            'orderDetails' => $preparedOrderDetails,
            'appAuth' => $this->AppAuth,
            'order' => $order,
            'taxRates' => isset($taxRates) ? $taxRates : null,
        ]);
        die($pdfWriter->writeInline());
    }

    public function editPurchasePrice($orderDetailId)
    {

        $this->set('title_for_layout', __d('admin', 'Edit_purchase_price'));

        $this->Tax = $this->getTableLocator()->get('Taxes');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown(true));

        $this->setFormReferer();

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Customers',
                'OrderDetailUnits',
                'OrderDetailPurchasePrices',
                'Products.Manufacturers',
            ]
        ])->first();

        if (empty($orderDetail)) {
            throw new RecordNotFoundException('order detail not found');
        }

        if (empty($this->getRequest()->getData())) {
            $orderDetail->order_detail_purchase_price->total_price_tax_excl = round($orderDetail->order_detail_purchase_price->total_price_tax_excl, 2);
            $this->set('orderDetail', $orderDetail);
            return;
        }

        $orderDetail = $this->OrderDetail->patchEntity(
            $orderDetail,
            $this->getRequest()->getData(),
            [
                'associated' => [
                    'OrderDetailPurchasePrices' => [
                        'validate' => 'edit',
                    ],
                ],
            ],
        );

        if ($orderDetail->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('orderDetail', $orderDetail);
        } else {
            $this->Product = $this->getTableLocator()->get('Products');

            $grossPrice = $this->Product->getGrossPrice(
                round($orderDetail->order_detail_purchase_price->total_price_tax_excl, 2),
                $orderDetail->order_detail_purchase_price->tax_rate,
            );

            $unitPriceExcl = round($orderDetail->order_detail_purchase_price->total_price_tax_excl, 2) / $orderDetail->product_amount;
            $unitTaxAmount = $this->Product->getUnitTax(
                $grossPrice,
                $unitPriceExcl,
                $orderDetail->product_amount,
            );

            $totalTaxAmount = $unitTaxAmount * $orderDetail->product_amount;

            $orderDetail->order_detail_purchase_price->tax_unit_amount = $unitTaxAmount;
            $orderDetail->order_detail_purchase_price->tax_total_amount = $totalTaxAmount;
            $orderDetail->order_detail_purchase_price->total_price_tax_incl = $grossPrice;

            $orderDetail = $this->OrderDetail->save(
                $orderDetail,
                [
                    'associated' => [
                        'OrderDetailPurchasePrices'
                    ],
                ],
            );

            $this->Flash->success(__d('admin', 'Purchase_price_has_been_saved_successfully'));
            $this->getRequest()->getSession()->write('highlightedRowId', $orderDetail->id_order_detail);

            $this->redirect($this->getPreparedReferer());
        }

        $this->set('orderDetail', $orderDetail);
    }

    public function profit()
    {

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfThisMonth();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getLastDayOfThisMonth();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        }
        $this->set('customerId', $customerId);

        $manufacturerId = '';
        if (! empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        }
        $this->set('manufacturerId', $manufacturerId);

        $productId = '';
        if (! empty($this->getRequest()->getQuery('productId'))) {
            $productId = h($this->getRequest()->getQuery('productId'));
        }
        $this->set('productId', $productId);

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->find('all', [
            'contain' => [
                'Customers',
                'OrderDetailPurchasePrices',
                'OrderDetailUnits',
                'Products.Manufacturers',
            ],
        ]);

        $orderDetails->where(function (QueryExpression $exp) use ($dateFrom, $dateTo) {
            $exp->gte('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom));
            $exp->lte('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo));
            $exp->gt('OrderDetails.id_customer', 0);
            return $exp;
        });

        if ($customerId != '') {
            $orderDetails->where(['OrderDetails.id_customer' => $customerId]);
        }

        if ($manufacturerId != '') {
            $orderDetails->where(['Products.id_manufacturer' => $manufacturerId]);
        }

        if ($productId != '') {
            $orderDetails->where(['OrderDetails.product_id' => $productId]);
        }

        $orderDetails = $this->paginate($orderDetails, [
            'sortableFields' => [
                'OrderDetails.product_amount',
                'OrderDetails.product_name',
                'OrderDetails.pickup_day',
                'Customers.' . Configure::read('app.customerMainNamePart'),
                'OrderDetailUnits.product_quantity_in_units',
                'OrderDetails.total_price_tax_excl',
                'OrderDetailPurchasePrices.total_price_tax_excl',
            ],
            'order' => [
                'OrderDetails.pickup_day' => 'DESC',
                'OrderDetails.created' => 'ASC',
            ],
        ])->toArray();

        $sumSellingPrice = 0;
        $sumPurchasePrice = 0;
        $sumProfit = 0;
        $i = 0;
        foreach($orderDetails as $orderDetail) {
            $orderDetails[$i]->purchase_price_ok = false;
            if (!empty($orderDetail->order_detail_purchase_price)) {
                $roundedPurchasePrice = round($orderDetail->order_detail_purchase_price->total_price_tax_excl, 2);
                $roundedSellingPrice = round($orderDetail->total_price_tax_excl, 2);
                $roundedProfit = round($roundedSellingPrice - $roundedPurchasePrice, 2);
                if ($roundedPurchasePrice > 0) {
                    $orderDetails[$i]->purchase_price_ok = true;
                    $orderDetails[$i]->order_detail_purchase_price->total_price_tax_excl = $roundedPurchasePrice;
                    $orderDetails[$i]->total_price_tax_excl = $roundedSellingPrice;
                    $orderDetails[$i]->profit = $roundedProfit;
                    $sumProfit += $roundedProfit;
                    $sumPurchasePrice += $roundedPurchasePrice;
                    $sumSellingPrice += $roundedSellingPrice;
                }
            }
            $i++;
        }
        $this->set('orderDetails', $orderDetails);
        $this->set('sums', [
            'purchasePrice' => $sumPurchasePrice,
            'sellingPrice' => $sumSellingPrice,
            'profit' => $sumProfit,
        ]);

        $this->set('title_for_layout', __d('admin', 'Profit'));

        $this->set('manufacturersForDropdown', $this->OrderDetail->Products->Manufacturers->getForDropdown());

    }

    public function index()
    {

        if (!empty($this->getRequest()->getQuery('message'))) {
            $this->Flash->success(htmlspecialchars_decode($this->getRequest()->getQuery('message')));
            $this->redirect($this->referer());
        }

        // for filter from action logs page
        $orderDetailId = '';
        if (! empty($this->getRequest()->getQuery('orderDetailId'))) {
            $orderDetailId = h($this->getRequest()->getQuery('orderDetailId'));
        }

        $pickupDay = [];
        if ($orderDetailId == '') {
            if (in_array('pickupDay', array_keys($this->getRequest()->getQueryParams()))) {
                $pickupDay = h($this->getRequest()->getQuery('pickupDay'));
                if ($pickupDay == '') {
                    throw new InvalidParameterException('parameter pickupDay must not be empty');
                }
                $explodedPickupDay = explode(',', $pickupDay[0]); // param can be passed comma separated
                if (count($explodedPickupDay) == 2) {
                    $pickupDay = $explodedPickupDay;
                }
            } else {
                // default values
                if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
                    $pickupDay[0] = Configure::read('app.timeHelper')->formatToDateShort(Configure::read('app.timeHelper')->getCurrentDateForDatabase());
                } else {
                    $pickupDay[0] = Configure::read('app.timeHelper')->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
                }
            }
        }

        $pickupDay = Configure::read('app.timeHelper')->sortArrayByDate($pickupDay);
        $this->set('pickupDay', $pickupDay);

        $manufacturerId = '';
        if (! empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        }
        $this->set('manufacturerId', $manufacturerId);

        $deposit = '';
        if (! empty($this->getRequest()->getQuery('deposit'))) {
            $deposit = h($this->getRequest()->getQuery('deposit'));
        }
        $this->set('deposit', $deposit);

        $productId = '';
        if (! empty($this->getRequest()->getQuery('productId'))) {
            $productId = h($this->getRequest()->getQuery('productId'));
        }
        $this->set('productId', $productId);

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        }
        $this->set('customerId', $customerId);

        $groupBy = '';
        if (! empty($this->getRequest()->getQuery('groupBy'))) {
            $groupBy = h($this->getRequest()->getQuery('groupBy'));
        }
        if ($this->AppAuth->isManufacturer() && $groupBy != 'product') {
            $groupBy = '';
        }

        $this->set('groupBy', $groupBy);

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $odParams = $this->OrderDetail->getOrderDetailParams($this->AppAuth, $manufacturerId, $productId, $customerId, $pickupDay, $orderDetailId, $deposit);

        $contain = $odParams['contain'];
        if (($groupBy == 'customer' || $groupBy == '') && count($pickupDay) == 1) {
            $this->OrderDetail->getAssociation('PickupDayEntities')->setConditions([
                'PickupDayEntities.pickup_day' => Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[0])
            ]);
            $contain[] = 'PickupDayEntities';
        }

        $group = null;

        switch($groupBy) {
            // be aware of sql-mode ONLY_FULL_GROUP_BY!
            case 'customer':
                $group[] = 'OrderDetails.id_customer';
                $group[] = 'Customers.firstname';
                $group[] = 'Customers.lastname';
                $group[] = 'Customers.email';
                if (count($pickupDay) == 1) {
                    $group[] = 'PickupDayEntities.comment';
                    $group[] = 'PickupDayEntities.products_picked_up';
                }
                break;
            case 'manufacturer':
                $group[] = 'Products.id_manufacturer';
                $group[] = 'Manufacturers.name';
                break;
            case 'product':
                $group[] = 'OrderDetails.product_id';
                $group[] = 'Products.name';
                $group[] = 'Products.id_manufacturer';
                $group[] = 'Manufacturers.name';
                break;
        }

        $query = $this->OrderDetail->find('all', [
            'conditions' => $odParams['conditions'],
            'contain' => $contain,
            'group' => $group
        ]);

        switch($groupBy) {
            case 'customer':
                $query = $this->addSelectGroupFields($query);
                $query->select(['OrderDetails.id_customer']);
                $query->select(['Customers.firstname', 'Customers.lastname', 'Customers.email']);
                if (count($pickupDay) == 1) {
                    $query->select(['PickupDayEntities.comment', 'PickupDayEntities.products_picked_up']);
                }
                break;
            case 'manufacturer':
                $query = $this->addSelectGroupFields($query);
                $query->select(['Products.id_manufacturer']);
                $query->select(['Manufacturers.name']);
                break;
            case 'product':
                $query = $this->addSelectGroupFields($query);
                $query->select(['OrderDetails.product_id']);
                $query->select(['Products.name', 'Products.id_manufacturer']);
                $query->select(['Manufacturers.name']);
                break;
        }

        $orderDetails = $this->paginate($query, [
            'sortableFields' => [
                'OrderDetails.product_amount',
                'OrderDetails.product_name',
                'OrderDetails.total_price_tax_incl',
                'OrderDetails.deposit',
                'OrderDetails.order_state',
                'OrderDetails.pickup_day',
                'Manufacturers.name',
                'Customers.' . Configure::read('app.customerMainNamePart'),
                'OrderDetailUnits.product_quantity_in_units',
                'sum_price',
                'sum_amount',
                'sum_deposit',
                'Products.name'
            ]
        ])->toArray();

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $orderDetails = $this->prepareGroupedOrderDetails($orderDetails, $groupBy, $pickupDay);
        $this->set('orderDetails', $orderDetails);

        $timebasedCurrencyOrderDetailInList = false;
        $sums = [
            'records_count' => 0,
            'amount' => 0,
            'price' => 0,
            'deposit' => 0,
            'units' => [
                'g' => 0,
                'kg' => 0,
                'l' => 0,
            ],
            'reduced_price' => 0
        ];

        foreach($orderDetails as $orderDetail) {
            $sums['records_count']++;
            if ($groupBy == '') {
                $sums['price'] += $orderDetail->total_price_tax_incl;
                $sums['amount'] += $orderDetail->product_amount;
                $sums['deposit'] += $orderDetail->deposit;
            } else {
                $sums['price'] += $orderDetail['sum_price'];
                $sums['amount'] += $orderDetail['sum_amount'];
                if ($groupBy == 'manufacturer') {
                    $sums['reduced_price'] += $orderDetail['reduced_price'];
                }
                $sums['deposit'] += $orderDetail['sum_deposit'];
            }
            if (!empty($orderDetail->order_detail_unit)) {
                $sums['units'][$orderDetail->order_detail_unit->unit_name] += $orderDetail->order_detail_unit->product_quantity_in_units;
            }
            if (!empty($orderDetail->timebased_currency_order_detail) || !empty($orderDetail['timebased_currency_order_detail_seconds_sum'])) {
                $timebasedCurrencyOrderDetailInList = true;
            }
        }
        $this->set('timebasedCurrencyOrderDetailInList', $timebasedCurrencyOrderDetailInList);
        $this->set('sums', $sums);

        // extract all email addresses for button
        $emailAddresses = [];
        if ($groupBy == '') {
            $emailAddresses = $query->all()->extract('customer.email')->toArray();
        }
        if ($groupBy == 'customer') {
            $emailAddresses = Hash::extract($orderDetails, '{n}.email');
        }
        $emailAddresses = array_unique($emailAddresses);
        $this->set('emailAddresses', $emailAddresses);

        $groupByForDropdown = [
            'product' => __d('admin', 'Group_by_product')
        ];
        if (!$this->AppAuth->isManufacturer()) {
            $groupByForDropdown['customer'] = __d('admin', 'Group_by_member');
            $groupByForDropdown['manufacturer'] = __d('admin', 'Group_by_manufacturer');
        }
        $this->set('groupByForDropdown', $groupByForDropdown);
        $this->set('manufacturersForDropdown', $this->OrderDetail->Products->Manufacturers->getForDropdown());

        $this->set('title_for_layout', __d('admin', 'Orders'));
    }

    private function addSelectGroupFields($query) {
        $query->select([
            'sum_price' => $query->func()->sum('OrderDetails.total_price_tax_incl'),
            'sum_amount' => $query->func()->sum('OrderDetails.product_amount'),
            'sum_deposit' => $query->func()->sum('OrderDetails.deposit'),
            'order_detail_count' => $query->func()->count('OrderDetails.id_order_detail'),
            'timebased_currency_order_detail_seconds_sum' => $query->func()->sum('TimebasedCurrencyOrderDetails.seconds')
        ]);
        return $query;
    }

    private function prepareGroupedOrderDetails($orderDetails, $groupBy)
    {

        switch ($groupBy) {
            case 'customer':
                $preparedOrderDetails = $this->OrderDetail->prepareOrderDetailsGroupedByCustomer($orderDetails);
                if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
                    $this->Invoice = $this->getTableLocator()->get('Invoices');
                    foreach($preparedOrderDetails as &$orderDetail) {
                        $orderDetail['invoiceData'] = $this->Invoice->getDataForCustomerInvoice($orderDetail['customer_id'], Configure::read('app.timeHelper')->getCurrentDateForDatabase());
                        $orderDetail['lastInvoices'] = $this->Invoice->getLastInvoicesForCustomer($orderDetail['customer_id']);
                    }
                }
                $sortField = $this->getSortFieldForGroupedOrderDetails('name');
                break;
            case 'manufacturer':
                $preparedOrderDetails = $this->OrderDetail->prepareOrderDetailsGroupedByManufacturer($orderDetails);
                $sortField = $this->getSortFieldForGroupedOrderDetails('name');
                break;
            case 'product':
                $preparedOrderDetails = $this->OrderDetail->prepareOrderDetailsGroupedByProduct($orderDetails);
                $sortField = $this->getSortFieldForGroupedOrderDetails('manufacturer_name');
                break;
            default:
                $deliveryDay = [];
                $manufacturerName = [];
                $productName = [];
                $customerName = [];
                foreach ($orderDetails as $orderDetail) {
                    $orderDetail->quantityInUnitsNotYetChanged = false;
                    if (!empty($orderDetail->order_detail_unit)) {
                        // quantity comparison can be removed in v4. it was replaced by mark_as_saved in v3.1. default value needs to be set to true then
                        if (round($orderDetail->order_detail_unit->product_quantity_in_units, 3) == round($orderDetail->order_detail_unit->quantity_in_units * $orderDetail->product_amount, 3)) {
                            $orderDetail->quantityInUnitsNotYetChanged = true;
                        }
                        if ($orderDetail->order_detail_unit->mark_as_saved) {
                            $orderDetail->quantityInUnitsNotYetChanged = false;
                        }
                    }
                    $deliveryDay[] = $orderDetail->pickup_day;
                    $manufacturerName[] = StringComponent::slugify($orderDetail->product->manufacturer->name);
                    $productName[] = StringComponent::slugify($orderDetail->product_name);
                    if (!empty($orderDetail->customer)) {
                        $customerName[] = StringComponent::slugify($orderDetail->customer->name);
                    } else {
                        $customerName[] = '';
                    }
                }
                if (!in_array('sort', array_keys($this->getRequest()->getQueryParams()))) {
                    array_multisort(
                        $deliveryDay, SORT_ASC,
                        $manufacturerName, SORT_ASC,
                        $productName, SORT_ASC,
                        $customerName, SORT_ASC,
                        $orderDetails
                    );
                }
                break;
        }

        if (isset($sortField)) {
            $sortDirection = $this->getSortDirectionForGroupedOrderDetails();
            $orderDetails = Hash::sort($preparedOrderDetails, '{n}.' . $sortField, $sortDirection);
        }

        if ($groupBy == 'customer') {
            $orderDetails = Hash::sort($orderDetails, '{n}.products_picked_up', 'ASC');
        }

        return $orderDetails;

    }

    public function editCustomer()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $customerId = (int) $this->getRequest()->getData('customerId');
        $editCustomerReason = strip_tags(html_entity_decode($this->getRequest()->getData('editCustomerReason')));
        $amount = (int) $this->getRequest()->getData('amount');

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Customers',
                'Products.Manufacturers',
                'OrderDetailUnits',
                'OrderDetailPurchasePrices',
            ]
        ])->first();

        $this->Customer = $this->getTableLocator()->get('Customers');
        $newCustomer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ]
        ])->first();

        $errors = [];
        if (empty($newCustomer)) {
            $errors[] = __d('admin', 'Please_select_a_new_member.');
        } else {
            if ($newCustomer->id_customer == $oldOrderDetail->id_customer) {
                $errors[] = __d('admin', 'The_same_member_must_not_be_selected.');
            }
        }

        if ($amount > $oldOrderDetail->product_amount || $amount < 1) {
            $errors[] = __d('admin', 'The_amount_is_not_valid.');
        }

        if ($editCustomerReason == '') {
            $errors[] = __d('admin', 'The_reason_for_changing_the_member_is_mandatory.');
        }

        if (!empty($errors)) {
            $this->set([
                'status' => 0,
                'msg' => join('<br />', $errors),
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $originalProductAmount = $oldOrderDetail->product_amount;
        $newAmountForOldOrderDetail = $oldOrderDetail->product_amount - $amount;

        if ($newAmountForOldOrderDetail > 0) {

            // order detail needs to be split up

            // 1) modify old order detail
            $pricePerUnit = $oldOrderDetail->total_price_tax_incl / $oldOrderDetail->product_amount;
            $productPrice = $pricePerUnit * $newAmountForOldOrderDetail;

            $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
            $this->changeOrderDetailPriceDepositTax($object, $productPrice, $newAmountForOldOrderDetail);

            if (!empty($object->order_detail_unit)) {
                $productQuantity = $oldOrderDetail->order_detail_unit->product_quantity_in_units / $originalProductAmount * $newAmountForOldOrderDetail;
                $this->changeOrderDetailQuantity($object->order_detail_unit, $productQuantity);
            }

            if (!empty($object->order_detail_purchase_price)) {
                $productPurchasePrice = $oldOrderDetail->order_detail_purchase_price->total_price_tax_incl / $oldOrderDetail->product_amount * $newAmountForOldOrderDetail;
                $this->changeOrderDetailPurchasePrice($object->order_detail_purchase_price, $productPurchasePrice, $newAmountForOldOrderDetail);
            }


            // 2) copy old order detail and modify it
            $newEntity = $oldOrderDetail;
            $newEntity->setNew(true);
            $newEntity->id_order_detail = null;
            $newEntity->id_customer = $customerId;
            $savedEntity = $this->OrderDetail->save($newEntity, [
                'associated' => false
            ]);

            $productPrice = $pricePerUnit * $amount;
            $this->changeOrderDetailPriceDepositTax($savedEntity, $productPrice, $amount);

            if (!empty($newEntity->order_detail_unit)) {
                $newEntity->order_detail_unit->id_order_detail = $savedEntity->id_order_detail;
                $newEntity->order_detail_unit->setNew(true);
                $newOrderDetailUnitEntity = $this->OrderDetail->OrderDetailUnits->save($newEntity->order_detail_unit);
                $savedEntity->order_detail_unit = $newOrderDetailUnitEntity;
                $productQuantity = $savedEntity->order_detail_unit->product_quantity_in_units / $originalProductAmount * $amount;
                $this->changeOrderDetailQuantity($savedEntity->order_detail_unit, $productQuantity);
            }

            if (!empty($newEntity->order_detail_purchase_price)) {
                $newEntity->order_detail_purchase_price->id_order_detail = $savedEntity->id_order_detail;
                $newEntity->order_detail_purchase_price->setNew(true);
                $newOrderDetailPurchasePriceEntity = $this->OrderDetail->OrderDetailPurchasePrices->save($newEntity->order_detail_purchase_price);
                $savedEntity->order_detail_purchase_price = $newOrderDetailPurchasePriceEntity;
                $productPurchasePrice = $productPurchasePrice / $newAmountForOldOrderDetail * $amount;
                $this->changeOrderDetailPurchasePrice($savedEntity->order_detail_purchase_price, $productPurchasePrice, $amount);
            }

        } else {

            // order detail does not need to be split up

            $this->OrderDetail->save(
                $this->OrderDetail->patchEntity(
                    $oldOrderDetail,
                    [
                        'id_customer' => $customerId
                    ]
                )
            );

        }

        $message = __d('admin', 'The_ordered_product_{0}_was_successfully_assigned_from_{1}_to_{2}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            Configure::read('app.htmlHelper')->getNameRespectingIsDeleted($oldOrderDetail->customer),
            '<b>' . $newCustomer->name . '</b>'
        ]);

        $amountString = '';
        if ($originalProductAmount != $amount) {
            $amountString = ' ' . __d('admin', 'Amount') . ': <b>' . $amount . '</b>';
            $message .= $amountString;
        }

        $message .= ' '.__d('admin', 'Reason').': <b>"' . $editCustomerReason . '"</b>';

        $recipients = [
            [
                'email' => $newCustomer->email,
                'customer' => $newCustomer
            ],
            [
                'email' => $oldOrderDetail->customer->email,
                'customer' => $oldOrderDetail->customer
            ]
        ];
        // send email to customers
        foreach($recipients as $recipient) {
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.order_detail_customer_changed');
            $email->setTo($recipient['email'])
            ->setSubject(__d('admin', 'Assigned_to_another_member') . ': ' . $oldOrderDetail->product_name)
            ->setViewVars([
                'oldOrderDetail' => $oldOrderDetail,
                'customer' => $recipient['customer'],
                'newCustomer' => $newCustomer,
                'editCustomerReason' => $editCustomerReason,
                'amountString' => $amountString,
                'appAuth' => $this->AppAuth
            ]);
            $email->send();
        }

        $message .= ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_{1}.', [
            '<b>' . $oldOrderDetail->customer->name . '</b>',
            '<b>' . $newCustomer->name . '</b>'
        ]);

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('order_detail_customer_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);
        $this->Flash->success($message);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

    public function editProductQuantity()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $productQuantity = trim($this->getRequest()->getData('productQuantity'));
        $doNotChangePrice = $this->getRequest()->getData('doNotChangePrice');
        $productQuantity = Configure::read('app.numberHelper')->parseFloatRespectingLocale($productQuantity);

        if (! is_numeric($orderDetailId) || !$productQuantity || $productQuantity < 0) {
            $message = __d('admin', 'The_delivered_quantity_is_not_valid.');
            if (! is_numeric($orderDetailId)) {
                $message = 'input format wrong';
            }
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'OrderDetailPurchasePrices',
                'OrderDetailUnits',
                'TimebasedCurrencyOrderDetails',
            ]
        ])->first();

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $objectOrderDetailUnit = clone $oldOrderDetail->order_detail_unit;

        if (!$doNotChangePrice) {
            $newProductPrice = round($oldOrderDetail->order_detail_unit->price_incl_per_unit / $oldOrderDetail->order_detail_unit->unit_amount * $productQuantity, 2);
            if ($oldOrderDetail->order_detail_unit->product_quantity_in_units > 0) {
                $toleranceFactor = 100;
                $oldToNewQuantityRelation = $productQuantity / $oldOrderDetail->order_detail_unit->product_quantity_in_units;
                if ($oldToNewQuantityRelation < 1 / $toleranceFactor || $oldToNewQuantityRelation > $toleranceFactor) {
                    $message = __d('admin', 'The_new_price_would_be_{0}_for_{1}_please_check_the_unit.', [
                        '<b>' . Configure::read('app.numberHelper')->formatAsCurrency($newProductPrice) . '</b>',
                        '<b>' . Configure::read('app.numberHelper')->formatUnitAsDecimal($productQuantity) . ' ' . $oldOrderDetail->order_detail_unit->unit_name . '</b>',
                    ]);
                    $this->set([
                        'status' => 0,
                        'msg' => $message,
                    ]);
                    $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
                    return;
                }
            }
            if (!empty($oldOrderDetail->order_detail_purchase_price)) {
                $productPurchasePrice = round($oldOrderDetail->order_detail_unit->purchase_price_incl_per_unit / $oldOrderDetail->order_detail_unit->unit_amount * $productQuantity, 2);
                $this->changeOrderDetailPurchasePrice($oldOrderDetail->order_detail_purchase_price, $productPurchasePrice, $object->product_amount);
            }
            $newOrderDetail = $this->changeOrderDetailPriceDepositTax($object, $newProductPrice, $object->product_amount);
            $this->changeTimebasedCurrencyOrderDetailPrice($object, $oldOrderDetail, $newProductPrice, $object->product_amount);
        }
        $this->changeOrderDetailQuantity($objectOrderDetailUnit, $productQuantity);

        $message = __d('admin', 'The_weight_of_the_ordered_product_{0}_(amount_{1})_was_successfully_apapted_from_{2}_to_{3}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            $oldOrderDetail->product_amount,
            Configure::read('app.numberHelper')->formatUnitAsDecimal($oldOrderDetail->order_detail_unit->product_quantity_in_units) . ' ' . $oldOrderDetail->order_detail_unit->unit_name,
            Configure::read('app.numberHelper')->formatUnitAsDecimal($productQuantity) . ' ' . $oldOrderDetail->order_detail_unit->unit_name
        ]);

        $quantityWasChanged = $oldOrderDetail->order_detail_unit->product_quantity_in_units != $productQuantity;

        // send email to customer if price was changed
        if (!$doNotChangePrice && $quantityWasChanged && Configure::read('app.sendEmailWhenOrderDetailQuantityOrPriceChanged')) {
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.order_detail_quantity_changed');
            $email->setTo($oldOrderDetail->customer->email)
            ->setSubject(__d('admin', 'Weight_adapted_for_"0":', [$oldOrderDetail->product_name]) . ' ' . Configure::read('app.numberHelper')->formatUnitAsDecimal($productQuantity) . ' ' . $oldOrderDetail->order_detail_unit->unit_name)
            ->setViewVars([
                'oldOrderDetail' => $oldOrderDetail,
                'newProductQuantityInUnits' => $productQuantity,
                'newOrderDetail' => $newOrderDetail,
                'appAuth' => $this->AppAuth
            ]);

            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->customer->name . '</b>']);

            $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
            $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_price_changed_notification);

            if (! $this->AppAuth->isManufacturer() && $oldOrderDetail->total_price_tax_incl > 0.00 && $sendOrderedProductPriceChangedNotification) {
                $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                    '<b>' . $oldOrderDetail->customer->name . '</b>',
                    '<b>' . $oldOrderDetail->product->manufacturer->name . '</b>'
                ]);
                $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
            }

            $email->send();

            $message .= $emailMessage;

        }

        if ($quantityWasChanged) {
            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('order_detail_product_quantity_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);
            $this->Flash->success($message);
        }

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

    public function editProductAmount()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $productAmount = trim($this->getRequest()->getData('productAmount'));
        $editAmountReason = strip_tags(html_entity_decode($this->getRequest()->getData('editAmountReason')));

        if (! is_numeric($orderDetailId) || ! is_numeric($productAmount) || $productAmount < 1) {
            $message = __d('admin', 'The_amount_is_not_valid.');
            if (! is_numeric($orderDetailId)) {
                $message = 'input format wrong';
            }
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'TimebasedCurrencyOrderDetails',
                'OrderDetailUnits',
                'OrderDetailPurchasePrices',
            ]
        ])->first();

        $productPrice = $oldOrderDetail->total_price_tax_incl / $oldOrderDetail->product_amount * $productAmount;

        if (!empty($oldOrderDetail->order_detail_purchase_price)) {
            $productPurchasePrice = $oldOrderDetail->order_detail_purchase_price->total_price_tax_incl / $oldOrderDetail->product_amount * $productAmount;
            $this->changeOrderDetailPurchasePrice($oldOrderDetail->order_detail_purchase_price, $productPurchasePrice, $productAmount);
        }

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function

        $newOrderDetail = $this->changeOrderDetailPriceDepositTax($object, $productPrice, $productAmount);
        $newQuantity = $this->increaseQuantityForProduct($newOrderDetail, $oldOrderDetail->product_amount);

        if (!empty($object->order_detail_unit)) {
            $productQuantity = $oldOrderDetail->order_detail_unit->product_quantity_in_units / $oldOrderDetail->product_amount * $productAmount;
            $this->changeOrderDetailQuantity($object->order_detail_unit, $productQuantity);
        }

        $this->changeTimebasedCurrencyOrderDetailPrice($object, $oldOrderDetail, $productPrice, $productAmount);

        $message = __d('admin', 'The_amount_of_the_ordered_product_{0}_was_successfully_changed_from_{1}_to_{2}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            $oldOrderDetail->product_amount,
            $productAmount
        ]);

        // send email to customer
        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('Admin.order_detail_amount_changed');
        $email->setTo($oldOrderDetail->customer->email)
        ->setSubject(__d('admin', 'Ordered_amount_adapted') . ': ' . $oldOrderDetail->product_name)
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newOrderDetail' => $newOrderDetail,
            'appAuth' => $this->AppAuth,
            'editAmountReason' => $editAmountReason
        ]);

        $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->customer->name . '</b>']);

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $sendOrderedProductAmountChangedNotification = $this->Manufacturer->getOptionSendOrderedProductAmountChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_amount_changed_notification);

        if (! $this->AppAuth->isManufacturer() && $oldOrderDetail->order_state == ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER && $sendOrderedProductAmountChangedNotification) {
            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                '<b>' . $oldOrderDetail->customer->name . '</b>',
                '<b>' . $oldOrderDetail->product->manufacturer->name . '</b>'
            ]);
            $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
        }

        $email->send();

        $message .= $emailMessage;

        if ($editAmountReason != '') {
            $message .= ' ' . __d('admin', 'Reason') . ': <b>"' . $editAmountReason . '"</b>';
        }

        if ($newQuantity !== false) {
            $message .= ' ' . __d('admin', 'The_stock_was_increased_to_{0}.', [
                Configure::read('app.numberHelper')->formatAsDecimal($newQuantity, 0)
            ]);
        }

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('order_detail_product_amount_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);

        $this->Flash->success($message);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    public function editProductPrice()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $editPriceReason = strip_tags(html_entity_decode($this->getRequest()->getData('editPriceReason')));

        $productPrice = trim($this->getRequest()->getData('productPrice'));
        $productPrice = Configure::read('app.numberHelper')->parseFloatRespectingLocale($productPrice);

        if (! is_numeric($orderDetailId) || !$productPrice || $productPrice < 0) {
            $message = __d('admin', 'The_price_is_not_valid.');
            if (! is_numeric($orderDetailId)) {
                $message = 'input format wrong';
            }
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'TimebasedCurrencyOrderDetails',
            ]
        ])->first();

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $newOrderDetail = $this->changeOrderDetailPriceDepositTax($object, $productPrice, $object->product_amount);

        $message = __d('admin', 'The_price_of_the_ordered_product_{0}_(amount_{1})_was_successfully_apapted_from_{2}_to_{3}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            $oldOrderDetail->product_amount,
            Configure::read('app.numberHelper')->formatAsDecimal($oldOrderDetail->total_price_tax_incl),
            Configure::read('app.numberHelper')->formatAsDecimal($productPrice)
        ]);

        $this->changeTimebasedCurrencyOrderDetailPrice($object, $oldOrderDetail, $productPrice, $object->product_amount);

        if (Configure::read('app.sendEmailWhenOrderDetailQuantityOrPriceChanged')) {
            // send email to customer
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.order_detail_price_changed');
            $email->setTo($oldOrderDetail->customer->email)
            ->setSubject(__d('admin', 'Ordered_price_adapted') . ': ' . $oldOrderDetail->product_name)
            ->setViewVars([
                'oldOrderDetail' => $oldOrderDetail,
                'newOrderDetail' => $newOrderDetail,
                'appAuth' => $this->AppAuth,
                'editPriceReason' => $editPriceReason
            ]);

            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->customer->name . '</b>']);

            $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
            $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_price_changed_notification);
            if (! $this->AppAuth->isManufacturer() && $oldOrderDetail->total_price_tax_incl > 0.00 && $sendOrderedProductPriceChangedNotification) {
                $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                    '<b>' . $oldOrderDetail->customer->name . '</b>',
                    '<b>' . $oldOrderDetail->product->manufacturer->name . '</b>'
                ]);
                $email->addCC($oldOrderDetail->product->manufacturer->address_manufacturer->email);
            }

            $email->send();

            $message .= $emailMessage;
        }

        if ($editPriceReason != '') {
            $message .= ' '.__d('admin', 'Reason').': <b>"' . $editPriceReason . '"</b>';
        }

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('order_detail_product_price_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);
        $this->Flash->success($message);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    public function editPickupDay()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $orderDetailIds = $this->getRequest()->getData('orderDetailIds');
        $pickupDay = $this->getRequest()->getData('pickupDay');
        $pickupDay = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);
        $editPickupDayReason = htmlspecialchars_decode(strip_tags(trim($this->getRequest()->getData('editPickupDayReason')), '<strong><b>'));

        try {
            if (empty($orderDetailIds)) {
                throw new InvalidParameterException('error - no order detail id passed');
            }
            $errorMessages = [];
            if ($editPickupDayReason == '') {
                $errorMessages[] = __d('admin', 'Please_enter_why_pickup_day_is_changed.');
            }

            $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
            $orderDetails = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail IN' => $orderDetailIds
                ],
                'contain' => [
                    'Customers',
                    'Products.Manufacturers'
                ]
            ]);
            if ($orderDetails->count() != count($orderDetailIds)) {
                throw new InvalidParameterException('error - order details wrong');
            }

            $oldPickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($orderDetails->toArray()[0]->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'))));
            $newPickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($pickupDay));

            // validate only once for the first order detail
            $entity = $this->OrderDetail->patchEntity(
                $orderDetails->toArray()[0],
                [
                    'pickup_day' => $pickupDay
                ],
                [
                    'validate' => 'pickupDay'
                ]
            );
            if ($entity->hasErrors()) {
                $errorMessages = array_merge($errorMessages, $this->OrderDetail->getAllValidationErrors($entity));
            }
            if (!empty($errorMessages)) {
                throw new InvalidParameterException(join('<br />', $errorMessages));
            }

            $customers = [];
            foreach ($orderDetails as $orderDetail) {
                $entity = $this->OrderDetail->patchEntity(
                    $orderDetail,
                    [
                        'pickup_day' => $pickupDay
                    ]
                );
                $this->OrderDetail->save($entity);
                if (!isset($customers[$orderDetail->id_customer])) {
                    $customers[$orderDetail->id_customer] = [];
                }
                $customers[$orderDetail->id_customer][] = $orderDetail;
            }

            foreach($customers as $orderDetails) {
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('Admin.order_detail_pickup_day_changed');
                $email->setTo($orderDetails[0]->customer->email)
                ->setSubject(__d('admin', 'The_pickup_day_of_your_order_was_changed_to').': ' . $newPickupDay)
                ->setViewVars([
                    'orderDetails' => $orderDetails,
                    'customer' => $orderDetails[0]->customer,
                    'appAuth' => $this->AppAuth,
                    'oldPickupDay' => $oldPickupDay,
                    'newPickupDay' => $newPickupDay,
                    'editPickupDayReason' => $editPickupDayReason
                ]);
                $email->send();
            }

            $message = __d('admin', 'The_pickup_day_of_{0,plural,=1{1_product} other{#_products}}_was_changed_successfully_to_{1}_and_{2,plural,=1{1_customer} other{#_customers}}_were_notified.', [count($orderDetailIds), '<b>'.$newPickupDay.'</b>', count($customers)]);

            if ($editPickupDayReason != '') {
                $message .= ' ' . __d('admin', 'Reason') . ': <b>"' . $editPickupDayReason . '"</b>';
            }

            $this->Flash->success($message);

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('order_detail_pickup_day_changed', $this->AppAuth->getUserId(), 0, 'order_details', $message . ' Ids: ' . join(', ', $orderDetailIds));

            $this->set([
                'result' => [],
                'status' => true,
                'msg' => 'ok',
            ]);
            $this->viewBuilder()->setOption('serialize', ['result', 'status', 'msg']);

        } catch (InvalidParameterException $e) {
            return $this->sendAjaxError($e);
        }

    }

    public function addFeedback()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $orderDetailFeedback = htmlspecialchars_decode(strip_tags(trim($this->getRequest()->getData('orderDetailFeedback')), '<strong><b><i><img>'));

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Customers',
                'Products.Manufacturers.AddressManufacturers',
                'OrderDetailFeedbacks'
            ]
        ])->first();

        try {
            if (empty($orderDetail)) {
                throw new InvalidParameterException('orderDetail not found: ' . $orderDetailId);
            }
            if (!empty($orderDetail->order_detail_feedback)) {
                throw new InvalidParameterException('orderDetail already has a feedback: ' . $orderDetailId);
            }

            $entity = $this->OrderDetail->OrderDetailFeedbacks->newEntity(
                [
                    'customer_id' => $this->AppAuth->getUserId(),
                    'id_order_detail' => $orderDetailId,
                    'text' => $orderDetailFeedback,
                ]
            );
            if ($entity->hasErrors()) {
                throw new InvalidParameterException(join(' ', $this->OrderDetail->OrderDetailFeedbacks->getAllValidationErrors($entity)));
            }

        } catch (InvalidParameterException $e) {
            return $this->sendAjaxError($e);
        }

        $result = $this->OrderDetail->OrderDetailFeedbacks->save($entity);

        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('Admin.order_detail_feedback_add');
        $email->setTo($orderDetail->product->manufacturer->address_manufacturer->email)
            ->setSubject(__d('admin', '{0}_has_written_a_feedback_to_product_{1}.', [
                $orderDetail->customer->name,
                '"' . $orderDetail->product_name . '"',
            ])
        )
        ->setViewVars([
            'orderDetail' => $orderDetail,
            'appAuth' => $this->AppAuth,
            'orderDetailFeedback' => $orderDetailFeedback,
        ]);

        $email->send();

        $this->Flash->success(__d('admin', 'The_feedback_was_saved_successfully_and_sent_to_{0}.', ['<b>' . $orderDetail->product->manufacturer->name . '</b>']));

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $actionLogMessage = __d('admin', '{0}_has_written_a_feedback_to_product_{1}.', [
            '<b>' . $orderDetail->customer->name . '</b>',
            '<b>' . $orderDetail->product_name . '</b>',
        ]);
        $this->ActionLog->customSave('order_detail_feedback_added', $this->AppAuth->getUserId(), $orderDetail->id_order_detail, 'order_details', $actionLogMessage . ' <div class="changed">' . $orderDetailFeedback . ' </div>');

        $this->set([
            'result' => $result,
            'status' => !empty($result),
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['result', 'status', 'msg']);

    }

    public function editPickupDayComment()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $customerId = $this->getRequest()->getData('customerId');
        $pickupDay = $this->getRequest()->getData('pickupDay');
        $pickupDay = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);
        $pickupDayComment = htmlspecialchars_decode(strip_tags(trim($this->getRequest()->getData('pickupDayComment')), '<strong><b>'));

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'id_customer' => $customerId
            ]
        ])->first();

        $this->PickupDay = $this->getTableLocator()->get('PickupDays');
        $result = $this->PickupDay->insertOrUpdate(
            [
                'customer_id' => $customerId,
                'pickup_day' => $pickupDay
            ],
            [
                'comment' => $pickupDayComment
            ]
        );

        $this->Flash->success(__d('admin', 'The_comment_was_changed_successfully.'));

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('order_comment_changed', $this->AppAuth->getUserId(), $customerId, 'customers', __d('admin', 'The_pickup_day_comment_of_{0}_was_changed:', [$customer->name]) . ' <div class="changed">' . $pickupDayComment . ' </div>');

        $this->set([
            'result' => $result,
            'status' => !empty($result),
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['result', 'status', 'msg']);

    }

    public function changeProductsPickedUp()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $customerIds = $this->getRequest()->getData('customerIds');
        $state = (int) $this->getRequest()->getData('state');
        $pickupDay = $this->getRequest()->getData('pickupDay');
        $pickupDay = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->PickupDay = $this->getTableLocator()->get('PickupDays');
        $this->PickupDay->setPrimaryKey(['customer_id', 'pickup_day']);

        $errorMessages = [];
        foreach($customerIds as $customerId) {

            if ($state) {

                $orderDetailsWithUnchangedWeight = $this->OrderDetail->find('all', [
                    'conditions' => [
                        'OrderDetails.id_customer' => $customerId,
                        'OrderDetails.pickup_day' => $pickupDay,
                        'OrderDetailUnits.mark_as_saved' => APP_OFF,
                    ],
                    'contain' => [
                        'OrderDetailUnits',
                        'Customers',
                    ]
                ])->toArray();

                if (count($orderDetailsWithUnchangedWeight) > 0) {
                    $errorMessages[] = [
                        'customerName' => $orderDetailsWithUnchangedWeight[0]->customer->name,
                        'orderDetailsWithUnchangedWeight' => count($orderDetailsWithUnchangedWeight),
                    ];
                }

            }
            $this->PickupDay = $this->getTableLocator()->get('PickupDays');
            $result = $this->PickupDay->changeState($customerId, $pickupDay, $state);
        }

        if (!empty($errorMessages)) {
            if (count($errorMessages) == 1) {
                $message = __d('admin', 'The_customer_{0}_still_has_{1,plural,=1{1_product} other{#_products}}_with_unchanged_weight.', [
                    '<b>' . $errorMessages[0]['customerName'] . '</b>',
                    $errorMessages[0]['orderDetailsWithUnchangedWeight'],
                ]);
            } else {
                $message = __d('admin', 'The_following_customers_still_have_products_with_unchanged_weight:');
                $message .= '<ul>';
                foreach($errorMessages as $errorMessage) {
                    $message .= '<li>';
                        $message .= '<b>' . $errorMessage['customerName'] . '</b>: ';
                        $message .= __d('admin', '{0,plural,=1{1_product} other{#_products}}', [
                            $errorMessage['orderDetailsWithUnchangedWeight'],
                        ]);
                    $message .= '</li>';
                }
                $message .= '</ul>';
            }
            $this->Flash->error($message);
        }

        $message = '';
        if (empty($result)) {
            $message = __d('admin', 'Errors_while_saving!');
        }

        $redirectUrl = '';
        if (preg_match('/customerId\='.$customerIds[0].'/', $this->referer())) {
            $redirectUrl = '/admin/order-details?pickupDay[]='.$this->getRequest()->getData('pickupDay').'&groupBy=customer';
        }

        $this->set([
            'pickupDay' => $pickupDay,
            'result' => $result,
            'status' => !empty($result),
            'redirectUrl' => $redirectUrl,
            'msg' => $message,
        ]);
        $this->viewBuilder()->setOption('serialize', ['pickupDay', 'result', 'status', 'redirectUrl', 'msg']);

    }

    /**
     * @param array $orderDetailIds
     */
    public function delete()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $orderDetailIds = $this->getRequest()->getData('orderDetailIds');
        $cancellationReason = strip_tags(html_entity_decode($this->getRequest()->getData('cancellationReason')));

        if (!(is_array($orderDetailIds))) {
            $this->set([
                'status' => 0,
                'msg' => 'param needs to be an array, given: ' . $orderDetailIds,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $flashMessage = '';
        foreach ($orderDetailIds as $orderDetailId) {
            $orderDetail = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail' => $orderDetailId
                ],
                'contain' => [
                    'Customers',
                    'Products.StockAvailables',
                    'Products.Manufacturers',
                    'Products.Manufacturers.AddressManufacturers',
                    'ProductAttributes.StockAvailables',
                    'TimebasedCurrencyOrderDetails',
                    'OrderDetailUnits',
                    'OrderDetailPurchasePrices',
                ]
            ])->first();

            $message = __d('admin', 'Product_{0}_from_manufacturer_{1}_with_a_price_of_{2}_ordered_on_{3}_was_successfully_cancelled.', [
                '<b>' . $orderDetail->product_name . '</b>',
                '<b>' . $orderDetail->product->manufacturer->name . '</b>',
                Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->total_price_tax_incl),
                $orderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'))
            ]);

            $this->OrderDetail->deleteOrderDetail($orderDetail);

            $newQuantity = $this->increaseQuantityForProduct($orderDetail, $orderDetail->product_amount * 2);

            // send email to customer
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.order_detail_deleted');
            $email->setTo($orderDetail->customer->email)
            ->setSubject(__d('admin', 'Product_was_cancelled').': ' . $orderDetail->product_name)
            ->setViewVars([
                'orderDetail' => $orderDetail,
                'appAuth' => $this->AppAuth,
                'cancellationReason' => $cancellationReason
            ]);

            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $orderDetail->customer->name . '</b>']);

            $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
            $sendOrderedProductDeletedNotification = $this->Manufacturer->getOptionSendOrderedProductDeletedNotification($orderDetail->product->manufacturer->send_ordered_product_deleted_notification);

            if (! $this->AppAuth->isManufacturer() && $orderDetail->order_state == ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER && $sendOrderedProductDeletedNotification) {
                $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                    '<b>' . $orderDetail->customer->name . '</b>',
                    '<b>' . $orderDetail->product->manufacturer->name . '</b>'
                ]);
                $email->addCC($orderDetail->product->manufacturer->address_manufacturer->email);
            }

            $email->send();

            $message .= $emailMessage;

            if ($cancellationReason != '') {
                $message .= ' '.__d('admin', 'Reason').': <b>"' . $cancellationReason . '"</b>';
            }

            if ($newQuantity !== false) {
                $message .= ' ' . __d('admin', 'The_stock_was_increased_to_{0}.', [
                    Configure::read('app.numberHelper')->formatAsDecimal($newQuantity, 0)
                ]);
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('order_detail_cancelled', $this->AppAuth->getUserId(), $orderDetail->product_id, 'products', $message);
        }

        $flashMessage = $message;
        $orderDetailsCount = count($orderDetailIds);
        if ($orderDetailsCount > 1) {
            $flashMessage = $orderDetailsCount . ' ' . __d('admin', '{0,plural,=1{product_was_cancelled_succesfully.} other{products_were_cancelled_succesfully.}}', $orderDetailsCount);
        }
        $this->Flash->success($flashMessage);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    /**
     * @param OrderDetailsTable $oldOrderDetail
     * @param float $productQuantity
     */
    private function changeOrderDetailQuantity($oldOrderDetailUnit, $productQuantity)
    {
        $orderDetailUnit2save = [
            'product_quantity_in_units' => $productQuantity,
            'mark_as_saved' => 1,
        ];
        $patchedEntity = $this->OrderDetail->OrderDetailUnits->patchEntity($oldOrderDetailUnit, $orderDetailUnit2save);
        $this->OrderDetail->OrderDetailUnits->save($patchedEntity);
    }

    private function changeOrderDetailPurchasePrice($purchasePriceObject, $productPurchasePrice, $productAmount)
    {
        $unitPriceExcl = $this->OrderDetail->Products->getNetPrice($productPurchasePrice / $productAmount, $purchasePriceObject->tax_rate);
        $unitTaxAmount = $this->OrderDetail->Products->getUnitTax($productPurchasePrice, $unitPriceExcl, $productAmount);
        $totalTaxAmount = $unitTaxAmount * $productAmount;
        $totalPriceTaxExcl = $productPurchasePrice - $totalTaxAmount;
        $orderDetailPurchasePrice2save = [
            'total_price_tax_incl' => $productPurchasePrice,
            'total_price_tax_excl' => $totalPriceTaxExcl,
            'tax_unit_amount' => $unitTaxAmount,
            'tax_total_amount' => $totalTaxAmount,
        ];
        $this->OrderDetail->OrderDetailPurchasePrices->save(
            $this->OrderDetail->OrderDetailPurchasePrices->patchEntity($purchasePriceObject, $orderDetailPurchasePrice2save)
        );
    }

    private function changeOrderDetailPriceDepositTax($oldOrderDetail, $productPrice, $productAmount)
    {

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');

        $unitPriceExcl = $this->OrderDetail->Products->getNetPrice($productPrice / $productAmount, $oldOrderDetail->tax_rate);
        $unitTaxAmount = $this->OrderDetail->Products->getUnitTax($productPrice, $unitPriceExcl, $productAmount);
        $totalTaxAmount = $unitTaxAmount * $productAmount;
        $totalPriceTaxExcl = $productPrice - $totalTaxAmount;

        // update order details
        $orderDetail2save = [
            'total_price_tax_incl' => $productPrice,
            'total_price_tax_excl' => $totalPriceTaxExcl,
            'product_amount' => $productAmount,
            'deposit' => $oldOrderDetail->deposit / $oldOrderDetail->product_amount * $productAmount,
            'tax_unit_amount' => $unitTaxAmount,
            'tax_total_amount' => $totalTaxAmount,
        ];

        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity($oldOrderDetail, $orderDetail2save)
        );

        $newOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $oldOrderDetail->id_order_detail
            ],
            'contain' => [
                'Customers',
                'Products.StockAvailables',
                'Products.Manufacturers',
                'ProductAttributes.StockAvailables',
                'OrderDetailUnits'
            ]
        ])->first();

        return $newOrderDetail;
    }

    private function changeTimebasedCurrencyOrderDetailPrice($object, $oldOrderDetail, $newPrice, $amount)
    {
        if (!empty($object->timebased_currency_order_detail)) {
            $this->TimebasedCurrencyOrderDetail = $this->getTableLocator()->get('TimebasedCurrencyOrderDetails');
            $this->TimebasedCurrencyOrderDetail->changePrice($object, $newPrice, $amount);
        }
    }

    private function increaseQuantityForProduct($orderDetail, $orderDetailAmountBeforeAmountChange)
    {

        // order detail references a product attribute
        if (!empty($orderDetail->product_attribute->stock_available)) {
            $stockAvailableObject = $orderDetail->product_attribute->stock_available;
        } else {
            $stockAvailableObject = $orderDetail->product->stock_available;
        }

        $quantity = $stockAvailableObject->quantity;

        if (!($stockAvailableObject->is_stock_product && $orderDetail->product->manufacturer->is_stock_management_enabled) && $stockAvailableObject->always_available) {
            return false;
        }

        // do the acutal updates for increasing quantity
        $this->StockAvailable = $this->getTableLocator()->get('StockAvailables');
        $originalPrimaryKey = $this->StockAvailable->getPrimaryKey();
        $this->StockAvailable->setPrimaryKey('id_stock_available');
        $newQuantity = $quantity + $orderDetailAmountBeforeAmountChange - $orderDetail->product_amount;
        $patchedEntity = $this->StockAvailable->patchEntity(
            $stockAvailableObject,
            [
                'quantity' => $newQuantity
            ]
        );
        $this->StockAvailable->save($patchedEntity);
        $this->StockAvailable->setPrimaryKey($originalPrimaryKey);

        return $newQuantity;
    }

    public function setElFinderUploadPath($orderDetailId)
    {
        $this->RequestHandler->renderAs($this, 'json');

        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('app.cakeServerName') . "/files/kcfinder/order_details/" . $orderDetailId,
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/order_details/" . $orderDetailId
        ];

        $this->set([
            'status' => true,
            'msg' => 'OK',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

}
