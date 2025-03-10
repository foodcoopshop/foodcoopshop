<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use App\Services\DeliveryRhythmService;
use App\Controller\Component\StringComponent;
use Cake\ORM\Query\SelectQuery;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait IndexTrait 
{

    public function index(): void
    {

        // for filter from action logs page
        $orderDetailId = h($this->getRequest()->getQuery('orderDetailId', ''));
        $this->set('orderDetailId', $orderDetailId);

        $pickupDay = [];
        if ($orderDetailId == '') {
            if (in_array('pickupDay', array_keys($this->getRequest()->getQueryParams()))) {
                $pickupDay = h($this->getRequest()->getQuery('pickupDay'));
                if ($pickupDay == '') {
                    throw new \Exception('parameter pickupDay must not be empty');
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
                    $pickupDay[0] = (new DeliveryRhythmService())->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
                }
            }
        }

        $pickupDay = Configure::read('app.timeHelper')->sortArrayByDate($pickupDay);
        $this->set('pickupDay', $pickupDay);

        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId', ''));
        $this->set('manufacturerId', $manufacturerId);

        $deposit = h($this->getRequest()->getQuery('deposit', ''));
        $this->set('deposit', $deposit);

        $productId = h($this->getRequest()->getQuery('productId', ''));
        $this->set('productId', $productId);

        $customerId = h($this->getRequest()->getQuery('customerId', ''));
        $this->set('customerId', $customerId);

        $cartType = h($this->getRequest()->getQuery('cartType', null));
        $this->set('cartType', $cartType);

        $filterByCartTypeEnabled = h($this->getRequest()->getQuery('filterByCartTypeEnabled', !is_null($cartType)));
        $this->set('filterByCartTypeEnabled', $filterByCartTypeEnabled);

        $groupBy = h($this->getRequest()->getQuery('groupBy', null));
        if ($this->identity->isManufacturer() && $groupBy != 'product') {
            $groupBy = '';
        }
        $this->set('groupBy', $groupBy);

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $odParams = $orderDetailsTable->getOrderDetailParams($manufacturerId, $productId, $customerId, $pickupDay, $orderDetailId, $deposit);

        $contain = $odParams['contain'];
        if (($groupBy == 'customer' || $groupBy == '') && count($pickupDay) == 1) {
            $orderDetailsTable->getAssociation('PickupDayEntities')->setConditions([
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

        $query = $orderDetailsTable->find('all',
            conditions:  $odParams['conditions'],
            contain:  $contain,
            group:  $group,
        );

        $orderDetailsTable->getAssociation('CartProducts.Carts')->setJoinType('INNER');
        $query->contain(['CartProducts.Carts' => function ($q) use ($cartType) {
            if (in_array($cartType, array_keys(Configure::read('app.htmlHelper')->getCartTypes()))) {
                $q->where([
                    'Carts.cart_type' => $cartType,
                ]);
            }
            return $q;
        }]);

        switch($groupBy) {
            case 'customer':
                $query = $this->addSelectGroupFields($query);
                $query->select(['OrderDetails.id_customer']);
                $query->select(['Customers.firstname', 'Customers.lastname', 'Customers.email', 'Customers.is_company']);
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
                $query->select('OrderDetailUnits.unit_name');
                $query->groupBy('OrderDetailUnits.unit_name');
                break;
            default:
                $customerTable = $this->getTableLocator()->get('Customers');
                $query = $customerTable->addCustomersNameForOrderSelect($query);
                $query->select($orderDetailsTable);
                $query->select($this->getTableLocator()->get('CartProducts')); // need to be called before ->Carts
                $query->select($this->getTableLocator()->get('Carts'));
                $query->select($this->getTableLocator()->get('OrderDetailUnits'));
                $query->select($this->getTableLocator()->get('OrderDetailFeedbacks'));
                $query->select($customerTable);
                $query->select($this->getTableLocator()->get('Products'));
                $query->select($this->getTableLocator()->get('Manufacturers'));
                $query->select($this->getTableLocator()->get('AddressManufacturers'));
                if (Configure::read('appDb.FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS')) {
                    $query->select($this->getTableLocator()->get('StorageLocations'));
                }
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
                'CustomerNameForOrder',
                'OrderDetailUnits.product_quantity_in_units',
                'sum_price',
                'sum_amount',
                'sum_deposit',
                'sum_units',
                'Products.name',
            ]
        ])->toArray();

        $orderDetails = $this->prepareGroupedOrderDetails($orderDetails, $groupBy);
        $this->set('orderDetails', $orderDetails);

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
        }
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
        if (!$this->identity->isManufacturer()) {
            $groupByForDropdown['customer'] = __d('admin', 'Group_by_member');
            $groupByForDropdown['manufacturer'] = __d('admin', 'Group_by_manufacturer');
        }
        $this->set('groupByForDropdown', $groupByForDropdown);
        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $this->set('manufacturersForDropdown', $manufacturersTable->getForDropdown());

        $this->set('title_for_layout', __d('admin', 'Orders'));
    }

    private function prepareGroupedOrderDetails($orderDetails, $groupBy): array
    {

        $preparedOrderDetails = [];
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');

        switch ($groupBy) {
            case 'customer':
                $preparedOrderDetails = $orderDetailsTable->prepareOrderDetailsGroupedByCustomer($orderDetails);
                if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
                    $invoicesTable = $this->getTableLocator()->get('Invoices');
                    foreach($preparedOrderDetails as &$orderDetail) {
                        $orderDetail['invoiceData'] = $invoicesTable->getDataForCustomerInvoice($orderDetail['customer_id'], Configure::read('app.timeHelper')->getCurrentDateForDatabase());
                        $orderDetail['latestInvoices'] = $invoicesTable->getLatestInvoicesForCustomer($orderDetail['customer_id']);
                    }
                }
                $sortField = $this->getSortFieldForGroupedOrderDetails('name');
                break;
            case 'manufacturer':
                $preparedOrderDetails = $orderDetailsTable->prepareOrderDetailsGroupedByManufacturer($orderDetails);
                $sortField = $this->getSortFieldForGroupedOrderDetails('name');
                break;
            case 'product':
                $preparedOrderDetails = $orderDetailsTable->prepareOrderDetailsGroupedByProduct($orderDetails);
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
                        $orderDetail->quantityInUnitsNotYetChanged = true;
                        if ($orderDetail->order_detail_unit->mark_as_saved) {
                            $orderDetail->quantityInUnitsNotYetChanged = false;
                        }
                    }
                    $deliveryDay[] = $orderDetail->pickup_day;
                    $manufacturerName[] = mb_strtolower(StringComponent::slugify($orderDetail->product->manufacturer->name));
                    $productName[] = mb_strtolower(StringComponent::slugify($orderDetail->product_name));
                    if (!empty($orderDetail->customer)) {
                        $customerName[] = mb_strtolower(StringComponent::slugify($orderDetail->customer->name));
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
            $isName = in_array($sortField, ['manufacturer_name', 'name']);
            $orderDetails = Hash::sort($preparedOrderDetails, '{n}.' . $sortField, $sortDirection, [
                'type' => $isName ? 'locale' : 'regular',
                'ignoreCase' => $isName,
            ]);
        }

        if ($groupBy == 'customer') {
            $orderDetails = Hash::sort($orderDetails, '{n}.products_picked_up', 'ASC');
        }

        return $orderDetails;

    }

    private function getSortFieldForGroupedOrderDetails($manufacturerNameField): string
    {
        $sortMatches = [
            'Manufacturers.name' => $manufacturerNameField,
            'sum_price' => 'sum_price',
            'sum_amount' => 'sum_amount',
            'sum_deposit' => 'sum_deposit',
            'sum_units' => 'sum_units',
        ];
        $sortField = 'name';
        if (!empty($this->getRequest()->getQuery('sort')) && isset($sortMatches[$this->getRequest()->getQuery('sort')])) {
            $sortField = $sortMatches[$this->getRequest()->getQuery('sort')];
        }
        return $sortField;
    }

    private function getSortDirectionForGroupedOrderDetails(): string
    {
        $sortDirection = 'ASC';
        if (!empty($this->getRequest()->getQuery('direction') && in_array($this->getRequest()->getQuery('direction'), ['asc', 'desc']))) {
            $sortDirection = h($this->getRequest()->getQuery('direction'));
        }
        return $sortDirection;
    }
    
    private function addSelectGroupFields($query): SelectQuery
    {
        $query->select([
            'sum_price' => $query->func()->sum('OrderDetails.total_price_tax_incl'),
            'sum_amount' => $query->func()->sum('OrderDetails.product_amount'),
            'sum_deposit' => $query->func()->sum('OrderDetails.deposit'),
            'sum_units' => $query->func()->sum('OrderDetailUnits.product_quantity_in_units'),
        ]);
        return $query;
    }


}
