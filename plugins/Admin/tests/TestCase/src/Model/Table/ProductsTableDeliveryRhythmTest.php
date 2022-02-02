<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\I18n\FrozenDate;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductsTableDeliveryRhythmTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public $Product;

    public function setUp(): void
    {
        parent::setUp();
        $this->Product = $this->getTableLocator()->get('Products');
    }

    public function testWeekWithFirstDeliveryDay()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-11-02'),
                    'is_stock_product' => '0'
                ]
                ),
            'currentDay' => '2018-10-07',
            'result' => '2018-11-02'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testWeekNoFirstDeliveryDay()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0'
                ]
                ),
            'currentDay' => '2018-10-07',
            'result' => '2018-10-12'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testWeekNormal()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0'
                ]
                ),
            'currentDay' => '2018-08-14',
            'result' => '2018-08-17'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testWeekWithSendOrderListDayOneDayBeforeDefault()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'delivery_rhythm_send_order_list_weekday' => 2
                ]
                ),
            'currentDay' => '2017-08-08',
            'result' => '2017-08-18'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testWeekWithSendOrderListDayTwoDaysBeforeDefault()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'delivery_rhythm_send_order_list_weekday' => 1
                ]
                ),
            'currentDay' => '2020-04-05',
            'result' => '2020-04-10'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekWithSendOrderListDayTwoDaysBeforeDefaultAndChangedSendOrderListsDayDeltaAllowOrdersConfigOff()
    {
        $this->changeConfiguration('FCS_SEND_FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 3);
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2021-02-05'),
                    'delivery_rhythm_send_order_list_weekday' => 0,
                ]
                ),
            'currentDay' => '2021-08-01',
            'result' => '2021-08-20',
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekWithSendOrderListDayTwoDaysBeforeDefaultAndChangedSendOrderListsDayDeltaAllowOrdersConfigOn()
    {
        $this->changeConfiguration('FCS_SEND_FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 3);
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2021-02-05'),
                    'delivery_rhythm_send_order_list_weekday' => 0,
                ]
                ),
            'currentDay' => '2021-08-01',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekWithSendOrderListDayMondayAllowOrdersConfigOff()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_send_order_list_weekday' => 1,
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2019-03-01')
                ]
                ),
            'currentDay' => '2019-02-25',
            'result' => '2019-03-15',
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekWithSendOrderListDayMondayAllowOrdersConfigOn()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_send_order_list_weekday' => 1,
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2019-03-01')
                ]
                ),
            'currentDay' => '2019-02-25',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekWithSendOrderListDayThursday()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_send_order_list_weekday' => 4,
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2019-03-01')
                ]
                ),
            'currentDay' => '2019-03-08',
            'result' => '2019-03-15'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testFirstFridayWithSendOrderListDaySunday()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'delivery_rhythm_send_order_list_weekday' => 1,
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2020-10-02')
                ]
                ),
            'currentDay' => '2020-09-28',
            'result' => '2020-11-06'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekNotCurrentWeekAAllowOrderConfigOff()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-10')
                ]
                ),
            'currentDay' => '2018-08-14',
            'result' => '2018-08-24',
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekNotCurrentWeekAAllowOrderConfigOn()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-10')
                ]
                ),
            'currentDay' => '2018-08-14',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekNotCurrentWeekBAllowOrderConfigOff()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-07-06')
                ]
                ),
            'currentDay' => '2018-09-15',
            'result' => '2018-09-28',
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekNotCurrentWeekBAllowOrderConfigOn()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-07-06')
                ]
                ),
            'currentDay' => '2018-09-15',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekCurrentWeekAllowOrderConfigOff()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-03')
                ]
                ),
            'currentDay' => '2018-08-14',
            'result' => '2018-08-17',
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekCurrentWeekAllowOrderConfigOnf()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-03')
                ]
                ),
            'currentDay' => '2018-08-14',
            'result' => '2018-08-17',
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekD()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2019-03-22')
                ]
                ),
            'currentDay' => '2019-03-15',
            'result' => '2019-03-22'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function test4Week()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '4',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-03')
                ]
                ),
            'currentDay' => '2018-08-07',
            'result' => '2018-08-31'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testFirstWeekdayOfMonthA()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                ]
                ),
            'currentDay' => '2017-08-07',
            'result' => '2017-09-01'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testFirstWeekdayOfMonthB()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '1',
                ]
                ),
            'currentDay' => '2017-08-07',
            'result' => '2017-08-11'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testLastWeekdayOfMonthA()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                ]
                ),
            'currentDay' => '2018-09-13',
            'result' => '2018-09-28'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testLastWeekdayOfMonthB()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                ]
                ),
            'currentDay' => '2018-08-07',
            'result' => '2018-08-31'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testLastWeekdayOfMonthC()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2021-02-26'),
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                ]
                ),
            'currentDay' => '2021-01-20',
            'result' => '2021-02-26'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testSecondWeekdayOfMonth()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                ]
                ),
            'currentDay' => '2020-11-20',
            'result' => '2020-12-11'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testThirdWeekdayOfMonth()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '3',
                    'is_stock_product' => '0',
                ]
                ),
            'currentDay' => '2020-11-20',
            'result' => '2020-12-18'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testFourthWeekdayOfMonth()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '4',
                    'is_stock_product' => '0',
                ]
                ),
            'currentDay' => '2020-11-30',
            'result' => '2020-12-25'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    public function testIndividual()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'individual',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-03')
                ]
                ),
            'currentDay' => '2017-08-07',
            'result' => '2018-08-03'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }

    private function assertPickupDay($product, $currentDay, $expectedResult)
    {
        $result = $this->Product->calculatePickupDayRespectingDeliveryRhythm($product, $currentDay);
        $this->assertEquals($expectedResult, $result);
    }

}
