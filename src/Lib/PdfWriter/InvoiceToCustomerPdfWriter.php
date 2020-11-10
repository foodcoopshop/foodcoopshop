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
namespace App\Lib\PdfWriter;

use App\Lib\Pdf\CustomerInvoiceTcpdf;
use Cake\Datasource\FactoryLocator;

class InvoiceToCustomerPdfWriter extends PdfWriter
{

    public $Invoice;

    public function __construct()
    {
        $this->plugin = 'Admin';
        $this->setPdfLibrary(new CustomerInvoiceTcpdf());
        $this->Invoice = FactoryLocator::get('Table')->get('Invoices');
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
