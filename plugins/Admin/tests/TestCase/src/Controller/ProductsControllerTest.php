<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use App\Model\Entity\OrderDetail;
use App\Model\Entity\Cronjob;
use Cake\ORM\TableRegistry;
use App\Model\Entity\Product;

class ProductsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public function testExportProducts(): void
    {
        $unitsTable = $this->getTableLocator()->get('Units');
        $unitEntityA = $unitsTable->get(8);
        $unitEntityA->use_weight_as_amount = 1;
        $unitsTable->save($unitEntityA);

        $this->loginAsSuperadmin();
        $this->post('/admin/products/export', [
            'productIds' => '351',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Id;Produkt;Hersteller;Status;Menge;Mindestlagerstand;Einheit;"Verkaufspreis brutto";"Preis pro";Lagerwert');
        $this->assertResponseContains('351;"Lagerprodukt 2";"Demo Gemüse-Hersteller";1;999;;kg;15,000000;"1 kg";14.985,00');
        $this->assertResponseContains(';;;;;;;;14.985,00');
    }

    public function testEditProductNewStatusOn(): void
    {
        $this->loginAsSuperadmin();
        $productId = 60;
        $status = APP_ON;
        $this->get('/admin/products/editNewStatus/' . $productId  . '/' . $status);
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ]
        )->first();
        $this->assertTrue($product->new->isToday());
    }

    public function testEditProductNewStatusOff(): void
    {
        $this->loginAsSuperadmin();
        $productId = 60;
        $status = APP_OFF;
        $this->get('/admin/products/editNewStatus/' . $productId  . '/' . $status);
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ]
        )->first();
        $this->assertTrue($product->new->addDays((int) Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') + 1)->isToday());
    }

    public function testEditProductStatus(): void
    {
        $this->loginAsSuperadmin();
        $productId = 60;
        $status = APP_OFF;
        $this->get('/admin/products/editStatus/' . $productId . '/0/' . $status);
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ]
        )->first();
        $this->assertEquals($product->active, $status);
    }

    public function testEditProductStatusBulkAsSuperadmin(): void
    {
        $this->loginAsSuperadmin();
        $productIds = [60, 102, 103];
        $status = APP_OFF;
        $this->ajaxPost('/admin/products/editStatusBulk', [
            'productIds' => $productIds,
            'status' => APP_OFF,
        ]);
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $products = $productsTable->find('all',
            conditions: [
                'Products.id_product IN' => $productIds,
            ]
        );
        foreach ($products as $product) {
            $this->assertEquals($product->active, $status);
        }
    }

    public function testEditProductStatusBulkAsManufacturerPermisionsNotOk(): void
    {
        $this->loginAsMeatManufacturer();
        $productIds = [60, 102, 103];
        $status = APP_OFF;
        $this->ajaxPost('/admin/products/editStatusBulk', [
            'productIds' => $productIds,
            'status' => $status,
        ]);
        $this->assertAccessDeniedFlashMessage();
    }

    public function testEditProductStatusBulkAsManufacturersPermissionsOk(): void
    {
        $this->loginAsMilkManufacturer();
        $productIds = [60];
        $status = APP_OFF;
        $this->ajaxPost('/admin/products/editStatusBulk', [
            'productIds' => $productIds,
            'status' => $status,
        ]);
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $products = $productsTable->find('all',
            conditions: [
                'Products.id_product IN' => $productIds,
            ]
        );
        foreach ($products as $product) {
            $this->assertEquals($product->active, $status);
        }
    }

    public function testEditProductCategoriesOk(): void
    {
        $this->loginAsSuperadmin();
        $productId = 60;
        $categories = [16];
        $this->ajaxPost('/admin/products/editCategories', [
            'productId' => $productId,
            'selectedCategories' => $categories,
        ]);
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ],
            contain: [
                'CategoryProducts',
            ],
        )->first();;
        $this->assertCount(2, $product->category_products);
        $this->assertEquals(16, $product->category_products[0]->id_category);
        $this->assertEquals(Configure::read('app.categoryAllProducts'), $product->category_products[1]->id_category);
    }

    public function testEditSellingPriceWithInvalidPriceAsSuperadmin(): void
    {
        $this->loginAsSuperadmin();
        $price = 'invalid-price';
        $this->changeProductPrice(346, $price);
        $response = $this->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString('input format not correct: ' . $price, $response->msg);
        $this->assertJsonError();
    }

    public function testEditSellingPriceOfNonExistingProductAsSuperadmin(): void
    {
        $this->loginAsSuperadmin();
        $productId = 1000;
        $this->changeProductPrice($productId, '0,15');
        $this->assertAccessDeniedFlashMessage();
    }

    public function testEditSellingPriceOfMeatManufactuerProductAsVegatableManufacturer(): void
    {
        $this->loginAsVegetableManufacturer();
        $productId = 102;
        $this->changeProductPrice($productId, '0,15');
        $this->assertAccessDeniedFlashMessage();
    }

    public function testEditSellingPriceOfMeatManufactuerWithPurchasePriceEnabled(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsMeatManufacturer();
        $productId = 102;
        $this->changeProductPrice($productId, '0,15');
        $this->assertAccessDeniedFlashMessage();
    }

    public function testEditSellingPriceOfProductAsSuperadminToZero(): void
    {
        $this->loginAsSuperadmin();
        $this->assertSellingPriceChange(346, '0', '0,00', '10');
    }

    public function testEditSellingPriceOfProductAsSuperadmin(): void
    {
        $this->loginAsSuperadmin();
        $this->assertSellingPriceChange(346, '2,20', '2,00', '10');
    }

    public function testEditSellingPriceOfProductAsSuperadminWithChangeOpenOrderDetails(): void
    {
        $this->loginAsSuperadmin();

        $productId = 346;
        $this->addProductToCart($productId, 5);
        $this->finishCart();

        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get(1),
                [
                    'order_state' => OrderDetail::STATE_BILLED_CASHLESS,
                ]
            )
        );

        $this->assertSellingPriceChange($productId, '2,10', '1,909091', '10', changeOpenOrderDetails: true);

        $openOrderDetails = $orderDetailsTable->find('all',
            conditions: [
                $orderDetailsTable->aliasField('product_id') => $productId,
            ])->toArray();

        // order was billed => no price change
        $this->assertEquals(1.82, $openOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(1.65, $openOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals(0.17, $openOrderDetails[0]->tax_unit_amount);
        $this->assertEquals(0.17, $openOrderDetails[0]->tax_total_amount);

        // order was NOT billed => price change
        $this->assertEquals(10.5, $openOrderDetails[1]->total_price_tax_incl);
        $this->assertEquals(9.55, $openOrderDetails[1]->total_price_tax_excl);
        $this->assertEquals(0.19, $openOrderDetails[1]->tax_unit_amount);
        $this->assertEquals(0.95, $openOrderDetails[1]->tax_total_amount);

        $this->assertFlashMessage('Der Preis des Produktes <b>Artischocke</b> wurde erfolgreich geändert.<br />Der Preis des folgenden bestellten Produktes wurde angepasst: ID: 4');

    }

    public function testEditSellingPricePerUnitOfProductAsSuperadmin(): void
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $this->assertSellingPriceChange($productId, 0, 0, 10, true, 15, 'g', 100, 50);
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId
            ],
            contain: [
                'UnitProducts'
            ]
        )->first();
        $this->assertRegExpWithUnquotedString($this->PricePerUnit->getPricePerUnitBaseInfo($product->unit_product->price_incl_per_unit, $product->unit_product->name, $product->unit_product->amount), '`15,00 € / 100 g');
    }

    public function testEditSellingPricePerUnitOfProductAsSuperadminChangeOpenOrderDetails(): void
    {
        $this->loginAsSuperadmin();
        $productId = 347;

        $this->addProductToCart($productId, 5);
        $this->finishCart();

        $this->addProductToCart($productId, 2);
        $this->finishCart();

        $this->addProductToCart($productId, 1);
        $this->finishCart();

        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get(5),
                [
                    'order_state' => OrderDetail::STATE_BILLED_CASHLESS,
                ]
            )
        );

        $this->assertSellingPriceChange($productId, 0, 0, 10, true, 150, 'kg', 1, 350, changeOpenOrderDetails: true);

        $openOrderDetails = $orderDetailsTable->find('all',
            conditions: [
                $orderDetailsTable->aliasField('product_id') => $productId,
            ],
            contain: [
                'OrderDetailUnits',
            ])->toArray();

        // order was NOT billed => price change and order_detail_unit change
        $this->assertEquals(262.5, $openOrderDetails[0]->total_price_tax_incl);
        $this->assertEquals(238.65, $openOrderDetails[0]->total_price_tax_excl);
        $this->assertEquals(150, $openOrderDetails[0]->order_detail_unit->price_incl_per_unit);
        $this->assertEquals('kg', $openOrderDetails[0]->order_detail_unit->unit_name);
        $this->assertEquals(1, $openOrderDetails[0]->order_detail_unit->unit_amount);

        // order was NOT billed => price change and order_detail_unit change
        $this->assertEquals(52.5, $openOrderDetails[2]->total_price_tax_incl);
        $this->assertEquals(47.73, $openOrderDetails[2]->total_price_tax_excl);
        $this->assertEquals(150, $openOrderDetails[2]->order_detail_unit->price_incl_per_unit);
        $this->assertEquals('kg', $openOrderDetails[2]->order_detail_unit->unit_name);
        $this->assertEquals(1, $openOrderDetails[2]->order_detail_unit->unit_amount);

        $this->assertFlashMessage('Der Preis des Produktes <b>Forelle</b> wurde erfolgreich geändert.<br />Der Preis der folgenden 2 bestellten Produkte wurde angepasst: ID: 4, 6');

    }

    public function testEditSellingPriceOfAttributeAsSuperadmin(): void
    {
        $this->loginAsSuperadmin();
        $this->assertSellingPriceChange('60-10', '1,25', '1,106195', '13');
    }

    public function testEditSellingPriceWith0PercentTax(): void
    {
        $this->loginAsSuperadmin();
        $this->assertSellingPriceChange('163', '1,60', '1,60', '0');
    }

    public function testEditPurchasePriceOfProductAsSuperadminNonExistingProduct(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->doPurchasePriceChange(4, '20');
        $this->assertJsonError();
        $this->assertRegExpWithUnquotedString('product not existing: id 4', $this->getJsonDecodedContent()->msg);
    }

    public function testEditPurchasePriceOfProductAsSuperadminInvalid(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->doPurchasePriceChange(346, '-1');
        $this->assertJsonError();
        $this->assertRegExpWithUnquotedString('Der Preis muss eine positive Zahl sein.', $this->getJsonDecodedContent()->msg);
    }

    public function testEditPurchasePriceOfProductAsSuperadmin(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $product = $this->doPurchasePriceChange(346, '2,20');
        $this->assertJsonOk();
        $this->assertEquals(1.833333, $product->purchase_price_product->price);
    }

    public function testEditPurchasePriceOfProductAsMeatManufacturer(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsMeatManufacturer();
        $this->doPurchasePriceChange(342, '2,20');
        $this->assertAccessDeniedFlashMessage();
    }

    public function testEditPurchasePricePerUnitOfProductAsSuperadmin(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $product = $this->doPurchasePriceChange(347, '1,00');
        $this->assertJsonOk();
        $this->assertEquals(1.00, $product->unit_product->purchase_price_incl_per_unit);
    }

    public function testEditPurchasePriceOfAttributeAsSuperadmin(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $product = $this->doPurchasePriceChange('60-10', '2,20');
        $this->assertJsonOk();
        $this->assertEquals(2, $product->product_attributes[0]->purchase_price_product_attribute->price);
    }

    public function testEditPurchasePricePerUnitOfAttributeAsSuperadmin(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $product = $this->doPurchasePriceChange('348-11', '13,30');
        $this->assertJsonOk();
        $this->assertEquals(13.30, $product->product_attributes[0]->unit_product_attribute->purchase_price_incl_per_unit);
    }

    public function testEditTaxSellingPriceAsManufacturerWithPurchasePriceEnabled(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsVegetableManufacturer();
        $this->assertTaxChange(346, 1, 2);
        $this->assertAccessDeniedFlashMessage();
    }

    public function testEditTaxSellingPriceInvalid(): void
    {
        $this->loginAsSuperadmin();
        $product = $this->assertTaxChange(346, 5, 2);
        $this->assertEquals($product->price, 1.652893);
    }

    public function testEditTaxSellingPriceValidA(): void
    {
        $this->loginAsSuperadmin();
        $product = $this->assertTaxChange(346, 1, 1);
        $this->assertEquals($product->price, 1.515152);
    }

    public function testEditTaxSellingPriceValidZero(): void
    {
        $this->loginAsSuperadmin();
        $product = $this->assertTaxChange(346, 0, 0);
        $this->assertEquals($product->price, 1.818182);
    }

    public function testEditTaxSellingPriceWithAttributesValidZero(): void
    {
        $this->loginAsSuperadmin();
        $product = $this->assertTaxChange(60, 0, 0);
        $this->assertEquals($product->product_attributes[0]->price, 0.616364);
    }

    public function testEditTaxSellingPriceWithAttributesValidA(): void
    {
        $this->loginAsSuperadmin();
        $product = $this->assertTaxChange(60, 2, 2);
        $this->assertEquals($product->product_attributes[0]->price, 0.560331);
    }

    public function testEditTaxPurchasePriceInvalid(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->assertTaxChange(344, 0, 0, 5, 'empty');
    }

    public function testEditTaxPurchasePriceValidA(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $product = $this->assertTaxChange(346, 0, 0, 3, 3);
        $this->assertEquals($product->purchase_price_product->price, 1.274336);
    }

    public function testEditTaxPurchasePriceWithAttributeValidA(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $product = $this->assertTaxChange(350, 0, 0, 3, 3);
        $this->assertEquals($product->product_attributes[0]->purchase_price_product_attribute->price, 1.238938);
    }

    public function testEditTaxPurchasePriceValidZero(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $product = $this->assertTaxChange(346, 0, 0, 0, 0);
        $this->assertEquals($product->purchase_price_product->price, 1.44);
    }

    public function testEditDeliveryRhythmInvalidDeliveryRhythmA(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '5-week');
        $this->assertRegExpWithUnquotedString('Der Lieferrhythmus ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmInvalidDeliveryRhythmB(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-week', '31.08.2018');
        $this->assertRegExpWithUnquotedString('Der Lieferrhythmus ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmInvalidFirstDeliveryDay(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '1-week', '30.08.2018');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein Freitag sein.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmOk1Week(): void
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '1-week');
        $this->assertJsonOk();
    }

    public function testEditDeliveryRhythmInvalid2WeekWithoutDate(): void
    {
        $productId = 346;
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm($productId, '2-week');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein Freitag sein.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmInvalid3WeekWithoutDate(): void
    {
        $productId = 346;
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm($productId, '3-week');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein Freitag sein.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmOkFirstOfMonth(): void
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '1-month', '03.08.2018');
        $this->assertJsonOk();
    }

    public function testEditDeliveryRhythmInvalidFirstOfMonth(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '1-month', '10.08.2018');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein erster Freitag im Monat sein.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmOkSecondOfMonth(): void
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '2-month', '08.01.2021');
        $this->assertJsonOk();
    }

    public function testEditDeliveryRhythmInvalidSecondOfMonth(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '2-month', '19.03.2021');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein zweiter Freitag im Monat sein.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmOkThirdOfMonth(): void
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '3-month', '15.01.2021');
        $this->assertJsonOk();
    }

    public function testEditDeliveryRhythmInvalidThirdOfMonth(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '3-month', '08.01.2021');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein dritter Freitag im Monat sein.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmOkFourthOfMonth(): void
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '4-month', '22.01.2021');
        $this->assertJsonOk();
    }

    public function testEditDeliveryRhythmInvalidFourthOfMonth(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '4-month', '15.01.2021');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein vierter Freitag im Monat sein.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmOkLastOfMonth(): void
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '0-month', '31.08.2018');
        $this->assertJsonOk();
    }

    public function testEditDeliveryRhythmInvalidLastOfMonth(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-month', '10.08.2018');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein letzter Freitag im Monat sein.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmInvalidIndividualWithoutDeliveryDay(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-individual');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmInvalidIndividualWithEmptyOrderPossibleUntil(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-individual', '2018-08-31', '');
        $this->assertRegExpWithUnquotedString('Das Bestellbar-bis-Datum ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmInvalidIndividualWithWrongOrderPossibleUntil(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-individual', '2018-08-31', '2018-09-30');
        $this->assertRegExpWithUnquotedString('Das Bestellbar-bis-Datum muss kleiner als der Liefertag sein.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmOkIndividual(): void
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '0-individual', '2018-08-31', '2018-08-28');
        $this->assertJsonOk();
    }

    public function testEditDeliveryRhythmIndividualInvalidSendOrderListDay(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-individual', '2018-08-31', '2018-08-28', '2', '2019-01-01');
        $this->assertRegExpWithUnquotedString('Das Datum für den Bestellisten-Versand muss zwischen Bestellbar-bis-Datum und dem Liefertag liegen.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditDeliveryRhythmOkWithDatabaseAsserts(): void
    {
        $productId = 346;
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm($productId, '1-month', '03.08.2018');
        $this->assertJsonOk();
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId
            ]
        )->first();
        $this->assertEquals($product->delivery_rhythm_type, 'month');
        $this->assertEquals($product->delivery_rhythm_count, 1);
        $this->assertEquals($product->delivery_rhythm_first_delivery_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')), '03.08.2018');
    }

    public function testEditDeliveryRhythmWeeklyInvalidSendOrderListsWeekday(): void
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '1-week', '', '', '15');
        $this->assertRegExpWithUnquotedString('Bitte gib eine Zahl zwischen 0 und 6 an.', $response->msg);
        $this->assertJsonError();
    }

    public function testDeleteProductOk(): void
    {
        $this->loginAsSuperadmin();
        $productId = 102;
        $product = $this->deleteProduct($productId);
        $this->assertJsonOk();
        $this->assertEquals($product->active, APP_DEL);
    }

    public function testDeleteProductWithOpenOrdersWithoutInvoiceingEnabled(): void
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $product = $this->deleteProduct($productId);
        $this->assertEquals($product->active, APP_DEL);
    }

    public function testDeleteProductWithOpenOrdersWithInvoicingEnabled(): void
    {
        $cronjobsTable = TableRegistry::getTableLocator()->get('Cronjobs');
        $cronjobsTable->save(
            $cronjobsTable->newEntity(
                [
                    'id' => Cronjob::SEND_INVOICES_TO_MANUFACTURERS_ID,
                    'name' => 'SendInvoicesToManufacturers',
                    'time_interval' => 'month',
                    'day_of_month' => 11,
                    'weekday' => null,
                    'not_before_time' => '07:30:00',
                    'active' => 1,
                ]
            )
        );
        $this->loginAsSuperadmin();
        $productId = 346;
        $product = $this->deleteProduct($productId);
        $this->assertEquals($product->active, APP_ON);
    }
    
    public function testDeleteProductAccessLoggedOut(): void
    {
        $this->deleteProduct(364);
        $this->assertAccessDeniedFlashMessage();
    }

    public function testDeleteProductAccessLoggedInAsWrongManufacturer(): void
    {
        $productId = 346;
        $this->loginAsMeatManufacturer();
        $product = $this->deleteProduct($productId);
        // active must not be changed!
        $this->assertEquals($product->active, APP_ON);
    }

    public function testProductAdminPricesAsManufacturerWithPurchasePriceEnabled(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsMeatManufacturer();
        $this->get($this->Slug->getProductAdmin());
        $this->assertResponseNotContains('product-price-edit-button');
        $this->assertResponseNotContains('product-deposit-edit-button');
        $this->assertResponseNotContains('product-purchase-price-edit-button');
        $this->assertResponseNotContains('purchase-price-tax-for-dialog');
        $this->assertResponseNotContains('tax-for-dialog');
    }

    public function testProductAdminPricesAsManufacturerWithPurchasePriceDisabled(): void
    {
        $this->loginAsMeatManufacturer();
        $this->get($this->Slug->getProductAdmin());
        $this->assertResponseContains('product-price-edit-button');
        $this->assertResponseContains('product-deposit-edit-button');
        $this->assertResponseNotContains('product-purchase-price-edit-button');
        $this->assertResponseNotContains('purchase-price-tax-for-dialog');
        $this->assertResponseContains('tax-for-dialog');
    }

    public function testProductAdminPricesAsSuperadminWithPurchasePriceEnabled(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->get($this->Slug->getProductAdmin(5));
        $this->assertResponseContains('product-deposit-edit-button');
        $this->assertResponseContains('product-price-edit-button');
        $this->assertResponseContains('product-purchase-price-edit-button');
        $this->assertResponseContains('purchase-price-tax-for-dialog');
        $this->assertResponseContains('tax-for-dialog');
    }

    public function testEditQuantityOk(): void
    {
    
        $this->loginAsSuperadmin();
        $productId = 346;

        $this->ajaxPost('/admin/products/editQuantity', [
            'productId' => $productId,
            'alwaysAvailable' => 0,
            'quantity' => 10,
            'quantityLimit' => -5,
            'defaultQuantityAfterSendingOrderLists' => 5,
            'changeReason' => 'change reason',
        ]);

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ],
            contain: [
                'StockAvailables',
            ],
        )->first();

        $actionLogsTable = TableRegistry::getTableLocator()->get('ActionLogs');
        $actionLogs = $actionLogsTable->find('all')->toArray();
        $this->assertEquals('Die Menge des Produktes <b>Artischocke</b> vom Hersteller <b>Demo Gemüse-Hersteller</b> wurde geändert: Verfügbare Menge: Alter Wert: <b>97</b> Neuer Wert: <b>10</b>, Standard-Menge pro Lieferrhythmus: <b>5</b>, Bestellbar bis zu einer Menge von: <b>-5</b>, Änderungsgrund: <b>change reason</b>.', $actionLogs[0]->text);

        $this->assertEquals(10, $product->stock_available->quantity);
        $this->assertEquals(-5, $product->stock_available->quantity_limit);
        $this->assertEquals(5, $product->stock_available->default_quantity_after_sending_order_lists);

    }

    public function testEditNameOk(): void
    {
    
        $this->loginAsSuperadmin();
        $productId = 346;

        $this->ajaxPost('/admin/products/editName', [
            'productId' => $productId,
            'name' => 'new name',
            'description' => 'new description',
            'descriptionShort' => 'new description short',
            'unity' => 'new unity',
            'idStorageLocation' => 1,
            'isDeclarationOk' => 1,
            'barcode' => '1234567890123',
        ]);

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ],
            contain: [
                'BarcodeProducts',
            ],
        )->first();

        $this->assertEquals('new name', $product->name);
        $this->assertEquals('new description', $product->description);
        $this->assertEquals('new description short', $product->description_short);
        $this->assertEquals('new unity', $product->unity);
        $this->assertEquals(1, $product->id_storage_location);
        $this->assertEquals(1, $product->is_declaration_ok);
        $this->assertEquals('1234567890123', $product->barcode_product->barcode);

        $actionLogsTable = TableRegistry::getTableLocator()->get('ActionLogs');
        $actionLogs = $actionLogsTable->find('all')->toArray();
        $this->assertEquals('Das Produkt <b>Artischocke</b> vom Hersteller <b>Demo Gemüse-Hersteller</b> wurde umbenannt in <i>"new name"</i>.', $actionLogs[0]->text);

    }

    public function testAjaxGetProductsForDropdown(): void
    {
        $this->loginAsSuperadmin();
        $this->ajaxGet('/admin/products/ajaxGetProductsForDropdown/4');
        $this->assertJsonOk();
        $this->assertNotEmpty($this->getJsonDecodedContent());
        $expectedDropdownData = '<option value="">Alle Produkte</option><optgroup label="online-5"><option value="340">Beuschl - Demo Fleisch-Hersteller</option><option value="103">Bratwürstel - Demo Fleisch-Hersteller</option><option value="347">Forelle - Demo Fleisch-Hersteller</option><option value="102">Frankfurter - Demo Fleisch-Hersteller</option><option value="348">Rindfleisch - Demo Fleisch-Hersteller</option></optgroup>';
        $this->assertEquals($expectedDropdownData, $this->getJsonDecodedContent()->dropdownData);
    }
    
    private function deleteProduct($productId): ?Product
    {
        $this->ajaxPost('/admin/products/delete', [
            'productIds' => [$productId]
        ]);
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId
            ]
        )->first();
        return $product;
    }

    private function doPurchasePriceChange($productId, $price): ?Product
    {

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $ids = $productsTable->getProductIdAndAttributeId($productId);

        $this->ajaxPost('/admin/products/editPurchasePrice', [
            'productId' => $productId,
            'purchasePrice' => $price,
        ]);

        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $ids['productId'],
            ],
            contain: [
                'ProductAttributes.PurchasePriceProductAttributes',
                'ProductAttributes.UnitProductAttributes',
                'PurchasePriceProducts',
                'UnitProducts',
            ],
        )->first();

        return $product;

    }

    private function assertSellingPriceChange($productId, $price, $expectedNetPrice, $taxRate, $pricePerUnitEnabled = false, $priceInclPerUnit = 0, $priceUnitName = '', $priceUnitAmount = 0, $priceQuantityInUnits = 0, $changeOpenOrderDetails = false): void
    {
        $price = Configure::read('app.numberHelper')->parseFloatRespectingLocale($price);
        $expectedNetPrice = Configure::read('app.numberHelper')->parseFloatRespectingLocale($expectedNetPrice);
        $this->changeProductPrice($productId, $price, $pricePerUnitEnabled, $priceInclPerUnit, $priceUnitName, $priceUnitAmount, $priceQuantityInUnits, $changeOpenOrderDetails);
        $this->assertJsonOk();
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $netPrice = $productsTable->getNetPrice($price, $taxRate);
        $this->assertEquals(floatval($expectedNetPrice), $netPrice);
    }

    private function assertTaxChange($productId, $newSellingPriceTaxId, $expectedSellingPriceTaxId, $newPurchasePriceTaxId = null, $expectedPurchasePriceTaxId = null): Product
    {
        $data = [
            'productId' => $productId,
            'taxId' => $newSellingPriceTaxId,
        ];
        if ($newPurchasePriceTaxId !== null) {
            $data['purchasePriceTaxId'] = $newPurchasePriceTaxId;
        }

        $this->ajaxPost('/admin/products/editTax', $data);

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ],
            contain: [
                'Taxes',
                'ProductAttributes.PurchasePriceProductAttributes',
                'Manufacturers',
                'PurchasePriceProducts',
            ],
        )->first();
        $this->assertEquals($product->id_tax, $expectedSellingPriceTaxId);

        if ($expectedPurchasePriceTaxId === 'empty') {
            $this->assertEmpty($product->purchase_price_product);
        } else {
            if ($expectedPurchasePriceTaxId !== null) {
                $this->assertEquals($product->purchase_price_product->tax_id, $expectedPurchasePriceTaxId);
            }
        }

        return $product;

    }

    public function testUploadAndDeleteProductImage(): void
    {
        $this->loginAsAdmin();

        $productId = 340;
        $filename = 'img/tests/test-image.jpg';

        $imagesTable = TableRegistry::getTableLocator()->get('Images');

        // START upload image
        $this->ajaxPost('/admin/products/saveUploadedImageProduct', [
            'objectId' => $productId,
            'filename' => $filename,
        ]);

        $this->assertJsonOk();
        $imageId = $this->getJsonDecodedContent()->imageId;

        $imageIdAsPath = $this->Html->getProductImageIdAsPath($imageId);
        $thumbsPath = $this->Html->getProductThumbsPath($imageIdAsPath);
        $expectedFilesizes = [4224,12364,36656];
        $i = 0;
        foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
            $thumbsFileName = $thumbsPath . DS . $imageId . $options['suffix'] . '.' . 'jpg';
            $this->assertEquals($expectedFilesizes[$i], file_exists($thumbsFileName));
            $this->assertTrue(file_exists($thumbsFileName));
            $i++;
        }

        $image = $imagesTable->find('all',
            conditions: [
                'Images.id_image' => $imageId,
            ],
        )->first();
        $this->assertNotEmpty($image);

        // START delete image
        $this->get('/admin/products/deleteImage/' . $productId);

        foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
            $thumbsFileName = $thumbsPath . DS . $imageId . $options['suffix'] . '.' . 'jpg';
            $this->assertFalse(file_exists($thumbsFileName));
        }

        $image = $imagesTable->find('all',
            conditions: [
                'Images.id_image' => $imageId,
            ],
        )->first();
        $this->assertEmpty($image);

    }

}
