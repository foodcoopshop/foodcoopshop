<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Command;

use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use App\Model\Entity\Payment;


class CheckCreditBalanceCommand extends AppCommand
{

    public function execute(Arguments $args, ConsoleIo $io): int
    {

        if (!Configure::read('app.htmlHelper')->paymentIsCashless()) {
            return static::CODE_ERROR;
        }

        $this->startTimeLogging();

        $customersTable = $this->getTableLocator()->get('Customers');
        $customersTable->dropManufacturersInNextFind();
        $conditions = [
            'Customers.active' => 1,
            'Customers.check_credit_reminder_enabled' => 1,
        ];
        $conditions[] = $customersTable->getConditionToExcludeHostingUser();

        $customers = $customersTable->find('all',
        conditions: $conditions,
        contain: [
            'AddressCustomers' // to make exclude happen using dropManufacturersInNextFind
        ]);
        $customers = $customersTable->sortByVirtualField($customers, 'name');

        $i = 0;
        $deltaSum = 0;
        $outString = '';

        $lastCsvUploadDate = null;
        if (!Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
            $paymentTable = $this->getTableLocator()->get('Payments');
            $payment = $paymentTable->find('all',
            fields: [
                'Payments.date_add',
            ],
            conditions: [
                'Payments.type' => Payment::TYPE_PRODUCT,
                'Payments.date_transaction_add IS NOT NULL',

            ],
            order: [
                'Payments.date_add' => 'DESC',
            ])->first();
            if (!empty($payment)) {
                $lastCsvUploadDate = $payment->date_add;
            }
        }

        foreach ($customers as $customer) {
            $delta = $customersTable->getCreditBalance($customer->id_customer);
            $personalTransactionCode = null;
            if (!Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
                $personalTransactionCode = $customersTable->getPersonalTransactionCode($customer->id_customer);
            }

            if ($delta < Configure::read('appDb.FCS_CHECK_CREDIT_BALANCE_LIMIT')) {
                $i ++;
                $deltaSum -= $delta;
                $delta = Configure::read('app.numberHelper')->formatAsCurrency($delta);
                $outString .= $customer->name . ': ' . $delta . '<br />';
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('Admin.check_credit_balance');
                $email->setTo($customer->email)
                    ->setSubject(__('Please_add_credit'))
                    ->setViewVars([
                    'customer' => $customer,
                    'newsletterCustomer' => $customer,
                    'delta' => $delta,
                    'lastCsvUploadDate' => $lastCsvUploadDate,
                    'personalTransactionCode' => $personalTransactionCode,
                    ])
                    ->addToQueue();
            }
        }

        $outString .= __('Sum') . ': ' . Configure::read('app.numberHelper')->formatAsCurrency($deltaSum * - 1) . '<br />';
        $outString .= __('Sent_emails') . ': ' . $i;

        $this->stopTimeLogging();

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('cronjob_check_credit_balance', 0, 0, '', $outString . '<br />' . $this->getRuntime());

        $io->out($outString);
        $io->out($this->getRuntime());

        return static::CODE_SUCCESS;

    }

}
