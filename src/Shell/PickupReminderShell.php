<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Shell;

use App\Mailer\AppEmail;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class PickupReminderShell extends AppShell
{

    public $cronjobRunDay;
    
    public function main()
    {

        parent::main();

        $this->initSimpleBrowser(); // for loggedUserId
        
        // $this->cronjobRunDay can is set in unit test
        if (!isset($this->args[0])) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        } else {
            $this->cronjobRunDay = $this->args[0];
        }
        
        $this->startTimeLogging();

        $conditions = [
            'Customers.active' => 1
        ];
        $conditions[] = $this->Customer->getConditionToExcludeHostingUser();
        $this->Customer->dropManufacturersInNextFind();
        
        $customers = $this->Customer->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'AddressCustomers' // to make exclude happen using dropManufacturersInNextFind
            ]
        ]);
        $customers = $this->Customer->sortByVirtualField($customers, 'name');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        
        $nextPickupDay = Configure::read('app.timeHelper')->getDeliveryDay(strtotime($this->cronjobRunDay));
        $formattedPickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday($nextPickupDay);
        $diffOrderAndPickupInDays = 6;
        
        $i = 0;
        $outString = '';
        foreach ($customers as $customer) {
            
            $futureOrderDetails = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_customer' => $customer->id_customer,
                    'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\') = \''.date('Y-m-d', $nextPickupDay).'\'',
                    'DATEDIFF(OrderDetails.pickup_day, DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\')) > ' . $diffOrderAndPickupInDays
                ],
                'contain' => [
                    'Products.Manufacturers'
                ],
                'order' => [
                    'OrderDetails.product_name' => 'ASC'
                ]
            ])->toArray();
            
            if (empty($futureOrderDetails)) {
                continue;
            }

            $email = new AppEmail();
            $email->setTo($customer->email)
            ->viewBuilder()->setTemplate('Admin.pickup_reminder');
            $email->setSubject(__('Pickup_reminder_for') . ' ' . $formattedPickupDay)
            ->setViewVars([
                'customer' => $customer,
                'diffOrderAndPickupInDays' => $diffOrderAndPickupInDays,
                'formattedPickupDay' => $formattedPickupDay,
                'futureOrderDetails' => $futureOrderDetails
            ])
            ->send();

            $outString .= $customer->name . '<br />';

            $i ++;
        }

        $outString .= __('Sent_emails') . ': ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_pickup_reminder', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);
        $this->out($this->getRuntime());
        
        return true;
        
    }
}
