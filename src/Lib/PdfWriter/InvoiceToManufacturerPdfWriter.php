<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\PdfWriter;

use App\Lib\Pdf\ListTcpdf;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;

class InvoiceToManufacturerPdfWriter extends PdfWriter
{

    use SetSumTrait;

    public $Manufacturer;

    public function __construct()
    {
        $this->plugin = 'Admin';
        $this->setPdfLibrary(new ListTcpdf());
        $this->Manufacturer = FactoryLocator::get('Table')->get('Manufacturers');
    }

    public function prepareAndSetData($manufacturerId, $dateFrom, $dateTo, $newInvoiceNumber, $validOrderStates, $period, $invoiceDate)
    {

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'AddressManufacturers'
            ],
        ])->first();

        $productResults = $this->Manufacturer->getDataForInvoiceOrOrderList($manufacturerId, 'product', $dateFrom, $dateTo, $validOrderStates, Configure::read('appDb.FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES'));
        $customerResults = $this->Manufacturer->getDataForInvoiceOrOrderList($manufacturerId, 'customer', $dateFrom, $dateTo, $validOrderStates, Configure::read('appDb.FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES'));

        $this->setSums($productResults);

        $this->setData([
            'productResults' => $productResults,
            'customerResults' => $customerResults,
            'newInvoiceNumber' => $newInvoiceNumber,
            'period' => $period,
            'invoiceDate' => $invoiceDate,
            'dateFrom' => date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(str_replace('/', '-', $dateFrom))),
            'dateTo' => date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(str_replace('/', '-', $dateTo))),
            'manufacturer' => $manufacturer,
            'variableMemberFee' => $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee),
        ]);

    }

}
