<?php

App::uses('AppCakeTestCase', 'Test');

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

    // called only after the first test method of this class
    public static function setUpBeforeClass()
    {
        self::initTestDatabase();
    }
    
    public function testRegistration()
    {
        $data = array(
            'Customer' => array(
                'email' => '',
                'firstname' => '',
                'lastname' => '',
                'newsletter' => 1
            ),
            'antiSpam' => 0,
            'AddressCustomer' => array(
                'address1' => '',
                'address2' => '',
                'postcode' => '',
                'city' => '',
                'phone_mobile' => '',
                'phone' => ''
            )
        );
        
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
        $data['Customer']['email'] = Configure::read('test.loginEmail');
        $data['AddressCustomer']['postcode'] = 'ABCDEF';
        $data['AddressCustomer']['phone_mobile'] = 'adsfkjasfasfdasfajaaa';
        $data['AddressCustomer']['phone'] = '897++asdf+d';
        $response = $this->addCustomer($data);
        $this->checkForMainErrorMessage($response);
        $this->assertRegExpWithUnquotedString('Diese E-Mail-Adresse wird bereits verwendet.', $response);
        $this->assertRegExpWithUnquotedString('Die PLZ ist nicht gültig.', $response);
        $this->assertRegExpWithUnquotedString('Die Handynummer ist nicht gültig.', $response);
        $this->assertRegExpWithUnquotedString('Die Telefonnummer ist nicht gültig.', $response);
        
        
        // 4) save user and check record
        $data['Customer']['email'] = 'new-foodcoopshop-user@mailinator.com';
        $data['Customer']['firstname'] = 'John';
        $data['Customer']['lastname'] = 'Doe';
        $data['AddressCustomer']['city'] = 'Scharnstein';
        $data['AddressCustomer']['address1'] = 'Mainstreet 1';
        $data['AddressCustomer']['postcode'] = '4644';
        $data['AddressCustomer']['phone_mobile'] = '+43698989898';
        $data['AddressCustomer']['phone'] = '';
        $response = $this->addCustomer($data);
        $this->assertRegExpWithUnquotedString('Deine Registrierung war erfolgreich.', $response);
        $this->assertUrl($this->browser->getUrl(), '/registrierung/abgeschlossen');
        
        // check for user group
        // check for active field
        // check newsletter field
        // check login possible?
        
    }
    
    private function checkForMainErrorMessage($response) {
        $this->assertRegExpWithUnquotedString('Beim Speichern sind Fehler aufgetreten!', $response);
    }
    
    
    /**
     * @param array $data
     * @return string
     */
    private function addCustomer($data) {
        $this->browser->post($this->Slug->getRegistration(), array(
            'data' => $data
        ));
        return $this->browser->getContent();
    }
}
?>