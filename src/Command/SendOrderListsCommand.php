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

use App\Services\DeliveryRhythmService;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\I18n\Date;
use App\Command\Traits\CronjobCommandTrait;

class SendOrderListsCommand extends AppCommand
{

    use CronjobCommandTrait;

    public function execute(Arguments $args, ConsoleIo $io): int
    {

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $queuedJobsTable = $this->getTableLocator()->get('Queue.QueuedJobs');

        $this->setCronjobRunDay($args);

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $pickupDay = $this->cronjobRunDay;
        } else {
            $pickupDay = (new DeliveryRhythmService())->getNextDeliveryDay(strtotime($this->cronjobRunDay));
        }

        // 1) get all manufacturers (not only active ones)
        $manufacturers = $manufacturersTable->find('all',
        order: [
            'Manufacturers.name' => 'ASC'
        ],
        contain: [
            'AddressManufacturers',
            'Customers.AddressCustomers'
        ])->toArray();

        // 2) get all order details with pickup day in the given date range
        $allOrderDetails = $orderDetailsTable->getOrderDetailsForSendingOrderLists(
            $pickupDay,
            $this->cronjobRunDay,
            Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY'),
        );

        // 3) add up the order detail by manufacturer
        $manufacturerOrders = [];
        foreach ($allOrderDetails as $orderDetail) {
            if (!isset($manufacturerOrders[$orderDetail->product->id_manufacturer])) {
                $manufacturerOrders[$orderDetail->product->id_manufacturer] = [
                    'order_details' => [],
                    'order_detail_amount_sum' => 0,
                    'order_detail_price_sum' => 0,
                ];
            }
            $manufacturerOrders[$orderDetail->product->id_manufacturer]['order_details'][] = $orderDetail;
            $manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_amount_sum'] += $orderDetail->product_amount;
            $manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_price_sum'] += $orderDetail->total_price_tax_incl;
        }

        // 4) merge the order detail count with the manufacturers array
        foreach ($manufacturers as $manufacturer) {
            $manufacturer->order_details = $manufacturerOrders[$manufacturer->id_manufacturer]['order_details'] ?? [];
            $manufacturer->order_detail_amount_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_amount_sum'] ?? 0;
            $manufacturer->order_detail_price_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_price_sum'] ?? 0;
        }

        $actionLogDatas = $this->getActionLogData($allOrderDetails, $manufacturers, $pickupDay);

        $outString = '';
        if (count($actionLogDatas) > 0) {
            $outString .= join('<br />', $actionLogDatas) . '<br />';
        }
        $outString .= __('Sent_order_lists') . ': ' . count($actionLogDatas);

        $actionLog = $actionLogsTable->customSave('cronjob_send_order_lists', 0, 0, '', $outString);

        foreach ($manufacturers as $manufacturer) {

            // it's possible, that - within one request - orders with different pickup days are available
            // => multiple order lists need to be sent then!
            // @see https://github.com/foodcoopshop/foodcoopshop/issues/408
            $groupedOrderDetails = [];
            foreach($manufacturer['order_details'] as $orderDetail) {
                $formattedPickupDay = $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
                if (!isset($groupedOrderDetails[$formattedPickupDay])) {
                    $groupedOrderDetails[$formattedPickupDay] = [];
                }
                $groupedOrderDetails[$formattedPickupDay][] = $orderDetail;
            }
            foreach($groupedOrderDetails as $pickupDayDbFormat => $orderDetails) {

                // avoid generating empty order lists
                /** @phpstan-ignore-next-line */
                if (empty($orderDetails)) {
                    continue;
                }

                $pickupDayFormatted = new Date($pickupDayDbFormat);
                $pickupDayFormatted = $pickupDayFormatted->i18nFormat(
                    Configure::read('app.timeHelper')->getI18Format('DateLong2')
                );
                $orderDetailIds = Hash::extract($orderDetails, '{n}.id_order_detail');

                $queuedJobsTable->createJob('GenerateOrderList', [
                    'pickupDayDbFormat' => $pickupDayDbFormat,
                    'pickupDayFormatted' => $pickupDayFormatted,
                    'orderDetailIds' => $orderDetailIds,
                    'manufacturerId' => $manufacturer->id_manufacturer,
                    'manufactuerName' => $manufacturer->name,
                    'actionLogId' => $actionLog->id,
                ]);

            }

        }

