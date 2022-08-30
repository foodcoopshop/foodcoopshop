<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\DeliveryRhythmConfigsTrait;
use App\View\Helper\MyTimeHelper;
use Cake\I18n\FrozenDate;
use Cake\View\View;

class MyTimeHelperTest extends AppCakeTestCase
{

    use DeliveryRhythmConfigsTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->MyTimeHelper = new MyTimeHelper(new View());
    }

    public function testFormatToDbFormatDateDe()
    {
        $result = $this->MyTimeHelper->formatToDbFormatDate('12.06.2018');
        $this->assertEquals($result, '2018-06-12');
    }

    public function testFormatToDbFormatDateEn()
    {
        $result = $this->MyTimeHelper->formatToDbFormatDate('06/12/2018');
        $this->assertEquals($result, '2018-06-12');
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

    public function testGetLastDayOfLastMonth()
    {
        $this->assertGetLastDayOfLastMonth('2018-03-11', '28.02.2018');
        $this->assertGetLastDayOfLastMonth('2018-01-11', '31.12.2017');
    }

    public function testGetFirstDayOfLastMonth()
    {
        $this->assertGetFirstDayOfLastMonth('2018-03-11', '01.02.2018');
        $this->assertGetFirstDayOfLastMonth('2018-01-11', '01.12.2017');
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

    private function assertGetLastOrderDay($product, $expected)
    {
        $result = $this->MyTimeHelper->getLastOrderDay(
            $product['next_delivery_day'],
            $product['delivery_rhythm_type'],
            $product['delivery_rhythm_count'],
            $product['delivery_rhythm_send_order_list_weekday'],
            $product['delivery_rhythm_order_possible_until'],
        );
        $this->assertEquals($expected, $result);
    }

    private function assertGetLastDayOfLastMonth($currentDay, $expected)
    {
        $result = $this->MyTimeHelper->getLastDayOfLastMonth($currentDay);
        $this->assertEquals($expected, $result);
    }

    private function assertGetFirstDayOfLastMonth($currentDay, $expected)
    {
        $result = $this->MyTimeHelper->getFirstDayOfLastMonth($currentDay);
        $this->assertEquals($expected, $result);
    }

    private function assertGetDeliveryDay($currentDay, $expected)
    {
        $result = $this->MyTimeHelper->getDeliveryDay(strtotime($currentDay));
        $result = date($this->MyTimeHelper->getI18Format('DateShortAlt'), $result);
        $this->assertEquals($expected, $result);
    }

    private function assertGetFormattedNextDeliveryDay($currentDay, $expected)
    {
        $result = $this->MyTimeHelper->getFormattedNextDeliveryDay(strtotime($currentDay));
        $this->assertEquals($expected, $result);
    }

}
