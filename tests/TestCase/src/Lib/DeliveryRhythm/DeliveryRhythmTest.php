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

class DeliveryRhythmTest extends AppCakeTestCase
{

    use DeliveryRhythmConfigsTrait;

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

}
