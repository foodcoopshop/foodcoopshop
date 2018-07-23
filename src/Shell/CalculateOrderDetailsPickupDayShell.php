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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Shell;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class CalculateOrderDetailsPickupDayShell extends AppShell
{

    public function main()
    {
        parent::main();
        
        $this->startTimeLogging();

        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->find('all');

        foreach($orderDetails as $orderDetail) {
            if ($orderDetail->created) {
                $createdFormattedAsDatabase = strtotime($orderDetail->created->i18nFormat(Configure::read('DateFormat.Database')));
                $pickupDay = Configure::read('app.timeHelper')->getDeliveryDay($createdFormattedAsDatabase);
                $pickupDay = date(Configure::read('DateFormat.DatabaseAlt'), $pickupDay);
                $created = $orderDetail->created;
                $modified = $orderDetail->modified;
            }
            $this->OrderDetail->save(
                $this->OrderDetail->patchEntity($orderDetail, [
                    'pickup_day' => $pickupDay,
                    'created' => $created,
                    'modified' => $modified
                ])
            );
        }
        
        $this->out('Pickup day for ' . $orderDetails->count() . ' order details calculated.');
        
        $this->stopTimeLogging();
        $this->out($this->getRuntime());
    }
}
