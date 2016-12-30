<?php

/**
 * CheckCreditBalanceShell
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
class CheckCreditBalanceShell extends AppShell
{

    public $uses = array(
        'Customer',
        'CakeActionLog'
    );

    public function main()
    {
        parent::main();
        
        $this->initSimpleBrowser(); // for loggedUserId
        
        $this->startTimeLogging();
        
        App::uses('AppEmail', 'Lib');
        
        $this->Customer->dropManufacturersInNextFind();
        $this->Customer->recursive = -1;
        $customers = $this->Customer->find('all', array(
            'conditions' => array(
                'Customer.active' => 1
            ),
            'order' => array(
                'Customer.name' => 'ASC'
            )
        ));
        
        $i = 0;
        $outString = '';
        $totalOrderSum = 0;
        
        foreach ($customers as $customer) {
            
            $delta = $this->Customer->getCreditBalance($customer['Customer']['id_customer']);
            
            if ($delta < 0) {
                $i ++;
                $deltaSum -= $delta;
                $delta = '€ ' . Configure::read('htmlHelper')->formatAsDecimal($delta); // creditBalance is rendered in email view => do not use formatAsEuro here because of &nbsp;
                $outString .= $customer['Customer']['name'] . ': ' . $delta . '<br />';
                $email = new AppEmail();
                $email->template('Admin.check_credit_balance')
                    ->to($customer['Customer']['email'])
                    ->emailFormat('html')
                    ->subject('Dein Guthaben ist aufgebraucht')
                    ->viewVars(array(
                    'customer' => $customer,
                    'delta' => $delta
                ))
                    ->send();
            }
        }
        
        $outString .= 'Summe: ' . Configure::read('htmlHelper')->formatAsEuro($deltaSum * - 1) . '<br />';
        $outString .= 'Verschickte E-Mails: ' . $i;
        
        $this->stopTimeLogging();
        
        $this->CakeActionLog->customSave('cronjob_check_credit_balance', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());
        
        $this->out($outString);
        $this->out($this->getRuntime());
    }
}
?>