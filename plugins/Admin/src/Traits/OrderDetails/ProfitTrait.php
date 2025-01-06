<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;

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

trait ProfitTrait 
{

    public function profit(): void
    {

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfThisMonth();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getLastDayOfThisMonth();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $customerIds = [];
        if (! empty($this->getRequest()->getQuery('customerIds'))) {
            $customerIds = h($this->getRequest()->getQuery('customerIds'));
        }
        // click on "all members" resets the filter
        if (isset($customerIds[0]) && $customerIds[0] == '') {
            $customerIds = [];
        }
        $this->set('customerIds', $customerIds);

        $manufacturerId = '';
        if (! empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        }
        $this->set('manufacturerId', $manufacturerId);

        $productId = '';
        if (! empty($this->getRequest()->getQuery('productId'))) {
            $productId = h($this->getRequest()->getQuery('productId'));
        }
        $this->set('productId', $productId);

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $orderDetailsTable->find('all',
            contain: [
                'Customers',
                'OrderDetailPurchasePrices',
                'OrderDetailUnits',
                'Products.Manufacturers',
            ],
        );

        $orderDetails->where(function (QueryExpression $exp) use ($dateFrom, $dateTo) {
            $exp->gte('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom));
            $exp->lte('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo));
            $exp->gt('OrderDetails.id_customer', 0);
            return $exp;
        });

        if (!empty($customerIds)) {
            $orderDetails->where(['OrderDetails.id_customer IN' => $customerIds]);
        }

        if ($manufacturerId != '') {
            $orderDetails->where(['Products.id_manufacturer' => $manufacturerId]);
        }

        if ($productId != '') {
            $orderDetails->where(['OrderDetails.product_id' => $productId]);
        }

        $orderDetails = $this->paginate($orderDetails, [
            'sortableFields' => [
                'OrderDetails.product_amount',
                'OrderDetails.product_name',
                'OrderDetails.pickup_day',
                'Customers.' . Configure::read('app.customerMainNamePart'),
                'OrderDetailUnits.product_quantity_in_units',
                'OrderDetails.total_price_tax_excl',
                'OrderDetailPurchasePrices.total_price_tax_excl',
            ],
            'order' => [
                'OrderDetails.pickup_day' => 'DESC',
                'OrderDetails.created' => 'ASC',
            ],
        ])->toArray();

        $sumAmount = 0;
        $sumSellingPrice = 0;
        $sumPurchasePrice = 0;
        $sumProfit = 0;
        $i = 0;
        foreach($orderDetails as $orderDetail) {
            $orderDetails[$i]->purchase_price_ok = false;
            if (!empty($orderDetail->order_detail_purchase_price)) {
                $roundedPurchasePrice = round((float) $orderDetail->order_detail_purchase_price->total_price_tax_excl, 2);
                $roundedSellingPrice = round((float) $orderDetail->total_price_tax_excl, 2);
                $roundedProfit = round($roundedSellingPrice - $roundedPurchasePrice, 2);
                if ($roundedPurchasePrice >= 0) {
                    $orderDetails[$i]->purchase_price_ok = true;
                    $orderDetails[$i]->order_detail_purchase_price->total_price_tax_excl = $roundedPurchasePrice;
                    $orderDetails[$i]->total_price_tax_excl = $roundedSellingPrice;
                    $orderDetails[$i]->profit = $roundedProfit;
                    $sumAmount += $orderDetail->product_amount;
                    $sumProfit += $roundedProfit;
                    $sumPurchasePrice += $roundedPurchasePrice;
                    $sumSellingPrice += $roundedSellingPrice;
                }
            }
            $i++;
        }
        $this->set('orderDetails', $orderDetails);

        $purchasePriceProductsTable = $this->getTableLocator()->get('PurchasePriceProducts');
        $this->set('sums', [
            'amount' => $sumAmount,
            'purchasePrice' => $sumPurchasePrice,
            'sellingPrice' => $sumSellingPrice,
            'profit' => $sumProfit,
            'surcharge' => $purchasePriceProductsTable->calculateSurchargeBySellingPriceGross($sumSellingPrice, 0, $sumPurchasePrice, 0),
        ]);

        $this->set('title_for_layout', __d('admin', 'Profit'));

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $this->set('manufacturersForDropdown', $manufacturersTable->getForDropdown());

    }

}
