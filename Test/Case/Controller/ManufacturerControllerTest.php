<?php

App::uses('AppCakeTestCase', 'Test');

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
class ManufacturersControllerTest extends AppCakeTestCase
{

    private $manufacturerId = 5;
    private $today;
    private $mustNotBeShownString = 'im wohlverdienten Urlaub.</h2>';

    public function setUp()
    {
        parent::setUp();
        $this->today = date('Y-m-d');
        $this->loginAsCustomer(); // test database does not show products to guests
    }

    public function testHolidayModeTodayToTomorrow()
    {

        $dateFrom = $this->today;
        $dateTo = date('Y-m-d', strtotime('+1 day'));

        $this->doHolidayModeCheck(
            $dateFrom,
            $dateTo,
            '<h2 class="info">Der Hersteller <b>Demo Gemüse-Hersteller</b> ist seit '.$this->Time->formatToDateShort($dateFrom).' bis '.$this->Time->formatToDateShort($dateTo).' im wohlverdienten Urlaub.</h2>',
            true,
            false
        );
    }

    public function testHolidayModeHolidayOver()
    {

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

        $this->doHolidayModeCheck(
            '0000-00-00',
            '0000-00-00',
            $this->mustNotBeShownString,
            false,
            true
        );
    }

    public function testHolidayModeFromDateInPast()
    {

        $dateFrom = date('Y-m-d', strtotime('-1 day'));

        $this->doHolidayModeCheck(
            $dateFrom,
            '0000-00-00',
            '<h2 class="info">Der Hersteller <b>Demo Gemüse-Hersteller</b> ist seit '.$this->Time->formatToDateShort($dateFrom).' im wohlverdienten Urlaub.</h2>',
            true,
            false
        );
    }

    public function testHolidayModeInFuture()
    {

        $dateFrom = date('Y-m-d', strtotime('+5 day'));
        $dateTo = date('Y-m-d', strtotime('+10 day'));

        $this->doHolidayModeCheck(
            $dateFrom,
            $dateTo,
            '<h2 class="info">Der Hersteller <b>Demo Gemüse-Hersteller</b> ist von '.$this->Time->formatToDateShort($dateFrom).' bis '.$this->Time->formatToDateShort($dateTo).' im wohlverdienten Urlaub.</h2>',
            true,
            true
        );
    }

    public function testHolidayModeToDateInFuture()
    {

        $dateTo = date('Y-m-d', strtotime('+10 day'));

        $this->doHolidayModeCheck(
            '0000-00-00',
            $dateTo,
            '<h2 class="info">Der Hersteller <b>Demo Gemüse-Hersteller</b> ist bis '.$this->Time->formatToDateShort($dateTo).' im wohlverdienten Urlaub.</h2>',
            true,
            false
        );
    }

    private function doHolidayModeCheck($dateFrom, $dateTo, $expectedString, $expectedStringIsVisible, $productsExpected)
    {

        $this->changeManufacturerHolidayMode($this->manufacturerId, $dateFrom, $dateTo);
        $this->browser->get(Configure::read('slugHelper')->getManufacturerDetail($this->manufacturerId, ''));

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
}
