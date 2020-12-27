<?php

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestEmailTransport;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CustomersFrontendControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    private function setUpProfileImageTests()
    {
        $profileImageSrcFileAndPath = WWW_ROOT . '/img/tests/test-image.jpg';
        $profileImageTargetFilename = Configure::read('test.customerId') . '-small.jpg';
        copy($profileImageSrcFileAndPath, Configure::read('app.customerImagesDir') . '/' . $profileImageTargetFilename);
        return $profileImageTargetFilename;
    }

    private function tearDownProfileImageTests($profileImageTargetFilename)
    {
        unlink(Configure::read('app.customerImagesDir') . '/' . $profileImageTargetFilename);

    }

    public function testMaxlengthAttributeExistsInRegistrationPage()
    {
        $this->get($this->Slug->getLogin());
        $this->assertResponseContains('maxlength="32"');
    }

    public function testProfileImagePrivacyForGuests()
    {
        $profileImageTargetFilename = $this->setUpProfileImageTests();
        $imageSrc = '/photos/profile-images/customers/' . $profileImageTargetFilename;
        $this->get($imageSrc);
        $this->assertResponseCode(404);
        $this->tearDownProfileImageTests($profileImageTargetFilename);
    }

    public function testProfileImagePrivacyForManufacturers()
    {
        $profileImageTargetFilename = $this->setUpProfileImageTests();
        $imageSrc = '/photos/profile-images/customers/' . $profileImageTargetFilename;
        $this->loginAsMeatManufacturer();
        $this->get($imageSrc);
        $this->assertResponseCode(404);
        $this->tearDownProfileImageTests($profileImageTargetFilename);
    }

    public function testProfileImagePrivacyForSuperadmins()
    {
        $profileImageTargetFilename = $this->setUpProfileImageTests();
        $imageSrc = '/photos/profile-images/customers/' . $profileImageTargetFilename;
        $this->loginAsSuperadmin();
        $this->get($imageSrc);
        $this->assertResponseOk();
        $this->assertContentType('image/jpeg');
        $this->tearDownProfileImageTests($profileImageTargetFilename);
    }

    public function testProfileImagePrivacyForDeletedMember()
    {
        $this->resetCustomerCreditBalance();
        $profileImageTargetFilename = $this->setUpProfileImageTests();
        $imageSrc = '/photos/profile-images/customers/' . $profileImageTargetFilename;

        $this->loginAsSuperadmin();
        $this->ajaxPost('/admin/customers/delete/' . Configure::read('test.customerId'), [
            'referer' => '/'
        ]);

        $this->get($imageSrc);
        $this->assertResponseCode(404);
    }

    public function testNewPasswordRequestWithWrongEmail()
    {
        $this->doPostNewPasswordRequest('this-is-no-email-address');
        $this->assertResponseContains('Die E-Mail-Adresse ist nicht gültig.');
    }

    public function testNewPasswordRequestWithNonExistingEmail()
    {
        $this->doPostNewPasswordRequest('test@test-fcs-test.at');
        $this->assertResponseContains('Wir haben diese E-Mail-Adresse nicht gefunden.');
    }

    public function testNewPasswordRequestWithValidEmail()
    {
        $this->doPostNewPasswordRequest(Configure::read('test.loginEmailCustomer'));
        $this->assertFlashMessage('Wir haben dir per E-Mail ein neues Passwort zugeschickt, es muss aber noch aktiviert werden.');

        $customer = $this->Customer->find('all', [
            'email' => Configure::read('test.loginEmailCustomer')
        ])->first();

        $this->get($this->Slug->getActivateNewPassword('non-existing-code'));
        $this->assertFlashMessage('Dein neues Passwort wurde bereits aktiviert oder der Aktivierungscode war nicht gültig.');

        $this->get($this->Slug->getActivateNewPassword($customer->activate_new_password_code));
        $this->assertFlashMessage('Dein neues Passwort wurde erfolgreich aktiviert und du bist bereits eingeloggt.');

        $this->assertMailSubjectContainsAt(0, 'Neues Passwort für FoodCoop Test');
        $this->assertMailContainsHtmlAt(0, 'Bitte klicke auf folgenden Link, um dein neues Passwort zu aktivieren');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailCustomer'));

        preg_match_all('/\<b\>(.*)\<\/b\>/', TestEmailTransport::getMessages()[0]->getBodyHtml(), $matches);

        $this->post($this->Slug->getLogin(), [
            'email' => Configure::read('test.loginEmailCustomer'),
            'passwd' => $matches[1][0],
        ]);
        $this->assertResponseCode(302); // if password is wrong, response code is 200
    }

    private function doPostNewPasswordRequest($email)
    {
        $this->post($this->Slug->getNewPasswordRequest(), [
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
            ]
        ];

        // 1) check for spam protection
        $this->addCustomer($data);
        $this->assertFlashMessage('S-p-a-m-!');

        // 2) check for missing required fields
        $data['antiSpam'] = 4;
        $this->addCustomer($data);
        $this->assertResponseContains('Bitte gib deine E-Mail-Adresse an.');
        $this->assertResponseContains('Bitte gib deinen Vornamen an.');
        $this->assertResponseContains('Bitte gib deinen Nachnamen an.');
        $this->assertResponseContains('Bitte gib deine Straße an.');
        $this->assertResponseContains('Bitte gib deinen Ort an.');
        $this->assertResponseContains('Bitte gib deine Handynummer an.');

        // 3) check for wrong data
        $data['Customers']['address_customer']['email'] = 'fcs-demo-mitglied@mailinator.com';
        $data['Customers']['address_customer']['postcode'] = 'ABCDEF';
        $data['Customers']['address_customer']['phone_mobile'] = 'adsfkjasfasfdasfajaaa';
        $data['Customers']['address_customer']['phone'] = '897++asdf+d';
        $this->addCustomer($data);
        $this->assertResponseContains('Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.');
        $this->assertResponseContains('Die PLZ ist nicht gültig.');
        $this->assertResponseContains('Die Handynummer ist nicht gültig.');
        $this->assertResponseContains('Die Telefonnummer ist nicht gültig.');
        $this->assertResponseContains('Bitte akzeptiere die Nutzungsbedingungen.');

        // 4) save user and check record
        $email = 'new-foodcoopshop-member-1@mailinator.com';
        $this->changeConfiguration('FCS_DEFAULT_NEW_MEMBER_ACTIVE', 0);
        $this->saveAndCheckValidCustomer($data, $email);

        $this->assertMailSubjectContainsAt(0, 'Willkommen');
        $this->assertMailContainsHtmlAt(0, 'war erfolgreich!');
        $this->assertMailContainsHtmlAt(0, 'Dein Mitgliedskonto ist zwar erstellt, aber noch nicht aktiviert.');
        $this->assertMailSentToAt(0, $email);

        $this->assertMailSubjectContainsAt(1, 'Neue Registrierung: John Doe');
        $this->assertMailContainsHtmlAt(1, 'Es gab gerade eine neue Registrierung: <b>John Doe</b>');
        $this->assertMailSentToAt(1, 'fcs-demo-superadmin@mailinator.com');

        // 5) register again with changed configuration
        $this->changeConfiguration('FCS_DEFAULT_NEW_MEMBER_ACTIVE', 1);
        $this->changeConfiguration('FCS_CUSTOMER_GROUP', 4);
        $email = 'new-foodcoopshop-member-2@mailinator.com';
        $this->saveAndCheckValidCustomer($data, $email);

        $this->assertMailSubjectContainsAt(2, 'Willkommen');
        $this->assertMailContainsHtmlAt(2, 'war erfolgreich!');
        $this->assertMailContainsHtmlAt(2, 'Zum Bestellen kannst du dich hier einloggen:');
        $this->assertMailSentToAt(2, $email);

        $this->assertMailSubjectContainsAt(3, 'Neue Registrierung: John Doe');
        $this->assertMailContainsHtmlAt(3, 'Es gab gerade eine neue Registrierung: <b>John Doe</b>');
        $this->assertMailSentToAt(3, 'fcs-demo-superadmin@mailinator.com');

    }

    public function testLoginPageWithOutputStringReplacements()
    {
        Configure::write('app.outputStringReplacements',
           include(APP . 'Lib' . DS . 'OutputFilter' . DS . 'config' . DS . 'de_DE' . DS . 'memberClientConfig.php'),
        );
        $this->get($this->Slug->getLogin());
        $this->assertResponseContains('Kundenkonto erstellen');
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

        $this->addCustomer($data);
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

    public function testDeleteWithNotYetBilledOrdersAndNotEqualPayments()
    {

        $this->loginAsSuperadmin();
        $this->ajaxPost('/admin/customers/delete/' . Configure::read('test.superadminId'), [
            'referer' => '/'
        ]);
        $response = $this->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString('<ul><li>Anzahl der Bestellungen, die noch nicht mit dem Hersteller verrechnet wurden: 3.</li><li>Das Guthaben beträgt 92,02 €. Es muss 0 betragen.</li>', $response->msg);
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => Configure::read('test.customerId')
            ]
        ])->first();
        $this->assertNotEmpty($customer);
    }

    public function testDeleteWithNotApprovedPayments()
    {

        $this->Payment = $this->getTableLocator()->get('Payments');
        $paymentId = 1;
        $this->Payment->save(
            $this->Payment->patchEntity(
                $this->Payment->get($paymentId),
                [
                    'date_add' => new FrozenDate(),
                    'approval' => APP_OFF
                ]
            )
        );

        $this->loginAsSuperadmin();
        $this->ajaxPost('/admin/customers/delete/' . Configure::read('test.superadminId'), [
            'referer' => '/'
        ]);
        $response = $this->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString('<li>Anzahl der nicht bestätigten Guthaben-Aufladungen in den letzten 2 Jahren: 1.</li>', $response->msg);
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => Configure::read('test.customerId')
            ]
        ])->first();
        $this->assertNotEmpty($customer);
    }

    public function testDeleteOk()
    {
        $this->resetCustomerCreditBalance();
        $this->loginAsSuperadmin();
        $this->ajaxPost('/admin/customers/delete/' . Configure::read('test.customerId'), [
            'referer' => '/'
        ]);
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => Configure::read('test.customerId')
            ]
        ])->first();
        $this->assertEmpty($customer);
    }

    public function testLoginAndAutoLogin()
    {
        // 1) login
        $userEmail = Configure::read('test.loginEmailSuperadmin');
        $this->post($this->Slug->getLogin(), [
            'email' => $userEmail,
            'passwd' => Configure::read('test.loginPassword'),
            'remember_me' => true
        ]);

        $this->assertSession(Configure::read('test.superadminId'), 'Auth.User.id_customer');
        $this->assertSession(Configure::read('test.loginEmailSuperadmin'), 'Auth.User.email');
        $this->assertSession(true, 'Auth.User.active');
        $this->assertSession(null, 'Auth.User.auto_login_hash');
        $this->assertSession(Configure::read('test.loginEmailSuperadmin'), 'Auth.User.address_customer.email');

        // 2) cookie must exist
        $cookie = $this->_response->getCookie('remember_me');
        $this->assertNotEmpty($cookie);
        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'email' => $userEmail
            ]
        ])->first();
        $autoLoginHash = $customer->auto_login_hash;

        // 3) login again (simulate login on other device)
        $this->post($this->Slug->getLogin(), [
            'email' => $userEmail,
            'passwd' => Configure::read('test.loginPassword'),
            'remember_me' => true
        ]);
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'email' => $userEmail
            ]
        ])->first();

        // 4) hash needs to be the same as after first login
        $this->assertEquals($autoLoginHash, $customer->auto_login_hash);
        $this->assertSession($autoLoginHash, 'Auth.User.auto_login_hash');

        $cookieValue = json_decode($cookie['value']);
        $this->assertEquals($customer->auto_login_hash, $cookieValue->auto_login_hash);

        // 5) logout
        $this->logout();
        $this->assertCookieNotSet('remember_me');
    }

    /**
     * @param array $data
     * @return string
     */
    private function addCustomer($data)
    {
        $this->post($this->Slug->getRegistration(), $data);
        return $this->_response;
    }
}
