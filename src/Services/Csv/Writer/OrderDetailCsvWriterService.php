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
use Cake\Routing\Router;

class OrderDetailCsvWriterService extends BaseCsvWriterService
{

    use OrderDetailsFilterTrait;

    private mixed $identity;

    public function __construct()
    {
        $this->identity = Router::getRequest()->getAttribute('identity');
    }
    
    /**
     * @return list<string>
     */
    public function getHeader(): array
    {
        $header = [
            __('Id'),
            __('Amount'),
            __('Product'),
            __('Manufacturer'),
            __('Gross_price'),
            __('Price_net'),
            __('Deposit'),
            __('Tax rate'),
            __('Weight'),
            __('Price_per'),
            __('Member'),
            __('Pickup_day'),
            __('Order state'),
            __('Cart type'),
            __('Order_date'),
        ];
        return $header;
    }

    /**
     * @return list<list<string|int|float|bool|null>>
     */
    public function getRecords(): array
    {

        $orderDetailId = h($this->getRequestQuery('orderDetailId', $this->getDefaultOrderDetailId()));
        $pickupDay = $this->getPickupDay($orderDetailId);
        $manufacturerId = h($this->getRequestQuery('manufacturerId', $this->getDefaultManufacturerId()));
        $deposit = h($this->getRequestQuery('deposit', $this->getDefaultDeposit()));
        $productId = h($this->getRequestQuery('productId', $this->getDefaultProductId()));
        $customerId = h($this->getRequestQuery('customerId', $this->getDefaultCustomerId()));
        $cartType = h($this->getRequestQuery('cartType', $this->getDefaultCartType()));
        $taxRate = h($this->getRequestQuery('taxRate', $this->getDefaultTaxRate()));
        $categoryIds = h($this->getRequestQuery('categoryIds', $this->getDefaultCategoryIds()));
        $groupBy = h($this->getRequestQuery('groupBy', $this->getDefaultGroupBy()));
        $sort = $this->getRequestQuery('sort');
        $direction = $this->getRequestQuery('direction', 'ASC');

        $orderDetails = $this->getOrderDetails($manufacturerId, $productId, $customerId, $pickupDay, $orderDetailId, $deposit, $groupBy, $cartType, $taxRate, $categoryIds);
        if (!is_null($sort)) {
            $orderDetails->orderBy([$sort => $direction]);
        }
        $orderDetails = $this->applyUngroupedDefaultSort($orderDetails->toArray());

        $records = [];
        foreach($orderDetails as $orderDetail) {
            $record = [
                $orderDetail->id_order_detail,
                $orderDetail->product_amount,
                $this->getProductName($orderDetail),
                $orderDetail->product->manufacturer->decoded_name,
                Configure::read('app.numberHelper')->formatAsDecimal($orderDetail->total_price_tax_incl),
                Configure::read('app.numberHelper')->formatAsDecimal($orderDetail->total_price_tax_excl),
                $orderDetail->deposit > 0 ? Configure::read('app.numberHelper')->formatAsDecimal($orderDetail->deposit) : '',
                Configure::read('app.numberHelper')->formatTaxRate($orderDetail->tax_rate),
                $this->getProductUnits($orderDetail),
                $this->getUnitName($orderDetail),
                $this->getCustomerName($orderDetail),
                $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')),
                Configure::read('app.htmlHelper')->getOrderStates()[$orderDetail->order_state],
                Configure::read('app.htmlHelper')->getCartTypes()[$orderDetail->cart_product->cart->cart_type],
                $orderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')),
            ];
            $records[] = $record;

        }

        return $records;
    }

    private function getProductName(OrderDetail $orderDetail): string
    {
        $splitProductName = $this->getSplitProductName($orderDetail);
        $productName = count($splitProductName) == 1 ? $orderDetail->product_name : $splitProductName[0];
        return $productName;
    }

    private function getCustomerName(OrderDetail $orderDetail): string
    {
        $customerName = Configure::read('app.htmlHelper')->getNameRespectingIsDeleted($orderDetail->customer);
        if ($this->identity !== null && $this->identity->isManufacturer() && $this->identity->getManufacturerAnonymizeCustomers()) {
            $customerName = Configure::read('app.htmlHelper')->anonymizeCustomerName($customerName, $orderDetail->id_customer);
        }
        return $customerName;
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
        $result = '';
        if (!empty($orderDetail->order_detail_unit)) {
            $unitAmount = $orderDetail->order_detail_unit->unit_amount;
            $unitName = $orderDetail->order_detail_unit->unit_name;
            $result = ($unitAmount > 1 ? Configure::read('app.numberHelper')->formatAsDecimal($unitAmount, 0) . ' ' : '') . $unitName;
        }

        if ($result == '') {
            $splitProductName = $this->getSplitProductName($orderDetail);
            if (count($splitProductName) == 2) {
                $result = $splitProductName[1];
            }
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    private function getSplitProductName(OrderDetail $orderDetail): array
    {
        return explode(' : ', $orderDetail->product_name);
    }

}