<?php
declare(strict_types=1);

namespace Admin\Traits\Manufacturers;

use Cake\Core\Configure;
use App\Services\CatalogService;
use App\Services\DeliveryRhythmService;

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

trait IndexTrait
{

    public function index()
    {

        $dateFrom = $this->getDefaultDate();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = $this->getDefaultDate();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $active = 1; // default value
        if (in_array('active', array_keys($this->getRequest()->getQueryParams()))) {
            $active = h($this->getRequest()->getQuery('active'));
        }
        $this->set('active', $active);

        $conditions = [];
        if ($active != 'all') {
            $conditions = [
                'Manufacturers.active' => $active
            ];
        }

        $query = $this->Manufacturer->find('all',
        conditions: $conditions,
        contain: [
            'AddressManufacturers',
            'Customers'
        ])
        ->select($this->Manufacturer)
        ->select($this->Manufacturer->Customers)
        ->select($this->Manufacturer->AddressManufacturers);

        $manufacturers = $this->paginate($query, [
            'sortableFields' => [
                'Manufacturers.name', 'Manufacturers.stock_management_enabled', 'Manufacturers.no_delivery_days', 'Manufacturers.is_private', 'Customers.' . Configure::read('app.customerMainNamePart'),
            ],
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ]);

        // extract all email addresses for button
        $emailAddresses = [];
        $emailAddresses = $query->all()->extract('address_manufacturer.email')->toArray();
        $emailAddresses = array_unique($emailAddresses);
        $this->set('emailAddresses', $emailAddresses);

        $this->Product = $this->getTableLocator()->get('Products');
        $this->Payment = $this->getTableLocator()->get('Payments');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Feedback = $this->getTableLocator()->get('Feedbacks');

        foreach ($manufacturers as $manufacturer) {
            $catalogService = new CatalogService();
            $manufacturer->product_count = $catalogService->getProductsByManufacturerId($manufacturer->id_manufacturer, true);
            $sumDepositDelivered = $this->OrderDetail->getDepositSum($manufacturer->id_manufacturer, false);
            $sumDepositReturned = $this->Payment->getMonthlyDepositSumByManufacturer($manufacturer->id_manufacturer, false);
            $manufacturer->sum_deposit_delivered = $sumDepositDelivered[0]['sumDepositDelivered'];
            $manufacturer->deposit_credit_balance = $sumDepositDelivered[0]['sumDepositDelivered'] - $sumDepositReturned[0]['sumDepositReturned'];
            if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
                $manufacturer->variable_member_fee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
            }
            if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
                $customer = $this->Manufacturer->getCustomerRecord($manufacturer->address_manufacturer->email);
                if (!empty($customer)) {
                    $manufacturer->feedback = $this->Feedback->find('all', conditions: [
                        'Feedbacks.customer_id' => $customer->id_customer,
                    ])->first();
                }
                $manufacturer->customer_record_id = $customer->id_customer ?? 0;
            }
            $manufacturer->sum_open_order_detail = $this->OrderDetail->getOpenOrderDetailSum($manufacturer->id_manufacturer, $dateFrom);
        }
        $this->set('manufacturers', $manufacturers);

        $this->set('title_for_layout', __d('admin', 'Manufacturers'));
    }

    private function getDefaultDate() {
        $defaultDate = '';
        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $defaultDate = Configure::read('app.timeHelper')->formatToDateShort(Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        } else {
            $defaultDate = (new DeliveryRhythmService())->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
        }
        return $defaultDate;
    }

}