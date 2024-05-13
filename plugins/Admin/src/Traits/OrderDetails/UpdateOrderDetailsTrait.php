<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use App\Model\Table\StockAvailablesTable;

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

trait UpdateOrderDetailsTrait 
{

    protected StockAvailablesTable $StockAvailable;

    private function changeOrderDetailQuantity($oldOrderDetailUnit, $productQuantity)
    {
        $orderDetailUnit2save = [
            'product_quantity_in_units' => $productQuantity,
            'mark_as_saved' => 1,
        ];
        $patchedEntity = $this->OrderDetail->OrderDetailUnits->patchEntity($oldOrderDetailUnit, $orderDetailUnit2save);
        $this->OrderDetail->OrderDetailUnits->save($patchedEntity);
    }

    private function changeOrderDetailPurchasePrice($purchasePriceObject, $productPurchasePrice, $productAmount)
    {
        $unitPriceExcl = $this->OrderDetail->Products->getNetPrice($productPurchasePrice / $productAmount, $purchasePriceObject->tax_rate);
        $unitTaxAmount = $this->OrderDetail->Products->getUnitTax($productPurchasePrice, $unitPriceExcl, $productAmount);
        $totalTaxAmount = $unitTaxAmount * $productAmount;
        $totalPriceTaxExcl = $productPurchasePrice - $totalTaxAmount;
        $orderDetailPurchasePrice2save = [
            'total_price_tax_incl' => $productPurchasePrice,
            'total_price_tax_excl' => $totalPriceTaxExcl,
            'tax_unit_amount' => $unitTaxAmount,
            'tax_total_amount' => $totalTaxAmount,
        ];
        $this->OrderDetail->OrderDetailPurchasePrices->save(
            $this->OrderDetail->OrderDetailPurchasePrices->patchEntity($purchasePriceObject, $orderDetailPurchasePrice2save)
        );
    }

    private function increaseQuantityForProduct($orderDetail, $orderDetailAmountBeforeAmountChange)
    {

        // order detail references a product attribute
        if (!empty($orderDetail->product_attribute->stock_available)) {
            $stockAvailableObject = $orderDetail->product_attribute->stock_available;
        } else {
            $stockAvailableObject = $orderDetail->product->stock_available;
        }

        $quantity = $stockAvailableObject->quantity;

        if (!($stockAvailableObject->is_stock_product && $orderDetail->product->manufacturer->is_stock_management_enabled)) {
            if ($stockAvailableObject->always_available) {
                return false;
            }
            if (!$stockAvailableObject->always_available && $stockAvailableObject->default_quantity_after_sending_order_lists > 0) {
                return false;
            }
        }

        // do the acutal updates for increasing quantity
        $this->StockAvailable = $this->getTableLocator()->get('StockAvailables');
        $originalPrimaryKey = $this->StockAvailable->getPrimaryKey();
        $this->StockAvailable->setPrimaryKey('id_stock_available');
        $newQuantity = $quantity + $orderDetailAmountBeforeAmountChange - $orderDetail->product_amount;
        $patchedEntity = $this->StockAvailable->patchEntity(
            $stockAvailableObject,
            [
                'quantity' => $newQuantity
            ]
        );
        $this->StockAvailable->save($patchedEntity);
        $this->StockAvailable->setPrimaryKey($originalPrimaryKey);

        return $newQuantity;
    }

}
