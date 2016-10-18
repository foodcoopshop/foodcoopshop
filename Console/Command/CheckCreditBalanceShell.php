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
        
        $allowedPaymentTypes = array(
            'product',
            'deposit'
        );
        if (! Configure::read('app.isDepositPaymentCashless')) {
            $allowedPaymentTypes = array(
                'product'
            );
        }
        
        $this->Customer->hasMany['CakePayments']['conditions'][] = 'CakePayments.type IN ("' . join('", "', $allowedPaymentTypes) . '")';
        
        $this->Customer->dropManufacturersInNextFind();
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
            
            // sum up all relevant product payments
            $ppSum = 0;
            foreach ($customer['CakePayments'] as $paymentProduct) {
                $ppSum += $paymentProduct['amount'];
            }
            
            // sum up all relevant orders
            $orderSum = 0;
            foreach ($customer['PaidCashFreeOrders'] as $order) {
                $orderSum += $order['total_paid'];
                if (Configure::read('app.isDepositPaymentCashless') && strtotime($order['date_add']) > strtotime(Configure::read('app.depositPaymentCashlessStartDate'))) {
                    $orderSum += $order['total_deposit'];
                }
            }
            
            $delta = round($ppSum - $orderSum, 2); // sometimes strange values like 2.8421709430404E-14 appear
            
            if ($delta < 0) {
                $i ++;
                $totalOrderSum -= $delta;
                $delta = '€ ' . Configure::read('htmlHelper')->formatAsDecimal($delta); // delta is rendered in email view => do not use formatAsEuro here because of &nbsp;
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
        
        $outString .= 'Summe: ' . Configure::read('htmlHelper')->formatAsEuro($totalOrderSum * - 1) . '<br />';
        $outString .= 'Verschickte E-Mails: ' . $i;
        
        $this->stopTimeLogging();
        
        $this->CakeActionLog->customSave('cronjob_check_credit_balance', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());
        
        $this->out($outString);
        $this->out($this->getRuntime());
    }
}
?>