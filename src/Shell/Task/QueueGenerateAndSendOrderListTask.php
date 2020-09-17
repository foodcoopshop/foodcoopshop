<?php
namespace App\Shell\Task;

use App\Lib\PdfWriter\OrderListByCustomerPdfWriter;
use App\Lib\PdfWriter\OrderListByProductPdfWriter;
use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Queue\Shell\Task\QueueTask;
use Queue\Shell\Task\QueueTaskInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class QueueGenerateAndSendOrderListTask extends QueueTask implements QueueTaskInterface {


    public $timeout = 30;

    public $retries = 2;

    public function run(array $data, $jobId) : void
    {

        $pickupDayDbFormat = $data['pickupDayDbFormat'];
        $pickupDayFormated = $data['pickupDayFormated'];
        $manufacturerId = $data['manufacturerId'];
        $orderDetailIds = $data['orderDetailIds'];

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId,
            ],
            'order' => [
                'Manufacturers.name' => 'ASC',
            ],
            'contain' => [
                'AddressManufacturers',
                'Customers.AddressCustomers',
            ],
        ])->first();

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
?>