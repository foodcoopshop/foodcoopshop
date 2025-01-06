<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;

class PricePerUnitHelperTest extends AppCakeTestCase
{

    public function testGetQuantityInUnitsStringForAttributesA(): void
    {
        $result = $this->PricePerUnit->getQuantityInUnitsStringForAttributes('500 g', true, true, 500, 'g', 2);
        $this->assertEquals($result, 'je ca. 500 g');
    }

    public function testGetQuantityInUnitsStringForAttributesB(): void
    {
        $result = $this->PricePerUnit->getQuantityInUnitsStringForAttributes('Stück', false, true, 1, 'kg', 2);
        $this->assertEquals($result, 'Stück, je ca. 1 kg');
    }

    public function testGetQuantityInUnitsStringForAttributesC(): void
    {
        $result = $this->PricePerUnit->getQuantityInUnitsStringForAttributes('Stück', true, true, 250, 'g');
        $this->assertEquals($result, 'ca. 250 g');
    }

    public function testGetQuantityInUnitsStringForAttributesD(): void
    {
        $result = $this->PricePerUnit->getQuantityInUnitsStringForAttributes('Stück', false, false, 250, 'g');
        $this->assertEquals($result, 'Stück');
    }

    public function testGetQuantityInUnitsStringForAttributesE(): void
    {
        $result = $this->PricePerUnit->getQuantityInUnitsStringForAttributes('Stück', false, true, 0.5, 'kg');
        $this->assertEquals($result, 'Stück, ca. 0,5 kg');
    }

}
