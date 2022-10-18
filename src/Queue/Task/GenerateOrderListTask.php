<?php
declare(strict_types=1);

namespace App\Queue\Task;

use App\Mailer\AppMailer;
use App\Lib\PdfWriter\OrderListByCustomerPdfWriter;
use App\Lib\PdfWriter\OrderListByProductPdfWriter;
use Cake\Core\Configure;
use Queue\Queue\Task;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class GenerateOrderListTask extends Task {

    use UpdateActionLogTrait;

    public $Manufacturer;

    public $QueuedJobs;

    public $timeout = 30;

    public $retries = 2;

    public function run(array $data, $jobId) : void
    {

        $pickupDayDbFormat = $data['pickupDayDbFormat'];
        $pickupDayFormatted = $data['pickupDayFormatted'];
        $manufacturerId = $data['manufacturerId'];
        $orderDetailIds = $data['orderDetailIds'];
        $actionLogId = $data['actionLogId'];

        $this->Manufacturer = $this->loadModel('Manufacturers');
        $manufacturer = $this->Manufacturer->getManufacturerByIdForSendingOrderListsOrInvoice($manufacturerId);

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

        if ($sendEmail) {

            $manufacturer = $this->Manufacturer->getManufacturerByIdForSendingOrderListsOrInvoice($manufacturerId);

            $ccRecipients = $this->Manufacturer->getOptionSendOrderListCc($manufacturer->send_order_list_cc);

            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.send_order_list');
            $email->setTo($manufacturer->address_manufacturer->email)
            ->setAttachments([
                $productPdfFile,
                $customerPdfFile,
            ])
            ->setSubject(__('Order_lists_for_the_day') . ' ' . $pickupDayFormatted)
            ->setViewVars([
                'manufacturer' => $manufacturer,
                'showManufacturerUnsubscribeLink' => true,
            ]);
            if (!empty($ccRecipients)) {
                $email->setCc($ccRecipients);
            }

            $email->afterRunParams = [
                'actionLogIdentifier' => 'send-order-list-' . $manufacturerId . '-' . $pickupDayFormatted,
                'actionLogId' => $actionLogId,
                'manufacturerId' => $manufacturerId,
                'orderDetailIds' => $orderDetailIds,
            ];
            $email->addToQueue();

        }

        $actionLogIdentifier = 'generate-order-list-' . $manufacturerId . '-' . $pickupDayFormatted;
        $this->updateActionLogSuccess($actionLogId, $actionLogIdentifier, $jobId);

    }

}
?>