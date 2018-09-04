<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\View\Helper\PricePerUnitHelper;
use Cake\View\View;

class PricePerUnitHelperTest extends AppCakeTestCase
{

    public function setUp()
    {
        $this->PricePerUnitHelper = new PricePerUnitHelper(new View());
    }

    public function testGetQuantityInUnitsStringForAttributesA()
    {
        $result = $this->PricePerUnitHelper->getQuantityInUnitsStringForAttributes('500 g', true, true, 500, 'g', 2);
        $this->assertEquals($result, 'je ca. 500 g');
    }

    public function testGetQuantityInUnitsStringForAttributesB()
    {
        $result = $this->PricePerUnitHelper->getQuantityInUnitsStringForAttributes('Stück', false, true, 1, 'kg', 2);
        $this->assertEquals($result, 'Stück, je ca. 1 kg');
    }

    public function testGetQuantityInUnitsStringForAttributesC()
    {
        $result = $this->PricePerUnitHelper->getQuantityInUnitsStringForAttributes('Stück', true, true, 250, 'g');
        $this->assertEquals($result, 'ca. 250 g');
    }

    public function testGetQuantityInUnitsStringForAttributesD()
    {
        $result = $this->PricePerUnitHelper->getQuantityInUnitsStringForAttributes('Stück', false, false, 250, 'g');
        $this->assertEquals($result, 'Stück');
    }

    public function testGetQuantityInUnitsStringForAttributesE()
    {
        $result = $this->PricePerUnitHelper->getQuantityInUnitsStringForAttributes('Stück', false, true, 0.5, 'kg');
        $this->assertEquals($result, 'Stück, ca. 0,5 kg');
    }

}
