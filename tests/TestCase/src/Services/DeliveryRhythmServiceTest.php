<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Services\DeliveryRhythmService;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\DeliveryRhythmConfigsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\View\Helper\MyTimeHelper;
use Cake\View\View;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\I18n\Date;

class DeliveryRhythmServiceTest extends AppCakeTestCase
{

    protected MyTimeHelper $MyTimeHelper;

    use DeliveryRhythmConfigsTrait;
    use IntegrationTestTrait;
    use LoginTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->MyTimeHelper = new MyTimeHelper(new View());
    }

    public function testGetOrderPeriodFirstDayByDeliveryDaySaturdayThursday()
    {
        $this->prepareSaturdayThursdayConfig();
        $this->assertGetOrderPeriodFirstDayByDeliveryDay(strtotime('12.01.2023'), '31.12.2022');
    }

    public function testGetOrderPeriodLastDayByDeliveryDaySaturdayThursday()
    {
        $this->prepareSaturdayThursdayConfig();
        $this->assertGetOrderPeriodLastDayByDeliveryDay(strtotime('12.01.2023'), '06.01.2023');
    }

    public function testGetOrderPeriodFirstDayByDeliveryDayWednesdayFriday()
    {
        $this->prepareWednesdayFridayConfig();
        $this->assertGetOrderPeriodFirstDayByDeliveryDay(strtotime('12.01.2023'), '04.01.2023');
    }

    public function testGetOrderPeriodLastDayByDeliveryDayWednesdayFriday()
    {
        $this->prepareWednesdayFridayConfig();
        $this->assertGetOrderPeriodLastDayByDeliveryDay(strtotime('12.01.2023'), '10.01.2023');
    }

    public function testGetDeliveryDayTuesdayFriday()
    {
        $this->prepareTuesdayFridayConfig();
        $this->assertGetDeliveryDay('25.07.2018', '03.08.2018');
    }

    public function testGetDeliveryDaySaturdayThursday()
    {
        $this->prepareSaturdayThursdayConfig();
        $this->assertGetDeliveryDay('24.08.2022', '01.09.2022'); // wednesday
        $this->assertGetDeliveryDay('25.08.2022', '01.09.2022'); // thursday
        $this->assertGetDeliveryDay('26.08.2022', '01.09.2022'); // friday
        $this->assertGetDeliveryDay('27.08.2022', '08.09.2022'); // saturday
        $this->assertGetDeliveryDay('28.08.2022', '08.09.2022'); // sunday
        $this->assertGetDeliveryDay('29.08.2022', '08.09.2022'); // monday
        $this->assertGetDeliveryDay('30.08.2022', '08.09.2022'); // tuesday
        $this->assertGetDeliveryDay('31.08.2022', '08.09.2022'); // wednesday
    }

    public function testGetOrderPeriodFirstDayThursdayFriday()
    {
        $this->prepareThursdayFridayConfig();
        $this->assertGetOrderPeriodFirstDay('27.11.2017', '23.11.2017'); // monday
        $this->assertGetOrderPeriodFirstDay('28.11.2017', '23.11.2017'); // tuesday
        $this->assertGetOrderPeriodFirstDay('29.11.2017', '23.11.2017'); // wednesday
        $this->assertGetOrderPeriodFirstDay('30.11.2017', '23.11.2017'); // thursday
        $this->assertGetOrderPeriodFirstDay('01.12.2017', '23.11.2017'); // friday
        $this->assertGetOrderPeriodFirstDay('02.12.2017', '30.11.2017'); // saturday
        $this->assertGetOrderPeriodFirstDay('03.12.2017', '30.11.2017'); // sunday
        $this->assertGetOrderPeriodFirstDay('04.12.2017', '30.11.2017'); // monday
        $this->assertGetOrderPeriodFirstDay('05.12.2017', '30.11.2017'); // tuesday
    }

    public function testGetOrderPeriodFirstDayWednesdayFriday()
    {
        $this->prepareWednesdayFridayConfig();
        $this->assertGetOrderPeriodFirstDay('27.11.2017', '22.11.2017'); // monday
        $this->assertGetOrderPeriodFirstDay('28.11.2017', '22.11.2017'); // tuesday
        $this->assertGetOrderPeriodFirstDay('29.11.2017', '22.11.2017'); // wednesday
        $this->assertGetOrderPeriodFirstDay('30.11.2017', '22.11.2017'); // thursday
        $this->assertGetOrderPeriodFirstDay('01.12.2017', '22.11.2017'); // friday
        $this->assertGetOrderPeriodFirstDay('02.12.2017', '29.11.2017'); // saturday
        $this->assertGetOrderPeriodFirstDay('03.12.2017', '29.11.2017'); // sunday
        $this->assertGetOrderPeriodFirstDay('04.12.2017', '29.11.2017'); // monday
        $this->assertGetOrderPeriodFirstDay('05.12.2017', '29.11.2017'); // tuesday
    }

    public function testGetOrderPeriodFirstDayTuesdayFriday()
    {
        $this->prepareTuesdayFridayConfig();
        $this->assertGetOrderPeriodFirstDay('27.11.2017', '21.11.2017'); // monday
        $this->assertGetOrderPeriodFirstDay('28.11.2017', '21.11.2017'); // tuesday
        $this->assertGetOrderPeriodFirstDay('29.11.2017', '21.11.2017'); // wednesday
        $this->assertGetOrderPeriodFirstDay('30.11.2017', '21.11.2017'); // thursday
        $this->assertGetOrderPeriodFirstDay('01.12.2017', '21.11.2017'); // friday
        $this->assertGetOrderPeriodFirstDay('02.12.2017', '28.11.2017'); // saturday
        $this->assertGetOrderPeriodFirstDay('03.12.2017', '28.11.2017'); // sunday
        $this->assertGetOrderPeriodFirstDay('04.12.2017', '28.11.2017'); // monday
        $this->assertGetOrderPeriodFirstDay('05.12.2017', '28.11.2017'); // tuesday
    }

    public function testGetOrderPeriodFirstDayMondayTuesday()
    {
        $this->prepareMondayTuesdayConfig();
        $this->assertGetOrderPeriodFirstDay('27.11.2017', '20.11.2017'); // monday
        $this->assertGetOrderPeriodFirstDay('28.11.2017', '20.11.2017'); // tuesday
        $this->assertGetOrderPeriodFirstDay('29.11.2017', '27.11.2017'); // wednesday
        $this->assertGetOrderPeriodFirstDay('30.11.2017', '27.11.2017'); // thursday
        $this->assertGetOrderPeriodFirstDay('01.12.2017', '27.11.2017'); // friday
        $this->assertGetOrderPeriodFirstDay('02.12.2017', '27.11.2017'); // saturday
        $this->assertGetOrderPeriodFirstDay('03.12.2017', '27.11.2017'); // sunday
        $this->assertGetOrderPeriodFirstDay('04.12.2017', '27.11.2017'); // monday
        $this->assertGetOrderPeriodFirstDay('05.12.2017', '27.11.2017'); // tuesday
    }

    public function testGetOrderPeriodFirstDaySaturdayThursday()
    {
        $this->prepareSaturdayThursdayConfig();
        $this->assertGetOrderPeriodFirstDay('22.08.2022', '20.08.2022'); // monday
        $this->assertGetOrderPeriodFirstDay('23.08.2022', '20.08.2022'); // tuesday
        $this->assertGetOrderPeriodFirstDay('24.08.2022', '20.08.2022'); // wednesday
        $this->assertGetOrderPeriodFirstDay('25.08.2022', '20.08.2022'); // thursday
        $this->assertGetOrderPeriodFirstDay('26.08.2022', '20.08.2022'); // friday
        $this->assertGetOrderPeriodFirstDay('27.08.2022', '27.08.2022'); // saturday
        $this->assertGetOrderPeriodFirstDay('28.08.2022', '27.08.2022'); // sunday
        $this->assertGetOrderPeriodFirstDay('29.08.2022', '27.08.2022'); // monday
        $this->assertGetOrderPeriodFirstDay('30.08.2022', '27.08.2022'); // tuesday
    }

    public function testGetOrderPeriodLastDayThursdayFriday()
    {
        $this->prepareThursdayFridayConfig();
        $this->assertGetOrderPeriodLastDay('27.11.2017', '29.11.2017'); // monday
        $this->assertGetOrderPeriodLastDay('28.11.2017', '29.11.2017'); // tuesday
        $this->assertGetOrderPeriodLastDay('29.11.2017', '29.11.2017'); // wednesday
        $this->assertGetOrderPeriodLastDay('30.11.2017', '29.11.2017'); // thursday
        $this->assertGetOrderPeriodLastDay('01.12.2017', '29.11.2017'); // friday
        $this->assertGetOrderPeriodLastDay('02.12.2017', '06.12.2017'); // saturday
        $this->assertGetOrderPeriodLastDay('03.12.2017', '06.12.2017'); // sunday
        $this->assertGetOrderPeriodLastDay('04.12.2017', '06.12.2017'); // monday
        $this->assertGetOrderPeriodLastDay('05.12.2017', '06.12.2017'); // tuesday
    }

    public function testGetOrderPeriodLastDayWednesdayFriday()
    {
        $this->prepareWednesdayFridayConfig();
        $this->assertGetOrderPeriodLastDay('27.11.2017', '28.11.2017'); // monday
        $this->assertGetOrderPeriodLastDay('28.11.2017', '28.11.2017'); // tuesday
        $this->assertGetOrderPeriodLastDay('29.11.2017', '28.11.2017'); // wednesday
        $this->assertGetOrderPeriodLastDay('30.11.2017', '28.11.2017'); // thursday
        $this->assertGetOrderPeriodLastDay('01.12.2017', '28.11.2017'); // friday
        $this->assertGetOrderPeriodLastDay('02.12.2017', '05.12.2017'); // saturday
        $this->assertGetOrderPeriodLastDay('03.12.2017', '05.12.2017'); // sunday
        $this->assertGetOrderPeriodLastDay('04.12.2017', '05.12.2017'); // monday
        $this->assertGetOrderPeriodLastDay('05.12.2017', '05.12.2017'); // tuesday
    }

    public function testGetOrderPeriodLastDayTuesdayFriday()
    {
        $this->prepareTuesdayFridayConfig();
        $this->assertGetOrderPeriodLastDay('27.11.2017', '27.11.2017'); // monday
        $this->assertGetOrderPeriodLastDay('28.11.2017', '27.11.2017'); // tuesday
        $this->assertGetOrderPeriodLastDay('29.11.2017', '27.11.2017'); // wednesday
        $this->assertGetOrderPeriodLastDay('30.11.2017', '27.11.2017'); // thursday
        $this->assertGetOrderPeriodLastDay('01.12.2017', '27.11.2017'); // friday
        $this->assertGetOrderPeriodLastDay('02.12.2017', '04.12.2017'); // saturday
        $this->assertGetOrderPeriodLastDay('03.12.2017', '04.12.2017'); // sunday
        $this->assertGetOrderPeriodLastDay('04.12.2017', '04.12.2017'); // monday
        $this->assertGetOrderPeriodLastDay('05.12.2017', '04.12.2017'); // tuesday
    }

    public function testGetOrderPeriodLastDayMondayTuesday()
    {
        $this->prepareMondayTuesdayConfig();
        $this->assertGetOrderPeriodLastDay('27.11.2017', '26.11.2017'); // monday
        $this->assertGetOrderPeriodLastDay('28.11.2017', '26.11.2017'); // tuesday
        $this->assertGetOrderPeriodLastDay('29.11.2017', '03.12.2017'); // wednesday
        $this->assertGetOrderPeriodLastDay('30.11.2017', '03.12.2017'); // thursday
        $this->assertGetOrderPeriodLastDay('01.12.2017', '03.12.2017'); // friday
        $this->assertGetOrderPeriodLastDay('02.12.2017', '03.12.2017'); // saturday
        $this->assertGetOrderPeriodLastDay('03.12.2017', '03.12.2017'); // sunday
        $this->assertGetOrderPeriodLastDay('04.12.2017', '03.12.2017'); // monday
        $this->assertGetOrderPeriodLastDay('05.12.2017', '03.12.2017'); // tuesday
        $this->assertGetOrderPeriodLastDay('06.12.2017', '10.12.2017'); // wednesday
    }

    public function testGetOrderPeriodLastDaySaturdayThursday()
    {
        $this->prepareSaturdayThursdayConfig();
        $this->assertGetOrderPeriodLastDay('29.08.2022', '02.09.2022'); // monday
        $this->assertGetOrderPeriodLastDay('30.08.2022', '02.09.2022'); // tuesday
        $this->assertGetOrderPeriodLastDay('31.08.2022', '02.09.2022'); // wednesday
        $this->assertGetOrderPeriodLastDay('01.09.2022', '02.09.2022'); // thursday
        $this->assertGetOrderPeriodLastDay('02.09.2022', '02.09.2022'); // friday
        $this->assertGetOrderPeriodLastDay('03.09.2022', '09.09.2022'); // saturday
        $this->assertGetOrderPeriodLastDay('04.09.2022', '09.09.2022'); // sunday
        $this->assertGetOrderPeriodLastDay('05.09.2022', '09.09.2022'); // monday
        $this->assertGetOrderPeriodLastDay('06.09.2022', '09.09.2022'); // tuesday
    }

    public function testGetFormattedNextDeliveryDayThursdayFriday()
    {
        $this->prepareThursdayFridayConfig();
        $this->assertGetFormattedNextDeliveryDay('08.10.2018', '12.10.2018'); // monday
        $this->assertGetFormattedNextDeliveryDay('09.10.2018', '12.10.2018'); // tuesday
        $this->assertGetFormattedNextDeliveryDay('10.10.2018', '12.10.2018'); // wednesday
        $this->assertGetFormattedNextDeliveryDay('11.10.2018', '12.10.2018'); // thursday
        $this->assertGetFormattedNextDeliveryDay('12.10.2018', '12.10.2018'); // friday
        $this->assertGetFormattedNextDeliveryDay('13.10.2018', '19.10.2018'); // saturday
        $this->assertGetFormattedNextDeliveryDay('14.10.2018', '19.10.2018'); // sunday
        $this->assertGetFormattedNextDeliveryDay('15.10.2018', '19.10.2018'); // monday
        $this->assertGetFormattedNextDeliveryDay('16.10.2018', '19.10.2018'); // tuesday
    }

    public function testGetFormattedNextDeliveryDayWednesdayFriday()
    {
        $this->prepareWednesdayFridayConfig();
        $this->assertGetFormattedNextDeliveryDay('08.10.2018', '12.10.2018'); // monday
        $this->assertGetFormattedNextDeliveryDay('09.10.2018', '12.10.2018'); // tuesday
        $this->assertGetFormattedNextDeliveryDay('10.10.2018', '12.10.2018'); // wednesday
        $this->assertGetFormattedNextDeliveryDay('11.10.2018', '12.10.2018'); // thursday
        $this->assertGetFormattedNextDeliveryDay('12.10.2018', '12.10.2018'); // friday
        $this->assertGetFormattedNextDeliveryDay('13.10.2018', '19.10.2018'); // saturday
        $this->assertGetFormattedNextDeliveryDay('14.10.2018', '19.10.2018'); // sunday
        $this->assertGetFormattedNextDeliveryDay('15.10.2018', '19.10.2018'); // monday
        $this->assertGetFormattedNextDeliveryDay('16.10.2018', '19.10.2018'); // tuesday
    }

    public function testGetFormattedNextDeliveryDaySaturdayThursday()
    {
        $this->prepareSaturdayThursdayConfig();
        $this->assertGetFormattedNextDeliveryDay('22.08.2022', '25.08.2022'); // monday
        $this->assertGetFormattedNextDeliveryDay('23.08.2022', '25.08.2022'); // tuesday
        $this->assertGetFormattedNextDeliveryDay('24.08.2022', '25.08.2022'); // wednesday
        $this->assertGetFormattedNextDeliveryDay('25.08.2022', '25.08.2022'); // thursday
        $this->assertGetFormattedNextDeliveryDay('26.08.2022', '25.08.2022'); // friday
        $this->assertGetFormattedNextDeliveryDay('27.08.2022', '01.09.2022'); // saturday
        $this->assertGetFormattedNextDeliveryDay('28.08.2022', '01.09.2022'); // sunday
        $this->assertGetFormattedNextDeliveryDay('29.08.2022', '01.09.2022'); // monday
        $this->assertGetFormattedNextDeliveryDay('30.08.2022', '01.09.2022'); // tuesday
    }

    public function testGetLastOrderDayWeeklySendOrderListsDayNormal()
    {
        $product = [
            'next_delivery_day' => '2020-12-04',
            'delivery_rhythm_type' => 'week',
            'delivery_rhythm_send_order_list_weekday' => 3,
            'delivery_rhythm_order_possible_until' => null,
        ];
        $this->assertGetLastOrderDay($product, '2020-12-01');
    }

    public function testGetLastOrderDayWeeklySendOrderListsDayMonday()
    {
        $product = [
            'next_delivery_day' => '2020-12-04',
            'delivery_rhythm_type' => 'week',
            'delivery_rhythm_send_order_list_weekday' => 2,
            'delivery_rhythm_order_possible_until' => null,
        ];
        $this->assertGetLastOrderDay($product, '2020-11-30');
    }

    public function testGetLastOrderDayMonthlySendOrderListsDayNormal()
    {
        $product = [
            'next_delivery_day' => '2020-12-25',
            'delivery_rhythm_type' => 'month',
            'delivery_rhythm_send_order_list_weekday' => 3,
            'delivery_rhythm_order_possible_until' => null,
        ];
        $this->assertGetLastOrderDay($product, '2020-12-22');
    }

    public function testGetLastOrderDayMonthlySendOrderListsDaySunday()
    {
        $product = [
            'next_delivery_day' => '2020-12-25',
            'delivery_rhythm_type' => 'month',
            'delivery_rhythm_send_order_list_weekday' => 1,
            'delivery_rhythm_order_possible_until' => null,
        ];
        $this->assertGetLastOrderDay($product, '2020-12-20');
    }

    public function testGetLastOrderDayIndividual()
    {
        $product = [
            'next_delivery_day' => '2020-12-25',
            'delivery_rhythm_type' => 'individual',
            'delivery_rhythm_send_order_list_weekday' => 3,
            'delivery_rhythm_order_possible_until' => new Date('2020-12-12'),
        ];
        $this->assertGetLastOrderDay($product, '2020-12-12');
    }

    public function test1WeekWithFirstDeliveryDayAllowOrdersConfigOff()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'delivery_rhythm_first_delivery_day' => new Date('2018-11-02'),
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2018-10-07',
            'result' => '2018-11-02',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test1WeekWithFirstDeliveryDayAllowOrdersConfigOn()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'delivery_rhythm_first_delivery_day' => new Date('2018-11-02'),
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2018-10-07',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test1WeekNormalNoFirstDeliveryDayWednesdayFriday()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2018-10-07',
            'result' => '2018-10-12',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test1WeekNormalNoFirstDeliveryDaySaturdayThursday()
    {
        $this->prepareSaturdayThursdayConfig();
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2022-08-26', // friday
            'result' => '2022-09-01',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekDeliveryDaySaturdayThursday()
    {
        $this->prepareSaturdayThursdayConfig();
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'delivery_rhythm_first_delivery_day' => new Date('2022-09-08'),
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2022-08-25', // thursday
            'result' => '2022-09-08',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test1WeekNormalNoFirstDeliveryDaySaturdayThursdayWithSendOrderListDayOneDayBeforeDefault()
    {
        $this->prepareSaturdayThursdayConfig();
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_send_order_list_weekday' => 5,
                ]
            ),
            'currentDay' => '2022-08-26',
            'result' => '2022-09-08',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test1WeekWithSendOrderListDayOneDayBeforeDefault()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_send_order_list_weekday' => 2,
                ]
            ),
            'currentDay' => '2017-08-08',
            'result' => '2017-08-18',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test1WeekWithSendOrderListDayTwoDaysBeforeDefault()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_send_order_list_weekday' => 1
                ]
            ),
            'currentDay' => '2020-04-05',
            'result' => '2020-04-10',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekWithSendOrderListDayTwoDaysBeforeDefaultAndChangedSendOrderListsDayDeltaAllowOrdersConfigOff()
    {
        $this->changeConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 3);
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2021-02-05'),
                    'delivery_rhythm_send_order_list_weekday' => 0,
                ]
            ),
            'currentDay' => '2021-08-01',
            'result' => '2021-08-20',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekWithSendOrderListDayTwoDaysBeforeDefaultAndChangedSendOrderListsDayDeltaAllowOrdersConfigOn()
    {
        $this->changeConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 3);
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2021-02-05'),
                    'delivery_rhythm_send_order_list_weekday' => 0,
                ]
            ),
            'currentDay' => '2021-08-01',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test1WeekWithSendOrderListDayMondayAllowOrdersConfigOff()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_send_order_list_weekday' => 1,
                ]
            ),
            'currentDay' => '2022-02-01',
            'result' => '2022-02-11',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test1WeekWithSendOrderListDayMondayAllowOrdersConfigOn()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_send_order_list_weekday' => 1,
                ]
            ),
            'currentDay' => '2022-02-01',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekWithSendOrderListDayMondayAllowOrdersConfigOff()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_send_order_list_weekday' => 1,
                    'delivery_rhythm_first_delivery_day' => new Date('2019-03-01'),
                ]
            ),
            'currentDay' => '2019-02-25',
            'result' => '2019-03-15',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekWithSendOrderListDayMondayAllowOrdersConfigOn()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_send_order_list_weekday' => 1,
                    'delivery_rhythm_first_delivery_day' => new Date('2019-03-01'),
                ]
            ),
            'currentDay' => '2019-02-25',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekWithSendOrderListDayThursday()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_send_order_list_weekday' => 4,
                    'delivery_rhythm_first_delivery_day' => new Date('2019-03-01'),
                ]
            ),
            'currentDay' => '2019-03-08',
            'result' => '2019-03-15',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test1MonthFirstFridayWithSendOrderListDaySunday()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_send_order_list_weekday' => 1,
                    'delivery_rhythm_first_delivery_day' => new Date('2020-10-02'),
                ]
            ),
            'currentDay' => '2020-09-28',
            'result' => '2020-11-06',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekNotCurrentWeekAAllowOrderConfigOff()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2018-08-10'),
                ]
            ),
            'currentDay' => '2018-08-14',
            'result' => '2018-08-24',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekNotCurrentWeekAAllowOrderConfigOn()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2018-08-10'),
                ]
            ),
            'currentDay' => '2018-08-14',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekNotCurrentWeekBAllowOrderConfigOff()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2018-07-06'),
                ]
            ),
            'currentDay' => '2018-09-15',
            'result' => '2018-09-28',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekNotCurrentWeekBAllowOrderConfigOn()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2018-07-06'),
                ]
            ),
            'currentDay' => '2018-09-15',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekCurrentWeekAllowOrderConfigOff()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2018-08-03'),
                ]
            ),
            'currentDay' => '2018-08-14',
            'result' => '2018-08-17',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2WeekCurrentWeekAllowOrderConfigOn()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2018-08-03'),
                ]
            ),
            'currentDay' => '2018-08-14',
            'result' => '2018-08-17',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    /*
    public function test2WeekSendOrderListDayMondayCurrentWeekAllowOrderConfigOn()
    {
        $this->changeConfiguration('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY', 1);
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_send_order_list_weekday' => 1,
                    'delivery_rhythm_first_delivery_day' => new Date('2023-11-17'),
                ]
            ),
            'currentDay' => '2023-11-07',
            'result' => 'delivery-rhythm-triggered-delivery-break',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }
    */

    public function test2WeekD()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2019-03-22'),
                ]
            ),
            'currentDay' => '2019-03-15',
            'result' => '2019-03-22',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test4Week()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '4',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2018-08-03'),
                ]
            ),
            'currentDay' => '2018-08-07',
            'result' => '2018-08-31',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test1MonthFirstWeekdayOfMonthA()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2017-08-07',
            'result' => '2017-09-01',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function testFirstWeekdayOfMonthB()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '1',
                    'manufacturer' => [
                        'stock_management_enabled' => '1',
                    ],
                ]
            ),
            'currentDay' => '2017-08-07',
            'result' => '2017-08-11',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function testLastMonthLastWeekdayOfMonthA()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2018-09-13',
            'result' => '2018-09-28',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function testLastMonthLastWeekdayOfMonthB()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2018-08-07',
            'result' => '2018-08-31',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function testLastMonthLastWeekdayOfMonthC()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_first_delivery_day' => new Date('2021-02-26'),
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2021-01-20',
            'result' => '2021-02-26',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test2Month()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2020-11-20',
            'result' => '2020-12-11',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test3Month()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '3',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2020-11-20',
            'result' => '2020-12-18',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function test4Month()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '4',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                ]
            ),
            'currentDay' => '2020-11-30',
            'result' => '2020-12-25',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    public function testIndividual()
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $data = [
            'product' => $productsTable->newEntity(
                [
                    'delivery_rhythm_type' => 'individual',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                    'manufacturer' => [
                        'stock_management_enabled' => '0',
                    ],
                    'delivery_rhythm_first_delivery_day' => new Date('2018-08-03'),
                ]
            ),
            'currentDay' => '2017-08-07',
            'result' => '2018-08-03',
        ];
        $this->assertGetNextPickupDayForProduct($data['product'], $data['currentDay'], $data['result']);
    }

    private function assertGetNextPickupDayForProduct($product, $currentDay, $expectedResult)
    {
        $result = (new DeliveryRhythmService())->getNextPickupDayForProduct($product, $currentDay);
        $this->assertEquals($expectedResult, $result);
    }

    private function assertGetOrderPeriodFirstDay($currentDay, $expected)
    {
        $result = (new DeliveryRhythmService())->getOrderPeriodFirstDay(strtotime($currentDay));
        $this->assertEquals($expected, $result);
    }

    private function assertGetOrderPeriodLastDay($currentDay, $expected)
    {
        $result = (new DeliveryRhythmService())->getOrderPeriodLastDay(strtotime($currentDay));
        $this->assertEquals($expected, $result);
    }

    private function assertGetDeliveryDay($currentDay, $expected)
    {
        $result = (new DeliveryRhythmService())->getDeliveryDay(strtotime($currentDay));
        $result = date($this->MyTimeHelper->getI18Format('DateShortAlt'), $result);
        $this->assertEquals($expected, $result);
    }

    private function assertGetFormattedNextDeliveryDay($currentDay, $expected)
    {
        $result = (new DeliveryRhythmService())->getFormattedNextDeliveryDay(strtotime($currentDay));
        $this->assertEquals($expected, $result);
    }

    private function assertGetOrderPeriodFirstDayByDeliveryDay($deliveryDay, $expected)
    {
        $result = (new DeliveryRhythmService())->getOrderPeriodFirstDayByDeliveryDay($deliveryDay);
        $this->assertEquals($expected, $result);
    }

    private function assertGetOrderPeriodLastDayByDeliveryDay($deliveryDay, $expected)
    {
        $result = (new DeliveryRhythmService())->getOrderPeriodLastDayByDeliveryDay($deliveryDay);
        $this->assertEquals($expected, $result);
    }

    private function assertGetLastOrderDay($product, $expected)
    {
        $result = (new DeliveryRhythmService())->getLastOrderDay(
            $product['next_delivery_day'],
            $product['delivery_rhythm_type'],
            $product['delivery_rhythm_send_order_list_weekday'],
            $product['delivery_rhythm_order_possible_until'],
        );
        $this->assertEquals($expected, $result);
    }

}
