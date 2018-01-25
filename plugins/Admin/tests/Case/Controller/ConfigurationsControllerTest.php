<?php

/**
 * ConfigurationsControllerTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;

class ConfigurationsControllerTest extends AppCakeTestCase
{

    public function testShowProductsForGuestsEnabledAndLoggedOut()
    {
        $this->changeConfiguration('FCS_SHOW_PRODUCTS_FOR_GUESTS', 1);
        $this->logout();
        $this->assertShowProductForGuestsEnabledOrLoggedIn($this->getTestUrlsForShowProductForGuests(), false);
    }

    public function testShowProductsForGuestsDisabledAndLoggedIn()
    {
        $this->loginAsSuperadmin();
        $this->assertShowProductForGuestsEnabledOrLoggedIn($this->getTestUrlsForShowProductForGuests(), true);
    }

    public function testShowProductsForGuestsDisabledAndLoggedOut()
    {
        $this->logout();
        foreach ($this->getTestUrlsForShowProductForGuests() as $url) {
            $this->browser->get($url);
            $this->assertRedirectToLoginPage();
        }
    }

    private function getTestUrlsForShowProductForGuests()
    {
        return [
            $this->Slug->getCategoryDetail(16, 'Fleischprodukte'),
            $this->Slug->getProductDetail(339, 'Kartoffel')
        ];
    }

    private function assertShowProductForGuestsEnabledOrLoggedIn($testUrls, $expectPrice)
    {
        $this->assertPagesForErrors($testUrls);
        foreach ($testUrls as $url) {
            $this->browser->get($url);
            $priceRegExp = '<div class="price">';
            $priceAssertFunction = 'assertRegExpWithUnquotedString';
            if (!$expectPrice) {
                $priceAssertFunction = 'assertNotRegExpWithUnquotedString';
            }
            $this->{$priceAssertFunction}($priceRegExp, $this->browser->getContent(), 'price expected: ' . $expectPrice);
            $this->assertUrl($this->browser->baseUrl . $url, $this->browser->getUrl(), 'url needs to stay the same - no redirect to login page expected!');
        }
    }
}
