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
use Cake\Core\Configure;
use App\Model\Entity\OrderDetail;

class OrderDetailCsvWriterService extends BaseCsvWriterService
{

    use OrderDetailsFilterTrait;
    
    public function getHeader(): array
    {
        $header = [
            __('Id'),
            __('Amount'),
            __('Product'),
            __('Manufacturer'),
            __('Price'),
            __('Deposit'),
            __('Weight'),
            __('Price_per'),
            __('Member'),
            __('Pickup_day'),
            __('Order state'),
            __('Cart type'),
            __('Order date'),
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
        $orderDetails = $this->applyUngroupedDefaultSort($orderDetails->toArray());

        $records = [];
        foreach($orderDetails as $orderDetail) {
            $record = [
                $orderDetail->id_order_detail,
                $orderDetail->product_amount,
                $orderDetail->product_name,
                $orderDetail->product->manufacturer->decoded_name,
                Configure::read('app.numberHelper')->formatAsDecimal($orderDetail->total_price_tax_incl),
                $orderDetail->deposit > 0 ? Configure::read('app.numberHelper')->formatAsDecimal($orderDetail->deposit) : '',
                $this->getProductUnits($orderDetail),
                $this->getUnitName($orderDetail),
                $orderDetail->customer->decoded_name,
                $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')),
                Configure::read('app.htmlHelper')->getOrderStates()[$orderDetail->order_state],
                Configure::read('app.htmlHelper')->getCartTypes()[$orderDetail->cart_product->cart->cart_type],
                $orderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')),
            ];
            $records[] = $record;

        }

        return $records;
    }

    private function getProductUnits(OrderDetail $orderDetail): float|string
    {
        if (empty($orderDetail->order_detail_unit)) {
            return '';
        }

        return Configure::read('app.numberHelper')->formatUnitAsDecimal($orderDetail->order_detail_unit->product_quantity_in_units);
    }

    private function getUnitName(OrderDetail $orderDetail): string
    {
        if (empty($orderDetail->order_detail_unit)) {
            return '';
        }

        $unitAmount = $orderDetail->order_detail_unit->unit_amount;
        $unitName = $orderDetail->order_detail_unit->unit_name;

        return ($unitAmount > 1 ? Configure::read('app.numberHelper')->formatAsDecimal($unitAmount, 0) . ' ' : '') . $unitName;
    }

}