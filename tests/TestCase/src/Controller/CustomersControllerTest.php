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

    public function testInfoChannelsDisplayedInConfigurations(): void
    {
        $this->loginAsSuperadmin();
        foreach (['1,2,3' => 'WhatsApp, Signal, SMS', '2' => 'Signal', '' => 'Keiner',] as $channels => $label) {
            $this->changeConfiguration('FCS_INFO_CHANNELS', (string) $channels);
            $this->get('/admin/configurations');
            $this->assertResponseOk();
            $this->assertResponseContains('<td>' . $label . '</td>');
        }
    }

    public function testInfoChannelColumnsAndFilters(): void
    {
        $this->changeConfiguration('FCS_INFO_CHANNELS', '1,2,3');
        $this->changeCustomer(Configure::read('test.customerId'), 'info_channel', 2);
        $this->changeCustomer(Configure::read('test.adminId'), 'info_channel', 1);
        $this->loginAsSuperadmin();
        $this->get('/admin/customers?info_channel=2');
        $this->assertResponseOk();
        $this->assertResponseContains('Customers.info_channel');
        $this->assertResponseContains('name="info_channel"');
        $this->assertResponseContains('<td class="center">Signal</td>');
        $this->assertResponseContains('data-customer-id="87"');
        $this->assertResponseNotContains('data-customer-id="88"');
        $this->assertResponseNotContains('data-customer-id="92"');

        $this->get('/admin/customers?info_channel=1&sort=Customers.info_channel&direction=desc');
        $this->assertResponseOk();
        $this->assertResponseContains('data-customer-id="88"');
        $this->assertResponseNotContains('data-customer-id="87"');
        $this->assertResponseNotContains('data-customer-id="92"');

        $this->get('/admin/customers?info_channel=0');
        $this->assertResponseOk();
        $this->assertResponseNotContains('<td class="center">Keiner</td>');
        $this->assertResponseContains('<option value="0" selected="selected">---</option>');
        $this->assertResponseContains('data-customer-id="92"');
        $this->assertResponseNotContains('data-customer-id="87"');
        $this->assertResponseNotContains('data-customer-id="88"');

        $this->get('/admin/customers?info_channel=');
        $this->assertResponseOk();
        $this->assertResponseContains('data-customer-id="87"');
        $this->assertResponseContains('data-customer-id="88"');
        $this->assertResponseContains('data-customer-id="92"');
    }

    public function testInfoChannelHiddenWhenNoChannelsConfigured(): void
    {
        $this->loginAsSuperadmin();
        $this->get('/admin/customers?info_channel=1');
        $this->assertResponseOk();
        $this->assertResponseNotContains('Customers.info_channel');
        $this->assertResponseNotContains('name="info_channel"');
        $this->assertResponseNotContains('btn-clipboard-mobile');
        $this->assertResponseContains('data-customer-id="87"');

        $this->changeConfiguration('FCS_INFO_CHANNELS', '3');
        $this->get('/admin/customers');
        $this->assertResponseOk();
        $this->assertResponseContains('Customers.info_channel');
        $this->assertResponseContains('name="info_channel"');
        $this->assertResponseContains('btn-clipboard-mobile');
        $this->assertResponseContains('data-mobile="0664/000000000"');
    }

    public function testProfileInfoChannelVisibilityMatchesConfiguration(): void
    {
        $this->loginAsCustomer();
        foreach (['', '1', '2', '3', '1,2,3',] as $channels) {
            $this->changeConfiguration('FCS_INFO_CHANNELS', $channels);
            $this->get($this->Slug->getCustomerProfile());
            $this->assertResponseOk();
            if ($channels === '') {
                $this->assertResponseNotContains('name="Customers[info_channel]"');
                continue;
            }
            $this->assertResponseContains('name="Customers[info_channel]"');
            foreach (Customer::CHANNEL_TYPE as $channel => $label) {
                $option = '<option value="' . $channel . '">' . $label . '</option>';
                if (in_array((string) $channel, explode(',', $channels), true)) {
                    $this->assertResponseContains($option);
                } else {
                    $this->assertResponseNotContains($option);
                }
            }
        }
    }

    public function testInfoChannelsEditableInOwnProfile(): void
    {
        $this->changeConfiguration('FCS_INFO_CHANNELS', '1,2,3');
        $this->loginAsCustomer();
        $this->get($this->Slug->getCustomerProfile());
        $this->assertResponseOk();
        $this->assertResponseContains('name="Customers[info_channel]"');

        $customersTable = $this->getTableLocator()->get('Customers');
        $customerId = Configure::read('test.customerId');
        $data = [
            'Customers' => [
                'firstname' => 'Demo',
                'lastname' => 'Mitglied',
                'info_channel' => 2,
                'address_customer' => ['email' => Configure::read('test.loginEmailCustomer'),],
            ],
            'referer' => '/',
        ];
        $this->post($this->Slug->getCustomerProfile(), $data);
        $this->assertResponseCode(302);
        $customer = $customersTable->get($customerId);
        $this->assertEquals(2, $customer->info_channel);

        $this->get($this->Slug->getCustomerProfile());
        $this->assertResponseContains('<option value="2" selected="selected">Signal</option>');
        foreach ([1, 3, 0,] as $channel) {
            $data['Customers']['info_channel'] = $channel;
            $this->post($this->Slug->getCustomerProfile(), $data);
            $this->assertResponseCode(302);
            $this->assertEquals($channel, $customersTable->get($customerId)->info_channel);
        }

        $this->changeConfiguration('FCS_INFO_CHANNELS', '1');
        $data['Customers']['info_channel'] = 2;
        $this->post($this->Slug->getCustomerProfile(), $data);
        $this->assertResponseContains('Der Infokanal ist nicht gültig.');
        $this->assertEquals(0, $customersTable->get($customerId)->info_channel);
    }

    public function testInfoChannelDescriptionsInProfile(): void
    {
        $this->changeConfiguration('FCS_INFO_CHANNELS', '1,2,3');
        $this->loginAsCustomer();
        $this->get($this->Slug->getCustomerProfile());
        $this->assertResponseOk();
        $this->assertResponseContains('<span class="after small">Ich möchte Informationen über diesen Kanal erhalten.</span>');
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
