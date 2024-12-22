<?php
declare(strict_types=1);

namespace App\Queue\Task;

use Queue\Queue\Task;
use Cake\Core\Configure;
use App\Mailer\AppMailer;
use App\Services\PdfWriter\InvoiceToManufacturerPdfWriterService;
use Queue\Model\Table\QueuedJobsTable;
use App\Model\Entity\OrderDetail;
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

class GenerateInvoiceForManufacturerTask extends Task {

    use UpdateActionLogTrait;

    public QueuedJobsTable $QueuedJobs;

    public ?int $timeout = 30;

    public ?int $retries = 2;

    public function run(array $data, $jobId) : void
    {

        $manufacturerId = $data['manufacturerId'];
        $invoicePdfFile = $data['invoicePdfFile'];
        $invoiceNumber = $data['invoiceNumber'];
        $actionLogId = $data['actionLogId'];
        $dateFrom = $data['dateFrom'];
        $dateTo = $data['dateTo'];

        $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturer = $manufacturersTable->getManufacturerByIdForSendingOrderListsOrInvoice($manufacturerId);

        $validOrderStates = [
            OrderDetail::STATE_OPEN,
            OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER,
        ];

        $invoiceDate = date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'));
        $invoicePeriodMonthAndYear = Configure::read('app.timeHelper')->getLastMonthNameAndYear();

        $pdfWriter = new InvoiceToManufacturerPdfWriterService();
        $pdfWriter->prepareAndSetData($manufacturer->id_manufacturer, $dateFrom, $dateTo, $invoiceNumber, $validOrderStates, $invoicePeriodMonthAndYear, $invoiceDate, $manufacturer->anonymize_customers);
        $pdfWriter->setFilename($invoicePdfFile);
        $pdfWriter->writeFile();

        $invoice2save = [
            'id_manufacturer' => $manufacturer->id_manufacturer,
            'invoice_number' => (int) $invoiceNumber,
            'user_id' => 0,
        ];
        $manufacturersTable->Invoices->save(
            $manufacturersTable->Invoices->newEntity($invoice2save)
        );

        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetailsTable->updateOrderState($dateFrom, $dateTo, $validOrderStates, Configure::read('app.htmlHelper')->getOrderStateBilled(), $manufacturer->id_manufacturer);

        $sendInvoice = $manufacturersTable->getOptionSendInvoice($manufacturer->send_invoice);
        if ($sendInvoice) {

            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.send_invoice_to_manufacturer');
            $email->setTo($manufacturer->address_manufacturer->email)
            ->setAttachments([
                $invoicePdfFile,
            ])
            ->setSubject(__('Invoice_number_abbreviataion_{0}_{1}', [$invoiceNumber, $invoicePeriodMonthAndYear]))
            ->setViewVars([
                'manufacturer' => $manufacturer,
                'invoicePeriodMonthAndYear' => $invoicePeriodMonthAndYear,
                'showManufacturerUnsubscribeLink' => true
            ]);
            $email->afterRunParams = [
                'actionLogIdentifier' => 'send-invoice-' . $manufacturer->id_manufacturer,
                'actionLogId' => $actionLogId,
            ];

            $email->customerAnonymizationForManufacturers = false; // always show contact person in email body
            $email->addToQueue();

        }

        $actionLogIdentifier = 'generate-invoice-' . $manufacturer->id_manufacturer;
        $this->updateActionLogSuccess($actionLogId, $actionLogIdentifier, $jobId);

    }

}
?>