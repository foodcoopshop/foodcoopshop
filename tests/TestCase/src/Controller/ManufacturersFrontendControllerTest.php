<?php

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;

class ManufacturersFrontendControllerTest extends AppCakeTestCase
{

    private $manufacturerId = 5;
    private $today;
    private $mustNotBeShownString = 'Lieferpause.</h2>';

    public function setUp()
    {
        parent::setUp();
        $this->today = date('Y-m-d');
    }

       public function testManufacturerDetailOnlinePublicLoggedOut()
    {
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getManufacturerDetail(4, 'Demo Manufacturer'));
        $this->assert200OkHeader();
    }

    public function testManufacturerDetailOfflinePublicLoggedOut()
    {
        $manufacturerId = 4;
        $this->changeManufacturer($manufacturerId, 'active', 0);
        $this->httpClient->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assert404NotFoundHeader();
    }

    public function testManufacturerDetailOnlinePrivateLoggedOut()
    {
        $manufacturerId = 4;
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assertAccessDeniedWithRedirectToLoginForm();
    }

    public function testManufacturerDetailOnlinePrivateLoggedIn()
    {
        $this->loginAsCustomer();
        $manufacturerId = 4;
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assert200OkHeader();
    }

    public function testManufacturerDetailNonExistingLoggedOut()
    {
        $manufacturerId = 1;
        $this->httpClient->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assert404NotFoundHeader();
    }
}
