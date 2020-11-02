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
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;

class InvoiceRetailModeEnabledPdfWriter extends PdfWriter
{

    public $Customer;

    public function __construct()
    {
        $this->plugin = 'Admin';
        $this->setPdfLibrary(new CustomerInvoiceTcpdf());
        $this->Customer = FactoryLocator::get('Table')->get('Customers');
    }

    public function prepareAndSetData($customerId, $dateFrom, $dateTo, $newInvoiceNumber, $validOrderStates, $period, $invoiceDate)
    {

        $result = $this->Customer->getDataForInvoice($customerId, $dateFrom, $dateTo, $validOrderStates);

        $sumPriceIncl = 0;
        $sumPriceExcl = 0;
        $sumTax = 0;
        foreach ($result->active_order_details as $orderDetail) {
            $sumPriceIncl += $orderDetail->total_price_tax_incl + $orderDetail->deposit;
            $sumPriceExcl += $orderDetail->total_price_tax_excl + $orderDetail->deposit_net;
            $sumTax += $orderDetail->order_detail_tax->total_amount + $orderDetail->deposit_tax;
        }

        $this->setData([
            'result' => $result,
            'sumPriceIncl' => $sumPriceIncl,
            'sumPriceExcl' => $sumPriceExcl,
            'sumTax' => $sumTax,
            'newInvoiceNumber' => $newInvoiceNumber,
            'period' => $period,
            'invoiceDate' => $invoiceDate,
            'dateFrom' => date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(str_replace('/', '-', $dateFrom))),
            'dateTo' => date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(str_replace('/', '-', $dateTo))),
            'customer' => $result,
        ]);

    }

}
