<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use Cake\Core\Configure;
use Cake\Utility\Hash;

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

trait IndexTrait {

    public function index()
    {
        $active = 1; // default value
        if (in_array('active', array_keys($this->getRequest()->getQueryParams()))) {
            $active = h($this->getRequest()->getQuery('active'));
        }
        $this->set('active', $active);

        $year = h($this->getRequest()->getQuery('year'));
        if (!in_array('year', array_keys($this->getRequest()->getQueryParams()))) {
            $year = date('Y');
        }
        $this->set('year', $year);

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');

        $firstOrderYear = $this->OrderDetail->getFirstOrderYear();
        $this->set('firstOrderYear', $firstOrderYear);

        $lastOrderYear = $this->OrderDetail->getLastOrderYear();
        $this->set('lastOrderYear', $lastOrderYear);

        $years = null;
        if ($lastOrderYear !== false && $firstOrderYear !== false) {
            $years = Configure::read('app.timeHelper')->getAllYearsUntilThisYear($lastOrderYear, $firstOrderYear, __d('admin', 'Member_fee') . ' ');
        }
        $this->set('years', $years);

        $conditions = [];
        if ($active != 'all') {
            $conditions['Customers.active'] = $active;
        }

        if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
            $newsletter = h($this->getRequest()->getQuery('newsletter'));
            if (!in_array('newsletter', array_keys($this->getRequest()->getQueryParams()))) {
                $newsletter = '';
            }
            $this->set('newsletter', $newsletter);
            if ($newsletter != '') {
                $conditions['Customers.newsletter_enabled'] = $newsletter;
            }
        }

        $this->Customer = $this->getTableLocator()->get('Customers');

        $conditions[] = $this->Customer->getConditionToExcludeHostingUser();

        $this->Customer->dropManufacturersInNextFind();

        $contain = [
            'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
        ];

        if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
            $contain[] = 'Feedbacks';
        }

        $query = $this->Customer->find('all',
        conditions: $conditions,
        contain: $contain);
        $query = $this->Customer->addCustomersNameForOrderSelect($query);
        $query->select($this->Customer);
        $query->select($this->Customer->AddressCustomers);
        if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
            $query->select($this->Customer->Feedbacks);
        }

        $query->select([
            'credit_balance' => 'Customers.id_customer', // add fake field to make custom sort icon work and avoid "Column not found: 1054 Unknown column"
            'last_pickup_day' => 'Customers.id_customer',
            'member_fee' => 'Customers.id_customer',
        ]);
        $query->select($this->Customer->AddressCustomers);
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
            'order' => $this->Customer->getCustomerOrderClause(),
        ]);

        $i = 0;
        $this->Payment = $this->getTableLocator()->get('Payments');

        foreach ($customers as $customer) {
            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $customer->credit_balance = $this->Customer->getCreditBalance($customer->id_customer);
            }
            $customer->different_pickup_day_count = $this->OrderDetail->getDifferentPickupDayCountByCustomerId($customer->id_customer);
            $customer->last_pickup_day = $this->OrderDetail->getLastPickupDay($customer->id_customer);
            $customer->last_pickup_day_sort = '';
            if (!is_null($customer->last_pickup_day)) {
                $customer->last_pickup_day_sort = $customer->last_pickup_day->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
            }
            $customer->member_fee = $this->OrderDetail->getMemberFee($customer->id_customer, $year);
            $i ++;
        }

        if (in_array('sort', array_keys($this->getRequest()->getQueryParams())) 
            && in_array($this->getRequest()->getQuery('sort'), ['credit_balance', 'member_fee', 'last_pickup_day',])) {
            $path = '{n}.' .$this->getRequest()->getQuery('sort');
            $type = 'numeric';
            if ($this->getRequest()->getQuery('sort') == 'last_pickup_day') {
                $path .= '_sort';
                $type = 'locale';
            }
            $customers = Hash::sort($customers->toArray(), $path, $this->getRequest()->getQuery('direction'), [
                'type' => $type,
                'ignoreCase' => true,
            ]);
        }

        $this->set('customers', $customers);

        $this->set('title_for_layout', __d('admin', 'Members'));
    }

}