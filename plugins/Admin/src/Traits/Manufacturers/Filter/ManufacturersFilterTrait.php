<?php
declare(strict_types=1);

namespace Admin\Traits\Manufacturers\Filter;

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use App\Services\DeliveryRhythmService;
use App\Services\CatalogService;

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

    private function getDefaultDate() {
        $defaultDate = '';
        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $defaultDate = Configure::read('app.timeHelper')->formatToDateShort(Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        } else {
            $defaultDate = (new DeliveryRhythmService())->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
        }
        return $defaultDate;
    }

    private function getDefaultActive() {
        return APP_ON;
    }

    public function getManufacturers($active, $dateFrom)
    {

        $conditions = [];
        if ($active != 'all') {
            $conditions = [
                'Manufacturers.active' => $active,
            ];
        }

        $manufacturersTable = FactoryLocator::get('Table')->get('Manufacturers');
        $query = $manufacturersTable->find('all',
        conditions: $conditions,
        contain: [
            'AddressManufacturers',
            'Customers'
        ])
        ->select($manufacturersTable)
        ->select($manufacturersTable->Customers)
        ->select($manufacturersTable->AddressManufacturers);

        $manufacturers = $this->paginate($query, [
            'sortableFields' => [
                'Manufacturers.name', 'Manufacturers.stock_management_enabled', 'Manufacturers.no_delivery_days', 'Manufacturers.is_private', 'Customers.' . Configure::read('app.customerMainNamePart'),
            ],
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ]);

        $paymentsTable = FactoryLocator::get('Table')->get('Payments');
        $orderDetailsTable = FactoryLocator::get('Table')->get('OrderDetails');
        $feedbacksTable = FactoryLocator::get('Table')->get('Feedbacks');

        foreach ($manufacturers as $manufacturer) {
            $catalogService = new CatalogService();
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