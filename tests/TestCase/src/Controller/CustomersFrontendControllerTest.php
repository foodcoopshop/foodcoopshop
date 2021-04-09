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
        $this->assertFlashMessage('Dein neues Passwort wurde erfolgreich aktiviert.');

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

    private $registrationDataEmpty = [
        'Customers' => [
            'firstname' => '',
            'lastname' => '',
            'email_order_reminder' => 1,
            'terms_of_use_accepted_date_checkbox' => 0,
            'id_default_group' => CUSTOMER_GROUP_SUPERADMIN, //must not be applied!
            'active' => APP_ON, // must not be applied!
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

    private function addValidRegistrationData()
    {
        $this->registrationDataEmpty['Customers']['address_customer']['email'] = 'fcs-demo-mitglied@mailinator.com';
        $this->registrationDataEmpty['Customers']['address_customer']['postcode'] = 'ABCDEF';
        $this->registrationDataEmpty['Customers']['address_customer']['phone_mobile'] = 'adsfkjasfasfdasfajaaa';
        $this->registrationDataEmpty['Customers']['address_customer']['phone'] = '897++asdf+d';
    }

    public function testRegistrationSpamProtection()
    {
        $this->addCustomer($this->registrationDataEmpty);
        $this->assertFlashMessage('S-p-a-m-!');
    }

    public function testRegistrationValidationEmptyData()
    {
        $this->registrationDataEmpty['antiSpam'] = 4;
        $this->addCustomer($this->registrationDataEmpty);
        $this->assertResponseContains('Bitte gib deine E-Mail-Adresse an.');
        $this->assertResponseContains('Bitte gib deinen Vornamen an.');
        $this->assertResponseContains('Bitte gib deinen Nachnamen an.');
        $this->assertResponseContains('Bitte gib deine Straße an.');
        $this->assertResponseContains('Bitte gib deinen Ort an.');
        $this->assertResponseContains('Bitte gib deine Handynummer an.');
    }

    public function testRegistrationValidationWrongData()
    {
        $this->registrationDataEmpty['antiSpam'] = 4;
        $this->addValidRegistrationData();
        $this->addCustomer($this->registrationDataEmpty);
        $this->assertResponseContains('Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.');
        $this->assertResponseContains('Die PLZ ist nicht gültig.');
        $this->assertResponseContains('Die Handynummer ist nicht gültig.');
        $this->assertResponseContains('Die Telefonnummer ist nicht gültig.');
        $this->assertResponseContains('Bitte akzeptiere die Nutzungsbedingungen.');
    }

    public function testRegistrationUserNotActive()
    {
        $this->registrationDataEmpty['antiSpam'] = 4;
        $this->addValidRegistrationData();
        $email = 'new-foodcoopshop-member-1@mailinator.com';

        $this->changeConfiguration('FCS_DEFAULT_NEW_MEMBER_ACTIVE', 0);
        $this->saveAndCheckValidCustomer($this->registrationDataEmpty, $email);

        $this->assertMailSubjectContainsAt(0, 'Willkommen');
        $this->assertMailContainsHtmlAt(0, 'war erfolgreich!');
        $this->assertMailContainsHtmlAt(0, 'Dein Mitgliedskonto ist zwar erstellt, aber noch nicht aktiviert.');
        $this->assertMailSentToAt(0, $email);

        $this->assertMailSubjectContainsAt(1, 'Neue Registrierung: John Doe');
        $this->assertMailContainsHtmlAt(1, 'Es gab gerade eine neue Registrierung: <b>John Doe</b>');
        $this->assertMailSentToAt(1, 'fcs-demo-superadmin@mailinator.com');

    }

    public function testRegistrationUserActive()
    {
        $this->registrationDataEmpty['antiSpam'] = 4;
        $this->addValidRegistrationData();

        $this->changeConfiguration('FCS_DEFAULT_NEW_MEMBER_ACTIVE', 1);
        $email = 'new-foodcoopshop-member-2@mailinator.com';
        $customer = $this->saveAndCheckValidCustomer($this->registrationDataEmpty, $email);

        $this->assertMailSubjectContainsAt(0, 'Willkommen');
        $this->assertMailContainsHtmlAt(0, 'war erfolgreich!');
        $this->assertMailContainsHtmlAt(0, 'Bitte bestätige deine E-Mail-Adresse:');
        $this->assertMailSentToAt(0, $email);

        $this->assertMailSubjectContainsAt(1, 'Neue Registrierung: John Doe');
        $this->assertMailContainsHtmlAt(1, 'Es gab gerade eine neue Registrierung: <b>John Doe</b>');
        $this->assertMailSentToAt(1, 'fcs-demo-superadmin@mailinator.com');

        $this->assertNotNull($customer->activate_email_code);

        // try to activate user with wrong activation code
        $this->get(Configure::read('app.slugHelper')->getActivateEmailAddress('adfasdfaa'));
        $this->assertFlashMessage('Deine E-Mail-Adresse wurde bereits aktiviert oder der Aktivierungscode war nicht gültig.');

        // try to activate user with correct activation code
        $this->get(Configure::read('app.slugHelper')->getActivateEmailAddress($customer->activate_email_code));

        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.email' => $email,
            ],
        ])->first();
        $this->assertNull($customer->activate_email_code);
        $this->assertEquals(true, (bool) $customer->active);

        $this->assertMailSubjectContainsAt(2, 'Deine E-Mail-Adresse wurde erfolgreich aktiviert.');
        $this->assertMailContainsAttachment('Nutzungsbedingungen.pdf');

        $this->assertFlashMessage('Deine E-Mail-Adresse wurde erfolgreich aktiviert, dein Passwort wurde dir soeben zugeschickt.');

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
                'AddressCustomers',
            ]
        ])->first();

        // check customer record
        $this->assertEquals(false, (bool) $customer->active);
        $this->assertEquals(CUSTOMER_GROUP_MEMBER, $customer->id_default_group);
        $this->assertEquals($customerAddressEmail, $customer->email);
        $this->assertEquals('John', $customer->firstname);
        $this->assertEquals('Doe', $customer->lastname);
        $this->assertEquals(1, $customer->email_order_reminder);
        $this->assertEquals(date('Y-m-d'), $customer->terms_of_use_accepted_date->i18nFormat(Configure::read('DateFormat.Database')));

        // check address record
        $this->assertEquals('John', $customer->address_customer->firstname);
        $this->assertEquals('Doe', $customer->address_customer->lastname);
        $this->assertEquals($customerAddressEmail, $customer->address_customer->email);
        $this->assertEquals($customerAddress1, $customer->address_customer->address1);
        $this->assertEquals($customerAddress2, $customer->address_customer->address2);
        $this->assertEquals($customerCity, $customer->address_customer->city);
        $this->assertEquals($customerPostcode, $customer->address_customer->postcode);
        $this->assertEquals($customerPhoneMobile, $customer->address_customer->phone_mobile);
        $this->assertEquals($customerPhone, $customer->address_customer->phone);

        return $customer;
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
