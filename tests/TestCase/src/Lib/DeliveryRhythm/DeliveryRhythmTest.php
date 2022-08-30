<?php
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

use App\Lib\DeliveryRhythm\DeliveryRhythm;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\DeliveryRhythmConfigsTrait;
use App\View\Helper\MyTimeHelper;
use Cake\I18n\FrozenDate;
use Cake\View\View;

class DeliveryRhythmTest extends AppCakeTestCase
{

    use DeliveryRhythmConfigsTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->MyTimeHelper = new MyTimeHelper(new View());
    }

    public function testGetDeliveryDayTuesdayFriday()
    {
        $this->prepareTuesdayFridayConfig();
        $this->assertGetDeliveryDay('25.07.2018', '03.08.2018');
    }

    public function xtestGetDeliveryDaySaturdayThursday()
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
        $this->assertGetOrderPeriodFirstDay('26.08.2022', '27.08.2022'); // friday
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

    public function testGetLastOrderDayWeeklySendOrderListsDayNormal()
    {
        $product = [
            'next_delivery_day' => '2020-12-04',
            'delivery_rhythm_type' => 'week',
            'delivery_rhythm_count' => 1,
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
            'delivery_rhythm_count' => 1,
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
            'delivery_rhythm_count' => 0,
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
            'delivery_rhythm_count' => 0,
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
            'delivery_rhythm_count' => 0,
            'delivery_rhythm_send_order_list_weekday' => 3,
            'delivery_rhythm_order_possible_until' => new FrozenDate('2020-12-12'),
        ];
        $this->assertGetLastOrderDay($product, '2020-12-12');
    }


    private function assertGetOrderPeriodFirstDay($currentDay, $expected)
    {
        $result = DeliveryRhythm::getOrderPeriodFirstDay(strtotime($currentDay));
        $this->assertEquals($expected, $result);
    }

    private function assertGetOrderPeriodLastDay($currentDay, $expected)
    {
        $result = DeliveryRhythm::getOrderPeriodLastDay(strtotime($currentDay));
        $this->assertEquals($expected, $result);
    }

    private function assertGetDeliveryDay($currentDay, $expected)
    {
        $result = DeliveryRhythm::getDeliveryDay(strtotime($currentDay));
        $result = date($this->MyTimeHelper->getI18Format('DateShortAlt'), $result);
        $this->assertEquals($expected, $result);
    }

    private function assertGetFormattedNextDeliveryDay($currentDay, $expected)
    {
        $result = DeliveryRhythm::getFormattedNextDeliveryDay(strtotime($currentDay));
        $this->assertEquals($expected, $result);
    }

    private function assertGetLastOrderDay($product, $expected)
    {
        $result = DeliveryRhythm::getLastOrderDay(
            $product['next_delivery_day'],
            $product['delivery_rhythm_type'],
            $product['delivery_rhythm_count'],
            $product['delivery_rhythm_send_order_list_weekday'],
            $product['delivery_rhythm_order_possible_until'],
        );
        $this->assertEquals($expected, $result);
    }


}
