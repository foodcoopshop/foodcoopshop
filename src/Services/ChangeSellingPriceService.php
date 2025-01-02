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

use Cake\Core\Configure;
use App\Model\Entity\Customer;
use App\Model\Entity\OrderDetail;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;

class ChangeSellingPriceService
{

    private function getOpenOrderDetails($productId, $productAttributeId): SelectQuery
    {
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $openOrderDetails = $orderDetailsTable->find('all',
            conditions: [
                $orderDetailsTable->aliasField('product_id') => $productId,
                $orderDetailsTable->aliasField('product_attribute_id') => $productAttributeId,
                $orderDetailsTable->aliasField('order_state NOT IN') => [OrderDetail::STATE_BILLED_CASHLESS, OrderDetail::STATE_BILLED_CASH],
                $orderDetailsTable->aliasField('shopping_price') => Customer::SELLING_PRICE,
            ],
            contain: [
                'OrderDetailUnits',
            ]);

            return $openOrderDetails;

    }

    public function changeOpenOrderDetailPricePerUnit(array $ids, float $grossPrice, string $unitName, int $unitAmount, float $quantityInUnits): array
    {

        $openOrderDetails = $this->getOpenOrderDetails($ids['productId'], $ids['attributeId']);
        if ($openOrderDetails->count() == 0) {
            return [];
        }

        $orderDetailUnitsTable = TableRegistry::getTableLocator()->get('OrderDetailUnits');
        $changedOpenOrderDetails = [];
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

            $unitChangeFactor = 1;
            $productQuantityInUnits = $openOrderDetail->order_detail_unit->product_quantity_in_units;
            if ($openOrderDetail->order_detail_unit->unit_name == 'kg' && $unitName == 'g') {
                $unitChangeFactor = 1000;
            }
            if ($openOrderDetail->order_detail_unit->unit_name == 'g' && $unitName == 'kg') {
                $unitChangeFactor = 0.001;
            }

            $patchedEntity = $orderDetailUnitsTable->patchEntity(
                $openOrderDetail->order_detail_unit,
                [
                    'price_incl_per_unit' => $grossPrice,
                    'unit_name' => $unitName,
                    'unit_amount' => $unitAmount,
                    'quantity_in_units' => $quantityInUnits,
                    'product_quantity_in_units' => $productQuantityInUnits * $unitChangeFactor,
                ],
            );
            $orderDetailUnitsTable->save($patchedEntity);
            $this->changeOrderDetailPriceDepositTax($openOrderDetail, $grossPriceTotal * $unitChangeFactor, $openOrderDetail->product_amount);
            $changedOpenOrderDetails[] = $openOrderDetail;
        }

        return $changedOpenOrderDetails;

    }

    public function changeOpenOrderDetailPrice(array $ids, float $grossPrice): array
    {

        $openOrderDetails = $this->getOpenOrderDetails($ids['productId'], $ids['attributeId']);
        if ($openOrderDetails->count() == 0) {
            return [];
        }

        $changedOpenOrderDetails = [];
        foreach($openOrderDetails as $openOrderDetail) {

            // never change price if price type changed
            if (!empty($openOrderDetail->order_detail_unit)) {
                continue;
            }

            $grossPriceTotal = $grossPrice * $openOrderDetail->product_amount;
            $this->changeOrderDetailPriceDepositTax($openOrderDetail, $grossPriceTotal, $openOrderDetail->product_amount);
            $changedOpenOrderDetails[] = $openOrderDetail;
        }

        return $changedOpenOrderDetails;

    }

    public function changeOrderDetailPriceDepositTax($orderDetail, $grossPrice, $productAmount): OrderDetail
    {

        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $productsTable = TableRegistry::getTableLocator()->get('Products');

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