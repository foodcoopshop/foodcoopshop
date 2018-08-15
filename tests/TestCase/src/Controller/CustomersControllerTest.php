<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * CustomersControllerTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CustomersControllerTest extends AppCakeTestCase
{

    public $EmailLog;

    public function setUp()
    {
        parent::setUp();
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
    }

    public function testNewPasswordRequestWithWrongEmail()
    {
        $this->doPostNewPasswordRequest('this-is-no-email-address');
        $this->assertRegExpWithUnquotedString('Die E-Mail-Adresse ist nicht gültig.', $this->browser->getContent());
    }

    public function testNewPasswordRequestWithNonExistingEmail()
    {
        $this->doPostNewPasswordRequest('test@test-fcs-test.at');
        $this->assertRegExpWithUnquotedString('Wir haben diese E-Mail-Adresse nicht gefunden.', $this->browser->getContent());
    }

    public function testNewPasswordRequestWithValidEmail()
    {
        $this->doPostNewPasswordRequest(Configure::read('test.loginEmailCustomer'));
        $this->assertRegExpWithUnquotedString('Wir haben dir einen Link zugeschickt, mit dem du dein neues Passwort generieren kannst.', $this->browser->getContent());

        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[0], 'Anfrage für neues Passwort für FoodCoop Test', ['bitte klicke auf folgenden Link, um dein neues Passwort zu generieren'], [Configure::read('test.loginEmailCustomer')]);

        $customer = $this->Customer->find('all', [
            'email' => Configure::read('test.loginEmailCustomer')
        ])->first();

        $this->browser->get($this->Slug->getApproveNewPassword('non-existing-code'));
        $this->assert404NotFoundHeader();

        $this->browser->get($this->Slug->getApproveNewPassword($customer->change_password_code));
        $this->assertRegExpWithUnquotedString('Wir haben dir dein neues Passwort zugeschickt.', $this->browser->getContent());
        $this->assertUrl($this->browser->getUrl(), $this->browser->baseUrl . $this->Slug->getLogin());

        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[1], 'Neues Passwort für FoodCoop Test generiert', ['du hast gerade ein neues Passwort generiert, es lautet:'], [Configure::read('test.loginEmailCustomer')]);
        preg_match_all('/\<b\>(.*)\<\/b\>/', $emailLogs[1]->message, $matches);

        // script would break if login does not work - no complaints means login works :-)
        $this->browser->loginEmail = Configure::read('test.loginEmailCustomer');
        $this->browser->loginPassword = $matches[1][0];
        $this->browser->doFoodCoopShopLogin();
    }

    private function doPostNewPasswordRequest($email)
    {
        $this->browser->post($this->Slug->getNewPasswordRequest(), [
            'Customers' => [
                'email' => $email
            ]
        ]);
    }

    public function testRegistration()
    {
        $data = [
            'Customers' => [
                'firstname' => '',
                'lastname' => '',
                'email_order_reminder' => 1,
                'terms_of_use_accepted_date_checkbox' => 0,
                'address_customer' => [
                    'email' => '',
                    'address1' => '',
                    'address2' => '',
                    'postcode' => '',
                    'city' => '',
                    'phone_mobile' => '',
                    'phone' => ''
                ]
            ],
            'antiSpam' => 0
        ];

        // 1) check for spam protection
        $response = $this->addCustomer($data);
        $this->assertRegExpWithUnquotedString('S-p-a-m-!', $response);

        // 2) check for missing required fields
        $data['antiSpam'] = 4;
        $response = $this->addCustomer($data);
        $this->checkForMainErrorMessage($response);
        $this->assertRegExpWithUnquotedString('Bitte gib deine E-Mail-Adresse an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib deinen Vornamen an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib deinen Nachnamen an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib deine Straße an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib deinen Ort an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib deine Handynummer an.', $response);

        // 3) check for wrong data
        $data['Customers']['address_customer']['email'] = 'fcs-demo-mitglied@mailinator.com';
        $data['Customers']['address_customer']['postcode'] = 'ABCDEF';
        $data['Customers']['address_customer']['phone_mobile'] = 'adsfkjasfasfdasfajaaa';
        $data['Customers']['address_customer']['phone'] = '897++asdf+d';
        $response = $this->addCustomer($data);
        $this->checkForMainErrorMessage($response);
        $this->assertRegExpWithUnquotedString('Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.', $response);
        $this->assertRegExpWithUnquotedString('Die PLZ ist nicht gültig.', $response);
        $this->assertRegExpWithUnquotedString('Die Handynummer ist nicht gültig.', $response);
        $this->assertRegExpWithUnquotedString('Die Telefonnummer ist nicht gültig.', $response);
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die Nutzungsbedingungen.', $response);

        // 4) save user and check record
        $email = 'new-foodcoopshop-member-1@mailinator.com';
        $this->changeConfiguration('FCS_DEFAULT_NEW_MEMBER_ACTIVE', 0);
        $this->saveAndCheckValidCustomer($data, $email);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[0], 'Willkommen', ['war erfolgreich!', 'Dein Mitgliedskonto ist zwar erstellt, aber noch nicht aktiviert.'], [$email]);

        // 5) register again with changed configuration
        $this->changeConfiguration('FCS_DEFAULT_NEW_MEMBER_ACTIVE', 1);
        $this->changeConfiguration('FCS_CUSTOMER_GROUP', 4);
        $email = 'new-foodcoopshop-member-2@mailinator.com';
        $this->saveAndCheckValidCustomer($data, $email);

        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[1], 'Willkommen', ['war erfolgreich!', 'Zum Bestellen kannst du dich hier einloggen:'], [$email]);
    }

    private function saveAndCheckValidCustomer($data, $email)
    {

        $customerFirstname = '  John  ';
        $customerLastname = '<b>Doe</b>';
        $customerCity = 'Scharnstein';
        $customerAddressEmail = $email;
        $customerAddress1 = 'Mainstreet 1';
        $customerAddress2 = 'Door 4';
        $customerPostcode = '4644';
        $customerPhoneMobile = '+436989898';
        $customerPhone = '07659856565';

        $data['Customers']['firstname'] = $customerFirstname;
        $data['Customers']['lastname'] = $customerLastname;
        $data['Customers']['terms_of_use_accepted_date_checkbox'] = 1;
        $data['Customers']['address_customer']['email'] = $customerAddressEmail;
        $data['Customers']['address_customer']['city'] = $customerCity;
        $data['Customers']['address_customer']['address1'] = $customerAddress1;
        $data['Customers']['address_customer']['address2'] = $customerAddress2;
        $data['Customers']['address_customer']['postcode'] = $customerPostcode;
        $data['Customers']['address_customer']['phone_mobile'] = $customerPhoneMobile;
        $data['Customers']['address_customer']['phone'] = $customerPhone;

        $response = $this->addCustomer($data);
        $this->assertRegExpWithUnquotedString('Deine Registrierung war erfolgreich.', $response);
        $this->assertUrl($this->browser->getUrl(), $this->browser->baseUrl . '/registrierung/abgeschlossen');

        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.email' => $customerAddressEmail
            ],
            'contain' => [
                'AddressCustomers'
            ]
        ])->first();

        // check customer record
        $this->assertEquals((bool) Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE'), (bool) $customer->active, 'saving field active failed');
        $this->assertEquals((int) Configure::read('appDb.FCS_CUSTOMER_GROUP'), $customer->id_default_group, 'saving user group failed');
        $this->assertEquals($customerAddressEmail, $customer->email, 'saving field email failed');
        $this->assertEquals('John', $customer->firstname, 'saving field firstname failed');
        $this->assertEquals('Doe', $customer->lastname, 'saving field lastname failed');
        $this->assertEquals(1, $customer->email_order_reminder, 'saving field email_order_reminder failed');
        $this->assertEquals(date('Y-m-d'), $customer->terms_of_use_accepted_date->i18nFormat(Configure::read('DateFormat.Database')), 'saving field terms_of_use_accepted_date failed');

        // check address record
        $this->assertEquals('John', $customer->address_customer->firstname, 'saving field firstname failed');
        $this->assertEquals('Doe', $customer->address_customer->lastname, 'saving field lastname failed');
        $this->assertEquals($customerAddressEmail, $customer->address_customer->email, 'saving field email failed');
        $this->assertEquals($customerAddress1, $customer->address_customer->address1, 'saving field address1 failed');
        $this->assertEquals($customerAddress2, $customer->address_customer->address2, 'saving field address2 failed');
        $this->assertEquals($customerCity, $customer->address_customer->city, 'saving field city failed');
        $this->assertEquals($customerPostcode, $customer->address_customer->postcode, 'saving field postcode failed');
        $this->assertEquals($customerPhoneMobile, $customer->address_customer->phone_mobile, 'saving field phone_mobile failed');
        $this->assertEquals($customerPhone, $customer->address_customer->phone, 'saving field phone failed');
    }
    
    public function testDeleteOk()
    {
        $this->loginAsSuperadmin();
        $this->browser->ajaxPost('/admin/customers/delete/' . Configure::read('test.customerId'), [
            'referer' => '/'
        ]);
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => Configure::read('test.customerId')
            ]
        ])->first();
        $this->assertEmpty($customer);
    }

    private function checkForMainErrorMessage($response)
    {
        $this->assertRegExpWithUnquotedString(__('Errors_while_saving!'), $response);
    }

    /**
     * @param array $data
     * @return string
     */
    private function addCustomer($data)
    {
        $this->browser->post($this->Slug->getRegistration(), $data);
        return $this->browser->getContent();
    }
}
