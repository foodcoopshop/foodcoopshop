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

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class SendOrderListsShell extends AppShell
{
    /**
     * sends order lists to manufacturers who have current orders
     * does not check the field Manufacturers.active! (can be theoretically offline when this cronjob runs)
     */
    public function main()
    {
        parent::main();

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');

        $this->startTimeLogging();
        
        // $this->cronjobRunDay can is set in unit test
        if (!isset($this->args[0])) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
        } else {
            $this->cronjobRunDay = $this->args[0];
        }
        
        $pickupDay = Configure::read('app.timeHelper')->getNextDeliveryDay(strtotime($this->cronjobRunDay));
        $formattedPickupDay = Configure::read('app.timeHelper')->formatToDateShort($pickupDay);

        // 1) get all manufacturers (not only active ones)
        $manufacturers = $this->Manufacturer->find('all', [
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ])->toArray();

        // 2) get all order details with pickup day in the given date range
        $orderDetails = $this->OrderDetail->getOrderDetailsForSendingOrderLists($pickupDay, $this->cronjobRunDay);
        
        // 3) add up the order detail by manufacturer
        $manufacturerOrders = [];
        foreach ($orderDetails as $orderDetail) {
            @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_amount_sum'] += $orderDetail->product_amount;
            @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_price_sum'] += $orderDetail->total_price_tax_incl;
        }

        // 4) merge the order detail count with the manufacturers array
        $i = 0;
        foreach ($manufacturers as $manufacturer) {
            $manufacturer->order_detail_amount_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_amount_sum'];
            $manufacturer->order_detail_price_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_price_sum'];
            $i++;
        }
        
        // 5) check if manufacturers have open order details and send email
        $i = 0;
        $outString = '';

        $this->initHttpClient();
        $this->httpClient->doFoodCoopShopLogin();
        foreach ($manufacturers as $manufacturer) {
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer->bulk_orders_allowed);
            $sendOrderList = $this->Manufacturer->getOptionSendOrderList($manufacturer->send_order_list);
            if (!empty($manufacturer->order_detail_amount_sum) && $sendOrderList && !$bulkOrdersAllowed) {
                $productString = __('{0,plural,=1{1_product} other{#_products}}', [$manufacturer->order_detail_amount_sum]);
                $outString .= ' - ' . $manufacturer->name . ': ' . $productString . ' / ' . Configure::read('app.numberHelper')->formatAsCurrency($manufacturer->order_detail_price_sum) . '<br />';
                $url = $this->httpClient->adminPrefix . '/manufacturers/sendOrderList?manufacturerId=' . $manufacturer->id_manufacturer . '&pickupDay=' . $formattedPickupDay . '&cronjobRunDay=' . $this->cronjobRunDay;
                $this->httpClient->get($url);
                $i ++;
            }
        }

        $this->httpClient->doFoodCoopShopLogout();

        $outString .= __('Sent_order_lists') . ': ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_send_order_lists', $this->httpClient->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);

        $this->out($this->getRuntime());
        
        return true;
        
    }
}
