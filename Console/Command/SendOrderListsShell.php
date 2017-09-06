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
class SendOrderListsShell extends AppShell
{

    public $uses = array(
        'Manufacturer',
        'Order',
        'Customer',
        'CakeActionLog',
        'Configuration' // for unit test mock object
    );

    /**
     * sends order lists to manufacturers who have current orders
     * does not check the field Manufacturer.active! (can be theoretically offline when this cronjob runs)
     */
    public function main()
    {
        parent::main();

        $this->startTimeLogging();

        $dateFrom = date('d.m.Y', strtotime('-7 day'));
        $dateTo = date('d.m.Y', strtotime('-1 day'));

        // $dateFrom = '01.02.2016';
        // $dateTo = '29.02.2016';

        // 1) get all manufacturers (not only active ones)
        $this->Manufacturer->unbindModel(array(
            'hasMany' => array(
                'CakeInvoices'
            )
        ));
        $manufacturers = $this->Manufacturer->find('all', array(
            'order' => array(
                'Manufacturer.name' => 'ASC'
            )
        ));

        // 2) get all orders in the given date range
        $this->Order->recursive = 2;
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'DATE_FORMAT(Order.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('timeHelper')->formatToDbFormatDate($dateFrom) . '\'',
                'DATE_FORMAT(Order.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('timeHelper')->formatToDbFormatDate($dateTo) . '\'',
                'Order.current_state' => ORDER_STATE_OPEN
            )
        ));

        // 3) add up the order detail by manufacturer
        $manufacturerOrders = array();
        foreach ($orders as $order) {
            foreach ($order['OrderDetails'] as $orderDetail) {
                @$manufacturerOrders[$orderDetail['Product']['id_manufacturer']]['order_detail_quantity_sum'] += $orderDetail['product_quantity'];
                @$manufacturerOrders[$orderDetail['Product']['id_manufacturer']]['order_detail_price_sum'] += $orderDetail['total_price_tax_incl'];
            }
        }

        // 4) merge the order detail count with the manufacturers array
        $i = 0;
        foreach ($manufacturers as $manufacturer) {
            @$manufacturers[$i]['order_detail_quantity_sum'] = $manufacturerOrders[$manufacturer['Manufacturer']['id_manufacturer']]['order_detail_quantity_sum'];
            @$manufacturers[$i]['order_detail_price_sum'] = $manufacturerOrders[$manufacturer['Manufacturer']['id_manufacturer']]['order_detail_price_sum'];
            $i ++;
        }

        // 5) check if manufacturers have open order details and send email
        $i = 0;
        $outString = 'Bestellzeitraum: ' . $dateFrom . ' bis ' . $dateTo . '<br />';

        $this->initSimpleBrowser();
        $this->browser->doFoodCoopShopLogin();

        foreach ($manufacturers as $manufacturer) {
            $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer['Manufacturer']['bulk_orders_allowed']);
            $sendOrderList = $this->Manufacturer->getOptionSendOrderList($manufacturer['Manufacturer']['send_order_list']);
            if (isset($manufacturer['order_detail_quantity_sum']) && $sendOrderList && !$bulkOrdersAllowed) {
                $productString = ($manufacturer['order_detail_quantity_sum'] == 1 ? 'Produkt' : 'Produkte');
                $outString .= ' - ' . $manufacturer['Manufacturer']['name'] . ': ' . $manufacturer['order_detail_quantity_sum'] . ' ' . $productString . ' / ' . Configure::read('htmlHelper')->formatAsEuro($manufacturer['order_detail_price_sum']) . '<br />';
                $url = $this->browser->adminPrefix . '/manufacturers/sendOrderList/' . $manufacturer['Manufacturer']['id_manufacturer'] . '/' . $dateFrom . '/' . $dateTo;
                $this->browser->get($url);
                $i ++;
            }
        }

        $this->browser->doFoodCoopShopLogout();

        $outString .= 'Verschickte Bestelllisten: ' . $i;

        $this->stopTimeLogging();

        $this->CakeActionLog->customSave('cronjob_send_order_lists', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);

        $this->out($this->getRuntime());
    }
}
