<?php
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
namespace App\Lib\Invoice;

use App\Lib\PdfWriter\InvoiceToCustomerPdfWriter;
use App\Lib\PdfWriter\InvoiceToCustomerWithTaxBasedOnInvoiceSumPdfWriter;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\FrozenDate;

class GenerateInvoiceToCustomer
{

    public $Customer;
    public $Invoice;
    public $OrderDetail;
    public $Payment;
    public $QueuedJobs;

    public function run($data, $currentDay, $paidInCash)
    {

        $this->Customer = FactoryLocator::get('Table')->get('Customers');
        $this->Invoice = FactoryLocator::get('Table')->get('Invoices');
        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');
        $this->Payment = FactoryLocator::get('Table')->get('Payments');
        $this->QueuedJobs = FactoryLocator::get('Table')->get('Queue.QueuedJobs');

        if (!$data->new_invoice_necessary) {
            throw new \Exception('safety check if data available - should always be checked before triggering this queue');
        }

        $invoiceDate = (new FrozenDate($currentDay))->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));

        $year = Configure::read('app.timeHelper')->getYearFromDbDate($currentDay);
        $invoiceNumber = $this->Invoice->getNextInvoiceNumberForCustomer($year, $this->Invoice->getLastInvoiceForCustomer());
        $invoicePdfFile =  Configure::read('app.htmlHelper')->getInvoiceLink(
            $data->name, $data->id_customer, Configure::read('app.timeHelper')->formatToDbFormatDate($currentDay), $invoiceNumber
        );

        if (!Configure::read('appDb.FCS_TAX_BASED_ON_NET_INVOICE_SUM')) {
            $pdfWriter = new InvoiceToCustomerPdfWriter();
        } else {
            $pdfWriter = new InvoiceToCustomerWithTaxBasedOnInvoiceSumPdfWriter();
        }
        $pdfWriter->prepareAndSetData($data, $paidInCash, $invoiceNumber, $invoiceDate);
        $pdfWriter->setFilename($invoicePdfFile);
        $pdfWriter->writeFile();

        $invoicePdfFileForDatabase = str_replace(Configure::read('app.folder_invoices'), '', $invoicePdfFile);
        $invoicePdfFileForDatabase = str_replace('\\', '/', $invoicePdfFileForDatabase);
        $newInvoice = $this->Invoice->saveInvoice(
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
            $this->Payment->linkReturnedDepositWithInvoice($data, $newInvoice->id);
            $this->OrderDetail->updateOrderDetails($data, $newInvoice->id);
        }

        if ($data->invoices_per_email_enabled) {
            $this->Customer = FactoryLocator::get('Table')->get('Customers');
            $sendInvoiceToCustomer = new SendInvoiceToCustomer();
            $sendInvoiceToCustomer->isCancellationInvoice = $data->is_cancellation_invoice;
            $sendInvoiceToCustomer->customerName = $data->name;
            $sendInvoiceToCustomer->customerEmail = $data->email;
            $sendInvoiceToCustomer->invoicePdfFile = $invoicePdfFile;
            $sendInvoiceToCustomer->invoiceNumber = $invoiceNumber;
            $sendInvoiceToCustomer->invoiceSumPriceIncl = $data->sumPriceIncl;
            $sendInvoiceToCustomer->invoiceDate = $invoiceDate;
            $sendInvoiceToCustomer->invoiceId = $newInvoice->id;
            $sendInvoiceToCustomer->originalInvoiceId = null;
            $sendInvoiceToCustomer->creditBalance = $this->Customer->getCreditBalance($data->id_customer);
            $sendInvoiceToCustomer->paidInCash = $paidInCash;
            $sendInvoiceToCustomer->run();
        }

        return $newInvoice;

    }

}
