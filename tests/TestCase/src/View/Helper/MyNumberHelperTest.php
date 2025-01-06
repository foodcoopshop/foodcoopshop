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
use App\View\Helper\MyNumberHelper;
use Cake\I18n\I18n;
use Cake\View\View;

class MyNumberHelperTest extends AppCakeTestCase
{

    protected MyNumberHelper $MyNumberHelper;

    public function setUp(): void
    {
        $this->MyNumberHelper = new MyNumberHelper(new View());
    }

    public function testFormatAsDecimalThreeDigitsMin2Digits(): void
    {
        $result = $this->MyNumberHelper->formatAsDecimal(100.003, 3, true, 2);
        $this->assertEquals($result, '100,003');
        $result = $this->MyNumberHelper->formatAsDecimal(100.01, 3, true, 2);
        $this->assertEquals($result, '100,01');
        $result = $this->MyNumberHelper->formatAsDecimal(100.1, 3, true, 2);
        $this->assertEquals($result, '100,10');
    }

    public function testFormatAsDecimalThreeDigits(): void
    {
        $result = $this->MyNumberHelper->formatAsDecimal(100, 3);
        $this->assertEquals($result, '100,000');
    }

    public function testFormatAsDecimalTwoDigitsDefault(): void
    {
        $result = $this->MyNumberHelper->formatAsDecimal(88.1);
        $this->assertEquals($result, '88,10');
    }

    public function testFormatAsDecimalRemoveTrailingZeros(): void
    {
        $result = $this->MyNumberHelper->formatAsDecimal(93.800, 2, true);
        $this->assertEquals($result, '93,8');
    }

    public function testFormatAsDecimalThousandSeparatorAndThreeDecimals(): void
    {
        $result = $this->MyNumberHelper->formatAsDecimal(4381.422, 2, true);
        $this->assertEquals($result, '4.381,422');
    }

    public function testFormatAsDecimalRoundToTwoDecimals(): void
    {
        $result = $this->MyNumberHelper->formatAsDecimal(1.228);
        $this->assertEquals($result, '1,23');
    }

    public function testParseFloatRespectingLocaleInvalidString(): void
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('invalid-price');
        $this->assertFalse($result);
    }

    public function testParseFloatRespectingLocaleValidGermanPrice(): void
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('3,44');
        $this->assertEquals($result, 3.44);
    }

    public function testParseFloatRespectingLocaleValidEnglishPriceWithWrongLocale(): void
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('3.45');
        $this->assertEquals($result, 3.45);
    }

    public function testParseFloatRespectingLocaleValidEnglishPriceWithEnglishLocale(): void
    {
        $originalLocale = I18n::getLocale();
        I18n::setLocale('en_US');
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('3.45');
        $this->assertEquals($result, 3.45);
        I18n::setLocale($originalLocale);
    }

    public function testParseFloatRespectingLocaleNegativeFloat(): void
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('-3,45');
        $this->assertEquals($result, -3.45);
    }

    public function testParseFloatRespectingLocaleInteger(): void
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('3');
        $this->assertEquals($result, 3);
    }

    public function testParseFloatRespectingLocaleDecimalBetween0And1(): void
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('0,5');
        $this->assertEquals($result, 0.5);
    }

    public function testParseFloatRespectingLocaleZero(): void
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('0,00');
        $this->assertEquals($result, 0);
    }

}
