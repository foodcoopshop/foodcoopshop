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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
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

    public function testHolidayModeTodayToTomorrow()
    {

        $this->markTestSkipped();
        $dateFrom = $this->today;
        $dateTo = date('Y-m-d', strtotime('+1 day'));

        $this->doHolidayModeCheck(
            $dateFrom,
            $dateTo,
            '<h2 class="info">Der Hersteller <b>Demo Gemüse-Hersteller</b> hat seit '.$this->Time->formatToDateShort($dateFrom).' bis '.$this->Time->formatToDateShort($dateTo).' Lieferpause.</h2>',
            true,
            false
        );
    }

    public function testHolidayModeHolidayOver()
    {

        $this->markTestSkipped();
        $this->doHolidayModeCheck(
            date('Y-m-d', strtotime('-10 day')),
            date('Y-m-d', strtotime('-5 day')),
            $this->mustNotBeShownString,
            false,
            true
        );
    }

    public function testNoHolidayModeSet()
    {

        $this->markTestSkipped();
        $this->doHolidayModeCheck(
            null,
            null,
            $this->mustNotBeShownString,
            false,
            true
        );
    }

    public function testHolidayModeFromDateInPast()
    {

        $this->markTestSkipped();
        $dateFrom = date('Y-m-d', strtotime('-1 day'));

        $this->doHolidayModeCheck(
            $dateFrom,
            null,
            '<h2 class="info">Der Hersteller <b>Demo Gemüse-Hersteller</b> hat seit '.$this->Time->formatToDateShort($dateFrom).' Lieferpause.</h2>',
            true,
            false
        );
    }

    public function testHolidayModeInFuture()
    {

        $this->markTestSkipped();
        $dateFrom = date('Y-m-d', strtotime('+5 day'));
        $dateTo = date('Y-m-d', strtotime('+10 day'));

        $this->doHolidayModeCheck(
            $dateFrom,
            $dateTo,
            '<h2 class="info">Der Hersteller <b>Demo Gemüse-Hersteller</b> hat von '.$this->Time->formatToDateShort($dateFrom).' bis '.$this->Time->formatToDateShort($dateTo).' Lieferpause.</h2>',
            true,
            true
        );
    }

    public function testHolidayModeToDateInFuture()
    {

        $this->markTestSkipped();
        $dateTo = date('Y-m-d', strtotime('+10 day'));

        $this->doHolidayModeCheck(
            null,
            $dateTo,
            '<h2 class="info">Der Hersteller <b>Demo Gemüse-Hersteller</b> hat bis '.$this->Time->formatToDateShort($dateTo).' Lieferpause.</h2>',
            true,
            false
        );
    }

    private function doHolidayModeCheck($dateFrom, $dateTo, $expectedString, $expectedStringIsVisible, $productsExpected)
    {

        $this->loginAsCustomer();
        $this->changeManufacturerHolidayMode($this->manufacturerId, $dateFrom, $dateTo);
        $this->browser->get(Configure::read('app.slugHelper')->getManufacturerDetail($this->manufacturerId, ''));

        if ($expectedStringIsVisible) {
            $this->assertRegExpWithUnquotedString($expectedString, $this->browser->getContent());
        } else {
            $this->assertNotRegExpWithUnquotedString($expectedString, $this->browser->getContent());
        }

        $productsShownPattern = '<div class="product-wrapper"';
        if ($productsExpected) {
            $this->assertRegExpWithUnquotedString($productsShownPattern, $this->browser->getContent());
        } else {
            $this->assertNotRegExpWithUnquotedString($productsShownPattern, $this->browser->getContent());
        }
    }

    public function testManufacturerDetailOnlinePublicLoggedOut()
    {
        $this->browser->get($this->Slug->getManufacturerDetail(4, 'Demo Manufacturer'));
        $this->assert200OkHeader();
    }

    public function testManufacturerDetailOfflinePublicLoggedOut()
    {
        $manufacturerId = 4;
        $this->changeManufacturer($manufacturerId, 'active', 0);
        $this->browser->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assert404NotFoundHeader();
    }

    public function testManufacturerDetailOnlinePrivateLoggedOut()
    {
        $manufacturerId = 4;
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $this->browser->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assertAccessDeniedWithRedirectToLoginForm();
    }

    public function testManufacturerDetailOnlinePrivateLoggedIn()
    {
        $this->loginAsCustomer();
        $manufacturerId = 4;
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $this->browser->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assert200OkHeader();
    }

    public function testManufacturerDetailNonExistingLoggedOut()
    {
        $manufacturerId = 1;
        $this->browser->get($this->Slug->getManufacturerDetail($manufacturerId, 'Demo Manufacturer'));
        $this->assert404NotFoundHeader();
    }
}
