<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\PdfWriter;

use App\Lib\Pdf\ListTcpdf;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;

class OrderListByCustomerPdfWriter extends PdfWriter
{

    public function __construct()
    {
        $this->plugin = 'Admin';
        $this->setPdfLibrary(new ListTcpdf());
        $this->Manufacturer = FactoryLocator::get('Table')->get('Manufacturers');
    }

    public function prepareAndSetData($manufacturerId, $pickupDay, $validOrderStates, $orderDetailIds)
    {

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'AddressManufacturers'
            ],
        ])->first();

        $productResults = $this->Manufacturer->getDataForInvoiceOrOrderList(
            $manufacturerId,
            'customer',
            $pickupDay,
            null,
            $validOrderStates,
            Configure::read('appDb.FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES'),
            $orderDetailIds,
        );

        // calculate sum of price
        $sumPriceIncl = 0;
        $sumPriceExcl = 0;
        $sumTax = 0;
        $sumAmount = 0;
        $sumTimebasedCurrencyPriceIncl = 0;
        foreach ($productResults as $result) {
            $sumPriceIncl += $result['OrderDetailPriceIncl'];
            $sumPriceExcl += $result['OrderDetailPriceExcl'];
            $sumTax += $result['OrderDetailTaxAmount'];
            $sumAmount += $result['OrderDetailAmount'];
            if (isset($result['OrderDetailTimebasedCurrencyPriceInclAmount'])) {
                $sumTimebasedCurrencyPriceIncl += $result['OrderDetailTimebasedCurrencyPriceInclAmount'];
            }
        }

        $this->setData([
            'productResults' => $productResults,
            'manufacturer' => $manufacturer,
            'currentDateForOrderLists' => Configure::read('app.timeHelper')->getCurrentDateTimeForFilename(),
            'sumPriceIncl' => $sumPriceIncl,
            'sumPriceExcl' => $sumPriceExcl,
            'sumTax' => $sumTax,
            'sumAmount' => $sumAmount,
            'sumTimebasedCurrencyPriceIncl' => $sumTimebasedCurrencyPriceIncl,
            'variableMemberFee' => $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee),
        ]);

    }

}

