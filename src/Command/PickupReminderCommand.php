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
use Cake\Database\Expression\QueryExpression;
use App\Services\DeliveryRhythmService;
use App\Command\Traits\CronjobCommandTrait;

class PickupReminderCommand extends AppCommand
{

    use CronjobCommandTrait;

    public function execute(Arguments $args, ConsoleIo $io): int
    {

        $this->setCronjobRunDay($args);

        $this->startTimeLogging();

        $conditions = [
            'Customers.pickup_day_reminder_enabled' => 1,
            'Customers.active' => 1,
        ];
        $customersTable = $this->getTableLocator()->get('Customers');
        $conditions[] = $customersTable->getConditionToExcludeHostingUser();
        $customersTable->dropManufacturersInNextFind();

        $customers = $customersTable->find('all',
        conditions: $conditions,
        contain: [
            'AddressCustomers' // to make exclude happen using dropManufacturersInNextFind
        ]);
        $customers = $customersTable->sortByVirtualField($customers, 'name');
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');

        $nextPickupDay = (new DeliveryRhythmService())->getDeliveryDay(strtotime($this->cronjobRunDay));
        $formattedPickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday($nextPickupDay);
        $diffOrderAndPickupInDays = 6;

        $i = 0;
        $outString = '';
        $exp = new QueryExpression();
        foreach ($customers as $customer) {

            $futureOrderDetails = $orderDetailsTable->find('all',
            conditions: [
                'OrderDetails.id_customer' => $customer->id_customer,
                $exp->eq('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', date('Y-m-d', $nextPickupDay)),
                $exp->gt('DATEDIFF(OrderDetails.pickup_day, DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\'))', $diffOrderAndPickupInDays),
            ],
            contain: [
                'Products.Manufacturers'
            ],
            order: [
                'OrderDetails.product_name' => 'ASC'
            ])->toArray();

            if (empty($futureOrderDetails)) {
                continue;
            }

            $email = new AppMailer();
            $email->setTo($customer->email)
            ->viewBuilder()->setTemplate('Admin.pickup_reminder');
            $email->setSubject(__('Pickup_reminder_for') . ' ' . $formattedPickupDay)
            ->setViewVars([
                'customer' => $customer,
                'newsletterCustomer' => $customer,
                'diffOrderAndPickupInDays' => $diffOrderAndPickupInDays,
                'formattedPickupDay' => $formattedPickupDay,
                'futureOrderDetails' => $futureOrderDetails
            ])
            ->addToQueue();

            $outString .= $customer->name . '<br />';

            $i ++;
        }

        $outString .= __('Sent_emails') . ': ' . $i;

        $this->stopTimeLogging();

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('cronjob_pickup_reminder', 0, 0, '', $outString . '<br />' . $this->getRuntime());

        $io->out($outString);
        $io->out($this->getRuntime());

        return static::CODE_SUCCESS;

    }
}
