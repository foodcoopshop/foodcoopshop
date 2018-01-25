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

class GlobalTest extends AppCakeTestCase
{

    public function setUp()
    {
        // do not import database - no database needed for this test
    }

    public function testIbanAustria()
    {
        $this->assertIban('AT193357281080332578', true);
    }

    public function testIbanGermany()
    {
        $this->assertIban('DE1933572810803325787323', true);
    }

    public function testIbanTooShort()
    {
        $this->assertIban('DE1933572810', false);
    }

    public function testIbanInvalidCountryString()
    {
        $this->assertIban('6T193357281080332578', false);
    }

    public function testIbanWith20Chars()
    {
        $this->assertIban('AT19335728108033257844', false);
    }

    public function testIbanWithoutLetterInDigitsArea()
    {
        $this->assertIban('DE193357281080X325787323', false);
    }

    private function assertIban($iban, $expected)
    {
        $this->assertEquals($expected, (boolean) preg_match(IBAN_REGEX, $iban));
    }
}
