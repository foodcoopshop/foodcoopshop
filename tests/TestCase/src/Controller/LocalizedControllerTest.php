<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 5.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Command\SavedLocalizedJsAsStaticFileCommand;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;

class LocalizedControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;

    public function testLocalizedJavascriptIsServedWithUtf8Charset(): void
    {
        $this->get(SavedLocalizedJsAsStaticFileCommand::ROUTE);

        $this->assertResponseCode(200);
        $this->assertResponseContains('foodcoopshop.translations =');
        $this->assertResponseContains('foodcoopshop.config =');
    }
}
