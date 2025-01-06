<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * Cronjob works properly if it's called on Configure::read('app.timeHelper')->getSendOrderListsDay() -1 or -2
 * eg: Order lists are sent on Wednesday => EmailOrderReminder can be called on Tuesday or Monday
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Command;

use App\Services\DeliveryRhythmService;
use App\Mailer\AppMailer;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use App\Command\Traits\CronjobCommandTrait;

class EmailOrderReminderCommand extends AppCommand
{

    use CronjobCommandTrait;

    public function execute(Arguments $args, ConsoleIo $io): int
    {

        $this->setCronjobRunDay($args);

        if (! Configure::read('app.emailOrderReminderEnabled')) {
            return static::CODE_SUCCESS;
        }

        $productsTable = $this->getTableLocator()->get('Products');
        $dummyProduct = $productsTable->newEntity([
            'delivery_rhythm_type' => 'week',
            'delivery_rhythm_count' => '1',
            'is_stock_product' => '0',
        ]);
        $nextDeliveryDay = (new DeliveryRhythmService())->getNextPickupDayForProduct($dummyProduct, $this->cronjobRunDay);

        if (Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL') != '') {
            $productsTable = $this->getTableLocator()->get('Products');
            if ($productsTable->deliveryBreakGlobalEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $nextDeliveryDay)) {
                return static::CODE_SUCCESS;
            }
        }

        $this->startTimeLogging();

        $conditions = [
            'Customers.email_order_reminder_enabled' => 1,
            'Customers.active' => 1,
        ];
        $customersTable = $this->getTableLocator()->get('Customers');
        $conditions[] = $customersTable->getConditionToExcludeHostingUser();
        $customersTable->dropManufacturersInNextFind();

        if (Configure::read('app.applyOpenOrderCheckForOrderReminder')) {
            $diffOrderCreatedAndDeliveryDayInDays = 6;
            $exp = new QueryExpression();
            $customersTable->getAssociation('ActiveOrderDetails')->setConditions([
                $exp->eq('DATE_FORMAT(ActiveOrderDetails.pickup_day, \'%Y-%m-%d\')', $nextDeliveryDay),
                $exp->lte('DATEDIFF(ActiveOrderDetails.pickup_day, DATE_FORMAT(ActiveOrderDetails.created, \'%Y-%m-%d\'))', $diffOrderCreatedAndDeliveryDayInDays),
            ]);
        }

        $customers = $customersTable->find('all',
        conditions: $conditions,
        contain: [
            'ActiveOrderDetails',
            'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
        ]);
        $customers = $customersTable->sortByVirtualField($customers, 'name');

        $i = 0;
        $outString = '';

        $sendOrderListWeekday = ((new DeliveryRhythmService())->getSendOrderListsWeekday() -1) % 7;
        $cronjobRunWeekday = date('N', strtotime($this->cronjobRunDay)) % 7;
        $lastOrderDayDiff = $sendOrderListWeekday - $cronjobRunWeekday;

        $lastOrderDayAsString = match($lastOrderDayDiff) {
            0 => __('today'),
            1 => __('tomorrow'),
            default => Configure::read('app.timeHelper')->getWeekdayName($sendOrderListWeekday),
        };

        foreach ($customers as $customer) {
            // customer has open orders, do not send email
            if (Configure::read('app.applyOpenOrderCheckForOrderReminder')) {
                if (count($customer->active_order_details) > 0) {
                    continue;
                }
            }

            $email = new AppMailer();
            $email->setTo($customer->email)
                ->viewBuilder()->setTemplate('Admin.email_order_reminder');
            $email->setSubject(__('Order_reminder') . ' ' . Configure::read('appDb.FCS_APP_NAME'))
                ->setViewVars([
                    'customer' => $customer,
                    'newsletterCustomer' => $customer,
                    'lastOrderDayDiff' => $lastOrderDayDiff,
                    'lastOrderDayAsString' => $lastOrderDayAsString,
                ])
            ->addToQueue();

            $outString .= $customer->name . '<br />';

            $i ++;
        }

        $outString .= __('Sent_emails') . ': ' . $i;

        $this->stopTimeLogging();

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('cronjob_email_order_reminder', 0, 0, '', $outString . '<br />' . $this->getRuntime());

        $io->out($outString);
        $io->out($this->getRuntime());

        return static::CODE_SUCCESS;

    }
}
