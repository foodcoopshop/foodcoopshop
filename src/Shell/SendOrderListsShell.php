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
use Cake\Utility\Hash;
use App\Lib\PdfWriter\OrderListByCustomerPdfWriter;
use App\Lib\PdfWriter\OrderListByProductPdfWriter;
use App\Mailer\AppMailer;

class SendOrderListsShell extends AppShell
{

    public function main()
    {
        parent::main();

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');

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

        // 1) get all manufacturers (not only active ones)
        $manufacturers = $this->Manufacturer->find('all', [
            'order' => [
                'Manufacturers.name' => 'ASC'
            ],
            'contain' => [
                'AddressManufacturers',
                'Customers.AddressCustomers'
            ],
        ])->toArray();

        // 2) get all order details with pickup day in the given date range
        $allOrderDetails = $this->OrderDetail->getOrderDetailsForSendingOrderLists(
            $pickupDay,
            $this->cronjobRunDay,
            Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY'),
        );

        // 3) add up the order detail by manufacturer
        $manufacturerOrders = [];
        foreach ($allOrderDetails as $orderDetail) {
            @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_details'][] = $orderDetail;
            @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_amount_sum'] += $orderDetail->product_amount;
            @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_price_sum'] += $orderDetail->total_price_tax_incl;
        }

        // 4) merge the order detail count with the manufacturers array
        $i = 0;
        foreach ($manufacturers as $manufacturer) {
            $manufacturer->order_details = $manufacturerOrders[$manufacturer->id_manufacturer]['order_details'];
            $manufacturer->order_detail_amount_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_amount_sum'];
            $manufacturer->order_detail_price_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_price_sum'];
            $i++;
        }

        foreach ($manufacturers as $manufacturer) {

            // it's possible, that - within one request - orders with different pickup days are available
            // => multiple order lists need to be sent then!
            // @see https://github.com/foodcoopshop/foodcoopshop/issues/408
            $groupedOrderDetails = [];
            foreach($manufacturer['order_details'] as $orderDetail) {
                @$groupedOrderDetails[$orderDetail->pickup_day->i18nFormat(
                    Configure::read('app.timeHelper')->getI18Format('Database')
                )][] = $orderDetail;
            }
            foreach($groupedOrderDetails as $pickupDayDbFormat => $orderDetails) {

                // avoid generating empty order lists
                if (empty($orderDetails)) {
                    continue;
                }

                $pickupDayFormated = new FrozenDate($pickupDayDbFormat);
                $pickupDayFormated = $pickupDayFormated->i18nFormat(
                    Configure::read('app.timeHelper')->getI18Format('DateLong2')
                );
                $orderDetailIds = Hash::extract($orderDetails, '{n}.id_order_detail');

                $currentDateForOrderLists = Configure::read('app.timeHelper')->getCurrentDateTimeForFilename();

                // START generate PDF grouped by PRODUCT
                $pdfWriter = new OrderListByProductPdfWriter();
                $productPdfFile = Configure::read('app.htmlHelper')->getOrderListLink(
                    $manufacturer->name, $manufacturer->id_manufacturer, $pickupDayDbFormat, __('product'), $currentDateForOrderLists
                );
                $pdfWriter->setFilename($productPdfFile);
                $pdfWriter->prepareAndSetData($manufacturer->id_manufacturer, $pickupDayDbFormat, [], $orderDetailIds);
                $pdfWriter->writeFile();
                // END generate PDF grouped by PRODUCT

                // START generate PDF grouped by CUSTOMER
                $pdfWriter = new OrderListByCustomerPdfWriter();
                $customerPdfFile = Configure::read('app.htmlHelper')->getOrderListLink(
                    $manufacturer->name, $manufacturer->id_manufacturer, $pickupDayDbFormat, __('member'), $currentDateForOrderLists
                );
                $pdfWriter->setFilename($customerPdfFile);
                $pdfWriter->prepareAndSetData($manufacturer->id_manufacturer, $pickupDayDbFormat, [], $orderDetailIds);
                $pdfWriter->writeFile();
                // END generate PDF grouped by CUSTOMER

                $sendEmail = $this->Manufacturer->getOptionSendOrderList($manufacturer->send_order_list);
                $ccRecipients = $this->Manufacturer->getOptionSendOrderListCc($manufacturer->send_order_list_cc);

                if ($sendEmail) {

                    $email = new AppMailer();
                    $email->viewBuilder()->setTemplate('Admin.send_order_list');
                    $email->setTo($manufacturer->address_manufacturer->email)
                    ->setAttachments([
                        $productPdfFile,
                        $customerPdfFile,
                    ])
                    ->setSubject(__('Order_lists_for_the_day') . ' ' . $pickupDayFormated)
                    ->setViewVars([
                        'manufacturer' => $manufacturer,
                        'appAuth' => $this->AppAuth,
                        'showManufacturerUnsubscribeLink' => true,
                    ]);
                    if (!empty($ccRecipients)) {
                        $email->setCc($ccRecipients);
                    }
                    $email->send();

                    $this->OrderDetail->updateOrderState(null, null, [ORDER_STATE_ORDER_PLACED], ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER, $manufacturer->id_manufacturer, $orderDetailIds);

                }

            }

        }


        $actionLogDatas = $this->writeActionLog($allOrderDetails, $manufacturers, $pickupDay);

        $this->resetQuantityToDefaultQuantity($allOrderDetails);

        $outString = '';
        if (count($actionLogDatas) > 0) {
            $outString .= join('<br />', $actionLogDatas) . '<br />';
        }
        $outString .= __('Sent_order_lists') . ': ' . count($actionLogDatas);

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_send_order_lists', 0, 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);

        $this->out($this->getRuntime());

        return true;

    }

    /**
     * prepare action log string is complicated because of
     * @see https://github.com/foodcoopshop/foodcoopshop/issues/408
     */
    protected function writeActionLog($orderDetails, $manufacturers, $pickupDay): array
    {

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');

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

        return $actionLogDatas;

    }

    /**
     * reset quantity to default_quantity_after_sending_order_lists
     */
    protected function resetQuantityToDefaultQuantity($orderDetails)
    {

        $this->Product = $this->getTableLocator()->get('Products');

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

    }

}
