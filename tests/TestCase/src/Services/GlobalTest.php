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

class GlobalTest extends AppCakeTestCase
{

    public function setUp(): void
    {
        // do not import database - no database needed for this test
    }

    public function testBicValid1(): void
    {
        $this->assertBic('RZOOAT2L510', true);
    }

    public function testBicValid2(): void
    {
        $this->assertBic('RZOOAT2L380', true);
    }

    public function testBicValid3(): void
    {
        $this->assertBic('RZOOAT2L', true);
    }

    private function assertBic($iban, $expected): void
    {
        $this->assertEquals($expected, (bool) preg_match(BIC_REGEX, $iban));
    }

}
