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

use Cake\Core\Configure;

class EmailOrderReminderShell extends AppShell
{

    public function main()
    {
        parent::main();

        if (! Configure::read('app.emailOrderReminderEnabled') || ! Configure::read('appDb.FCS_CART_ENABLED')) {
            return;
        }

        $this->initSimpleBrowser(); // for loggedUserId

        $this->startTimeLogging();

        $conditions = [
            'Customers.newsletter' => 1,
            'Customers.active' => 1
        ];
        $conditions[] = $this->Customer->getConditionToExcludeHostingUser();
        $this->Customer->dropManufacturersInNextFind();
        $this->Customer->unbindModel([
            'hasMany' => ['PaidCashFreeOrders', 'Payments', 'ValidOrder']
        ]);

        $this->Customer->hasMany['ActiveOrders']['conditions'][] = 'DATE_FORMAT(ActiveOrders.date_add, \'%d.%m.%Y\') >= \'' . Configure::read('app.timeHelper')->getOrderPeriodFirstDay(Configure::read('app.timeHelper')->getCurrentDay()). '\'';
        $this->Customer->hasMany['ActiveOrders']['conditions'][] = 'DATE_FORMAT(ActiveOrders.date_add, \'%d.%m.%Y\') <= \'' . Configure::read('app.timeHelper')->getOrderPeriodLastDay(Configure::read('app.timeHelper')->getCurrentDay()). '\'';

        $customers = $this->Customer->find('all', [
            'conditions' => $conditions,
            'order' => [
                'Customers.name' => 'ASC'
            ]
        ]);

        $i = 0;
        $outString = '';
        foreach ($customers as $customer) {
            // customer has open orders, do not send email
            if (count($customer['ActiveOrders']) > 0) {
                continue;
            }

            $Email = new AppEmail();
            $Email->to($customer['Customers']['email'])
                ->template('Admin.email_order_reminder')
                ->emailFormat('html')
                ->subject('Bestell-Erinnerung ' . Configure::read('appDb.FCS_APP_NAME'))
                ->viewVars([
                  'customer' => $customer,
                  'lastOrderDayAsString' => (Configure::read('app.sendOrderListsWeekday') - date('N')) == 1 ? 'heute' : 'morgen'
                ])
                ->send();

            $outString .= $customer['Customers']['name'] . '<br />';

            $i ++;
        }

        $outString .= 'Verschickte E-Mails: ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_email_order_reminder', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);
        $this->out($this->getRuntime());
    }
}
