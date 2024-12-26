<?php
declare(strict_types=1);

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
namespace App\Services\Invoice;

use App\Services\PdfWriter\InvoiceToCustomerPdfWriterService;
use App\Services\PdfWriter\InvoiceToCustomerWithTaxBasedOnInvoiceSumPdfWriterService;
use Cake\Core\Configure;
use Cake\I18n\Date;
use Cake\ORM\TableRegistry;

class GenerateInvoiceToCustomerService
{

    public function run($data, $currentDay, $paidInCash)
    {

        $customersTable = TableRegistry::getTableLocator()->get('Customers');
        $invoicesTable = TableRegistry::getTableLocator()->get('Invoices');
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $paymentsTable = TableRegistry::getTableLocator()->get('Payments');

        if (!$data->new_invoice_necessary) {
            throw new \Exception('safety check if data available - should always be checked before triggering this queue');
        }

        $invoiceDate = (new Date($currentDay))->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));

        $year = Configure::read('app.timeHelper')->getYearFromDbDate($currentDay);
        $invoiceNumber = $invoicesTable->getNextInvoiceNumberForCustomer($year, $invoicesTable->getLastInvoiceForCustomer());
        $invoicePdfFile =  Configure::read('app.htmlHelper')->getInvoiceLink(
            $data->name, $data->id_customer, Configure::read('app.timeHelper')->formatToDbFormatDate($currentDay), $invoiceNumber
        );

        if (!Configure::read('appDb.FCS_TAX_BASED_ON_NET_INVOICE_SUM')) {
            $pdfWriter = new InvoiceToCustomerPdfWriterService();
        } else {
            $pdfWriter = new InvoiceToCustomerWithTaxBasedOnInvoiceSumPdfWriterService();
        }
        $pdfWriter->prepareAndSetData($data, $paidInCash, $invoiceNumber, $invoiceDate);
        $pdfWriter->setFilename($invoicePdfFile);
        $pdfWriter->writeFile();

        $invoicePdfFileForDatabase = str_replace(Configure::read('app.folder_invoices'), '', $invoicePdfFile);
        $invoicePdfFileForDatabase = str_replace('\\', '/', $invoicePdfFileForDatabase);
        $newInvoice = $invoicesTable->saveInvoice(
            null,
            $data->id_customer,
            $data->tax_rates,
            $invoiceNumber,
            $invoicePdfFileForDatabase,
            $currentDay,
            $paidInCash,
            $data->invoices_per_email_enabled,
        );

        if (!$data->is_cancellation_invoice) {
            $paymentsTable->linkReturnedDepositWithInvoice($data, $newInvoice->id);
            $orderDetailsTable->updateOrderDetails($data, $newInvoice->id);
        }

        if ($data->invoices_per_email_enabled) {
            $customersTable = TableRegistry::getTableLocator()->get('Customers');
            $service = new SendInvoiceToCustomerService();
            $service->isCancellationInvoice = $data->is_cancellation_invoice;
            $service->customerName = $data->name;
            $service->customerEmail = $data->email;
            $service->invoicePdfFile = $invoicePdfFile;
            $service->invoiceNumber = $invoiceNumber;
            $service->invoiceSumPriceIncl = $data->sumPriceIncl;
            $service->invoiceDate = $invoiceDate;
            $service->invoiceId = $newInvoice->id;
            $service->originalInvoiceId = null;
            $service->creditBalance = $customersTable->getCreditBalance($data->id_customer);
            $service->paidInCash = $paidInCash;
            $service->run();
        }

        return $newInvoice;

    }

}
