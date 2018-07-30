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

class MigrateOrdersPickupDayShell extends AppShell
{

    public function main()
    {
        parent::main();
        
        $this->startTimeLogging();
        $this->migrateDataToOrderDetails();
        $this->migrateDataToPickupDays();
        $this->stopTimeLogging();
        $this->out($this->getRuntime());
    }
    
    private function migrateDataToPickupDays()
    {
        $this->Order = TableRegistry::getTableLocator()->get('Orders');
        $this->PickupDay = TableRegistry::getTableLocator()->get('PickupDays');
        $this->PickupDay->setPrimaryKey(['customer_id', 'pickup_day']);
        
        $orders = $this->Order->find('all');
        $i = 0;
        foreach($orders as $order) {
            
            $productsPickedUp = 0;
            if (in_array($order->current_state, [
                ORDER_STATE_CASH,
                ORDER_STATE_CASH_FREE
            ])) {
                $productsPickedUp = 1;
            }
            
            $pickupDay = Configure::read('app.timeHelper')->getDbFormattedPickupDayByDbFormattedDate($order->date_add->i18nFormat(Configure::read('DateFormat.Database')));
            
            if ($order->comment != '' || $productsPickedUp == 1) {
                $this->PickupDay->save(
                    $this->PickupDay->newEntity([
                        'pickup_day' => $pickupDay,
                        'customer_id' => $order->id_customer,
                        'comment' => $order->comment,
                        'products_picked_up' => $productsPickedUp
                    ])
                );
                $i++;
            }
            
        }
        
        $this->out('Comments from ' . $i . ' orders migrated.');
        
    }


    private function migrateDataToOrderDetails()
    {
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->find('all');
        
        foreach($orderDetails as $orderDetail) {
            if ($orderDetail->created) {
                $pickupDay = Configure::read('app.timeHelper')->getDbFormattedPickupDayByDbFormattedDate($orderDetail->created->i18nFormat(Configure::read('DateFormat.Database')));
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
        
    }
    
}
