<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services;

use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure;
use App\Model\Entity\Customer;

class ChangeSellingPriceService
{

    private function getOpenOrderDetails($productId, $productAttributeId)
    {
        $orderDetailsTable = FactoryLocator::get('Table')->get('OrderDetails');
        $openOrderDetails = $orderDetailsTable->find('all',
            conditions: [
                $orderDetailsTable->aliasField('product_id') => $productId,
                $orderDetailsTable->aliasField('product_attribute_id') => $productAttributeId,
                $orderDetailsTable->aliasField('order_state NOT IN') => [ORDER_STATE_BILLED_CASH, ORDER_STATE_BILLED_CASHLESS],
                $orderDetailsTable->aliasField('shopping_price') => Customer::SELLING_PRICE,
            ],
            contain: [
                'OrderDetailUnits',
            ]);

            return $openOrderDetails;

    }

    public function changeOpenOrderDetailPricePerUnit(array $ids, float $grossPrice, string $unitName, int $unitAmount, float $quantityInUnits)
    {

        if (!Configure::read('app.changeOpenOrderDetailPriceOnProductPriceChange')) {
            return false;
        }

        $openOrderDetails = $this->getOpenOrderDetails($ids['productId'], $ids['attributeId']);
        if (empty($openOrderDetails)) {
            return false;
        }

        $orderDetailUnitsTable = FactoryLocator::get('Table')->get('OrderDetailUnits');
        foreach($openOrderDetails as $openOrderDetail) {

            // never change price if price type changed
            if (empty($openOrderDetail->order_detail_unit)) {
                continue;
            }

            $grossPriceTotal = Configure::read('app.pricePerUnitHelper')->getPrice(
                $grossPrice,
                $unitAmount,
                $openOrderDetail->order_detail_unit->product_quantity_in_units,
            );
            $patchedEntity = $orderDetailUnitsTable->patchEntity(
                $openOrderDetail->order_detail_unit,
                [
                    'price_incl_per_unit' => $grossPrice,
                    'unit_name' => $unitName,
                    'unit_amount' => $unitAmount,
                    'quantity_in_units' => $quantityInUnits,
                ],
            );
            $orderDetailUnitsTable->save($patchedEntity);
            $this->changeOrderDetailPriceDepositTax($openOrderDetail, $grossPriceTotal, $openOrderDetail->product_amount);
        }

    }

    public function changeOpenOrderDetailPrice(array $ids, float $grossPrice)
    {

        if (!Configure::read('app.changeOpenOrderDetailPriceOnProductPriceChange')) {
            return false;
        }

        $openOrderDetails = $this->getOpenOrderDetails($ids['productId'], $ids['attributeId']);
        if (empty($openOrderDetails)) {
            return false;
        }

        foreach($openOrderDetails as $openOrderDetail) {

            // never change price if price type changed
            if (!empty($openOrderDetail->order_detail_unit)) {
                continue;
            }

            $grossPriceTotal = $grossPrice * $openOrderDetail->product_amount;
            $this->changeOrderDetailPriceDepositTax($openOrderDetail, $grossPriceTotal, $openOrderDetail->product_amount);
        }

    }

    public function changeOrderDetailPriceDepositTax($orderDetail, $grossPrice, $productAmount)
    {

        $orderDetailsTable = FactoryLocator::get('Table')->get('OrderDetails');
        $productsTable = FactoryLocator::get('Table')->get('Products');

        $unitPriceExcl = $productsTable->getNetPrice($grossPrice / $productAmount, $orderDetail->tax_rate);
        $unitTaxAmount = $productsTable->getUnitTax($grossPrice, $unitPriceExcl, $productAmount);
        $totalTaxAmount = $unitTaxAmount * $productAmount;
        $totalPriceTaxExcl = $grossPrice - $totalTaxAmount;

        $orderDetail2save = [
            'total_price_tax_incl' => $grossPrice,
            'total_price_tax_excl' => $totalPriceTaxExcl,
            'product_amount' => $productAmount,
            'deposit' => $orderDetail->deposit / $orderDetail->product_amount * $productAmount,
            'tax_unit_amount' => $unitTaxAmount,
            'tax_total_amount' => $totalTaxAmount,
        ];

        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity($orderDetail, $orderDetail2save)
        );

        $newOrderDetail = $orderDetailsTable->find('all',
            conditions: [
                'OrderDetails.id_order_detail' => $orderDetail->id_order_detail,
            ],
            contain: [
                'Customers',
                'Products.StockAvailables',
                'Products.Manufacturers',
                'ProductAttributes.StockAvailables',
                'OrderDetailUnits',
            ],
        )->first();

        return $newOrderDetail;
    }

}