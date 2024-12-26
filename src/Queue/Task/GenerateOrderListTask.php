<?php
declare(strict_types=1);

namespace App\Queue\Task;

use Queue\Queue\Task;
use Cake\Core\Configure;
use App\Mailer\AppMailer;
use App\Services\PdfWriter\OrderListByProductPdfWriterService;
use App\Services\PdfWriter\OrderListByCustomerPdfWriterService;
use Cake\ORM\TableRegistry;

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
    
    public ?int $timeout = 30;
    public ?int $retries = 2;

    private function generateOrderListProduct($isAnonymized, $manufacturer, $pickupDayDbFormat, $currentDateForOrderLists, $orderDetailIds): string
    {
        $pdfWriter = new OrderListByProductPdfWriterService();
        $productPdfFile = Configure::read('app.htmlHelper')->getOrderListLink(
            $manufacturer->name, $manufacturer->id_manufacturer, $pickupDayDbFormat, __('product'), $currentDateForOrderLists, $isAnonymized
        );
        $pdfWriter->setFilename($productPdfFile);
        $pdfWriter->prepareAndSetData($manufacturer->id_manufacturer, $pickupDayDbFormat, [], $orderDetailIds, $isAnonymized);
        $pdfWriter->writeFile();
        return $productPdfFile;
    }

    private function generateOrderListCustomer($isAnonymized, $manufacturer, $pickupDayDbFormat, $currentDateForOrderLists, $orderDetailIds): string
    {
        $pdfWriter = new OrderListByCustomerPdfWriterService();
        $customerPdfFile = Configure::read('app.htmlHelper')->getOrderListLink(
            $manufacturer->name, $manufacturer->id_manufacturer, $pickupDayDbFormat, __('member'), $currentDateForOrderLists, $isAnonymized
        );
        $pdfWriter->setFilename($customerPdfFile);
        $pdfWriter->prepareAndSetData($manufacturer->id_manufacturer, $pickupDayDbFormat, [], $orderDetailIds, $isAnonymized);
        $pdfWriter->writeFile();
        return $customerPdfFile;
    }

    public function run(array $data, $jobId) : void
    {

        $pickupDayDbFormat = $data['pickupDayDbFormat'];
        $pickupDayFormatted = $data['pickupDayFormatted'];
        $manufacturerId = $data['manufacturerId'];
        $orderDetailIds = $data['orderDetailIds'];
        $actionLogId = $data['actionLogId'];

        $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturer = $manufacturersTable->getManufacturerByIdForSendingOrderListsOrInvoice($manufacturerId);

        $currentDateForOrderLists = Configure::read('app.timeHelper')->getCurrentDateTimeForFilename();

        $attachments = [
            $this->generateOrderListProduct(false, $manufacturer, $pickupDayDbFormat, $currentDateForOrderLists, $orderDetailIds),
            $this->generateOrderListCustomer(false, $manufacturer, $pickupDayDbFormat, $currentDateForOrderLists, $orderDetailIds),
        ];
        
        if ($manufacturer->anonymize_customers) {
            $attachments = [
                $this->generateOrderListProduct(true, $manufacturer, $pickupDayDbFormat, $currentDateForOrderLists, $orderDetailIds),
                $this->generateOrderListCustomer(true, $manufacturer, $pickupDayDbFormat, $currentDateForOrderLists, $orderDetailIds),
            ];
        }

        $sendEmail = $manufacturersTable->getOptionSendOrderList($manufacturer->send_order_list);

        if ($sendEmail) {

            $ccRecipients = $manufacturersTable->getOptionSendOrderListCc($manufacturer->send_order_list_cc);

            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.send_order_list');
            $email->setTo($manufacturer->address_manufacturer->email)
            ->setAttachments($attachments)
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

            $email->customerAnonymizationForManufacturers = false; // always show contact person in email body
            $email->addToQueue();

        }

        $actionLogIdentifier = 'generate-order-list-' . $manufacturerId . '-' . $pickupDayFormatted;
        $this->updateActionLogSuccess($actionLogId, $actionLogIdentifier, $jobId);

    }

}
?>