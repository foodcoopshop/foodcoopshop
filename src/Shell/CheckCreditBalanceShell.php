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

        $this->initSimpleBrowser(); // for loggedUserId

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

            if ($customer->email == 'fcs-demo-mitglied@mailinator.com' || $delta < 0) {
                $i ++;
                $deltaSum -= $delta;
                $delta = '€ ' . Configure::read('app.htmlHelper')->formatAsDecimal($delta); // creditBalance is rendered in email view => do not use formatAsEuro here because of &nbsp;
                $outString .= $customer->name . ': ' . $delta . '<br />';
                $email = new AppEmail();
                $email->setTemplate('Admin.check_credit_balance')
                    ->setTo($customer->email)
                    ->setSubject('Dein Guthaben ist aufgebraucht')
                    ->setViewVars([
                    'customer' => $customer,
                    'delta' => $delta
                    ])
                    ->send();
            }
        }

        $outString .= 'Summe: ' . Configure::read('app.htmlHelper')->formatAsEuro($deltaSum * - 1) . '<br />';
        $outString .= 'Verschickte E-Mails: ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_check_credit_balance', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);
        $this->out($this->getRuntime());
    }
}
