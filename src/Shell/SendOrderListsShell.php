<?php
/**
 * SendOrderListsShell
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

        $this->ActionLog = TableRegistry::get('ActionLogs');
        $this->Order = TableRegistry::get('Orders');
        $this->Manufacturer = TableRegistry::get('Manufacturers');

        $this->startTimeLogging();

        $dateFrom = Configure::read('app.timeHelper')->getOrderPeriodFirstDay(Configure::read('app.timeHelper')->getCurrentDay());
        $dateTo = Configure::read('app.timeHelper')->getOrderPeriodLastDay(Configure::read('app.timeHelper')->getCurrentDay());

        // $dateFrom = '01.02.2016';
        // $dateTo = '29.02.2016';

        // 1) get all manufacturers (not only active ones)
        $manufacturers = $this->Manufacturer->find('all', [
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ])->toArray();

        // 2) get all orders in the given date range
        $orders = $this->Order->find('all', [
            'conditions' => [
                'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'',
                'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'',
                'Orders.current_state' => ORDER_STATE_OPEN
            ],
            'contain' => [
                'OrderDetails.Products'
            ]
        ]);

        // 3) add up the order detail by manufacturer
        $manufacturerOrders = [];
        foreach ($orders as $order) {
            foreach ($order->order_details as $orderDetail) {
                @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_quantity_sum'] += $orderDetail->product_quantity;
                @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_price_sum'] += $orderDetail->total_price_tax_incl;
            }
        }

        // 4) merge the order detail count with the manufacturers array
        $i = 0;
        foreach ($manufacturers as $manufacturer) {
            $manufacturer->order_detail_quantity_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_quantity_sum'];
            $manufacturer->order_detail_price_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_price_sum'];
            $i++;
        }

        // 5) check if manufacturers have open order details and send email
        $i = 0;
        $outString = 'Bestellzeitraum: ' . $dateFrom . ' bis ' . $dateTo . '<br />';

        $this->initSimpleBrowser();
        $this->browser->doFoodCoopShopLogin();

        foreach ($manufacturers as $manufacturer) {
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer->bulk_orders_allowed);
            $sendOrderList = $this->Manufacturer->getOptionSendOrderList($manufacturer->send_order_list);
            if (!empty($manufacturer->order_detail_quantity_sum) && $sendOrderList && !$bulkOrdersAllowed) {
                $productString = ($manufacturer->order_detail_quantity_sum == 1 ? 'Produkt' : 'Produkte');
                $outString .= ' - ' . $manufacturer->name . ': ' . $manufacturer->order_detail_quantity_sum . ' ' . $productString . ' / ' . Configure::read('app.htmlHelper')->formatAsEuro($manufacturer->order_detail_price_sum) . '<br />';
                $url = $this->browser->adminPrefix . '/manufacturers/sendOrderList/' . $manufacturer->id_manufacturer . '/' . $dateFrom . '/' . $dateTo;
                $this->browser->get($url);
                $i ++;
            }
        }

        $this->browser->doFoodCoopShopLogout();

        $outString .= 'Verschickte Bestelllisten: ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_send_order_lists', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);

        $this->out($this->getRuntime());
    }
}
