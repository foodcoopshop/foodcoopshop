<?php
declare(strict_types=1);

namespace Admin\Traits\Customers\Filter;

use Cake\Utility\Hash;
use Cake\Core\Configure;
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

trait CustomersFilterTrait 
{

    public function getDefaultYear(): string
    {
        return date('Y');
    }

    public function getDefaultActive(): int
    {
        return APP_ON;
    }

    public function getDefaultNewsletter(): string
    {
        return '';
    }

    public function getCustomers($active, $year, $newsletter): array
    {

        $customersTable = TableRegistry::getTableLocator()->get('Customers');
        $addressCustomersTable = TableRegistry::getTableLocator()->get('AddressCustomers');
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $feedbacksTable = TableRegistry::getTableLocator()->get('Feedbacks');

        $conditions = [];
        if ($active != 'all') {
            $conditions['Customers.active'] = $active;
        }

        if ($newsletter != '') {
            $conditions['Customers.newsletter_enabled'] = $newsletter;
        }

        $conditions[] = $customersTable->getConditionToExcludeHostingUser();

        $customersTable->dropManufacturersInNextFind();

        $contain = [
            'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
        ];

        if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
            $contain[] = 'Feedbacks';
        }

        $query = $customersTable->find('all',
        conditions: $conditions,
        contain: $contain);
        $query = $customersTable->addCustomersNameForOrderSelect($query);
        $query->select($customersTable);
        $query->select($addressCustomersTable);
        if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
            $query->select($feedbacksTable);
        }

        $query->select([
            'credit_balance' => 'Customers.id_customer', // add fake field to make custom sort icon work and avoid "Column not found: 1054 Unknown column"
            'last_pickup_day' => 'Customers.id_customer',
            'member_fee' => 'Customers.id_customer',
        ]);
        $query->select($addressCustomersTable);
        $customers = $this->paginate($query, [
            'sortableFields' => [
                'CustomerNameForOrder',
                'Customers.id_default_group',
                'Customers.id_customer',
                'Customers.email',
                'Customers.active',
                'Customers.email_order_reminder_enabled',
                'Customers.check_credit_reminder_enabled',
                'Customers.date_add',
                'Customers.newsletter_enabled',
                'Feedbacks.modified',
                'credit_balance',
                'member_fee',
                'last_pickup_day',
            ],
            'order' => $customersTable->getCustomerOrderClause($this->getRequestQuery('direction') ?? 'ASC'),
        ]);

        $i = 0;

        foreach ($customers as $customer) {
            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $customer->credit_balance = $customersTable->getCreditBalance($customer->id_customer);
            }
            $customer->different_pickup_day_count = $orderDetailsTable->getDifferentPickupDayCountByCustomerId($customer->id_customer);
            $customer->last_pickup_day = $orderDetailsTable->getLastPickupDay($customer->id_customer);
            $customer->last_pickup_day_sort = '';
            if (!is_null($customer->last_pickup_day)) {
                $customer->last_pickup_day_sort = $customer->last_pickup_day->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
            }
            $customer->member_fee = $orderDetailsTable->getMemberFee($customer->id_customer, $year);
            $i ++;
        }

        $customers = $customers->toArray();

        if (in_array('sort', array_keys($this->getRequestQueryParams())) 
            && in_array($this->getRequestQuery('sort'), ['credit_balance', 'member_fee', 'last_pickup_day',])) {
            $path = '{n}.' .$this->getRequestQuery('sort');
            $type = 'numeric';
            if ($this->getRequestQuery('sort') == 'last_pickup_day') {
                $path .= '_sort';
                $type = 'locale';
            }
            $customers = Hash::sort($customers, $path, $this->getRequestQuery('direction'), [
                'type' => $type,
                'ignoreCase' => true,
            ]);
        }

        return $customers;

    }

}