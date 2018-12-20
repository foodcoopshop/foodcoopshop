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

class GlobalTest extends AppCakeTestCase
{

    public function setUp()
    {
        // do not import database - no database needed for this test
    }

    public function testBicValid1()
    {
        $this->assertBic('RZOOAT2L510', true);
    }
    
    public function testBicValid2()
    {
        $this->assertBic('RZOOAT2L380', true);
    }
    
    public function testBicValid3()
    {
        $this->assertBic('RZOOAT2L', true);
    }
    
    private function assertBic($iban, $expected)
    {
        $this->assertEquals($expected, (boolean) preg_match(BIC_REGEX, $iban));
    }
    
}
