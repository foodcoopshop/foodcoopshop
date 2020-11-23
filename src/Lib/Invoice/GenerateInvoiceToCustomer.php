<?php
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
namespace App\Lib\Invoice;

use App\Lib\PdfWriter\InvoiceToCustomerPdfWriter;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
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
            throw new Exception('safety check if data available - should always be checked before triggering this queue');
        }

        $invoiceDate = (new FrozenDate($currentDay))->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));

        $year = Configure::read('app.timeHelper')->getYearFromDbDate($currentDay);
        $invoiceNumber = $this->Invoice->getNextInvoiceNumberForCustomer($year, $this->Invoice->getLastInvoiceForCustomer());
        $invoicePdfFile =  Configure::read('app.htmlHelper')->getInvoiceLink(
            $data->name, $data->id_customer, Configure::read('app.timeHelper')->formatToDbFormatDate($currentDay), $invoiceNumber
        );

        $pdfWriter = new InvoiceToCustomerPdfWriter();
        $pdfWriter->prepareAndSetData($data, $paidInCash, $invoiceNumber, $invoiceDate);
        $pdfWriter->setFilename($invoicePdfFile);
        $pdfWriter->writeFile();

        $newInvoice = $this->saveInvoice($data, $invoiceNumber, $invoicePdfFile, $currentDay, $paidInCash);
        $this->linkReturnedDepositWithInvoice($data, $newInvoice->id);
        $this->updateOrderDetails($data, $newInvoice->id);

        $this->QueuedJobs->createJob('SendInvoiceToCustomer', [
            'isCancellationInvoice' => $data->is_cancellation_invoice,
            'customerName' => $data->name,
            'customerEmail' => $data->email,
            'invoicePdfFile' => $invoicePdfFile,
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => $invoiceDate,
            'invoiceId' => $newInvoice->id,
        ]);

        return $newInvoice;

    }

    private function updateOrderDetails($data, $invoiceId)
    {
        foreach($data->active_order_details as $orderDetail) {
            // important to get a fresh order detail entity as price fields could be changed for cancellation invoices
            $orderDetail = $this->OrderDetail->get($orderDetail->id_order_detail);
            $orderDetail->order_state = Configure::read('app.htmlHelper')->getOrderStateBilled();
            $orderDetail->id_invoice = $invoiceId;
            $this->OrderDetail->save($orderDetail);
        }
    }

    private function linkReturnedDepositWithInvoice($data, $invoiceId)
    {
        foreach($data->returned_deposit['entities'] as $payment) {
            // important to get a fresh payment entity as amount field could be changed for cancellation invoices
            $payment = $this->Payment->get($payment->id);
            $payment->invoice_id = $invoiceId;
            $this->Payment->save($payment);
        }
    }

    private function saveInvoice($data, $invoiceNumber, $invoicePdfFile, $currentDay, $paidInCash)
    {

        $invoicePdfFileForDatabase = str_replace(Configure::read('app.folder_invoices'), '', $invoicePdfFile);
        $invoicePdfFileForDatabase = str_replace('\\', '/', $invoicePdfFileForDatabase);

        $invoiceData = [
            'id_customer' => $data->id_customer,
            'invoice_number' => $invoiceNumber,
            'filename' => $invoicePdfFileForDatabase,
            'created' => new FrozenDate($currentDay),
            'paid_in_cash' => $paidInCash,
            'invoice_taxes' => [],
        ];
        foreach($data->tax_rates as $taxRate => $values) {
            $invoiceData['invoice_taxes'][] = [
                'tax_rate' => $taxRate,
                'total_price_tax_excl' => $values['sum_price_excl'],
                'total_price_tax_incl' => $values['sum_price_incl'],
                'total_price_tax' => $values['sum_tax'],
            ];
        }
        $invoiceEntity = $this->Invoice->newEntity($invoiceData);

        $newInvoice = $this->Invoice->save($invoiceEntity, [
            'associated' => [
                'InvoiceTaxes',
            ],
        ]);
        
        return $newInvoice;

    }

}
