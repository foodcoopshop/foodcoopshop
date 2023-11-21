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

use App\Lib\DeliveryRhythm\DeliveryRhythm;
use App\Mailer\AppMailer;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;

class EmailOrderReminderCommand extends AppCommand
{

    public $cronjobRunDay;
    public $ActionLog;
    public $Customer;
    public $Product;

    public function execute(Arguments $args, ConsoleIo $io)
    {

        if (!$args->getArgumentAt(0)) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        } else {
            $this->cronjobRunDay = $args->getArgumentAt(0);
        }

        if (! Configure::read('app.emailOrderReminderEnabled')) {
            return static::CODE_SUCCESS;
        }

        $productsTable = $this->getTableLocator()->get('Products');
        $dummyProduct = $productsTable->newEntity([
            'delivery_rhythm_type' => 'week',
            'delivery_rhythm_count' => '1',
            'is_stock_product' => '0',
        ]);
        $nextDeliveryDay = DeliveryRhythm::getNextPickupDayForProduct($dummyProduct, $this->cronjobRunDay);

        if (Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL') != '') {
            $this->Product = $this->getTableLocator()->get('Products');
            if ($this->Product->deliveryBreakGlobalEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $nextDeliveryDay)) {
                return static::CODE_SUCCESS;
            }
        }

        $this->startTimeLogging();

        $conditions = [
            'Customers.email_order_reminder_enabled' => 1,
            'Customers.active' => 1,
        ];
        $this->Customer = $this->getTableLocator()->get('Customers');
        $conditions[] = $this->Customer->getConditionToExcludeHostingUser();
        $this->Customer->dropManufacturersInNextFind();

        $diffOrderCreatedAndDeliveryDayInDays = 6;
        $exp = new QueryExpression();
        $this->Customer->getAssociation('ActiveOrderDetails')->setConditions([
            $exp->eq('DATE_FORMAT(ActiveOrderDetails.pickup_day, \'%Y-%m-%d\')', $nextDeliveryDay),
            $exp->lte('DATEDIFF(ActiveOrderDetails.pickup_day, DATE_FORMAT(ActiveOrderDetails.created, \'%Y-%m-%d\'))', $diffOrderCreatedAndDeliveryDayInDays),
        ]);

        $customers = $this->Customer->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'ActiveOrderDetails',
                'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
            ]
        ]);
        $customers = $this->Customer->sortByVirtualField($customers, 'name');

        $i = 0;
        $outString = '';
        foreach ($customers as $customer) {
            // customer has open orders, do not send email
            if (count($customer->active_order_details) > 0) {
                continue;
            }

            $email = new AppMailer();
            $email->setTo($customer->email)
            ->viewBuilder()->setTemplate('Admin.email_order_reminder');
            $email->setSubject(__('Order_reminder') . ' ' . Configure::read('appDb.FCS_APP_NAME'))
            ->setViewVars([
                'customer' => $customer,
                'newsletterCustomer' => $customer,
                'lastOrderDayAsString' => (DeliveryRhythm::getSendOrderListsWeekday() - date('N', strtotime($this->cronjobRunDay))) == 1 ? __('today') : __('tomorrow')
            ])
            ->addToQueue();

            $outString .= $customer->name . '<br />';

            $i ++;
        }

        $outString .= __('Sent_emails') . ': ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('cronjob_email_order_reminder', 0, 0, '', $outString . '<br />' . $this->getRuntime());

        $io->out($outString);
        $io->out($this->getRuntime());

        return static::CODE_SUCCESS;

    }
}
