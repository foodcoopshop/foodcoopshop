<?php
declare(strict_types=1);

namespace Admin\Traits\Manufacturers\Filter;

use Cake\Core\Configure;
use App\Services\DeliveryRhythmService;
use App\Services\CatalogService;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\ORM\TableRegistry;
use Cake\ORM\Query\SelectQuery;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait ManufacturersFilterTrait 
{

    private function getDefaultDate(): string
    {
        $defaultDate = '';
        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $defaultDate = Configure::read('app.timeHelper')->formatToDateShort(Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        } else {
            $defaultDate = (new DeliveryRhythmService())->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
        }
        return $defaultDate;
    }

    private function getDefaultActive(): int
    {
        return APP_ON;
    }

    public function getManufacturers($active, $dateFrom): SelectQuery|PaginatedInterface
    {

        $conditions = [];
        if ($active != 'all') {
            $conditions = [
                'Manufacturers.active' => $active,
            ];
        }

        $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
        $customersTable = TableRegistry::getTableLocator()->get('Customers');
        $addressManufacturersTable = TableRegistry::getTableLocator()->get('AddressManufacturers');

        $query = $manufacturersTable->find('all',
        conditions: $conditions,
        contain: [
            'AddressManufacturers',
            'Customers'
        ])
        ->select($manufacturersTable)
        ->select($customersTable)
        ->select($addressManufacturersTable);

        $manufacturers = $this->paginate($query, [
            'sortableFields' => [
                'Manufacturers.name', 'Manufacturers.stock_management_enabled', 'Manufacturers.no_delivery_days', 'Manufacturers.is_private', 'Customers.' . Configure::read('app.customerMainNamePart'),
            ],
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ]);

        $paymentsTable = TableRegistry::getTableLocator()->get('Payments');
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $feedbacksTable = TableRegistry::getTableLocator()->get('Feedbacks');

        $catalogService = new CatalogService();
        $catalogService->showOnlyProductsForNextWeekFilterEnabled = false;
        
        foreach ($manufacturers as $manufacturer) {
            $manufacturer->product_count = $catalogService->getProductsByManufacturerId($manufacturer->id_manufacturer, true);
            $sumDepositDelivered = $orderDetailsTable->getDepositSum($manufacturer->id_manufacturer, false);
            $sumDepositReturned = $paymentsTable->getMonthlyDepositSumByManufacturer($manufacturer->id_manufacturer, false);
            $manufacturer->sum_deposit_delivered = $sumDepositDelivered[0]['sumDepositDelivered'];
            $manufacturer->deposit_credit_balance = $sumDepositDelivered[0]['sumDepositDelivered'] - $sumDepositReturned[0]['sumDepositReturned'];
            if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
                $manufacturer->variable_member_fee = $manufacturersTable->getOptionVariableMemberFee($manufacturer->variable_member_fee);
            }
            if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
                $customer = $manufacturersTable->getCustomerRecord($manufacturer->address_manufacturer->email);
                if (!empty($customer)) {
                    $manufacturer->feedback = $feedbacksTable->find('all', conditions: [
                        'Feedbacks.customer_id' => $customer->id_customer,
                    ])->first();
                }
                $manufacturer->customer_record_id = $customer->id_customer ?? 0;
            }
            $manufacturer->sum_open_order_detail = $orderDetailsTable->getOpenOrderDetailSum($manufacturer->id_manufacturer, $dateFrom);
        }

        return $manufacturers;

    }

}