<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use App\Controller\Component\StringComponent;
use Cake\ORM\Query\SelectQuery;
use Admin\Traits\OrderDetails\Filter\OrderDetailsFilterTrait;
use Admin\Traits\QueryFilterTrait;

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

    use QueryFilterTrait;
    use OrderDetailsFilterTrait;

    public function index(): void
    {

        $orderDetailId = h($this->getRequest()->getQuery('orderDetailId', $this->getDefaultOrderDetailId()));
        $this->set('orderDetailId', $orderDetailId);

        $pickupDay = $this->getPickupDay($orderDetailId);
        $this->set('pickupDay', $pickupDay);

        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId', $this->getDefaultManufacturerId()));
        $this->set('manufacturerId', $manufacturerId);

        $deposit = h($this->getRequest()->getQuery('deposit', $this->getDefaultDeposit()));
        $this->set('deposit', $deposit);

        $productId = h($this->getRequest()->getQuery('productId', $this->getDefaultProductId()));
        $this->set('productId', $productId);

        $customerId = h($this->getRequest()->getQuery('customerId', $this->getDefaultCustomerId()));
        $this->set('customerId', $customerId);

        $cartType = h($this->getRequest()->getQuery('cartType', $this->getDefaultCartType()));
        $this->set('cartType', $cartType);

        $filterByCartTypeEnabled = h($this->getRequest()->getQuery('filterByCartTypeEnabled', $this->getDefaultFilterByCartTypeEnabled($cartType)));
        $this->set('filterByCartTypeEnabled', $filterByCartTypeEnabled);

        $groupBy = h($this->getRequestQuery('groupBy', $this->getDefaultGroupBy()));
        if ($this->identity->isManufacturer() && $groupBy != 'product') {
          $groupBy = '';
        }
        $this->set('groupBy', $groupBy);

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

        if (count($pickupDay) > 1) {
            if (Configure::read('app.timeHelper')->isDifferenceGreaterThanTwoYears($pickupDay[0], $pickupDay[1])) {
                $this->Flash->error(__d('admin', 'The date range must not be greater than two years.'));
                $this->set('sums', $sums);
                return;
            }
        }

        $query = $this->getOrderDetails($manufacturerId, $productId, $customerId, $pickupDay, $orderDetailId, $deposit, $groupBy, $cartType);

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
            ],
        ])->toArray();

        $orderDetails = $this->prepareGroupedOrderDetails($orderDetails, $groupBy);

        $this->set('orderDetails', $orderDetails);

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

    }

    private function prepareGroupedOrderDetails(array $orderDetails, string $groupBy): array
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

    private function getSortFieldForGroupedOrderDetails(string $manufacturerNameField): string
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

}
