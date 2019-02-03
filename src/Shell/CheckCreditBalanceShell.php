<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Shell;

use App\Mailer\AppEmail;
use Cake\Core\Configure;

class CheckCreditBalanceShell extends AppShell
{

    public function main()
    {
        parent::main();

        if (!Configure::read('app.htmlHelper')->paymentIsCashless()) {
            return;
        }

        $this->initHttpClient(); // for loggedUserId

        $this->startTimeLogging();

        $this->Customer->dropManufacturersInNextFind();
        $customers = $this->Customer->find('all', [
            'conditions' => [
                'Customers.active' => 1
            ],
            'contain' => [
                'AddressCustomers' // to make exclude happen using dropManufacturersInNextFind
            ]
        ]);
        $customers = $this->Customer->sortByVirtualField($customers, 'name');

        $i = 0;
        $deltaSum = 0;
        $outString = '';

        foreach ($customers as $customer) {
            $delta = $this->Customer->getCreditBalance($customer->id_customer);

            if ($delta < 0) {
                $i ++;
                $deltaSum -= $delta;
                $delta = Configure::read('app.numberHelper')->formatAsCurrency($delta);
                $outString .= $customer->name . ': ' . $delta . '<br />';
                $email = new AppEmail();
                $email->viewBuilder()->setTemplate('Admin.check_credit_balance');
                $email->setTo($customer->email)
                    ->setSubject(__('Your_credit_is_used_up'))
                    ->setViewVars([
                    'customer' => $customer,
                    'delta' => $delta
                    ])
                    ->send();
            }
        }

        $outString .= __('Sum') . ': ' . Configure::read('app.numberHelper')->formatAsCurrency($deltaSum * - 1) . '<br />';
        $outString .= __('Sent_emails') . ': ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_check_credit_balance', $this->httpClient->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);
        $this->out($this->getRuntime());
        
        return true;
    
    }
}
