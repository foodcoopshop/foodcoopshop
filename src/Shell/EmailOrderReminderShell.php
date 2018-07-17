<?php

/**
 * EmailOrderReminderShell
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * Cronjob works properly if it's called on Configure::read('app.sendOrderListsWeekDay') -1 or -2
 * eg: Order lists are sent on Wednesday => EmailOrderReminder can be called on Tuesday or Monday
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Shell;

use App\Mailer\AppEmail;
use Cake\Core\Configure;

class EmailOrderReminderShell extends AppShell
{

    public function main()
    {

        if (! Configure::read('app.emailOrderReminderEnabled') || ! Configure::read('appDb.FCS_CART_ENABLED')) {
            return;
        }

        parent::main();

        $this->initSimpleBrowser(); // for loggedUserId

        $this->startTimeLogging();

        $conditions = [
            'Customers.email_order_reminder' => 1,
            'Customers.active' => 1
        ];
        $conditions[] = $this->Customer->getConditionToExcludeHostingUser();
        $this->Customer->dropManufacturersInNextFind();

        $this->Customer->getAssociation('ActiveOrders')->setConditions(
            [
                'DATE_FORMAT(ActiveOrders.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate(
                    Configure::read('app.timeHelper')->getOrderPeriodFirstDay(
                        Configure::read('app.timeHelper')->getCurrentDay()
                    )
                ). '\'',
                'DATE_FORMAT(ActiveOrders.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate(
                    Configure::read('app.timeHelper')->getOrderPeriodLastDay(
                        Configure::read('app.timeHelper')->getCurrentDay()
                    )
                ). '\'',
            ]
        );

        $customers = $this->Customer->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'ActiveOrders',
                'AddressCustomers' // to make exclude happen using dropManufacturersInNextFind
            ]
        ]);
        $customers = $this->Customer->sortByVirtualField($customers, 'name');

        $i = 0;
        $outString = '';
        foreach ($customers as $customer) {
            // customer has open orders, do not send email
            if (count($customer->active_orders) > 0) {
                continue;
            }

            $email = new AppEmail();
            $email->setTo($customer->email)
            ->setTemplate('Admin.email_order_reminder')
            ->setSubject(__('Order_reminder') . ' ' . Configure::read('appDb.FCS_APP_NAME'))
            ->setViewVars([
                'customer' => $customer,
                'lastOrderDayAsString' => (Configure::read('app.sendOrderListsWeekday') - date('N')) == 1 ? __('today') : __('tomorrow')
            ])
            ->send();

            $outString .= $customer->name . '<br />';

            $i ++;
        }

        $outString .= __('Sent_emails') . ': ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_email_order_reminder', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);
        $this->out($this->getRuntime());
    }
}
