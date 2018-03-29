<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\View\Helper\MyTimeHelper;
use Cake\Core\Configure;
use Cake\View\View;

class MyTimeHelperTest extends AppCakeTestCase
{

    public function setUp()
    {
        $this->MyTimeHelper = new MyTimeHelper(new View());
    }
    
    public function testFormatDecimalToHoursAndMinutes()
    {
        $tests = [
            [
                'decimal' => 0,
                'expected' => '00min'
            ],
            [
                'decimal' => -1,
                'expected' => '-1h 00min'
            ],
            [
                'decimal' => 1,
                'expected' => '1h 00min'
            ],
            [
                'decimal' => 0.5,
                'expected' => '30min'
            ],
            [
                'decimal' => -0.5,
                'expected' => '-30min'
            ],
            [
                'decimal' => 3.34,
                'expected' => '3h 20min'
            ]
        ];
        
        foreach ($tests as $test) {
            $result = $this->MyTimeHelper->formatDecimalToHoursAndMinutes($test['decimal']);
            $this->assertEquals($test['expected'], $result);
        }
    }

    private function prepareWednesdayFridayConfig()
    {
        Configure::write('app.deliveryDayDelta', 2);
        Configure::write('app.sendOrderListsWeekday', 3);
    }

    private function prepareTuesdayFridayConfig()
    {
        Configure::write('app.deliveryDayDelta', 3);
        Configure::write('app.sendOrderListsWeekday', 2);
    }

    private function prepareMondayTuesdayConfig()
    {
        Configure::write('app.deliveryDayDelta', 1);
        Configure::write('app.sendOrderListsWeekday', 1);
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

    public function testGetDateForShopOrderWednesdayFriday()
    {
        $this->prepareWednesdayFridayConfig();
        $this->assertGetDateForShopOrder('29.11.2017 01:00:00', '2017-11-28 00:00:00'); // wednesday
        $this->assertGetDateForShopOrder('30.11.2017 02:00:00', '2017-11-28 00:00:00'); // thursday
        $this->assertGetDateForShopOrder('01.12.2017 03:00:00', '2017-11-28 00:00:00'); // friday
        $this->assertGetDateForShopOrder('02.12.2017 04:00:00', '2017-11-28 00:00:00'); // saturday
        $this->assertGetDateForShopOrder('03.12.2017 05:00:00', '2017-11-28 00:00:00'); // sunday
    }

    public function testGetDateForShopOrderTuesdayFriday()
    {
        $this->prepareTuesdayFridayConfig();
        $this->assertGetDateForShopOrder('28.11.2017 06:00:00', '2017-11-27 00:00:00'); // tuesday
        $this->assertGetDateForShopOrder('29.11.2017 07:00:00', '2017-11-27 00:00:00'); // wednesday
        $this->assertGetDateForShopOrder('30.11.2017 08:00:00', '2017-11-27 00:00:00'); // thursday
        $this->assertGetDateForShopOrder('01.12.2017 09:00:00', '2017-11-27 00:00:00'); // friday
        $this->assertGetDateForShopOrder('02.12.2017 10:00:00', '2017-11-27 00:00:00'); // saturday
    }

    public function testGetDateForShopOrderMondayTuesday()
    {
        $this->prepareMondayTuesdayConfig();
        $this->assertGetDateForShopOrder('27.11.2017 11:00:00', '2017-11-26 00:00:00'); // monday
        $this->assertGetDateForShopOrder('28.11.2017 12:00:00', '2017-11-26 00:00:00'); // tuesday
        $this->assertGetDateForShopOrder('29.11.2017 13:00:00', '2017-11-26 00:00:00'); // wednesday
        $this->assertGetDateForShopOrder('30.11.2017 14:00:00', '2017-11-26 00:00:00'); // thursday
    }

    private function assertGetOrderPeriodFirstDay($currentDay, $expected)
    {
        $result = $this->MyTimeHelper->getOrderPeriodFirstDay(strtotime($currentDay));
        $this->assertEquals($expected, $result);
    }

    private function assertGetOrderPeriodLastDay($currentDay, $expected)
    {
        $result = $this->MyTimeHelper->getOrderPeriodLastDay(strtotime($currentDay));
        $this->assertEquals($expected, $result);
    }

    private function assertGetDateForShopOrder($currentDay, $expected)
    {
        $result = $this->MyTimeHelper->getDateForShopOrder(strtotime($currentDay));
        $this->assertEquals($expected, $result);
    }
}
