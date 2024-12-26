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

    public function testExportCustomers() {
        $this->loginAsSuperadmin();
        $this->get('/admin/customers/export?active=1');

        $this->assertResponseOk();
        $this->assertResponseContains('Id;Name;PLZ;Ort;"Straße + Nummer";Adresszusatz;Handy;Telefon;Gruppe;E-Mail;Status;Guthaben;Bestell-Erinnerung;Guthaben-Aufladung-Erinnerung;Reg.-Datum;"Letzter Abholtag";Kommentar');
        $this->assertResponseContains('87;"Demo Mitglied";4644;Scharnstein;"Demostrasse 4";;0664/000000000;;Mitglied;fcs-demo-mitglied@mailinator.com;1;100.000,00;1;1;02.12.2014;;');
    }

    public function testGetCustomersForDropdownAsSuperadminWithAllManufacturers()
    {
        $this->loginAsSuperadmin();
        $this->ajaxGet('/admin/customers/getCustomersForDropdown/1');
        $response = $this->getJsonDecodedContent();
        $expectedHtml = '<option value="">alle Mitglieder</option><optgroup label="Mitglieder: aktiv"><option value="88">Demo Admin</option><option value="87">Demo Mitglied</option><option value="92">Demo Superadmin</option></optgroup><optgroup label="Hersteller: aktiv"><option value="91">Demo Fleisch-Hersteller</option><option value="89">Demo Gemüse-Hersteller</option><option value="90">Demo Milch-Hersteller</option></optgroup><optgroup label="Mitglieder: inaktiv"><option value="93">Demo SB-Kunde</option></optgroup>';
        $this->assertEquals($expectedHtml, $response->dropdownData);
    }

    public function testGetCustomersForDropdownAsCustomerWithAllManufacturers()
    {
        $this->loginAsCustomer();
        $this->ajaxGet('/admin/customers/getCustomersForDropdown/1');
        $response = $this->getJsonDecodedContent();
        $expectedHtml = '<option value="">alle Mitglieder</option><optgroup label="Mitglieder: aktiv"><option value="87">Demo Mitglied</option></optgroup>';
        $this->assertEquals($expectedHtml, $response->dropdownData);
    }

    public function testEditGroupAsSuperadmin()
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

    public function testEditGroupAsAdmin()
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

    public function testCustomerEdit()
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

    public function testChangePasswordNotOk()
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

    public function testChangePasswordOk()
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
