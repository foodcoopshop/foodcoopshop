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

        $categoryIds = h($this->getRequest()->getQuery('categoryIds', $this->getDefaultCategoryIds()));
        $this->set('categoryIds', $categoryIds);

        $taxRate = h($this->getRequest()->getQuery('taxRate', $this->getDefaultTaxRate()));
        $this->set('taxRate', $taxRate);

        $additionalFiltersEnabled = h($this->getRequest()->getQuery('additionalFiltersEnabled', $this->getDefaultAdditionalFiltersEnabled($cartType, $categoryIds, $taxRate)));
        $this->set('additionalFiltersEnabled', $additionalFiltersEnabled);

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

        if ($additionalFiltersEnabled) {
            $categoriesTable = $this->getTableLocator()->get('Categories');
            $this->set('categoriesForDropdown', $categoriesTable->getForSelect(null, true));
            $taxesTable = $this->getTableLocator()->get('Taxes');
            $taxRatesForDropdown = $taxesTable->getForDropdown(true);
            $this->set('taxRatesForDropdown', $taxRatesForDropdown);
        }

        $this->set('title_for_layout', __d('admin', 'Orders'));

        $sums = [
            'records_count' => 0,
            'amount' => 0,
            'price' => 0,
            'price_net' => 0,
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

        $query = $this->getOrderDetails($manufacturerId, $productId, $customerId, $pickupDay, $orderDetailId, $deposit, $groupBy, $cartType, $taxRate, $categoryIds);

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
                $sums['price_net'] += $orderDetail->total_price_tax_excl;
                $sums['amount'] += $orderDetail->product_amount;
                $sums['deposit'] += $orderDetail->deposit;
            } else {
                $orderDetailIsArray = is_array($orderDetail);
                $sums['price'] += $orderDetailIsArray ? $orderDetail['sum_price'] : $orderDetail->sum_price;
                $sums['price_net'] += $orderDetailIsArray ? $orderDetail['sum_price_net'] : $orderDetail->sum_price_net;
                $sums['amount'] += $orderDetailIsArray ? $orderDetail['sum_amount'] : $orderDetail->sum_amount;
                if ($groupBy == 'manufacturer') {
                    $sums['reduced_price'] += $orderDetailIsArray ? $orderDetail['reduced_price'] : $orderDetail->reduced_price;
                }
                $sums['deposit'] += $orderDetailIsArray ? $orderDetail['sum_deposit'] : $orderDetail->sum_deposit;
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

     /**
     * @param \App\Model\Entity\OrderDetail[] $orderDetails
     * @return \App\Model\Entity\OrderDetail[]|list<array<string, mixed>>
     */
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
                $orderDetails = $this->applyUngroupedDefaultSort($orderDetails);
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
