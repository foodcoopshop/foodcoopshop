<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;

class ManufacturersFrontendControllerTest extends AppCakeTestCase
{
    use AppIntegrationTestTrait;
    use LoginTrait;

    protected string $today;
    protected string $mustNotBeShownString = 'Lieferpause.</h2>';

    public function setUp(): void
    {
        parent::setUp();
        $this->today = date('Y-m-d');
    }

       public function testManufacturerDetailOnlinePublicLoggedOut()
    {
        $this->get($this->Slug->getManufacturerDetail(4, 'Demo Fleisch Hersteller'));
        $this->assertResponseCode(200);
    }

    public function testManufacturerDetailOfflinePublicLoggedOut()
    {
        $manufacturerId = 4;
        $this->changeManufacturer($manufacturerId, 'active', 0);
        $this->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assertResponseCode(404);
    }

    public function testManufacturerDetailOnlinePrivateLoggedOut()
    {
        $manufacturerId = 4;
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $this->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assertAccessDeniedFlashMessage();
    }

    public function testManufacturerDetailOnlinePrivateLoggedIn()
    {
        $this->loginAsCustomer();
        $manufacturerId = 4;
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $this->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Fleisch Hersteller'));
        $this->assertResponseCode(200);
    }

    public function testManufacturerDetailNonExistingLoggedOut()
    {
        $manufacturerId = 1;
        $this->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assertResponseCode(404);
    }
}
