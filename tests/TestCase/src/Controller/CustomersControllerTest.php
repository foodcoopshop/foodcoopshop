<?php
declare(strict_types=1);

/**
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use App\Model\Entity\Customer;

class CustomersControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function testExportCustomers(): void
    {
        $this->loginAsSuperadmin();
        $this->get('/admin/customers/export?active=1');

        $this->assertResponseOk();
        $this->assertResponseContains('Id;Name;PLZ;Ort;"Straße + Nummer";Adresszusatz;Handy;Telefon;Gruppe;E-Mail;Status;Guthaben;Bestell-Erinnerung;Guthaben-Erinnerung;Reg.-Datum;"Letzter Abholtag";Kommentar');
        $this->assertResponseContains('87;"Demo Mitglied";4644;Scharnstein;"Demostrasse 4";;0664/000000000;;Mitglied;fcs-demo-mitglied@mailinator.com;1;100.000,00;1;1;02.12.2014;;');
    }

    public function testInfoChannelColumnsAndFilters(): void
    {
        $this->changeConfiguration('FCS_INFO_SIGNAL_ENABLED', 1);
        $this->changeConfiguration('FCS_INFO_WHATSAPP_ENABLED', 1);
        $this->changeCustomer(Configure::read('test.customerId'), 'info_signal_enabled', 1);
        $this->changeCustomer(Configure::read('test.adminId'), 'info_whatsapp_enabled', 1);
        $this->loginAsSuperadmin();
        $this->get('/admin/customers?info_signal=1');
        $this->assertResponseOk();
        $this->assertResponseContains('Customers.info_signal_enabled');
        $this->assertResponseContains('Customers.info_whatsapp_enabled');
        $this->assertResponseContains('name="info_signal"');
        $this->assertResponseContains('name="info_whatsapp"');
        $this->assertResponseContains('data-customer-id="87"');
        $this->assertResponseNotContains('data-customer-id="88"');
        $this->assertResponseNotContains('data-customer-id="92"');

        $this->get('/admin/customers?info_signal=0&info_whatsapp=1&sort=Customers.info_whatsapp_enabled&direction=desc');
        $this->assertResponseOk();
        $this->assertResponseContains('data-customer-id="88"');
        $this->assertResponseNotContains('data-customer-id="87"');
        $this->assertResponseNotContains('data-customer-id="92"');

        $this->get('/admin/customers?info_signal=&info_whatsapp=');
        $this->assertResponseOk();
        $this->assertResponseContains('data-customer-id="87"');
        $this->assertResponseContains('data-customer-id="88"');
        $this->assertResponseContains('data-customer-id="92"');
    }

    public function testInfoChannelConfigurationSwitchesAreIndependent(): void
    {
        $this->changeConfiguration('FCS_INFO_SIGNAL_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->get('/admin/customers?info_whatsapp=1');
        $this->assertResponseOk();
        $this->assertResponseContains('Customers.info_signal_enabled');
        $this->assertResponseNotContains('Customers.info_whatsapp_enabled');
        $this->assertResponseContains('name="info_signal"');
        $this->assertResponseNotContains('name="info_whatsapp"');
        $this->assertResponseContains('btn-clipboard-mobile');
        $this->assertResponseContains('data-mobile="0664/000000000"');
        $this->assertResponseContains('data-customer-id="87"');

        $this->changeConfiguration('FCS_INFO_SIGNAL_ENABLED', 0);
        $this->changeConfiguration('FCS_INFO_WHATSAPP_ENABLED', 1);
        $this->get('/admin/customers?info_signal=1');
        $this->assertResponseOk();
        $this->assertResponseNotContains('Customers.info_signal_enabled');
        $this->assertResponseContains('Customers.info_whatsapp_enabled');
        $this->assertResponseNotContains('name="info_signal"');
        $this->assertResponseContains('name="info_whatsapp"');
        $this->assertResponseContains('btn-clipboard-mobile');
        $this->assertResponseContains('data-mobile="0664/000000000"');
        $this->assertResponseContains('data-customer-id="87"');
    }

    public function testProfileInfoChannelVisibilityMatchesConfiguration(): void
    {
        $this->loginAsCustomer();
        foreach ([[0, 0,], [1, 0,], [0, 1,], [1, 1,],] as [$signalEnabled, $whatsappEnabled]) {
            $this->changeConfiguration('FCS_INFO_SIGNAL_ENABLED', $signalEnabled);
            $this->changeConfiguration('FCS_INFO_WHATSAPP_ENABLED', $whatsappEnabled);
            $this->get($this->Slug->getCustomerProfile());
            $this->assertResponseOk();
            foreach (['signal' => $signalEnabled, 'whatsapp' => $whatsappEnabled,] as $channel => $enabled) {
                $field = 'name="Customers[info_' . $channel . '_enabled]"';
                if ($enabled) {
                    $this->assertResponseContains($field);
                } else {
                    $this->assertResponseNotContains($field);
                }
            }
        }
    }

    public function testInfoChannelsEditableInOwnProfile(): void
    {
        $this->changeConfiguration('FCS_INFO_SIGNAL_ENABLED', 1);
        $this->changeConfiguration('FCS_INFO_WHATSAPP_ENABLED', 1);
        $this->loginAsCustomer();
        $this->get($this->Slug->getCustomerProfile());
        $this->assertResponseOk();
        $this->assertResponseContains('name="Customers[info_signal_enabled]"');
        $this->assertResponseContains('name="Customers[info_whatsapp_enabled]"');

        $customersTable = $this->getTableLocator()->get('Customers');
        $customerId = Configure::read('test.customerId');
        $data = [
            'Customers' => [
                'firstname' => 'Demo',
                'lastname' => 'Mitglied',
                'info_signal_enabled' => 1,
                'info_whatsapp_enabled' => 0,
                'address_customer' => ['email' => Configure::read('test.loginEmailCustomer'),],
            ],
            'referer' => '/',
        ];
        $this->post($this->Slug->getCustomerProfile(), $data);
        $this->assertResponseCode(302);
        $customer = $customersTable->get($customerId);
        $this->assertTrue((bool) $customer->info_signal_enabled);
        $this->assertFalse((bool) $customer->info_whatsapp_enabled);

        $this->get($this->Slug->getCustomerProfile());
        $this->assertMatchesRegularExpression('/<input(?=[^>]*name="Customers\[info_signal_enabled\]")(?=[^>]*checked)[^>]*>/', (string) $this->_response->getBody());
        $data['Customers']['info_signal_enabled'] = 0;
        $data['Customers']['info_whatsapp_enabled'] = 1;
        $this->post($this->Slug->getCustomerProfile(), $data);
        $this->assertResponseCode(302);
        $customer = $customersTable->get($customerId);
        $this->assertFalse((bool) $customer->info_signal_enabled);
        $this->assertTrue((bool) $customer->info_whatsapp_enabled);
    }

    public function testInfoChannelDescriptionsInProfile(): void
    {
        $this->changeConfiguration('FCS_INFO_SIGNAL_ENABLED', 1);
        $this->changeConfiguration('FCS_INFO_WHATSAPP_ENABLED', 1);
        $this->loginAsCustomer();
        $this->get($this->Slug->getCustomerProfile());
        $this->assertResponseOk();
        $this->assertResponseContains('<span class="after small">Ich möchte Informationen über Signal erhalten.</span>');
        $this->assertResponseContains('<span class="after small">Ich möchte Informationen über WhatsApp erhalten.</span>');
    }

    public function testGetCustomersForDropdownAsSuperadminWithAllManufacturers(): void
    {
        $this->loginAsSuperadmin();
        $this->ajaxGet('/admin/customers/getCustomersForDropdown/1');
        $response = $this->getJsonDecodedResponse();
        $expectedDropdownData = json_decode(json_encode([
            ['value' => '', 'text' => 'alle Mitglieder'],
            ['label' => 'Mitglieder: aktiv', 'options' => [
                ['value' => '88', 'text' => 'Demo Admin'],
                ['value' => '87', 'text' => 'Demo Mitglied'],
                ['value' => '92', 'text' => 'Demo Superadmin'],
            ]],
            ['label' => 'Hersteller: aktiv', 'options' => [
                ['value' => '91', 'text' => 'Demo Fleisch-Hersteller'],
                ['value' => '89', 'text' => 'Demo Gemüse-Hersteller'],
                ['value' => '90', 'text' => 'Demo Milch-Hersteller'],
            ]],
            ['label' => 'Mitglieder: inaktiv', 'options' => [
                ['value' => '93', 'text' => 'Demo SB-Kunde'],
            ]],
        ]));
        $this->assertEquals($expectedDropdownData, $response->dropdownData);
    }

    public function testGetCustomersForDropdownAsCustomerWithAllManufacturers(): void
    {
        $this->loginAsCustomer();
        $this->ajaxGet('/admin/customers/getCustomersForDropdown/1');
        $response = $this->getJsonDecodedResponse();
        $expectedDropdownData = json_decode(json_encode([
            ['value' => '', 'text' => 'alle Mitglieder'],
            ['label' => 'Mitglieder: aktiv', 'options' => [
                ['value' => '87', 'text' => 'Demo Mitglied'],
            ]],
        ]));
        $this->assertEquals($expectedDropdownData, $response->dropdownData);
    }

    public function testEditGroupAsSuperadmin(): void
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $this->loginAsSuperadmin();
        $customerId = Configure::read('test.customerId');
        $this->ajaxPost('/admin/customers/editGroup', [
            'customerId' => $customerId,
            'groupId' => Customer::GROUP_ADMIN,
        ]);
        $customer = $customersTable->find('all',
            conditions: [
                'Customers.id_customer' => $customerId,
            ],
        )->first();
        $this->assertEquals(Customer::GROUP_ADMIN, $customer->id_default_group);
    }

    public function testEditGroupAsAdmin(): void
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $this->loginAsAdmin();
        $customerId = Configure::read('test.customerId');
        $this->ajaxPost('/admin/customers/editGroup', [
            'customerId' => $customerId,
            'groupId' => Customer::GROUP_SUPERADMIN,
        ]);
        $customer = $customersTable->find('all',
            conditions: [
                'Customers.id_customer' => $customerId,
            ],
        )->first();
        $this->assertEquals(Customer::GROUP_MEMBER, $customer->id_default_group);
    }

    public function testCustomerEdit(): void
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $this->loginAsSuperadmin();
        $data = [
            'Customers' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'address_customer' => [
                    'email' => Configure::read('test.loginEmailSuperadmin'),
                    'postcode' => 5555,
                    'city' => 'Demo City',
                    'phone_mobile' => '06604343434',
                    'phone' => '06604343434',
                    'address2' => 'Top 2',
                ],
            ],
            'referer' => '/',
        ];
        $this->post($this->Slug->getCustomerEdit(Configure::read('test.superadminId')), $data);

        $customer = $customersTable->find('all',
            conditions: [
                'Customers.id_customer' => Configure::read('test.superadminId'),
            ],
            contain: [
                'AddressCustomers',
            ],
        )->first();

        $this->assertEquals($data['Customers']['firstname'], $customer->firstname);
        $this->assertEquals($data['Customers']['lastname'], $customer->lastname);
        $this->assertEquals($data['Customers']['address_customer']['email'], $customer->email);
        $this->assertEquals($data['Customers']['address_customer']['postcode'], $customer->address_customer->postcode);
        $this->assertEquals($data['Customers']['address_customer']['address2'], $customer->address_customer->address2);
        $this->assertEquals($data['Customers']['address_customer']['city'], $customer->address_customer->city);
        $this->assertEquals($data['Customers']['address_customer']['phone'], $customer->address_customer->phone);
        $this->assertEquals($data['Customers']['address_customer']['phone_mobile'], $customer->address_customer->phone_mobile);

    }

    public function testChangePasswordNotOk(): void
    {

        $this->loginAsSuperadmin();

        $data = [
            'Customers' => [
                'passwd_old' => 'test',
                'passwd_1' => '1234567',
                'passwd_2' => '123',
            ],
        ];
        $this->post($this->Slug->getChangePassword(), $data);
        $this->assertResponseContains('Dein altes Passwort ist leider falsch.');
        $this->assertResponseContains('Die Passwörter stimmen nicht überein.');
        $this->assertResponseContains('Das Passwort muss aus mindestens 8 Zeichen bestehen.');

    }

    public function testChangePasswordOk(): void
    {

        $this->loginAsSuperadmin();

        $customersTable = $this->getTableLocator()->get('Customers');
        $oldCustomer = $customersTable->find('all',
        conditions: [
            'Customers.id_customer' => Configure::read('test.superadminId'),
        ],
        )->first();

        $newPassword = '12345678';
        $data = [
            'Customers' => [
                'passwd_old' => 'foodcoopshop',
                'passwd_1' => $newPassword,
                'passwd_2' => $newPassword,
            ],
        ];
        $this->post($this->Slug->getChangePassword(), $data);
        $this->assertFlashMessage('Dein neues Passwort wurde erfolgreich gespeichert.');

        $newCustomer = $customersTable->find('all',
            conditions: [
                'Customers.id_customer' => Configure::read('test.superadminId'),
            ],
        )->first();

        $this->assertNotEquals($oldCustomer->passwd, $newCustomer->passwd);

    }

}
