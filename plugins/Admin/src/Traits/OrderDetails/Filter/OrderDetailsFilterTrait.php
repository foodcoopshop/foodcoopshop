<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails\Filter;

use Cake\Core\Configure;
use App\Services\DeliveryRhythmService;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;
use App\Controller\Component\StringComponent;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait OrderDetailsFilterTrait 
{

    public function getDefaultOrderDetailId(): string
    {
        return '';
    }

    public function getPickupDay(int|string $orderDetailId): array
    {
        $pickupDay = [];
        if ($orderDetailId == '') {
            if (in_array('pickupDay', array_keys($this->getRequestQueryParams()))) {
                $pickupDay = h($this->getRequestQuery('pickupDay'));
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
        return $pickupDay;
    }

    public function getDefaultManufacturerId(): string
    {
        return '';
    }

    public function getDefaultDeposit(): string
    {
        return '';
    }

    public function getDefaultProductId(): string
    {
        return '';
    }

    public function getDefaultCustomerId(): string
    {
        return '';
    }

    public function getDefaultCartType(): null
    {
        return null;
    }

    public function getDefaultFilterByCartTypeEnabled(int|string|null $cartType): bool
    {
        return !is_null($cartType);
    }

    public function getDefaultGroupBy(): string
    {
        return '';
    }

    public function getOrderDetails(
        int|string $manufacturerId,
        int|string $productId,
        int|string $customerId,
        array $pickupDay,
        int|string $orderDetailId,
        float|string $deposit,
        string $groupBy,
        ?string $cartType
        ): SelectQuery
    {
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
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
                $customerTable = TableRegistry::getTableLocator()->get('Customers');
                $query = $customerTable->addCustomersNameForOrderSelect($query);
                $query->select($orderDetailsTable);
                $query->select(TableRegistry::getTableLocator()->get('CartProducts')); // need to be called before ->Carts
                $query->select(TableRegistry::getTableLocator()->get('Carts'));
                $query->select(TableRegistry::getTableLocator()->get('OrderDetailUnits'));
                $query->select(TableRegistry::getTableLocator()->get('OrderDetailFeedbacks'));
                $query->select($customerTable);
                $query->select(TableRegistry::getTableLocator()->get('Products'));
                $query->select(TableRegistry::getTableLocator()->get('Manufacturers'));
                $query->select(TableRegistry::getTableLocator()->get('AddressManufacturers'));
                if (Configure::read('appDb.FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS')) {
                    $query->select(TableRegistry::getTableLocator()->get('StorageLocations'));
                }
                break;
        }

        return $query;
    }

    private function applyUngroupedDefaultSort(array $orderDetails): array
    {
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
        if (!in_array('sort', array_keys($this->getRequestQueryParams()))) {
            array_multisort(
                $deliveryDay, SORT_ASC,
                $manufacturerName, SORT_ASC,
                $productName, SORT_ASC,
                $customerName, SORT_ASC,
                $orderDetails
            );
        }
        return $orderDetails;
    }

    private function addSelectGroupFields(SelectQuery $query): SelectQuery
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