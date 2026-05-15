<?php
declare(strict_types=1);

use App\Services\CssConfigurationService;
use PHPUnit\Framework\TestCase;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CssConfigurationServiceTest extends TestCase
{

    public function testValidateAndFormat(): void
    {
        $css = (new CssConfigurationService())->format('body{color:red}');
        $this->assertStringContainsString('body {', $css);
        $this->assertStringContainsString('color: red;', $css);
    }

    public function testEmptyCss(): void
    {
        $this->assertSame('', (new CssConfigurationService())->format(''));
    }

}