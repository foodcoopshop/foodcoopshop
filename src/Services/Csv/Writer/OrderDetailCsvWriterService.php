<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Csv\Writer;

use Admin\Traits\OrderDetails\Filter\OrderDetailsFilterTrait;

class OrderDetailCsvWriterService extends BaseCsvWriterService
{

    use OrderDetailsFilterTrait;
    
    public function getHeader(): array
    {
        $header = [
            __('Id'),
        ];
        return $header;
    }

    public function getRecords(): array
    {

        $orderDetailId = h($this->getRequestQuery('orderDetailId', $this->getDefaultOrderDetailId()));
        $pickupDay = $this->getPickupDay($orderDetailId);
        $manufacturerId = h($this->getRequestQuery('manufacturerId', $this->getDefaultManufacturerId()));
        $deposit = h($this->getRequestQuery('deposit', $this->getDefaultDeposit()));
        $productId = h($this->getRequestQuery('productId', $this->getDefaultProductId()));
        $customerId = h($this->getRequestQuery('customerId', $this->getDefaultCustomerId()));
        $cartType = h($this->getRequestQuery('cartType', $this->getDefaultCartType()));
        $groupBy = h($this->getRequestQuery('groupBy', $this->getDefaultGroupBy()));

        $orderDetails = $this->getOrderDetails($manufacturerId, $productId, $customerId, $pickupDay, $orderDetailId, $deposit, $groupBy, $cartType);
        $records = [];
        foreach($orderDetails as $orderDetail) {
            $record = [
                $orderDetail->id_order_detail,
            ];
            $records[] = $record;

        }

        return $records;
    }


}