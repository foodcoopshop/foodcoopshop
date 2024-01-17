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
namespace App\Services\PdfWriter;

use App\Services\Pdf\ListTcpdfService;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use App\Services\PdfWriter\Traits\SetSumTrait;

abstract class OrderListPdfWriterService extends PdfWriterService
{

    use SetSumTrait;

    public $Manufacturer;

    public function __construct()
    {
        $this->plugin = 'Admin';
        $this->setPdfLibrary(new ListTcpdfService());
        $this->Manufacturer = FactoryLocator::get('Table')->get('Manufacturers');
    }

    public function prepareAndSetData($manufacturerId, $pickupDay, $validOrderStates, $orderDetailIds, $isAnonymized)
    {

        $reflect = new \ReflectionClass($this);
        $type = str_replace('OrderListBy', '', $reflect->getShortName());
        $type = str_replace('PdfWriter', '', $type);
        $type = strtolower($type);
        $type = str_replace('service', '', $type);

        if (!in_array($type, ['customer', 'product'])) {
            throw new \Exception('type not valid: ' . $type);
        }

        $manufacturer = $this->Manufacturer->find('all',
            conditions: [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            contain: [
                'AddressManufacturers'
            ],
        )->first();

        $results = $this->Manufacturer->getDataForInvoiceOrOrderList(
            $manufacturerId,
            $type,
            $pickupDay,
            null,
            $validOrderStates,
            (bool) $manufacturer->include_stock_products_in_order_lists,
            $orderDetailIds,
        );

        if ($isAnonymized) {
            $results = $this->Manufacturer->anonymizeCustomersInInvoiceOrOrderList($results);
        }

        $this->setSums($results);

        $preparedResults = [
            'manufacturer' => $manufacturer,
            'currentDateForOrderLists' => Configure::read('app.timeHelper')->getCurrentDateTimeForFilename(),
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

