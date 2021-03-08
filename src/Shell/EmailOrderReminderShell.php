<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * Cronjob works properly if it's called on Configure::read('app.timeHelper')->getSendOrderListsDay() -1 or -2
 * eg: Order lists are sent on Wednesday => EmailOrderReminder can be called on Tuesday or Monday
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Shell;

use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;

class EmailOrderReminderShell extends AppShell
{

    public function main()
    {

        // $this->cronjobRunDay can is set in unit test
        if (!isset($this->args[0])) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        } else {
            $this->cronjobRunDay = $this->args[0];
        }

        if (! Configure::read('app.emailOrderReminderEnabled')) {
            return true;
        }

        $nextDeliveryDay = Configure::read('app.timeHelper')->getNextDeliveryDay(strtotime($this->cronjobRunDay));
        if (Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL') != '') {
            $this->Product = $this->getTableLocator()->get('Products');
            if ($this->Product->deliveryBreakEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $nextDeliveryDay)) {
                return true;
            }
        }

        parent::main();

        $this->startTimeLogging();

        $conditions = [
            'Customers.email_order_reminder' => 1,
            'Customers.active' => 1
        ];
        $conditions[] = $this->Customer->getConditionToExcludeHostingUser();
        $this->Customer->dropManufacturersInNextFind();

        $this->Customer->getAssociation('ActiveOrderDetails')->setConditions([
            (new QueryExpression())->eq('DATE_FORMAT(ActiveOrderDetails.pickup_day, \'%Y-%m-%d\')', $nextDeliveryDay),
        ]);

        $customers = $this->Customer->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'ActiveOrderDetails',
                'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
            ]
        ]);
        $customers = $this->Customer->sortByVirtualField($customers, 'name');

        $i = 0;
        $outString = '';
        foreach ($customers as $customer) {
            // customer has open orders, do not send email
            if (count($customer->active_order_details) > 0) {
                continue;
            }

            $email = new AppMailer();
            $email->setTo($customer->email)
            ->viewBuilder()->setTemplate('Admin.email_order_reminder');
            $email->setSubject(__('Order_reminder') . ' ' . Configure::read('appDb.FCS_APP_NAME'))
            ->setViewVars([
                'customer' => $customer,
                'lastOrderDayAsString' => (Configure::read('app.timeHelper')->getSendOrderListsWeekday() - date('N', strtotime($this->cronjobRunDay))) == 1 ? __('today') : __('tomorrow')
            ])
            ->send();

            $outString .= $customer->name . '<br />';

            $i ++;
        }

        $outString .= __('Sent_emails') . ': ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_email_order_reminder', 0, 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);
        $this->out($this->getRuntime());

        return true;

    }
}
