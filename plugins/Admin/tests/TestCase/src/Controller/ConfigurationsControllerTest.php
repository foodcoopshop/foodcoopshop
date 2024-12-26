<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.3
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\AssertPagesForErrorsTrait;
use App\Test\TestCase\Traits\LoginTrait;

class ConfigurationsControllerTest extends AppCakeTestCase
{

    use AssertPagesForErrorsTrait;
    use AppIntegrationTestTrait;
    use LoginTrait;

    /**
     * needs to login as superadmin and logs user out automatically
     */
    protected function changeConfigurationEditForm(string $configKey, string|array $newValue)
    {
        $this->loginAsSuperadmin();
        $configurationsTable = $this->getTableLocator()->get('Configurations');
        $configuration = $configurationsTable->find('all',
            conditions: [
                'Configurations.active' => APP_ON,
                'Configurations.name' => $configKey
            ]
        )->first();
        $this->post('/admin/configurations/edit/'.$configuration->name, [
           'Configurations' => [
               'value' => $newValue
           ],
           'referer' => '/'
        ]);
    }

    public function testConfigurationEditFormFcsAppNameEmpty()
    {
        $this->changeConfigurationEditForm('FCS_APP_NAME', '');
        $this->assertResponseContains('Bitte gib den Namen der Foodcoop an.');
    }

    public function testConfigurationEditFormFcsAppNameNotEnoughChars()
    {
        $this->changeConfigurationEditForm('FCS_APP_NAME', 'Bla');
        $this->assertResponseContains('Die Anzahl der Zeichen muss zwischen 5 und 255 liegen.');
    }

    public function testConfigurationEditFormFcsRegistrationEmailTextStripTags()
    {
        $configurationName = 'FCS_REGISTRATION_EMAIL_TEXT';
        $newValue = '<b>HalloHallo</b>';
        $this->changeConfigurationEditForm($configurationName, $newValue);
        $this->assertFlashMessage('Die Einstellung wurde erfolgreich geändert.');
        $configurationsTable = $this->getTableLocator()->get('Configurations');
        $configuration = $configurationsTable->find('all',
            conditions: [
                'Configurations.name' => $configurationName
            ]
        )->first();
        $this->assertEquals($configuration->value, $newValue, 'html tags stripped');
    }

    public function testConfigurationEditFormFcsAppNameStripTags()
    {
        $this->changeConfigurationEditForm('FCS_APP_NAME', '<b>HalloHallo</b>');
        $this->assertFlashMessage('Die Einstellung wurde erfolgreich geändert.');
        $configurationsTable = $this->getTableLocator()->get('Configurations');
        $configuration = $configurationsTable->find('all',
            conditions: [
                'Configurations.name' => 'FCS_APP_NAME'
            ]
        )->first();
        $this->assertEquals($configuration->value, 'HalloHallo', 'html tags not stripped');
    }

    public function testShowProductsForGuestsEnabledAndLoggedOut()
    {
        $this->changeConfiguration('FCS_SHOW_PRODUCTS_FOR_GUESTS', 1);
        $this->assertShowProductForGuestsEnabledOrLoggedIn($this->getTestUrlsForShowProductForGuests(), false);
    }

    public function testConfigurationEditFormFcsGlobalDeliveryBreak()
    {
        $this->changeConfigurationEditForm('FCS_NO_DELIVERY_DAYS_GLOBAL', ['2018-02-02','2018-02-09']);
        $this->assertResponseContains('Für die folgenden Liefertag(e) sind bereits Bestellungen vorhanden: 02.02.2018 (3x).');
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
            $this->get($url);
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
            $this->get($url);
            $priceRegExp = '<div class="price" title=';
            $priceAssertFunction = 'assertRegExpWithUnquotedString';
            if (!$expectPrice) {
                $priceAssertFunction = 'assertDoesNotMatchRegularExpressionWithUnquotedString';
            }
            $this->{$priceAssertFunction}($priceRegExp, $this->_response->getBody()->__toString(), 'price expected: ' . $expectPrice);
        }
    }
}
