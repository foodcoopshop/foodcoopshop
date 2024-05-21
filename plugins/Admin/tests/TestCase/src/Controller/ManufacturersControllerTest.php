<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Laminas\Diactoros\UploadedFile;

class ManufacturersControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public $Manufacturer;
    protected $OrderDetail;
    protected $Product;

    public $manufacturerData = [
        'Manufacturers' => [
            'name' => 'Test Manufacturer',
            'bank_name' => 'Test Bank',
            'iban' => 'Iban',
            'bic' => 'bic',
            'no_delivery_days' => '',
            'active' => 1,
            'additional_text_for_invoice' => '',
            'uid_number' => '',
            'tmp_image' => '',
            'delete_image' => '',
            'firmenbuchnummer' => '<b>number</b>',
            'firmengericht' => '',
            'aufsichtsbehoerde' => '',
            'kammer' => '',
            'homepage' => '',
            'short_description' => '<i>Test Description</i>',
            'description' => '<b>Text</b><img src="data:image/jpg;base64,iVBORw0KGgoAAAANSUCYII=" /><script>alert("evil");</script><img src=n onerror=alert("evil")>',
            'address_manufacturer' => [
                'firstname' => '',
                'lastname' => '',
                'email' => 'fcs-demo-gemuese-hersteller@mailinator.com',
                'phone_mobile' => '',
                'phone' => '',
                'address1' => 'Street 1',
                'address2' => 'Street 2',
                'postcode' => '',
                'city' => 'Test City'
            ]
        ],
        'referer' => '/'
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
    }

    public function testAddWithValidationErrors()
    {
        $this->loginAsSuperadmin();

        $this->add($this->manufacturerData);

        $this->assertResponseContains('Beim Speichern sind Fehler aufgetreten!');
        $this->assertResponseContains('Bitte gib einen gültigen IBAN ein.');
        $this->assertResponseContains('Bitte gib einen gültigen BIC ein.');
        $this->assertResponseContains('Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.');
        $this->assertResponseContains('Bitte gib den Vornamen an.');
        $this->assertResponseContains('Bitte gib den Nachnamen an.');
    }

    public function testAdd()
    {
        $this->loginAsSuperadmin();

        $this->manufacturerData['Manufacturers']['iban'] = 'AT193357281080332578';
        $this->manufacturerData['Manufacturers']['bic'] = 'BFKKAT2K';
        $this->manufacturerData['Manufacturers']['address_manufacturer']['email'] = 'test-manufacturer@mailinator.com';
        $this->manufacturerData['Manufacturers']['address_manufacturer']['firstname'] = 'Test';
        $this->manufacturerData['Manufacturers']['address_manufacturer']['lastname'] = 'Manufacturers';
        $this->manufacturerData['Manufacturers']['homepage'] = 'www.foodcoopshop.com';

        $this->add($this->manufacturerData);
        $this->assertFlashMessage('Der Hersteller <b>Test Manufacturer</b> wurde erstellt.');

        // get inserted manufacturer from database and check detail page for patterns
        $manufacturer = $this->Manufacturer->find('all',
            conditions: [
                'Manufacturers.name' => $this->manufacturerData['Manufacturers']['name']
            ],
            contain: [
                'AddressManufacturers'
            ]
        )->first();

        $this->get($this->Slug->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name));
        $this->assertResponseContains('<span class="left">' . $manufacturer->name);

        $this->doTestCustomerRecord($manufacturer);

        $this->assertEquals($manufacturer->description, '<b>Text</b><img src="invalid-image" alt="invalid-image" /><img src="n" alt="n" />');
        $this->assertEquals($manufacturer->short_description, '<i>Test Description</i>');
        $this->assertEquals($manufacturer->firmenbuchnummer, 'number');
        $this->assertEquals($manufacturer->is_private, true);
    }

    public function testEditOptionsMain()
    {
        $this->loginAsSuperadmin();

        $manufacturerId = 4;
        $newSendOrderList = 0;
        $newSendInvoice = 0;
        $newSendOrderedProductPriceChangedNotification = 0;
        $newSendOrderedProductAmountChangedNotification = 0;
        $newSendInstantOrderNotification = 0;
        $newDefaultTaxId = 3;
        $newDefaultTaxIdPurchasePrice = 3;
        $newStatus = APP_OFF;

        $newSendOrderListCc = ['office@rothauer-it.com', 'test@test.com'];
        $emailErrorMsg = 'Mindestens eine E-Mail-Adresse ist nicht gültig. Mehrere bitte mit , trennen (ohne Leerzeichen).';

        $this->post(
            $this->Slug->getManufacturerEditOptions($manufacturerId),
            [
                'Manufacturers' => [
                    'send_order_list' => $newSendOrderList, // do not use 0 here
                    'send_invoice' => $newSendInvoice, // do not use 0 here
                    'send_order_list_cc' => 'office@rothauer-it.com;test@test.com', // wrong: comma expected as separator
                ]
            ]
        );
        $this->assertResponseContains($emailErrorMsg);

        $this->post(
            $this->Slug->getManufacturerEditOptions($manufacturerId),
            [
                'Manufacturers' => [
                    'send_order_list' => $newSendOrderList,
                    'send_invoice' => $newSendInvoice,
                    'send_order_list_cc' => 'office@rothauer-it.com,test@testcom' // wrong: no dot in domain
                ]
            ]
        );
        $this->assertResponseContains($emailErrorMsg);

        $this->post(
            $this->Slug->getManufacturerEditOptions($manufacturerId),
            [
                'Manufacturers' => [
                    'send_order_list' => $newSendOrderList,
                    'send_invoice' => $newSendInvoice,
                    'send_order_list_cc' => implode(',', $newSendOrderListCc), // correct
                    'send_ordered_product_price_changed_notification' => $newSendOrderedProductPriceChangedNotification,
                    'send_ordered_product_amount_changed_notification' => $newSendOrderedProductAmountChangedNotification,
                    'send_instant_order_notification' => $newSendInstantOrderNotification,
                    'default_tax_id' => $newDefaultTaxId,
                    'default_tax_id_purchase_price' => $newDefaultTaxIdPurchasePrice,
                    // althouth the following property is not tested, it needs to be included in the request to avoid
                    // [InvalidArgumentException] Cannot convert value of type `boolean` to integer
                    'send_ordered_product_deleted_notification' => 1,
                    'active' => $newStatus,
                ],
                'referer' => '/'
            ]
        );

        $manufacturerNew = $this->Manufacturer->find('all',
            conditions: [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            contain: [
                'AddressManufacturers'
            ],
        )->first();

        $sendOrderList = $this->Manufacturer->getOptionSendOrderList($manufacturerNew->send_order_list);
        $this->assertEquals($sendOrderList, $newSendOrderList, 'saving option send_order_list failed');

        $sendInvoice = $this->Manufacturer->getOptionSendInvoice($manufacturerNew->send_invoice);
        $this->assertEquals($sendInvoice, $newSendInvoice, 'saving option invoice failed');

        $sendOrderListCc = $this->Manufacturer->getOptionSendOrderListCc($manufacturerNew->send_order_list_cc);
        $this->assertEquals($sendOrderListCc, $newSendOrderListCc, 'saving option send_order_list_cc failed');

        $sendOrderedProductPriceChangedNotification = $this->Manufacturer->getOptionSendOrderedProductPriceChangedNotification($manufacturerNew->send_ordered_product_price_changed_notification);
        $this->assertEquals($sendOrderedProductPriceChangedNotification, $newSendOrderedProductPriceChangedNotification, 'saving option send_ordered_product_price_changed_notification failed');

        $sendOrderedProductAmountChangedNotification = $this->Manufacturer->getOptionSendOrderedProductAmountChangedNotification($manufacturerNew->send_ordered_product_amount_changed_notification);
        $this->assertEquals($sendOrderedProductAmountChangedNotification, $newSendOrderedProductAmountChangedNotification, 'saving option send_ordered_product_amount_changed_notification failed');

        $sendInstantOrderNotification = $this->Manufacturer->getOptionSendInstantOrderNotification($manufacturerNew->send_instant_order_notification);
        $this->assertEquals($sendInstantOrderNotification, $newSendInstantOrderNotification, 'saving option send_instant_order_notification failed');

        $defaultTaxId = $this->Manufacturer->getOptionDefaultTaxId($manufacturerNew->default_tax_id);
        $this->assertEquals($defaultTaxId, $newDefaultTaxId, 'saving option default_tax_id failed');

        $customerRecord = $this->Manufacturer->getCustomerRecord($manufacturerNew->address_manufacturer->email);
        $this->assertEquals($newStatus, $customerRecord->active);
        $this->assertEquals($newStatus, $manufacturerNew->active);

        $this->logout();
    }

    public function testEditOptionsNoDeliveryDays()
    {
        $this->loginAsSuperadmin();

        $manufacturerId = 15;
        $noDeliveryDays = date('Y-m-d', strtotime('friday next week'));
        $noDeliveryDayA = date('Y-m-d', strtotime($noDeliveryDays . ' + 10 day'));
        $noDeliveryDayB = date('Y-m-d', strtotime($noDeliveryDays . ' + 11 day'));

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Product = $this->getTableLocator()->get('Products');

        $orderDetailEntityA = $this->OrderDetail->get(1);
        $orderDetailEntityA->pickup_day = $noDeliveryDayA;
        $this->OrderDetail->save($orderDetailEntityA);

        $orderDetailEntityB = $this->OrderDetail->get(2);
        $orderDetailEntityB->pickup_day = $noDeliveryDayB;
        $this->OrderDetail->save($orderDetailEntityB);

        $orderDetailEntityC = $this->OrderDetail->get(3);
        $orderDetailEntityC->pickup_day = $noDeliveryDayB;
        $this->OrderDetail->save($orderDetailEntityC);

        $this->Product->updateAll(['id_manufacturer' => $manufacturerId], []);

        $this->post(
            $this->Slug->getManufacturerEditOptions($manufacturerId),
            [
                'Manufacturers' => [
                    'no_delivery_days' => [$noDeliveryDayA, $noDeliveryDayB]
                ],
                'referer' => '/'
            ]
        );
        $this->assertResponseContains(
            'Für die folgenden Liefertag(e) sind bereits Bestellungen vorhanden: '
            . Configure::read('app.timeHelper')->formatToDateShort($noDeliveryDayA) . ' (1x), '
            . Configure::read('app.timeHelper')->formatToDateShort($noDeliveryDayB) . ' (2x)',
        );

        $this->post(
            $this->Slug->getManufacturerEditOptions($manufacturerId),
            [
                'Manufacturers' => [
                    'no_delivery_days' => [$noDeliveryDays],
                    // althouth the following property is not tested, it needs to be included in the request to avoid
                    // [InvalidArgumentException] Cannot convert value of type `boolean` to integer
                    'send_invoice' => 1,
                    'send_order_list' => 1,
                    'send_instant_order_notification' => 1,
                    'send_ordered_product_deleted_notification' => 1,
                    'send_ordered_product_price_changed_notification' => 1,
                    'send_ordered_product_amount_changed_notification' => 1,
                ],
                'referer' => '/'
            ]
        );

        $this->assertFlashMessage('Die Einstellungen des Herstellers <b>Demo Milch-Hersteller</b> wurden erfolgreich gespeichert.');

        $manufacturerNew = $this->Manufacturer->find('all',
            conditions: [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        )->first();

        $this->assertEquals($noDeliveryDays, $manufacturerNew->no_delivery_days);

        $this->logout();
    }

    public function testUploadAndDeleteProfileImage()
    {
        $this->loginAsSuperadmin();
        $manufacturerId = 4;
        $mediumFile = WWW_ROOT . 'files/images/manufacturers/4-medium_default.jpg';
        $largeFile = WWW_ROOT . 'files/images/manufacturers/4-large_default.jpg';

        // 1) prepare files
        if (file_exists($mediumFile)) {
            unlink($mediumFile);
        }
        if (file_exists($largeFile)) {
            unlink($largeFile);
        }

        $uploadFile = WWW_ROOT . 'img/tests/test-image.jpg';
        $newFile = WWW_ROOT . 'img/tests/test-image-new.jpg';
        $path = dirname($newFile);
        chmod($path, 0755);
        copy($uploadFile, $newFile);

        // 2) upload image
        $upload = new UploadedFile(
            $newFile,
            filesize($newFile),
            UPLOAD_ERR_OK,
            'random-image-name.jpg',
        );

        $this->ajaxPost(
            '/admin/tools/doTmpImageUpload',
            [
                'upload' => $upload,
            ]
        );
        $object = json_decode($this->_response->getBody()->__toString());

        $manufacturer = [
            'Manufacturers' => [
                'name' => 'Manufacturer & Sons',
                'address_manufacturer' => [
                    'firstname' => 'firstname',
                    'lastname' => 'lastname',
                    'email' => Configure::read('test.loginEmailMeatManufacturer'),
                ],
            ],
            'referer' => '/',
        ];

        $manufacturerUploadImage = $manufacturer;
        $manufacturerUploadImage['Manufacturers']['tmp_image'] = $object->filename;
        $this->post($this->Slug->getManufacturerEdit($manufacturerId), $manufacturerUploadImage);
        $this->assertFlashMessage('Der Hersteller <b>Manufacturer &amp; Sons</b> wurde geändert.');

        $this->assertFileExists($mediumFile);
        $this->assertFileExists($largeFile);

        // 3) delete image
        $manufacturerDeleteImage = $manufacturer;
        $manufacturerDeleteImage['Manufacturers']['delete_image'] = true;
        $this->post($this->Slug->getManufacturerEdit($manufacturerId), $manufacturerDeleteImage);
        $this->assertFlashMessage('Der Hersteller <b>Manufacturer &amp; Sons</b> wurde geändert.');

        $this->assertFileDoesNotExist($mediumFile);
        $this->assertFileDoesNotExist($largeFile);
    }

    public function testEditMain()
    {
        $this->loginAsSuperadmin();

        $manufacturerId = 4;

        $this->post(
            $this->Slug->getManufacturerEdit($manufacturerId),
            [
                'Manufacturers' => [
                    'name' => 'Manufacturer & Sons',
                    'address_manufacturer' => [
                        'email' => 'fcs-demo-mitglied@mailinator.com'
                    ]
                ],
                'referer' => '/'
            ]
        );
        // test with valid customer email address must fail
        $this->assertResponseContains('Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.');

        // test with valid manufacturer email address must fail
        $this->post(
            $this->Slug->getManufacturerEdit($manufacturerId),
            [
                'Manufacturers' => [
                    'name' => 'Manufacturer & Sons',
                    'address_manufacturer' => [
                        'email' => 'fcs-demo-gemuese-hersteller@mailinator.com'
                    ],
                ],
                'referer' => '/'
            ]
        );
        $this->assertResponseContains('Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.');

        // test with valid email address
        $this->post(
            $this->Slug->getManufacturerEdit($manufacturerId),
            [
                'Manufacturers' => [
                    'name' => 'Manufacturer & Sons',
                    'homepage' => 'https://www.foodcoopshop.com',
                    'address_manufacturer' => [
                        'firstname' => 'firstname',
                        'lastname' => 'lastname',
                        'email' => 'new-email-address@mailinator.com'
                    ],
                ],
                'referer' => '/'
            ]
        );
        $this->assertFlashMessage('Der Hersteller <b>Manufacturer &amp; Sons</b> wurde geändert.');

        $manufacturer = $this->Manufacturer->find('all',
            conditions: [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            contain: [
                'AddressManufacturers'
            ]
        )->first();
        $this->doTestCustomerRecord($manufacturer);

        $this->logout();
    }

    private function doTestCustomerRecord($manufacturer): void
    {
        $customerRecord = $this->Manufacturer->getCustomerRecord($manufacturer->address_manufacturer->email);
        $this->assertEquals($manufacturer->address_manufacturer->firstname, $customerRecord->firstname);
        $this->assertEquals($manufacturer->address_manufacturer->lastname, $customerRecord->lastname);
        $this->assertEquals($manufacturer->address_manufacturer->email, $customerRecord->email);
        $this->assertEquals(APP_ON, $customerRecord->active);
    }

    private function add($data): void
    {
        $this->post($this->Slug->getManufacturerAdd(), $data);
    }
}
