<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Model\Table\InvoicesTable;
use App\Model\Table\OrderDetailsTable;
use App\Model\Table\PaymentsTable;
use App\Model\Table\PickupDaysTable;
use App\Services\HelloCash\HelloCashService;
use App\Services\Invoice\GenerateInvoiceToCustomerService;
use App\Services\PdfWriter\InvoiceToCustomerPdfWriterService;
use App\Services\PdfWriter\InvoiceToCustomerWithTaxBasedOnInvoiceSumPdfWriterService;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Http\Exception\NotFoundException;
use Cake\View\JsonView;
use Cake\I18n\DateTime;

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
class InvoicesController extends AdminAppController
{

    protected InvoicesTable $Invoice;
    protected OrderDetailsTable $OrderDetail;
    protected PickupDaysTable $PickupDay;
    protected PaymentsTable $Payment;

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    public function downloadAsZipFile()
    {

        $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        $dateTo = h($this->getRequest()->getQuery('dateTo'));
        $customerId = h($this->getRequest()->getQuery('customerId'));

        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoices = $this->Invoice->find('all');
        $invoices = $this->setInvoiceConditions($invoices, $dateFrom, $dateTo, $customerId);

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

        $customerTable = $this->getTableLocator()->get('Customers');
        $customer = $customerTable->find('all', conditions: [
            'Customers.id_customer' => $customerId,
        ])->first();

        if (empty($customer)) {
            throw new \Exception('customer not found');
        }

        $invoiceData = $customerTable->Invoices->getDataForCustomerInvoice($customer->id_customer, Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        if (!$invoiceData->new_invoice_necessary) {
            $this->Flash->success(__d('admin', 'No_data_available_to_generate_an_invoice.'));
            $this->redirect($this->referer());
            return;
        }

        $currentDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();

        if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {

            $helloCashService = new HelloCashService();
            $responseObject = $helloCashService->generateInvoice($invoiceData, $currentDay, $paidInCash, false);
            $invoiceId = $responseObject->invoice_id;
            $invoiceRoute = Configure::read('app.slugHelper')->getHelloCashReceipt($invoiceId);
            $invoiceNumber = $responseObject->invoice_number;

        } else {

            $invoiceToCustomerService = new GenerateInvoiceToCustomerService();
            $newInvoice = $invoiceToCustomerService->run($invoiceData, $currentDay, $paidInCash);
            $invoiceRoute = Configure::read('app.slugHelper')->getInvoiceDownloadRoute($newInvoice->filename);
            $invoiceNumber = $newInvoice->invoice_number;
            $invoiceId = $newInvoice->id;
        }

        if ($paidInCash && $invoiceData->sumPriceIncl != 0) {

            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $this->Payment = $this->getTableLocator()->get('Payments');
                $paymentEntity = $this->Payment->newEntity(
                    [
                        'status' => APP_ON,
                        'approval' => APP_ON,
                        'type' => $invoiceData->sumPriceIncl > 0 ? 'product' : 'payback',
                        'id_customer' => $customerId,
                        'id_manufacturer' => 0,
                        'date_add' => DateTime::now(),
                        'date_changed' => DateTime::now(),
                        'amount' => abs($invoiceData->sumPriceIncl),
                        'approval_comment' => __d('admin', 'Paid_in_cash') . ', ' . __d('admin', 'Invoice_number_abbreviation') . ': ' . $invoiceNumber,
                        'created_by' => $this->identity->getId(),
                    ]
                );
                $this->Payment->save($paymentEntity);
            }

            // mark row as picked up
            $this->PickupDay = $this->getTableLocator()->get('PickupDays');
            $this->PickupDay->changeState(
                $customerId,
                Configure::read('app.timeHelper')->formatToDbFormatDate($currentDay),
                APP_ON,
            );

            if (!$customer->invoices_per_email_enabled) {
                $this->request->getSession()->write('invoiceRouteForAutoPrint', $invoiceRoute);
            }

        }

        $linkToInvoice = Configure::read('app.htmlHelper')->link(
            __d('admin', 'Print_receipt'),
            $invoiceRoute,
            [
                'class' => 'btn btn-outline-light btn-flash-message',
                'target' => '_blank',
                'escape' => false,
            ],
        );
        $messageString = __d('admin', 'Invoice_number_{0}_of_{1}_was_generated_successfully.', [
            '<b>' . $invoiceNumber . '</b>',
            '<b>' . $customer->name . '</b>',
        ]);
        $this->Flash->success($messageString . '<br />' . $linkToInvoice);

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('invoice_added', $this->identity->getId(), $invoiceId, 'invoices', $messageString);

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

        $customerTable = $this->getTableLocator()->get('Customers');
        $customer = $customerTable->find('all', conditions: [
            'Customers.id_customer' => $customerId,
        ])->first();

        if (empty($customer)) {
            throw new NotFoundException();
        }

        $invoiceData = $customerTable->Invoices->getDataForCustomerInvoice($customerId, $currentDay);
        if (!$invoiceData->new_invoice_necessary) {
            die(__d('admin', 'No_data_available_to_generate_an_invoice.'));
        }

        if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {

            $helloCashService = new HelloCashService();
            $responseObject = $helloCashService->generateInvoice($invoiceData, $currentDay, $paidInCash, true);
            $invoiceId = $responseObject->invoice_id;
            $this->redirect(Configure::read('app.slugHelper')->getHelloCashReceipt($invoiceId));
            return;

        } else {

            $newInvoiceNumber = 'xxx';
            $newInvoiceDate = 'xx.xx.xxxx';

            if (!Configure::read('appDb.FCS_TAX_BASED_ON_NET_INVOICE_SUM')) {
                $pdfWriter = new InvoiceToCustomerPdfWriterService();
            } else {
                $pdfWriter = new InvoiceToCustomerWithTaxBasedOnInvoiceSumPdfWriterService();
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

    }

    public function cancel()
    {

        $this->request = $this->request->withParam('_ext', 'json');

        $invoiceId = h($this->getRequest()->getData('invoiceId'));

        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Payment = $this->getTableLocator()->get('Payments');

        $invoice = $this->Invoice->find('all',
        contain: [
            'Payments',
            'Customers',
            'OrderDetails.OrderDetailUnits',
            'OrderDetails.Products.Manufacturers',
        ],
        conditions: [
            'Invoices.id' => $invoiceId,
        ])->first();

        if (empty($invoice)) {
            throw new NotFoundException();
        }

        $this->OrderDetail->onInvoiceCancellation($invoice->order_details);
        $this->Payment->onInvoiceCancellation($invoice->payments);

        $currentDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();

        if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {

            $helloCashService = new HelloCashService();
            $responseObject = $helloCashService->cancelInvoice($invoice->customer->id_customer, $invoice->id, $currentDay);
            $cancelledInvoiceNumber = $responseObject->invoice_number;
            $invoiceId = $responseObject->cancellation_details->cancellation_number;
            $cancellationInvoiceNumber = $responseObject->cancellation_details->cancellation_number;
            $invoiceRoute = Configure::read('app.slugHelper')->getHelloCashReceipt($responseObject->invoice_id, true);

        } else {

            $cancellationFactor = -1;
            foreach($invoice->payments as $payment) {
                $payment->amount *= $cancellationFactor;
            }

            foreach($invoice->order_details as $orderDetail) {
                $orderDetail->total_price_tax_excl *= $cancellationFactor;
                $orderDetail->total_price_tax_incl *= $cancellationFactor;
                $orderDetail->deposit *= $cancellationFactor;
                $orderDetail->tax_unit_amount *= $cancellationFactor;
                $orderDetail->tax_total_amount *= $cancellationFactor;
            }

            $invoiceData = $this->Invoice->prepareDataForCustomerInvoice($invoice->order_details, $invoice->payments, $invoice);

            $customer = $this->Customer->find('all',
            conditions: [
                'Customers.id_customer' => $invoice->id_customer,
            ],
            contain: [
                'AddressCustomers',
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

            $invoiceToCustomerService = new GenerateInvoiceToCustomerService();
            $newInvoice = $invoiceToCustomerService->run($customer, $currentDay, $invoice->paid_in_cash);
            $invoiceId = $newInvoice->id;
            $cancelledInvoiceNumber = $invoice->invoice_number;
            $cancellationInvoiceNumber = $newInvoice->invoice_number;
            $invoiceRoute = Configure::read('app.slugHelper')->getInvoiceDownloadRoute($newInvoice->filename);

        }

        $invoice->cancellation_invoice_id = $invoiceId;
        $this->Invoice->save($invoice);

        // cancel automatically added payment
        if ($invoice->paid_in_cash) {

            if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
                $this->Payment = $this->getTableLocator()->get('Payments');
                $approvalString = __d('admin', 'Paid_in_cash') . ', ' . __d('admin', 'Invoice_number_abbreviation') . ': ' . $cancelledInvoiceNumber;
                $this->Payment->updateAll([
                    'status' => APP_DEL,
                    'date_changed' => DateTime::now(),
                    'approval_comment' => __d('admin', 'Invoice_cancelled') . ': ' . $approvalString
                ], [
                    'type IN' => ['product', 'payback'],
                    'id_customer' => $invoice->customer->id_customer,
                    'approval_comment' => $approvalString,
                ]);
            }

            // remove "mark row as picked up"
            $this->PickupDay = $this->getTableLocator()->get('PickupDays');
            $this->PickupDay->changeState(
                $invoice->customer->id_customer,
                Configure::read('app.timeHelper')->formatToDbFormatDate($currentDay),
                APP_OFF,
            );

            if (!$invoice->customer->invoices_per_email_enabled) {
                $this->request->getSession()->write('invoiceRouteForAutoPrint', $invoiceRoute);
            }

        }

        $linkToInvoice = Configure::read('app.htmlHelper')->link(
            __d('admin', 'Download'),
            $invoiceRoute,
            [
                'class' => 'btn btn-outline-light btn-flash-message',
                'target' => '_blank',
                'escape' => false,
            ],
        );

        $messageString = __d('admin', 'Invoice_number_{0}_of_{1}_was_successfully_cancelled.', [
            '<b>' . $cancelledInvoiceNumber . '</b>',
            '<b>' . $invoice->customer->name . '</b>',
        ]);

        $messageString .= '<br />' . __d('admin', 'Cancellation_invoice_number_{0}_was_generated_successfully.', [
            '<b>' . $cancellationInvoiceNumber . '</b>',
        ]);

        $this->Flash->success($messageString . $linkToInvoice);

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('invoice_cancelled', $this->identity->getId(), $invoiceId, 'invoices', $messageString);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
            'invoiceId' => $invoiceId,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'invoiceId']);

    }

    public function myInvoices()
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

        $customerId = $this->identity->getId();

        $this->set('customerId', $customerId);

        $this->processIndex($dateFrom, $dateTo, $customerId);

        $this->set('isOverviewMode', false);

        $this->set('title_for_layout', __d('admin', 'My_invoices'));

        $this->render('index');

    }

