<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Shell;

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
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
        $this->Product = TableRegistry::getTableLocator()->get('Products');

        $this->startTimeLogging();

        // $this->cronjobRunDay can is set in unit test
        if (!isset($this->args[0])) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
        } else {
            $this->cronjobRunDay = $this->args[0];
        }

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $pickupDay = $this->cronjobRunDay;
        } else {
            $pickupDay = Configure::read('app.timeHelper')->getNextDeliveryDay(strtotime($this->cronjobRunDay));
        }
        $formattedPickupDay = Configure::read('app.timeHelper')->formatToDateShort($pickupDay);

        // 1) get all manufacturers (not only active ones)
        $manufacturers = $this->Manufacturer->find('all', [
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ])->toArray();

        // 2) get all order details with pickup day in the given date range
        $orderDetails = $this->OrderDetail->getOrderDetailsForSendingOrderLists(
            $pickupDay,
            $this->cronjobRunDay,
            Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY'),
        );

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
        $this->initHttpClient();
        $this->httpClient->doFoodCoopShopLogin();
        foreach ($manufacturers as $manufacturer) {
            $sendOrderList = $this->Manufacturer->getOptionSendOrderList($manufacturer->send_order_list);
            if (!empty($manufacturer->order_detail_amount_sum) && $sendOrderList) {
                $url = $this->httpClient->adminPrefix . '/manufacturers/sendOrderList?manufacturerId=' . $manufacturer->id_manufacturer . '&pickupDay=' . $formattedPickupDay . '&cronjobRunDay=' . $this->cronjobRunDay;
                $this->httpClient->get($url);
            }
        }

        // prepare action log string is complicated because of
        // @see https://github.com/foodcoopshop/foodcoopshop/issues/408
        $tmpActionLogDatas = [];
        foreach($orderDetails as $orderDetail) {
            $orderDetailPickupDay = $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
            $manufacturerId = $orderDetail->product->id_manufacturer;
            @$tmpActionLogDatas[$manufacturerId][$orderDetailPickupDay]['order_detail_amount_sum'] += $orderDetail->product_amount;
            @$tmpActionLogDatas[$manufacturerId][$orderDetailPickupDay]['order_detail_price_sum'] += $orderDetail->total_price_tax_incl;
        }
        $actionLogDatas = [];
        foreach ($manufacturers as $manufacturer) {
            $sendOrderList = $this->Manufacturer->getOptionSendOrderList($manufacturer->send_order_list);
            if ($sendOrderList) {
                if (in_array($manufacturer->id_manufacturer, array_keys($tmpActionLogDatas))) {
                    ksort($tmpActionLogDatas[$manufacturer->id_manufacturer]);
                    foreach($tmpActionLogDatas[$manufacturer->id_manufacturer] as $pickupDayDbFormat => $tmpActionLogData) {
                        $pickupDayFormated = new FrozenDate($pickupDayDbFormat);
                        $pickupDayFormated = $pickupDayFormated->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
                        $newData = '- ' .
                            html_entity_decode($manufacturer->name) . ': ' .
                            __('{0,plural,=1{1_product} other{#_products}}', [$tmpActionLogData['order_detail_amount_sum']]) . ' / ' .
                            Configure::read('app.numberHelper')->formatAsCurrency($tmpActionLogData['order_detail_price_sum']);
                            if ($pickupDayDbFormat != $pickupDay) {
                                $newData .=  ' / ' . __('Delivery_day') . ': ' . $pickupDayFormated;
                            }
                        $actionLogDatas[] = $newData;
                    }
                }
            }
        }

        // reset quantity to default_quantity_after_sending_order_lists
        $productsToSave = [];
        foreach($orderDetails as $orderDetail) {
            $compositeProductId = $this->Product->getCompositeProductIdAndAttributeId($orderDetail->product_id, $orderDetail->product_attribute_id);
            $stockAvailableObject = $orderDetail->product->stock_available;
            if (!empty($orderDetail->product_attribute)) {
                $stockAvailableObject = $orderDetail->product_attribute->stock_available;
            }
            if (!is_null($stockAvailableObject->default_quantity_after_sending_order_lists) && $stockAvailableObject->quantity != $stockAvailableObject->default_quantity_after_sending_order_lists) {
                $productsToSave[] = [
                    $compositeProductId => [
                        'quantity' => $stockAvailableObject->default_quantity_after_sending_order_lists
                    ]
                ];
            }
        }
        if (!empty($productsToSave)) {
            $this->Product->changeQuantity($productsToSave);
        }

        $this->httpClient->doFoodCoopShopLogout();

        $outString = '';
        if (count($actionLogDatas) > 0) {
            $outString .= join('<br />', $actionLogDatas) . '<br />';
        }
        $outString .= __('Sent_order_lists') . ': ' . count($actionLogDatas);

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_send_order_lists', $this->httpClient->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);

        $this->out($this->getRuntime());

        return true;

    }
}
