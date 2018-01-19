<?php

App::uses('AppCakeTestCase', 'Test');
App::uses('Manufacturers', 'Model');

/**
 * ManufacturersControllerTest
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
class ManufacturersControllerTest extends AppCakeTestCase
{

    public $Manufacturer;

    public function setUp()
    {
        parent::setUp();
        $this->Manufacturer = new Manufacturer();
    }

    public function testAdd()
    {
        $this->loginAsSuperadmin();

        $manufacturerData = array(
            'Manufacturers' => array(
                'name' => 'Test Manufacturer',
                'bank_name' => 'Test Bank',
                'iban' => 'Iban',
                'bic' => 'bic',
                'holiday_from' => null,
                'holiday_to' => null,
                'active' => 1,
                'additional_text_for_invoice' => '',
                'uid_number' => '',
                'tmp_image' => '',
                'delete_image' => '',
                'firmenbuchnummer' => '',
                'firmengericht' => '',
                'aufsichtsbehoerde' => '',
                'kammer' => '',
                'homepage' => '',
                'short_description' => 'Test Description'
            ),
            'Addresses' => array(
                'firstname' => '',
                'lastname' => '',
                'email' => 'fcs-demo-gemuese-hersteller@mailinator.com',
                'phone_mobile' => '',
                'phone' => '',
                'address1' => 'Street 1',
                'address2' => 'Street 2',
                'postcode' => '',
                'city' => 'Test City'
            ),
            'referer' => ''
        );
        $response = $this->add($manufacturerData);

        // provoke errors
        $this->assertRegExpWithUnquotedString('Beim Speichern sind 5 Fehler aufgetreten!', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib einen gültigen IBAN ein.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib einen gültigen BIC ein.', $response);
        $this->assertRegExpWithUnquotedString('Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib den Vornamen des Rechnungsempfängers an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib den Nachnamen des Rechnungsempfängers an.', $response);

        // set proper data and post again
        $manufacturerData['Manufacturers']['iban'] = 'AT193357281080332578';
        $manufacturerData['Manufacturers']['bic'] = 'BFKKAT2K';
        $manufacturerData['Addresses']['email'] = 'test-manufacturer@mailinator.com';
        $manufacturerData['Addresses']['firstname'] = 'Test';
        $manufacturerData['Addresses']['lastname'] = 'Manufacturers';
        $manufacturerData['Manufacturers']['homepage'] = 'www.foodcoopshop.com';

        $response = $this->add($manufacturerData);

        $this->assertRegExpWithUnquotedString('Der Hersteller wurde erfolgreich gespeichert.', $response);

        // get inserted manufacturer from database and check detail page for patterns
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.name' => $manufacturerData['Manufacturers']['name']
            )
        ));

        $response = $this->browser->get($this->Slug->getManufacturerDetail($manufacturer['Manufacturers']['id_manufacturer'], $manufacturer['Manufacturers']['name']));
        $this->assertRegExpWithUnquotedString('<h1>' . $manufacturer['Manufacturers']['name'], $response);

        $this->doTestCustomerRecord($manufacturer);

        $this->logout();
    }

    public function testEditOptions()
    {
        $this->loginAsSuperadmin();

        $manufacturerId = 4;
        $newSendOrderList = false;
        $newSendInvoice = false;
        $newSendOrderedProductPriceChangedNotification = false;
        $newSendOrderedProductQuantityChangedNotification = false;
        $newSendShopOrderNotification = false;
        $newBulkOrdersAllowed = false;

        $newSendOrderListCc = array('office@rothauer-it.com', 'test@test.com');
        $emailErrorMsg = 'Mindestens eine E-Mail-Adresse ist nicht gültig. Mehrere bitte mit , trennen (ohne Leerzeichen).';

        $manufacturerOld = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));

        $this->browser->get($this->Slug->getManufacturerEditOptions($manufacturerId));

        $this->browser->setFieldById('ManufacturerSendOrderList', $newSendOrderList); // do not use 0 here
        $this->browser->setFieldById('ManufacturerSendInvoice', $newSendInvoice);     // do not use 0 here

        $this->browser->setFieldById('ManufacturerSendOrderListCc', 'office@rothauer-it.com;test@test.com');  // wrong: comma expected as separator
        $this->browser->submitFormById('ManufacturerEditOptionsForm');
        $this->assertRegExpWithUnquotedString($emailErrorMsg, $this->browser->getContent());

        $this->browser->setFieldById('ManufacturerSendOrderListCc', 'office@rothauer-it.com,test@testcom');   // wrong: no dot in domain
        $this->browser->submitFormById('ManufacturerEditOptionsForm');
        $this->assertRegExpWithUnquotedString($emailErrorMsg, $this->browser->getContent());

        $this->browser->setFieldById('ManufacturerSendOrderListCc', implode(',', $newSendOrderListCc));  // correct
        $this->browser->submitFormById('ManufacturerEditOptionsForm');

        $this->browser->setFieldById('ManufacturerSendOrderedProductPriceChangedNotification', $newSendOrderedProductPriceChangedNotification);       // do not use 0 here
        $this->browser->setFieldById('ManufacturerSendOrderedProductQuantityChangedNotification', $newSendOrderedProductQuantityChangedNotification); // do not use 0 here
        $this->browser->setFieldById('ManufacturerSendShopOrderNotification', $newSendShopOrderNotification);                                         // do not use 0 here
        $this->browser->setFieldById('ManufacturerBulkOrdersAllowed', $newBulkOrdersAllowed);                                         // do not use 0 here

        $manufacturerNew = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));

        $sendOrderList = $this->Manufacturer->getOptionSendOrderList($manufacturerNew['Manufacturers']['send_order_list']);
        $this->assertEquals($sendOrderList, $newSendOrderList, 'saving option send_order_list failed');

        $sendInvoice = $this->Manufacturer->getOptionSendInvoice($manufacturerNew['Manufacturers']['send_invoice']);
        $this->assertEquals($sendInvoice, $newSendInvoice, 'saving option invoice failed');

        $sendOrderListCc = $this->Manufacturer->getOptionSendOrderListCc($manufacturerNew['Manufacturers']['send_order_list_cc']);
        $this->assertEquals($sendOrderListCc, $newSendOrderListCc, 'saving option send_order_list_cc failed');

        $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($manufacturerNew['Manufacturers']['send_ordered_product_price_changed_notification']);
        $this->assertEquals($sendOrderedProductPriceChangedNotification, $newSendOrderedProductPriceChangedNotification, 'saving option send_ordered_product_price_changed_notification failed');

        $sendOrderedProductQuantityChangedNotification = $this->Manufacturer->getOptionSendOrderedProductQuantityChangedNotification($manufacturerNew['Manufacturers']['send_ordered_product_quantity_changed_notification']);
        $this->assertEquals($sendOrderedProductQuantityChangedNotification, $newSendOrderedProductQuantityChangedNotification, 'saving option send_ordered_product_quantity_changed_notification failed');

        $sendShopOrderNotification = $this->Manufacturer->getOptionSendShopOrderNotification($manufacturerNew['Manufacturers']['send_shop_order_notification']);
        $this->assertEquals($sendShopOrderNotification, $newSendShopOrderNotification, 'saving option send_shop_order_notification failed');

        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturerNew['Manufacturers']['bulk_orders_allowed']);
        $this->assertEquals($bulkOrdersAllowed, $newBulkOrdersAllowed, 'saving option bulk_orders_allowed failed');

        $this->logout();
    }

    public function testEdit()
    {
        $this->loginAsSuperadmin();

        $manufacturerId = 4;
        $this->getEdit($manufacturerId);

        $this->browser->setFieldById('ManufacturerName', 'Huhuu');

        // test with valid customer email address must fail
        $this->browser->setFieldById('AddressEmail', 'foodcoopshop-demo-mitglied@mailinator.com');
        $this->browser->submitFormById('ManufacturerEditForm');
        $this->assertRegExpWithUnquotedString('Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.', $this->browser->getContent());

        // test with valid manufacturer email address must fail
        $this->browser->setFieldById('AddressEmail', 'fcs-demo-gemuese-hersteller@mailinator.com');
        $this->browser->submitFormById('ManufacturerEditForm');
        $this->assertRegExpWithUnquotedString('Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.', $this->browser->getContent());

        // test with valid email address
        $this->browser->setFieldById('AddressEmail', 'new-email-address@mailinator.com');
        $this->browser->submitFormById('ManufacturerEditForm');
        $this->assertRegExpWithUnquotedString('Der Hersteller wurde erfolgreich gespeichert.', $this->browser->getContent());

        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));
        $this->doTestCustomerRecord($manufacturer);

        $this->logout();
    }

    public function testAutomaticAddingOfCustomerRecord()
    {

        $this->loginAsSuperadmin();

        // manufacturer 16 does not yet have a related customer record (foreign_key: email)
        $manufacturerId = 16;
        $this->getEdit($manufacturerId);

        // saving customer must add a customer record
        $this->browser->submitFormById('ManufacturerEditForm');
        $this->assertRegExpWithUnquotedString('Der Hersteller wurde erfolgreich gespeichert.', $this->browser->getContent());

        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));
        $this->doTestCustomerRecord($manufacturer);

        $this->logout();
    }

    private function doTestCustomerRecord($manufacturer)
    {
        $customerRecord = $this->Manufacturer->getCustomerRecord($manufacturer);
        $this->assertEquals($manufacturer['Addresses']['firstname'], $customerRecord['Customers']['firstname']);
        $this->assertEquals($manufacturer['Addresses']['lastname'], $customerRecord['Customers']['lastname']);
        $this->assertEquals(APP_ON, $customerRecord['Customers']['active']);
    }

    /**
     *
     * @param array $data
     * @return string
     */
    private function getEdit($manufacturerId)
    {
        $this->browser->get($this->Slug->getManufacturerEdit($manufacturerId));
        return $this->browser->getContent();
    }

    /**
     *
     * @param array $data
     * @return string
     */
    private function add($data)
    {
        $this->browser->post($this->Slug->getManufacturerAdd(), array(
            'data' => $data
        ));
        return $this->browser->getContent();
    }
}
