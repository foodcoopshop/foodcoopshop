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
use App\Controller\Component\StringComponent;
use Cake\Datasource\FactoryLocator;

class OrderDetailsPdfWriterService extends PdfWriterService
{

    public $OrderDetail;

    public function __construct()
    {
        $this->plugin = 'Admin';
        $this->setPdfLibrary(new ListTcpdfService());
        if (Configure::read('app.additionalTextForReceipt') != '') {
            Configure::write('appDb.FCS_APP_ADDRESS',  Configure::read('appDb.FCS_APP_ADDRESS') . '<br />' . Configure::read('app.additionalTextForReceipt'));
        }

    }

    public function prepareAndSetData($pickupDay, $order)
    {

        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');
        $odParams = $this->OrderDetail->getOrderDetailParams('', '', '', $pickupDay, '', '');

        if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
            $this->OrderDetail->getAssociation('PickupDayEntities')->setConditions([
                'PickupDayEntities.pickup_day' => Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[0])
            ]);
            $odParams['contain'][] = 'PickupDayEntities';
        }

        $orderDetails = $this->OrderDetail->find('all',
            conditions: $odParams['conditions'],
            contain: $odParams['contain'],
        )->toArray();

        $storageLocation = [];
        $customerName = [];
        $manufacturerName = [];
        $productName = [];
        foreach($orderDetails as $orderDetail) {
            $storageLocationValue = 0;
            if (!is_null($order) && !is_null($orderDetail->product->storage_location)) {
                $storageLocationValue = $orderDetail->product->storage_location->rank;
            }
            $storageLocation[] = $storageLocationValue;
            $customerName[] = mb_strtolower(StringComponent::slugify($orderDetail->customer->name));
            $manufacturerName[] = mb_strtolower(StringComponent::slugify($orderDetail->product->manufacturer->name));
            $productName[] = mb_strtolower(StringComponent::slugify($orderDetail->product_name));
        }
        array_multisort(
            $customerName, SORT_ASC,
            $storageLocation, SORT_ASC,
            $manufacturerName, SORT_ASC,
            $productName, SORT_ASC,
            $orderDetails,
        );

        $preparedOrderDetails = [];
        foreach($orderDetails as $orderDetail) {
            if (!isset($preparedOrderDetails[$orderDetail->id_customer])) {
                $preparedOrderDetails[$orderDetail->id_customer] = [];
            }
            $preparedOrderDetails[$orderDetail->id_customer][] = $orderDetail;
        }
        
        $this->setData([
            'orderDetails' => $preparedOrderDetails,
            'order' => $order,
        ]);
        
    }

}