    public function index()
    {

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfThisMonth();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getLastDayOfThisMonth();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        }
        $this->set('customerId', $customerId);

        $this->processIndex($dateFrom, $dateTo, $customerId);

        $this->set('title_for_layout', __d('admin', 'Journal'));
        $this->set('isOverviewMode', true);

    }

    protected function processIndex($dateFrom, $dateTo, $customerId)
    {

        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->Invoice = $this->getTableLocator()->get('Invoices');

        $query = $this->Invoice->find('all', contain: [
            'InvoiceTaxes',
            'Customers',
            'CancellationInvoices',
            'CancelledInvoices',
        ]);
        $query = $this->setInvoiceConditions($query, $dateFrom, $dateTo, $customerId);

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
                'Invoices.created' => 'DESC',
                'Invoices.id' => 'DESC',
            ]
        ]);

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

        $preparedTaxRates = $this->Invoice->getPreparedTaxRatesForSumTable($invoices);
        $this->set('taxRates', $preparedTaxRates['taxRates']);
        $this->set('taxRatesSums', $preparedTaxRates['taxRatesSums']);

    }

    protected function setInvoiceConditions($query, $dateFrom, $dateTo, $customerId)
    {

        $query->where(function (QueryExpression $exp) use ($dateFrom, $dateTo) {
            $exp->gte('DATE_FORMAT(Invoices.created, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom));
            $exp->lte('DATE_FORMAT(Invoices.created, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo));
            $exp->gt('Invoices.id_customer', 0);
            return $exp;
        });

        if ($customerId != '') {
            $query->where(['Invoices.id_customer' => $customerId]);
        }

        return $query;

    }

}
