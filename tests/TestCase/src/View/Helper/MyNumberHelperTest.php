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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\View\Helper\MyNumberHelper;
use Cake\I18n\I18n;
use Cake\View\View;

class MyNumberHelperTest extends AppCakeTestCase
{

    public function setUp()
    {
        $this->MyNumberHelper = new MyNumberHelper(new View());
    }

    public function testFormatAsDecimalThreeDigits()
    {
        $result = $this->MyNumberHelper->formatAsDecimal(100, 3);
        $this->assertEquals($result, '100,000');
    }

    public function testFormatAsDecimalTwoDigitsDefault()
    {
        $result = $this->MyNumberHelper->formatAsDecimal(88.1);
        $this->assertEquals($result, '88,10');
    }

    public function testFormatAsDecimalRemoveTrailingZeros()
    {
        $result = $this->MyNumberHelper->formatAsDecimal(93.800, 2, true);
        $this->assertEquals($result, '93,8');
    }

    public function testFormatAsDecimalThousandSeparatorAndThreeDecimals()
    {
        $result = $this->MyNumberHelper->formatAsDecimal(4381.422, 2, true);
        $this->assertEquals($result, '4.381,422');
    }
    
    public function testFormatAsDecimalRoundToTwoDecimals()
    {
        $result = $this->MyNumberHelper->formatAsDecimal(1.228);
        $this->assertEquals($result, '1,23');
    }
    
    public function testParseFloatRespectingLocaleInvalidString()
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('invalid-price');
        $this->assertFalse($result);
    }

    public function testParseFloatRespectingLocaleValidGermanPrice()
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('3,44');
        $this->assertEquals($result, 3.44);
    }

    public function testParseFloatRespectingLocaleValidEnglishPriceWithWrongLocale()
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('3.45');
        $this->assertEquals($result, 3.45);
    }

    public function testParseFloatRespectingLocaleValidEnglishPriceWithEnglishLocale()
    {
        $originalLocale = I18n::getLocale();
        I18n::setLocale('en_US');
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('3.45');
        $this->assertEquals($result, 3.45);
        I18n::setLocale($originalLocale);
    }

    public function testParseFloatRespectingLocaleNegativeFloat()
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('-3,45');
        $this->assertEquals($result, -3.45);
    }

    public function testParseFloatRespectingLocaleInteger()
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('3');
        $this->assertEquals($result, 3);
    }

    public function testParseFloatRespectingLocaleZero()
    {
        $result = $this->MyNumberHelper->parseFloatRespectingLocale('0,00');
        $this->assertEquals($result, 0);
    }

}
