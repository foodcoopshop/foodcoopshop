<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.3
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;

class ConfigurationsControllerTest extends AppCakeTestCase
{

    /**
     * needs to login as superadmin and logs user out automatically
     * eventually create a new httpClient instance for this method
     *
     * @param string $configKey
     * @param string $newValue
     */
    protected function changeConfigurationEditForm($configKey, $newValue)
    {
        $this->loginAsSuperadmin();
        $configuration = $this->Configuration->find('all', [
            'conditions' => [
                'Configurations.active' => APP_ON,
                'Configurations.name' => $configKey
            ]
        ])->first();
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->post('/admin/configurations/edit/'.$configuration->id_configuration, [
           'Configurations' => [
               'value' => $newValue
           ],
           'referer' => '/'
        ]);
    }

    public function testConfigurationEditFormFcsCustomerGroupOk()
    {
        $this->changeConfigurationEditForm('FCS_CUSTOMER_GROUP', CUSTOMER_GROUP_ADMIN);
        $this->assertRegExpWithUnquotedString('Die Einstellung wurde erfolgreich geändert.', $this->httpClient->getContent());
    }

    public function testConfigurationEditFormFcsCustomerGroupInvalidId()
    {
        $this->changeConfigurationEditForm('FCS_CUSTOMER_GROUP', 44);
        $this->assertRegExpWithUnquotedString('Bitte gib eine Zahl zwischen 3 und 4 an.', $this->httpClient->getContent());
    }

    public function testConfigurationEditFormFcsAppNameEmpty()
    {
        $this->changeConfigurationEditForm('FCS_APP_NAME', '');
        $this->assertRegExpWithUnquotedString('Bitte gib den Namen der Foodcoop an.', $this->httpClient->getContent());
    }

    public function testConfigurationEditFormFcsAppNameNotEnoughChars()
    {
        $this->changeConfigurationEditForm('FCS_APP_NAME', 'Bla');
        $this->assertRegExpWithUnquotedString('Die Anzahl der Zeichen muss zwischen 5 und 255 liegen.', $this->httpClient->getContent());
    }

    public function testConfigurationEditFormFcsRegistrationEmailTextStripTags()
    {
        $configurationName = 'FCS_REGISTRATION_EMAIL_TEXT';
        $newValue = '<b>HalloHallo</b>';
        $this->changeConfigurationEditForm($configurationName, $newValue);
        $this->assertRegExpWithUnquotedString('Die Einstellung wurde erfolgreich geändert.', $this->httpClient->getContent());
        $configuration = $this->Configuration->find('all', [
            'conditions' => [
                'Configurations.name' => $configurationName
            ]
        ])->first();
        $this->assertEquals($configuration->value, $newValue, 'html tags stripped');
    }

    public function testConfigurationEditFormFcsAppNameStripTags()
    {
        $this->changeConfigurationEditForm('FCS_APP_NAME', '<b>HalloHallo</b>');
        $this->assertRegExpWithUnquotedString('Die Einstellung wurde erfolgreich geändert.', $this->httpClient->getContent());
        $configuration = $this->Configuration->find('all', [
            'conditions' => [
                'Configurations.name' => 'FCS_APP_NAME'
            ]
        ])->first();
        $this->assertEquals($configuration->value, 'HalloHallo', 'html tags not stripped');
    }

    public function testShowProductsForGuestsEnabledAndLoggedOut()
    {
        $this->changeConfiguration('FCS_SHOW_PRODUCTS_FOR_GUESTS', 1);
        $this->assertShowProductForGuestsEnabledOrLoggedIn($this->getTestUrlsForShowProductForGuests(), false);
    }

    public function testConfigurationEditFormFcsGlobalDeliveryBreak()
    {
        $this->changeConfigurationEditForm('FCS_NO_DELIVERY_DAYS_GLOBAL', ['2018-02-02']);
        $this->assertRegExpWithUnquotedString('Für die folgenden Liefertag(e) sind bereits Bestellungen vorhanden: 02.02.2018 (3x).', $this->httpClient->getContent());
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
            $this->httpClient->get($url);
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
            $this->httpClient->get($url);
            $priceRegExp = '<div class="price">';
            $priceAssertFunction = 'assertRegExpWithUnquotedString';
            if (!$expectPrice) {
                $priceAssertFunction = 'assertDoesNotMatchRegularExpressionWithUnquotedString';
            }
            $this->{$priceAssertFunction}($priceRegExp, $this->httpClient->getContent(), 'price expected: ' . $expectPrice);
        }
    }
}
