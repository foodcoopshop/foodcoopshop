<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Services\DeliveryRhythmService;
use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use Cake\Datasource\FactoryLocator;
use Cake\Database\Expression\QueryExpression;
use Cake\Routing\Router;
use App\Model\Entity\Cart;
use App\Model\Entity\OrderDetail;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class OrderDetailsTable extends AppTable
{

    use ProductCacheClearAfterSaveAndDeleteTrait;

    protected $Manufacturer;

    public function initialize(array $config): void
    {
        $this->setTable('order_detail');
        parent::initialize($config);
        $this->setPrimaryKey('id_order_detail');

        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->hasOne('OrderDetailFeedbacks', [
            'foreignKey' => 'id_order_detail'
        ]);
        $this->belongsTo('PickupDayEntities', [
            'className' => 'PickupDays', // field has same name and would clash
            'foreignKey' => [
                'id_customer'
            ]
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'type' => 'INNER'
        ]);
        $this->belongsTo('ProductAttributes', [
            'foreignKey' => 'product_attribute_id'
        ]);
        $this->hasOne('OrderDetailUnits', [
            'foreignKey' => 'id_order_detail'
        ]);
        $this->hasOne('OrderDetailPurchasePrices', [
            'foreignKey' => 'id_order_detail'
        ]);
        $this->belongsTo('CartProducts', [
            'foreignKey' => 'id_cart_product'
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationPickupDay(Validator $validator)
    {
        $validator->notEquals('pickup_day', '1970-01-01', __('The_pickup_day_is_not_valid.'));
        return $validator;
    }

    public function validationName(Validator $validator)
    {
        $validator->notEmptyString('product_name', __('Please_enter_a_name.'));
        return $validator;
    }

    public function getOrderDetailsForDeliveryNotes($manufacturerId, $dateFrom, $dateTo)
    {
        $query = $this->find('all',
        conditions: [
            'Products.id_manufacturer' => $manufacturerId,
        ],
        contain: [
            'Products.Manufacturers.AddressManufacturers',
            'OrderDetailPurchasePrices',
            'OrderDetailUnits',
        ],
        order: [
            'ProductName' => 'ASC',
        ]);
        $query->where(function (QueryExpression $exp) use ($dateFrom, $dateTo) {
            $exp->gte('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom));
            $exp->lte('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo));
            return $exp;
        });
        $query->select([
            'SumAmount' => $query->func()->sum('OrderDetails.product_amount'),
            'ProductName' => 'OrderDetails.product_name',
            'SumWeight' => $query->func()->sum('OrderDetailUnits.product_quantity_in_units'),
            'Unit' => 'OrderDetailUnits.unit_name',
            'SumPurchasePriceNet' => $query->func()->sum('ROUND(OrderDetailPurchasePrices.total_price_tax_excl, 2)'),
            'SumPurchasePriceTax' => $query->func()->sum('OrderDetailPurchasePrices.tax_total_amount'),
            'PurchasePriceTaxRate' => 'OrderDetailPurchasePrices.tax_rate',
            'SumPurchasePriceGross' => $query->func()->sum('OrderDetailPurchasePrices.total_price_tax_incl'),
        ]);
        $query->groupBy([
            'OrderDetails.product_name',
            'OrderDetailPurchasePrices.tax_rate',
            'OrderDetailUnits.unit_name',
        ]);
        return $query;
    }

    public function getLastPickupDay($customerId)
    {
        $query = $this->find('all',
        conditions: [
            'OrderDetails.id_customer' => $customerId,
        ],
        order: [
            'OrderDetails.pickup_day' => 'DESC'
        ])->first();
        return $query;
    }

    private function getLastOrFirstOrderYear(string $manufacturerId, string $sort)
    {
        $conditions = [];
        if ($manufacturerId != 'all') {
            $conditions['Products.id_manufacturer'] = $manufacturerId;
        }
        $orderDetail = $this->find('all',
        conditions: $conditions,
        order: [
            'OrderDetails.pickup_day' => $sort,
        ],
        contain: [
            'Products',
        ])->first();
        return $orderDetail;
    }

    public function getFirstOrderYear(string $manufacturerId = 'all'): int|false
    {
        $orderDetail = $this->getLastOrFirstOrderYear($manufacturerId, 'ASC');
        if (empty($orderDetail)) {
            return false;
        }
        return (int) $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Year'));
    }

    public function getLastOrderYear(string $manufacturerId = 'all'): int|false
    {
        $orderDetail = $this->getLastOrFirstOrderYear($manufacturerId, 'DESC');
        if (empty($orderDetail)) {
            return false;
        }
        return (int) $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Year'));
    }

    public function getFirstDayOfLastOrderMonth(string $manufacturerId = 'all'): string|false
    {
        $orderDetail = $this->getLastOrFirstOrderYear($manufacturerId, 'DESC');
        if (empty($orderDetail)) {
            return false;
        }
        return $orderDetail->pickup_day->i18nFormat('Y-MM') . '-01';
    }

    public function addLastMonthsCondition($query, $firstDayOfLastOrderMonth, $lastMonths)
    {
        $lastMonths--;
        $query->where(function (QueryExpression $exp) use ($firstDayOfLastOrderMonth, $lastMonths) {
            return $exp->add('OrderDetails.pickup_day >= DATE_SUB("' . $firstDayOfLastOrderMonth . '", INTERVAL ' . $lastMonths . ' MONTH)');
        });
        return $query;
    }

    public function getTotalOrderDetails(string $pickupDay, int $productId, int $attributeId)
    {

        if ($pickupDay == 'delivery-rhythm-triggered-delivery-break') {
            return null;
        }

        $query = $this->find('all', conditions: [
            'OrderDetails.pickup_day' => $pickupDay,
            'OrderDetails.product_id' => $productId,
            'OrderDetails.product_attribute_id' => $attributeId,
        ]);
        $query->select([
            'SumAmount' => $query->func()->sum('OrderDetails.product_amount'),
        ]);
        $query->groupBy([
            'OrderDetails.pickup_day',
            'OrderDetails.product_id',
            'OrderDetails.product_attribute_id',
        ]);
        $query = $query->toArray();
        if (count($query) > 0) {
            return $query[0]->SumAmount;
        }
        return null;
    }

    public function getOrderDetailsForOrderListPreview($pickupDay)
    {
        $query = $this->find('all',
        conditions: [
            'OrderDetails.pickup_day' => $pickupDay,
        ],
        contain: [
            'Products',
        ]);
        return $query;
    }

    public function getMemberFee($customerId, $year)
    {

        $productIds = Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS');

        if ($productIds != '') {

            $conditions = [
                'OrderDetails.id_customer' => $customerId,
                'OrderDetails.product_id IN' => explode(',', Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS')),
            ];
            $query = $this->find('all', conditions: $conditions);
            if ($year != '') {
                $query->where(function (QueryExpression $exp) use ($year) {
                    return $exp->eq('DATE_FORMAT(OrderDetails.pickup_day, \'%Y\')', $year);
                });
            }
            $query->select([
                'SumPriceIncl' => $query->func()->sum('OrderDetails.total_price_tax_incl'),
            ]);
            $query->groupBy('OrderDetails.id_customer');
            $result = $query->toArray();

            if (isset($result[0])) {
                return $result[0]['SumPriceIncl'];
            }

        }

        return 0;

    }

    public function getOrderDetailsForSendingOrderLists($pickupDay, $cronjobRunDay, $customerCanSelectPickupDay)
    {
        $query = $this->find('all', contain: [
            'Products.Manufacturers',
            'Products.StockAvailables',
            'ProductAttributes.StockAvailables'
        ]);
        $query->where(['OrderDetails.order_state' => OrderDetail::STATE_OPEN]);
        $query->where(['IF(Manufacturers.include_stock_products_in_order_lists = 0, (Products.is_stock_product = 0 OR Manufacturers.stock_management_enabled = 0), 1)']);

        if ($customerCanSelectPickupDay) {
            $query->where(['OrderDetails.pickup_day' => $pickupDay]);
        } else {
            $cronjobRunDayWeekday = date('w', strtotime($cronjobRunDay));
            $query->where(function ($exp, $query) use ($cronjobRunDayWeekday, $cronjobRunDay, $pickupDay) {
                return $exp->or([
                    $query->newExpr()->and([
                        'Products.delivery_rhythm_type <> "individual"',
                        $query->newExpr()->eq('Products.delivery_rhythm_send_order_list_weekday', $cronjobRunDayWeekday),
                        $query->newExpr()->eq('OrderDetails.pickup_day', $pickupDay),
                    ]),
                    $query->newExpr()->and([
                        'Products.delivery_rhythm_type = "individual"',
                        $query->newExpr()->eq('Products.delivery_rhythm_send_order_list_day', $cronjobRunDay),
                        'OrderDetails.pickup_day = Products.delivery_rhythm_first_delivery_day',
                    ]),
                ]);
            });
        }
        return $query;
    }

    public function getTaxSums($orderDetails)
    {
        $taxRates = [];
        $defaultArray = [
            'sum_price_excl' => 0,
            'sum_tax' => 0,
            'sum_price_incl' => 0,
        ];
        foreach($orderDetails as $orderDetail) {
            $taxRate = Configure::read('app.numberHelper')->formatTaxRate($orderDetail->tax_rate);
            if (!isset($taxRates[$taxRate])) {
                $taxRates[$taxRate] = $defaultArray;
            }
            $taxRates[$taxRate]['sum_price_excl'] += $orderDetail->total_price_tax_excl;
            $taxRates[$taxRate]['sum_tax'] += $orderDetail->tax_total_amount;
            $taxRates[$taxRate]['sum_price_incl'] += $orderDetail->total_price_tax_incl;
        }

        return $taxRates;
    }


    public function getDepositTax($depositGross, $amount, $taxRate)
    {
        $depositGrossPerPiece = round($depositGross / $amount, 2);
        $depositTax = $depositGrossPerPiece - round($depositGrossPerPiece / (1 + $taxRate / 100), 2);
        $depositTax = $depositTax * $amount;
        return $depositTax;
    }

    public function getDepositNet($depositGross, $amount, $taxRate)
    {
        $depositNet = $depositGross - $this->getDepositTax($depositGross, $amount, $taxRate);
        return $depositNet;
    }


    public function getLastOrderDetailsForDropdown($customerId)
    {

        $ordersToLoad = 3;

        $foundOrders = 0;
        $result = [];

        $i = 1;
        while($foundOrders < $ordersToLoad) {

            $dateFrom = strtotime('- '.$i * 7 . 'day', strtotime((new DeliveryRhythmService())->getOrderPeriodFirstDay(Configure::read('app.timeHelper')->getCurrentDay())));
            $dateTo = strtotime('- '.$i * 7 . 'day', strtotime((new DeliveryRhythmService())->getOrderPeriodLastDay(Configure::read('app.timeHelper')->getCurrentDay())));

            // stop trying to search for valid orders if year is two years ago
            // one year is not enough for usage in first weeks of january
            if (date('Y', $dateFrom) == date('Y') - 2) {
                break;
            }

            $orderDetails = $this->getOrderDetailQueryForPeriodAndCustomerId($dateFrom, $dateTo, $customerId);

            if (count($orderDetails) > 0) {
                $deliveryDay = Configure::read('app.timeHelper')->formatToDateShort(date('Y-m-d', (new DeliveryRhythmService())->getDeliveryDay($dateTo)));
                $result[$deliveryDay] = __('Pickup_day') . ' ' . $deliveryDay . ' - ' . __('{0,plural,=1{1_product} other{#_products}}', [count($orderDetails)]);
                $foundOrders++;
            }

            $i++;

        }

        return $result;

    }

    private function getFutureOrdersConditions($customerId)
    {
        return [
            'OrderDetails.id_customer' => $customerId,
            'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\') > DATE_FORMAT(NOW(), \'%Y-%m-%d\')'
        ];
    }

    public function getFutureOrdersByCustomerId($customerId)
    {
        $futureOrders = $this->find('all',
        conditions: $this->getFutureOrdersConditions($customerId),
        order: [
            'OrderDetails.product_id' => 'ASC',
            'OrderDetails.pickup_day' => 'ASC',
        ]);
        return $futureOrders;
    }

    public function getGroupedFutureOrdersByCustomerId($customerId)
    {
        $query = $this->find('all',
        fields: ['OrderDetails.pickup_day'],
        conditions: $this->getFutureOrdersConditions($customerId),
        order: [
            'OrderDetails.pickup_day' => 'ASC'
        ]);
        $query->select(
            ['orderDetailsCount' => $query->func()->count('OrderDetails.pickup_day')]
        );
        $query->groupBy('OrderDetails.pickup_day');
        return $query->toArray();
    }

    public function updateOrderState($dateFrom, $dateTo, $oldOrderStates, $newOrderState, $manufacturerId, $orderDetailIds = [])
    {

        // update with condition on association does not work with ->update or ->updateAll
        $orderDetails = $this->find('all',
        conditions: [
            'Products.id_manufacturer' => $manufacturerId,
        ],
        contain: [
            'Products',
        ])
        ->where(function (QueryExpression $exp) use ($oldOrderStates) {
            return $exp->in('OrderDetails.order_state', $oldOrderStates);
        });;

        if (!empty($orderDetailIds)) {
            $orderDetails->where(function (QueryExpression $exp) use ($orderDetailIds) {
                return $exp->in('OrderDetails.id_order_detail', $orderDetailIds);
            });
        } else {
            $orderDetails->where(function (QueryExpression $exp) use ($dateFrom, $dateTo) {
                $exp->gte('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom));
                $exp->lte('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo));
                return $exp;
            });
        }

        foreach($orderDetails as $orderDetail) {
            $orderDetail->order_state = $newOrderState;
            $this->save($orderDetail);
        }

    }

    public function getOrderDetailQueryForPeriodAndCustomerId($dateFrom, $dateTo, $customerId)
    {
        $cartsAssociation = $this->getAssociation('CartProducts')->getAssociation('Carts');
        $cartsAssociation->setJoinType('INNER');
        $cartsAssociation->setConditions([
            'Carts.cart_type' => Cart::TYPE_WEEKLY_RHYTHM,
            'Carts.status' => APP_OFF,
        ]);

        $orderDetails = $this->find('all',
        conditions: [
            'OrderDetails.id_customer' => $customerId,
        ],
        fields: [
            'OrderDetails.id_order_detail',
            'OrderDetails.product_id',
            'OrderDetails.product_attribute_id',
            'OrderDetails.product_amount',
        ],
        order: [
            'OrderDetails.created' => 'DESC',
        ],
        contain: [
            'CartProducts.Carts',
            'Products.Manufacturers',
        ])->where(function (QueryExpression $exp) use ($dateFrom, $dateTo) {
            $exp->gte('DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate(date('Y-m-d', $dateFrom)));
            $exp->lte('DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate(date('Y-m-d', $dateTo)));
            return $exp;
        })->toArray();

        return $orderDetails;
    }

    public function deleteOrderDetail($orderDetail)
    {
        $this->delete($orderDetail);

        if (!empty($orderDetail->order_detail_unit)) {
            $orderDetailUnitsTable = FactoryLocator::get('Table')->get('OrderDetailUnits');
            $orderDetailUnitsTable->delete($orderDetail->order_detail_unit);
        }

        if (!empty($orderDetail->order_detail_purchase_price)) {
            $orderDetailPurchasePricesTable = FactoryLocator::get('Table')->get('OrderDetailPurchasePrices');
            $orderDetailPurchasePricesTable->delete($orderDetail->order_detail_purchase_price);
        }

    }

    /**
     * @param int $manufacturerId (false, int)
     * @param boolean $groupBy (false, 'month', 'year')
     * @return array
     */
    public function getDepositSum($manufacturerId, $groupBy)
    {

        $params = [
            'depositForManufacturersStartDate' => Configure::read('app.depositForManufacturersStartDate'),
        ];

        $sql =  'SELECT SUM(od.deposit) as sumDepositDelivered ';
        $sql .= match($groupBy) {
            'month' => ', DATE_FORMAT(od.pickup_day, \'%Y-%c\') as monthAndYear ',
            'year'  => ', DATE_FORMAT(od.pickup_day, \'%Y\') as Year ',
            default => '',
        };

        $sql .= 'FROM '.$this->tablePrefix.'order_detail od ';
        $sql .= 'LEFT JOIN '.$this->tablePrefix.'product p ON p.id_product = od.product_id ';
        $sql .= 'WHERE 1 ';

        if ($manufacturerId > 0) {
            $sql .= 'AND p.id_manufacturer = :manufacturerId ';
            $params['manufacturerId'] = $manufacturerId;
        }

        $sql .= 'AND DATE_FORMAT(od.pickup_day, \'%Y-%m-%d\') >= :depositForManufacturersStartDate ';

        $sql .= match($groupBy) {
            'month' => 'GROUP BY monthAndYear ORDER BY monthAndYear DESC;',
            'year'  => 'GROUP BY Year ORDER BY Year DESC;',
            default => 'ORDER BY od.pickup_day DESC;',
        };

        $statement = $this->getConnection()->getDriver()->prepare($sql);
        $statement->execute($params);
        $orderDetails = $statement->fetchAll('assoc');

        return $orderDetails;
    }

    public function getOpenOrderDetailSum(int $manufacturerId, $dateFrom)
    {
        $productsTable = FactoryLocator::get('Table')->get('Products');
        $query = $this->find('all',
        conditions: [
            $productsTable->aliasField('id_manufacturer') => $manufacturerId,
            $this->aliasField('order_state NOT IN') => [OrderDetail::STATE_BILLED_CASHLESS, OrderDetail::STATE_BILLED_CASH],
            $this->aliasField('pickup_day') => Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom),
        ],
        contain: ['Products']);
        $query->select([
            'sumOrderDetail' => $query->func()->sum($this->aliasField('total_price_tax_incl')),
        ])
        ->groupBy($productsTable->aliasField('id_manufacturer'));

        $orderDetails = $query->toArray();
        if (isset($orderDetails[0])) {
            return $orderDetails[0]['sumOrderDetail'];
        } else {
            return 0;
        }
    }

    private function prepareSumProduct($customerId)
    {
        $query = $this->find('all', conditions: [
            'OrderDetails.id_customer' => $customerId,
        ])
        ->where(function (QueryExpression $exp) {
            return $exp->in('OrderDetails.order_state', Configure::read('app.htmlHelper')->getOrderStatesCashless());
        });
        return $query;
    }

    public function getDifferentPickupDayCountByCustomerId($customerId)
    {
        $query = $this->find('all', conditions: [
            'OrderDetails.id_customer' => $customerId,
        ]);
        $query->select([
            'different_pickup_day_count' => $query->func()->count('DISTINCT(OrderDetails.pickup_day)'),
        ]);
        return $query->toArray()[0]['different_pickup_day_count'];
    }

    public function getCountByCustomerId($customerId)
    {
        $query = $this->find('all', conditions: [
            'OrderDetails.id_customer' => $customerId,
        ]);
        return $query->count();
    }

    public function getMonthlySumProductByCustomer($customerId)
    {
        $query = $this->prepareSumProduct($customerId);
        $query->groupBy('MonthAndYear');
        $query->select([
            'SumTotalPaid' => $query->func()->sum('OrderDetails.total_price_tax_incl'),
            'SumDeposit' => $query->func()->sum('OrderDetails.deposit'),
            'MonthAndYear' => 'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%c\')'
        ]);
        return $query->toArray();
    }

    public function getMonthlySumProductByManufacturer($manufacturerId, $year)
    {
        $conditions = [];
        if ($manufacturerId != 'all') {
            $conditions['Products.id_manufacturer'] = $manufacturerId;
        } else {
            // do not show any non-associated products that might be found in database
            $conditions[] = 'Products.id_manufacturer > 0';
        }

        $query = $this->find('all',
        conditions: $conditions,
        contain: [
            'Products',
        ]);

        if ($year != '') {
            $query->where(function (QueryExpression $exp) use ($year) {
                return $exp->eq('DATE_FORMAT(OrderDetails.pickup_day, \'%Y\')', $year);
            });
        }

        $query->groupBy('MonthAndYear');
        $query->select([
            'SumTotalPaid' => $query->func()->sum('OrderDetails.total_price_tax_incl'),
            'MonthAndYear' => 'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%c\')',
        ]);
        return $query;
    }

    public function getSumProduct($customerId)
    {
        $query = $this->prepareSumProduct($customerId);
        $query->select(
            ['SumTotalPaid' => $query->func()->sum('OrderDetails.total_price_tax_incl')]
        );
        return $query->toArray()[0]['SumTotalPaid'];
    }

    public function getSumDeposit($customerId)
    {
        $query = $this->find('all', conditions: [
            'OrderDetails.id_customer' => $customerId,
        ]);
        $query = $this->setOrderStateCondition($query, Configure::read('app.htmlHelper')->getOrderStatesCashless());
        $query->where(function (QueryExpression $exp) {
            return $exp->gte('DATE_FORMAT(OrderDetails.created, \'%Y-%m-%d\')', Configure::read('app.depositPaymentCashlessStartDate'));
        });
        $query->select(
            ['SumTotalDeposit' => $query->func()->sum('OrderDetails.deposit')]
        );
        return $query->toArray()[0]['SumTotalDeposit'];
    }

    public function setOrderStateCondition($query, $orderStates)
    {
        if ($orderStates == '' || empty($orderStates) || empty($orderStates[0])) {
            return $query;
        }
        if (!is_array($orderStates)) {
            $orderStates = [$orderStates];
        }
        $query->where(function (QueryExpression $exp) use ($orderStates) {
            return $exp->in('OrderDetails.order_state', $orderStates);
        });
        return $query;
    }

    public function getVariableMemberFeeReducedPrice($price, $variableMemberFee)
    {
        return $price * (100 - $variableMemberFee) / 100;
    }

    public function prepareOrderDetailsGroupedByProduct($orderDetails)
    {
        $preparedOrderDetails = [];
        foreach ($orderDetails as $orderDetail) {
            $preparedOrderDetail = [];
            if (!empty($orderDetail->order_detail_unit)) {
                $preparedOrderDetail['unit_name'] = $orderDetail->order_detail_unit->unit_name;
            }
            $preparedOrderDetail['sum_price'] = $orderDetail->sum_price;
            $preparedOrderDetail['sum_amount'] = $orderDetail->sum_amount;
            $preparedOrderDetail['sum_deposit'] = $orderDetail->sum_deposit;
            $preparedOrderDetail['sum_units'] = $orderDetail->sum_units;
            $preparedOrderDetail['product_id'] = $orderDetail->product_id;
            $preparedOrderDetail['name'] = $orderDetail->product->name;
            $preparedOrderDetail['manufacturer_id'] = $orderDetail->product->id_manufacturer;
            $preparedOrderDetail['manufacturer_name'] = $orderDetail->product->manufacturer->name;
            $preparedOrderDetails[] = $preparedOrderDetail;
        }

        return $preparedOrderDetails;
    }

    public function prepareOrderDetailsGroupedByManufacturer($orderDetails)
    {
        $preparedOrderDetails = [];
        $this->Manufacturer = FactoryLocator::get('Table')->get('Manufacturers');
        foreach ($orderDetails as $orderDetail) {
            $preparedOrderDetail = [];
            $preparedOrderDetail['sum_price'] = $orderDetail->sum_price;
            $preparedOrderDetail['sum_amount'] = $orderDetail->sum_amount;
            $preparedOrderDetail['sum_deposit'] = $orderDetail->sum_deposit;
            $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($orderDetail->product->manufacturer->variable_member_fee);
            $preparedOrderDetail['variable_member_fee'] = $variableMemberFee;
            $preparedOrderDetail['manufacturer_id'] = $orderDetail->product->id_manufacturer;
            $preparedOrderDetail['name'] = $orderDetail->product->manufacturer->name;
            $preparedOrderDetails[] = $preparedOrderDetail;
        }

        foreach($preparedOrderDetails as &$pod) {
            $pod['reduced_price'] = $this->getVariableMemberFeeReducedPrice($pod['sum_price'], $pod['variable_member_fee']);
        }

        return $preparedOrderDetails;
    }

    /**
     * $param $orderDetails is already grouped!
     */
    public function prepareOrderDetailsGroupedByCustomer($orderDetails): array
    {
        $preparedOrderDetails = [];
        foreach ($orderDetails as $orderDetail) {
            $preparedOrderDetail = [];
            $preparedOrderDetail['sum_price'] = $orderDetail->sum_price;
            $preparedOrderDetail['sum_amount'] = $orderDetail->sum_amount;
            $preparedOrderDetail['sum_deposit'] = $orderDetail->sum_deposit;
            $preparedOrderDetail['order_detail_count'] = $orderDetail->order_detail_count;
            $preparedOrderDetail['customer_id'] = $orderDetail->id_customer;
            $preparedOrderDetail['name'] = Configure::read('app.htmlHelper')->getNameRespectingIsDeleted($orderDetail->customer);
            $preparedOrderDetail['email'] = '';
            if ($orderDetail->customer) {
                $preparedOrderDetail['email'] = $orderDetail->customer->email;
            }
            $productsPickedUp = false;
            if (!empty($orderDetail->pickup_day_entity)) {
                $preparedOrderDetail['comment'] = $orderDetail->pickup_day_entity->comment;
                $preparedOrderDetail['products_picked_up_tmp'] = $orderDetail->pickup_day_entity->products_picked_up;
            }
            if (isset($preparedOrderDetail['products_picked_up_tmp']) && $preparedOrderDetail['products_picked_up_tmp']) {
                $productsPickedUp = true;
                $preparedOrderDetail['row_class'] = ['selected'];
            }
            $preparedOrderDetail['products_picked_up'] = $productsPickedUp;
            unset($preparedOrderDetail['products_picked_up_tmp']);
            $preparedOrderDetails[] = $preparedOrderDetail;
        }

        foreach($preparedOrderDetails as &$orderDetail) {
            $orderDetail['different_pickup_day_count'] = $this->getDifferentPickupDayCountByCustomerId($orderDetail['customer_id']);
        }
        return $preparedOrderDetails;
    }

    public function onInvoiceCancellation($orderDetails)
    {
        foreach($orderDetails as $orderDetail) {
            $orderDetail->order_state = OrderDetail::STATE_OPEN;
            $orderDetail->id_invoice = null;
            $this->save($orderDetail);
        }
    }

    public function updateOrderDetails($data, $invoiceId)
    {
        foreach($data->active_order_details as $orderDetail) {
            // important to get a fresh order detail entity as price fields could be changed for cancellation invoices
            $orderDetail = $this->get($orderDetail->id_order_detail);
            $orderDetail->order_state = Configure::read('app.htmlHelper')->getOrderStateBilled();
            $orderDetail->id_invoice = $invoiceId;
            $this->save($orderDetail);
        }
    }

    public function getOrderDetailParams($manufacturerId, $productId, $customerId, $pickupDay, $orderDetailId, $deposit)
    {
        $conditions = [];

        $identity = Router::getRequest()->getAttribute('identity');

        $exp = new QueryExpression();
        if (count($pickupDay) == 2) {
            $conditions[] = $exp->gte('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[0]));
            $conditions[] = $exp->lte('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[1]));
        } else {
            $conditions[] = $exp->eq('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay[0]));
        }

        if ($productId != '') {
            $conditions['OrderDetails.product_id'] = $productId;
        }

        if ($orderDetailId != '') {
            $conditions['OrderDetails.id_order_detail'] = $orderDetailId;
        }

        if ($deposit != '') {
            $conditions[] = 'OrderDetails.deposit > 0';
        }

        $contain = [
            'Customers',
            'Products.Manufacturers.AddressManufacturers',
            'OrderDetailUnits',
            'OrderDetailFeedbacks',
        ];

        if (Configure::read('appDb.FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS')) {
            $contain[] = 'Products.StorageLocations';
        }

        if ($customerId != '') {
            $conditions['OrderDetails.id_customer'] = $customerId;
        }

        if ($manufacturerId != '') {
            $conditions['Products.id_manufacturer'] = $manufacturerId;
        }

        // override params that manufacturer is not allowed to change
        if ($identity !== null && $identity->isManufacturer()) {
            $conditions['Products.id_manufacturer'] = $identity->getManufacturerId();
            if ($customerId =! '') {
                unset($conditions['OrderDetails.id_customer']);
            }
        }

        // customers are only allowed to see their own data
        if ($identity !== null && $identity->isCustomer()) {
            $conditions['OrderDetails.id_customer'] = $identity->getId();
        }

        $odParams = [
            'conditions' => $conditions,
            'contain' => $contain
        ];

        return $odParams;
    }

}
