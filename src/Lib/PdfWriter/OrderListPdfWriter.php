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
use App\Lib\Error\Exception\InvalidParameterException;

abstract class OrderListPdfWriter extends PdfWriter
{

    public $Manufacturer;

    public function __construct()
    {
        $this->plugin = 'Admin';
        $this->setPdfLibrary(new ListTcpdf());
        $this->Manufacturer = FactoryLocator::get('Table')->get('Manufacturers');
    }

    public function prepareAndSetData($manufacturerId, $pickupDay, $validOrderStates, $orderDetailIds)
    {

        $reflect = new \ReflectionClass($this);
        $type = str_replace('OrderListBy', '', $reflect->getShortName());
        $type = str_replace('PdfWriter', '', $type);
        $type = strtolower($type);

        if (!in_array($type, ['customer', 'product'])) {
            throw new InvalidParameterException('type not valid');
        }

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'AddressManufacturers'
            ],
        ])->first();

        $results = $this->Manufacturer->getDataForInvoiceOrOrderList(
            $manufacturerId,
            $type,
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
        foreach ($results as $result) {
            $sumPriceIncl += $result['OrderDetailPriceIncl'];
            $sumPriceExcl += $result['OrderDetailPriceExcl'];
            $sumTax += $result['OrderDetailTaxAmount'];
            $sumAmount += $result['OrderDetailAmount'];
            if (isset($result['OrderDetailTimebasedCurrencyPriceInclAmount'])) {
                $sumTimebasedCurrencyPriceIncl += $result['OrderDetailTimebasedCurrencyPriceInclAmount'];
            }
        }

        $preparedResults = [
            'manufacturer' => $manufacturer,
            'currentDateForOrderLists' => Configure::read('app.timeHelper')->getCurrentDateTimeForFilename(),
            'sumPriceIncl' => $sumPriceIncl,
            'sumPriceExcl' => $sumPriceExcl,
            'sumTax' => $sumTax,
            'sumAmount' => $sumAmount,
            'sumTimebasedCurrencyPriceIncl' => $sumTimebasedCurrencyPriceIncl,
            'variableMemberFee' => $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee),
        ];

        if ($type == 'customer') {
            $preparedResults['customerResults'] = $results;
        } else {
            $preparedResults['productResults'] = $results;
        }

        $this->setData($preparedResults);

    }

}