        $this->resetQuantityToDefaultQuantity($allOrderDetails);

        $io->out($outString);

        return static::CODE_SUCCESS;

    }

    /**
     * prepare action log string is complicated because of
     * @see https://github.com/foodcoopshop/foodcoopshop/issues/408
     */
    protected function getActionLogData($orderDetails, $manufacturers, $pickupDay): array
    {

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');

        $tmpActionLogDatas = [];
        foreach($orderDetails as $orderDetail) {
            $orderDetailPickupDay = $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
            $manufacturerId = $orderDetail->product->id_manufacturer;
            if (!isset($tmpActionLogDatas[$manufacturerId])) {
                $tmpActionLogDatas[$manufacturerId] = [];
            }
            if (!isset($tmpActionLogDatas[$manufacturerId][$orderDetailPickupDay])) {
                $tmpActionLogDatas[$manufacturerId][$orderDetailPickupDay] = [
                    'order_detail_amount_sum' => 0,
                    'order_detail_price_sum' => 0,
                ];
            }
            $tmpActionLogDatas[$manufacturerId][$orderDetailPickupDay]['order_detail_amount_sum'] += $orderDetail->product_amount;
            $tmpActionLogDatas[$manufacturerId][$orderDetailPickupDay]['order_detail_price_sum'] += $orderDetail->total_price_tax_incl;
        }
        $actionLogDatas = [];
        foreach ($manufacturers as $manufacturer) {
            $sendOrderList = $manufacturersTable->getOptionSendOrderList($manufacturer->send_order_list);
            if ($sendOrderList) {
                if (in_array($manufacturer->id_manufacturer, array_keys($tmpActionLogDatas))) {
                    ksort($tmpActionLogDatas[$manufacturer->id_manufacturer]);
                    foreach($tmpActionLogDatas[$manufacturer->id_manufacturer] as $pickupDayDbFormat => $tmpActionLogData) {
                        $pickupDayFormatted = new Date($pickupDayDbFormat);
                        $pickupDayFormatted = $pickupDayFormatted->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
                        $identifier = $manufacturer->id_manufacturer . '-' . $pickupDayFormatted;
                        $newData = '- <i class="fas fa-book not-ok" data-identifier="generate-order-list-'.$identifier.'"></i> <i class="fas fa-envelope not-ok" data-identifier="send-order-list-'.$identifier.'"></i> ';
                        $newData .= $manufacturer->decoded_name . ': ' .
                            __('{0,plural,=1{1_product} other{#_products}}', [$tmpActionLogData['order_detail_amount_sum']]) . ' / ' .
                            Configure::read('app.numberHelper')->formatAsCurrency($tmpActionLogData['order_detail_price_sum']);
                            if ($pickupDayDbFormat != $pickupDay) {
                                $newData .=  ' / ' . __('Delivery_day') . ': ' . $pickupDayFormatted;
                            }
                        $actionLogDatas[] = $newData;
                    }
                }
            }
        }

        return $actionLogDatas;

    }

    /**
     * reset quantity to default_quantity_after_sending_order_lists
     */
    protected function resetQuantityToDefaultQuantity($orderDetails): void
    {

        $productsTable = $this->getTableLocator()->get('Products');

        $productsToSave = [];
        foreach($orderDetails as $orderDetail) {
            $compositeProductId = $productsTable->getCompositeProductIdAndAttributeId($orderDetail->product_id, $orderDetail->product_attribute_id);
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
            $productsTable->changeQuantity($productsToSave);
        }

    }

}
