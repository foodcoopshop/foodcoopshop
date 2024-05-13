<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use App\Model\Table\ProductsTable;
use App\Model\Table\TaxesTable;
use Cake\Datasource\Exception\RecordNotFoundException;

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

trait EditPurchasePriceTrait 
{

    protected ProductsTable $Product;
    protected TaxesTable $Tax;

    public function editPurchasePrice($orderDetailId)
    {
        $this->set('title_for_layout', __d('admin', 'Edit_purchase_price'));

        $this->Tax = $this->getTableLocator()->get('Taxes');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown(true));

        $this->setFormReferer();

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetail = $this->OrderDetail->find('all',
            conditions: [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            contain: [
                'Customers',
                'OrderDetailUnits',
                'OrderDetailPurchasePrices',
                'Products.Manufacturers',
            ]
        )->first();

        if (empty($orderDetail)) {
            throw new RecordNotFoundException('order detail not found');
        }

        if (empty($this->getRequest()->getData())) {
            $orderDetail->order_detail_purchase_price->total_price_tax_excl = round((float) $orderDetail->order_detail_purchase_price->total_price_tax_excl, 2);
            $this->set('orderDetail', $orderDetail);
            return;
        }

        $orderDetail = $this->OrderDetail->patchEntity(
            $orderDetail,
            $this->getRequest()->getData(),
            [
                'associated' => [
                    'OrderDetailPurchasePrices' => [
                        'validate' => 'edit',
                    ],
                ],
            ],
        );

        if ($orderDetail->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('orderDetail', $orderDetail);
        } else {
            $this->Product = $this->getTableLocator()->get('Products');

            $grossPrice = $this->Product->getGrossPrice(
                round((float) $orderDetail->order_detail_purchase_price->total_price_tax_excl, 2),
                $orderDetail->order_detail_purchase_price->tax_rate,
            );

            $unitPriceExcl = round((float) $orderDetail->order_detail_purchase_price->total_price_tax_excl, 2) / $orderDetail->product_amount;
            $unitTaxAmount = $this->Product->getUnitTax(
                $grossPrice,
                $unitPriceExcl,
                $orderDetail->product_amount,
            );

            $totalTaxAmount = $unitTaxAmount * $orderDetail->product_amount;

            $orderDetail->order_detail_purchase_price->tax_unit_amount = $unitTaxAmount;
            $orderDetail->order_detail_purchase_price->tax_total_amount = $totalTaxAmount;
            $orderDetail->order_detail_purchase_price->total_price_tax_incl = $grossPrice;

            $orderDetail = $this->OrderDetail->save(
                $orderDetail,
                [
                    'associated' => [
                        'OrderDetailPurchasePrices'
                    ],
                ],
            );

            $this->Flash->success(__d('admin', 'Purchase_price_has_been_saved_successfully.'));
            $this->getRequest()->getSession()->write('highlightedRowId', $orderDetail->id_order_detail);

            $this->redirect($this->getPreparedReferer());
        }

        $this->set('orderDetail', $orderDetail);
    }

}
