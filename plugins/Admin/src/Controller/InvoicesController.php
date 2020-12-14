<?php

namespace Admin\Controller;

use App\Lib\Invoice\GenerateInvoiceToCustomer;
use App\Lib\PdfWriter\InvoiceToCustomerPdfWriter;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Http\Exception\NotFoundException;

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
class InvoicesController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $this->AppAuth->isSuperadmin();
    }

    public function downloadAsZipFile()
    {

        $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        $dateTo = h($this->getRequest()->getQuery('dateTo'));
        $customerId = h($this->getRequest()->getQuery('customerId'));

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoices = $this->Invoice->find('all', [
            'conditions' => $this->getInvoiceConditions($dateFrom, $dateTo, $customerId),
        ]);

        if (empty($invoices)) {
            throw new NotFoundException();
        }

        $zipFilename = __d('admin', 'Invoices') . '.zip';

        $zip = new \ZipArchive();
        $tmpZipFilePath = TMP . $zipFilename;
        if (file_exists($tmpZipFilePath)) {
            unlink($tmpZipFilePath);
        }
        $zip->open($tmpZipFilePath, \ZipArchive::CREATE);

        foreach($invoices as $invoice) {
            $invoiceFileWithPath = Configure::read('app.folder_invoices') . DS . $invoice->filename;
            $zip->addFile($invoiceFileWithPath, substr($invoice->filename, 1));
        }

        $zip->close();

        $this->disableAutoRender();

        $this->response = $this->response->withType('zip');
        $this->response = $this->response->withFile($tmpZipFilePath);
        $this->response = $this->response->withHeader('Content-Disposition', 'inline; filename="' . $zipFilename . '"');

        if (file_exists($tmpZipFilePath)) {
            unlink($tmpZipFilePath);
        }

        return $this->response;

    }

    public function generate()
    {

        $customerId = h($this->getRequest()->getQuery('customerId'));
        $paidInCash = h($this->getRequest()->getQuery('paidInCash'));

        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId,
            ],
        ])->first();

        if (empty($customer)) {
            throw new Exception('customer not found');
        }

        $invoiceData = $this->Customer->Invoices->getDataForCustomerInvoice($customer->id_customer, Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        if (!$invoiceData->new_invoice_necessary) {
            $this->Flash->success(__d('admin', 'No_data_available_to_generate_an_invoice.'));
            $this->redirect($this->referer());
            return;
        }

        $currentDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();

        $invoiceToCustomer = new GenerateInvoiceToCustomer();
        $newInvoice = $invoiceToCustomer->run($invoiceData, $currentDay, $paidInCash);

        $linkToInvoice = Configure::read('app.htmlHelper')->link(
            __d('admin', 'Download'),
            '/admin/lists/getInvoice?file=' . $newInvoice->filename,
            [
                'class' => 'btn btn-outline-light btn-flash-message',
                'target' => '_blank',
                'escape' => false,
            ],
        );
        $messageString = __d('admin', 'Invoice_number_{0}_of_{1}_was_generated_successfully.', [
            '<b>' . $newInvoice->invoice_number . '</b>',
            '<b>' . $customer   ->name . '</b>',
        ]);
        $this->Flash->success($messageString . ' ' . $linkToInvoice);

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('invoice_added', $this->AppAuth->getUserId(), $newInvoice->id, 'invoices', $messageString);

        $this->redirect($this->referer());

    }

    public function preview()
    {

        $customerId = h($this->getRequest()->getQuery('customerId'));
        $paidInCash = h($this->getRequest()->getQuery('paidInCash'));

        $currentDay = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
        if (!empty($this->getRequest()->getQuery('currentDay'))) {
            $currentDay = $this->getRequest()->getQuery('currentDay');
        }

        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId,
            ],
        ])->first();

        if (empty($customer)) {
            throw new NotFoundException();
        }

        $newInvoiceNumber = 'xxx';
        $newInvoiceDate = 'xx.xx.xxxx';

        $pdfWriter = new InvoiceToCustomerPdfWriter();
        $invoiceData = $this->Customer->Invoices->getDataForCustomerInvoice($customerId, $currentDay);
        if (!$invoiceData->new_invoice_necessary) {
            die(__d('admin', 'No_data_available_to_generate_an_invoice.'));
        }

        $pdfWriter->prepareAndSetData($invoiceData, $paidInCash, $newInvoiceNumber, $newInvoiceDate);

        if (!empty($this->request->getQuery('outputType')) && $this->request->getQuery('outputType') == 'html') {
            return $this->response->withStringBody($pdfWriter->writeHtml());
        }

        $invoicePdfFile = Configure::read('app.htmlHelper')->getInvoiceLink($customer->name, $customerId, date('Y-m-d'), $newInvoiceNumber);
        $invoicePdfFile = explode(DS, $invoicePdfFile);
        $invoicePdfFile = end($invoicePdfFile);
        $invoicePdfFile = substr($invoicePdfFile, 11);
        $invoicePdfFile = $this->request->getQuery('dateFrom'). '-' . $this->request->getQuery('dateTo') . '-' . $invoicePdfFile;
        $pdfWriter->setFilename($invoicePdfFile);

        die($pdfWriter->writeInline());
    }

    public function cancel()
    {

        $this->RequestHandler->renderAs($this, 'json');

        $invoiceId = h($this->getRequest()->getData('invoiceId'));

        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Payment = $this->getTableLocator()->get('Payments');

        $invoice = $this->Invoice->find('all', [
            'contain' => [
                'Payments',
                'Customers',
                'OrderDetails.OrderDetailTaxes',
                'OrderDetails.OrderDetailUnits',
                'OrderDetails.Taxes',
                'OrderDetails.Products.Manufacturers',
            ],
            'conditions' => [
                'Invoices.id' => $invoiceId,
            ],
        ])->first();

        if (empty($invoice)) {
            throw new NotFoundException();
        }

        foreach($invoice->order_details as $orderDetail) {
            $orderDetail->orderState = ORDER_STATE_ORDER_PLACED;
            $orderDetail->id_invoice = null;
            $this->OrderDetail->save($orderDetail);
        }

        foreach($invoice->payments  as $payment) {
            $payment->invoice_id = null;
            $this->Payment->save($orderDetail);
        }

        $cancellationFactor = -1;
        foreach($invoice->payments as $payment) {
            $payment->amount *= $cancellationFactor;
        }

        foreach($invoice->order_details as $orderDetail) {
            $orderDetail->total_price_tax_excl *= $cancellationFactor;
            $orderDetail->total_price_tax_incl *= $cancellationFactor;
            $orderDetail->deposit *= $cancellationFactor;
            $orderDetail->order_detail_tax->unit_amount *= $cancellationFactor;
            $orderDetail->order_detail_tax->total_amount *= $cancellationFactor;
        }

        $invoiceData = $this->Invoice->prepareDataForCustomerInvoice($invoice->order_details, $invoice->payments, $invoice);

        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $invoice->id_customer,
            ],
            'contain' => [
                'AddressCustomers',
            ]
        ])->first();

        $customer->id_customer = $invoice->customer->id_customer;
        $customer->active_order_details = $invoiceData['active_order_details'];
        $customer->ordered_deposit = $invoiceData['ordered_deposit'];
        $customer->returned_deposit = $invoiceData['returned_deposit'];
        $customer->tax_rates = $invoiceData['tax_rates'];
        $customer->sumPriceIncl = $invoiceData['sumPriceIncl'];
        $customer->sumPriceExcl = $invoiceData['sumPriceExcl'];
        $customer->sumTax = $invoiceData['sumTax'];
        $customer->cancelledInvoice = $invoiceData['cancelledInvoice'];
        $customer->new_invoice_necessary = true;
        $customer->is_cancellation_invoice = true;

        $currentDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();

        $invoiceToCustomer = new GenerateInvoiceToCustomer();
        $newInvoice = $invoiceToCustomer->run($customer, $currentDay, $invoice->paid_in_cash);

        $invoice->status = APP_DEL;
        $invoice->cancellation_invoice_id = $newInvoice->id;
        $this->Invoice->save($invoice);

        $linkToInvoice = Configure::read('app.htmlHelper')->link(
            __d('admin', 'Download'),
            '/admin/lists/getInvoice?file=' . $newInvoice->filename,
            [
                'class' => 'btn btn-outline-light btn-flash-message',
                'target' => '_blank',
                'escape' => false,
            ],
        );

        $messageString = __d('admin', 'Invoice_number_{0}_of_{1}_was_successfully_cancelled.', [
            '<b>' . $invoice->invoice_number . '</b>',
            '<b>' . $invoice->customer->name . '</b>',
        ]);

        $messageString .= ' ' . __d('admin', 'Cancellation_invoice_number_{0}_was_generated_successfully.', [
            '<b>' . $newInvoice->invoice_number . '</b>',
        ]);

        $this->Flash->success($messageString . $linkToInvoice);

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('invoice_cancelled', $this->AppAuth->getUserId(), $newInvoice->id, 'invoices', $messageString);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

    public function index()
    {

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getLastDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        }
        $this->set('customerId', $customerId);

        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->Invoice = $this->getTableLocator()->get('Invoices');

        $query = $this->Invoice->find('all', [
            'contain' => [
                'InvoiceTaxes',
                'Customers',
                'CancellationInvoices',
                'CancelledInvoices',
            ],
            'conditions' => $this->getInvoiceConditions($dateFrom, $dateTo, $customerId),
        ]);

        $invoices = $this->paginate($query, [
            'sortableFields' => [
                'Invoices.id',
                'Invoices.invoice_number',
                'Invoices.created',
                'Customers.' . Configure::read('app.customerMainNamePart'),
                'Invoices.paid_in_cash',
                'Invoices.email_status',
            ],
            'order' => [
                'Invoices.id' => 'DESC'
            ]
        ])->toArray();

        $this->set('invoices', $invoices);

        $invoiceSums = [
            'total_sum_price_excl' => 0,
            'total_sum_tax' => 0,
            'total_sum_price_incl' => 0,
        ];

        foreach($invoices as $invoice) {
            foreach($invoice->invoice_taxes as $invoiceTax) {
                $invoiceSums['total_sum_price_excl'] += $invoiceTax->total_price_tax_excl;
                $invoiceSums['total_sum_tax'] += $invoiceTax->total_price_tax;
                $invoiceSums['total_sum_price_incl'] += $invoiceTax->total_price_tax_incl;
            }
        }
        $this->set('invoiceSums', $invoiceSums);

        $this->set('customersForDropdown', $this->Customer->getForDropdown());
        $this->set('title_for_layout', __d('admin', 'Journal'));

        $preparedTaxRates = $this->Invoice->getPreparedTaxRatesForSumTable($invoices);
        $this->set('taxRates', $preparedTaxRates['taxRates']);
        $this->set('taxRatesSums', $preparedTaxRates['taxRatesSums']);

    }

    private function getInvoiceConditions($dateFrom, $dateTo, $customerId)
    {

        $conditions = [
            'Invoices.id_customer > 0',
            'DATE_FORMAT(Invoices.created, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'',
            'DATE_FORMAT(Invoices.created, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'',
        ];

        if ($customerId != '') {
            $conditions['Invoices.id_customer'] = $customerId;
        }

        return $conditions;

    }

}
