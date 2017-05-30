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
class EmailOrderReminderShell extends AppShell
{

    public function main()
    {
        parent::main();

        if (! Configure::read('app.emailOrderReminderEnabled') || ! Configure::read('app.db_config_FCS_CART_ENABLED')) {
            return;
        }

        App::uses('AppEmail', 'Lib');

        $this->initSimpleBrowser(); // for loggedUserId

        $this->startTimeLogging();

        $conditions = array(
            'Customer.newsletter' => 1,
            'Customer.active' => 1
        );
        $conditions[] = $this->Customer->getConditionToExcludeHostingUser();
        $this->Customer->dropManufacturersInNextFind();
        $this->Customer->unbindModel(array(
            'hasMany' => array('PaidCashFreeOrders', 'CakePayments', 'ValidOrder')
        ));

        $activeOrderConditions = array();
        $activeOrderConditions[] = 'DATE_FORMAT(ActiveOrders.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('timeHelper')->getOrderPeriodFirstDay(). '\'';
        $activeOrderConditions[] = 'DATE_FORMAT(ActiveOrders.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('timeHelper')->getOrderPeriodLastDay(). '\'';
        $this->Customer->hasMany['ActiveOrders']['conditions'] = $activeOrderConditions;

        $customers = $this->Customer->find('all', array(
            'conditions' => $conditions,
            'order' => array(
                'Customer.name' => 'ASC'
            )
        ));

        $i = 0;
        $outString = '';
        foreach ($customers as $customer) {
            // customer has open orders, do not send email
            if (count($customer['ActiveOrders']) > 0) {
                continue;
            }

            $Email = new AppEmail();
            $Email->to($customer['Customer']['email'])
                ->template('Admin.email_order_reminder')
                ->emailFormat('html')
                ->subject('Bestell-Erinnerung ' . Configure::read('app.db_config_FCS_APP_NAME'))
                ->viewVars(array(
                  'customer' => $customer,
                  'lastOrderDayAsString' => (Configure::read('app.sendOrderListsWeekday') - date('N')) == 1 ? 'heute' : 'morgen'
                ))
                ->send();

            $outString .= $customer['Customer']['name'] . '<br />';

            $i ++;
        }

        $outString .= 'Verschickte E-Mails: ' . $i;

        $this->stopTimeLogging();

        $this->CakeActionLog->customSave('cronjob_email_order_reminder', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);
        $this->out($this->getRuntime());
    }
}
