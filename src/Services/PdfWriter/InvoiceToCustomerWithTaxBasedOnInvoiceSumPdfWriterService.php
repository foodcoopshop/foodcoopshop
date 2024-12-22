<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\PdfWriter;

use App\Services\Pdf\CustomerInvoiceWithTaxBasedOnInvoiceSumTcpdfService;

class InvoiceToCustomerWithTaxBasedOnInvoiceSumPdfWriterService extends PdfWriterService
{

    public function __construct()
    {
        $this->plugin = 'Admin';
        $this->setPdfLibrary(new CustomerInvoiceWithTaxBasedOnInvoiceSumTcpdfService());
        $this->templateFile = DS . 'pdf' . DS . 'invoice_to_customer_service';
    }

    public function prepareAndSetData($data, $paidInCash, $newInvoiceNumber, $invoiceDate)
    {
        $this->setData([
            'result' => $data,
            'sumPriceIncl' => $data->sumPriceIncl,
            'sumPriceExcl' => $data->sumPriceExcl,
            'sumTax' => $data->sumTax,
            'newInvoiceNumber' => $newInvoiceNumber,
            'invoiceDate' => $invoiceDate,
            'paidInCash' => $paidInCash,
        ]);
    }

}
