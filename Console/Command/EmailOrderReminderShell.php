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
        
        // hersteller sind zwar kunden (hersteller login, haben aber keine kunden-adresse und werden somit nicht berücksichtigt)
        $customers = $this->Customer->find('all', array(
            'conditions' => array(
                'Customer.newsletter' => 1,
                'Customer.active' => 1
            ),
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
                'customer' => $customer
            ))
                ->send();
            
            $this->out($message);
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
?>