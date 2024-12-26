<?php
declare(strict_types=1);

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
use Cake\View\View;

class MyTimeHelperTest extends AppCakeTestCase
{

    protected MyTimeHelper $MyTimeHelper;

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

    public function testConvertSecondsInMinutesAndSeconds()
    {
        $this->assertEquals('1 Sekunde', $this->MyTimeHelper->convertSecondsInMinutesAndSeconds(1));
        $this->assertEquals('59 Sekunden', $this->MyTimeHelper->convertSecondsInMinutesAndSeconds(59));
        $this->assertEquals('1 Minute 1 Sekunde', $this->MyTimeHelper->convertSecondsInMinutesAndSeconds(61));
        $this->assertEquals('1 Minute 59 Sekunden', $this->MyTimeHelper->convertSecondsInMinutesAndSeconds(119));
        $this->assertEquals('2 Minuten 1 Sekunde', $this->MyTimeHelper->convertSecondsInMinutesAndSeconds(121));
        $this->assertEquals('2 Minuten 59 Sekunden', $this->MyTimeHelper->convertSecondsInMinutesAndSeconds(179));
        $this->assertEquals('1 Minute 30,5 Sekunden', $this->MyTimeHelper->convertSecondsInMinutesAndSeconds(90.5));
    }

}
