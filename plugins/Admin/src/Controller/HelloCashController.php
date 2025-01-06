<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Services\HelloCash\HelloCashService;
use Cake\Http\Response;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class HelloCashController extends AdminAppController
{

    protected HelloCashService $helloCashService;

    public function initialize(): void
    {
        parent::initialize();
        $this->helloCashService = new HelloCashService();
    }

    public function getReceipt($invoiceId, $cancellation): void
    {
        $this->viewBuilder()->setLayout('ajax');
        $helloCashInvoice = $this->helloCashService->getReceipt($invoiceId, $cancellation);

        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoice = $invoicesTable->find(
            conditions: [
                'Invoices.id' => $invoiceId,
            ],
            contain: [
                'InvoiceTaxes',
                'Customers',
            ],
        )->first();

        $this->set('helloCashInvoice', $helloCashInvoice);
        $this->set('invoice', $invoice);
    }

    public function getInvoice($invoiceId, $cancellation): Response
    {
        $this->disableAutoRender();
        $pdfAsString = $this->helloCashService->getInvoice($invoiceId, $cancellation);
        $this->response = $this->response->withType('pdf');

        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoice = $invoicesTable->get($invoiceId);

        $filename = $invoice->invoice_number . '.pdf';
        $this->response = $this->response->withHeader('Content-Disposition', 'inline; filename="' . $filename . '"');

        $this->response = $this->response->withStringBody($pdfAsString);
        return $this->response;
    }

}
