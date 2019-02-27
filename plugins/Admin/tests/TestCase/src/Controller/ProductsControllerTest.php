<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class ProductsControllerTest extends AppCakeTestCase
{

    public $Product;

    public function setUp()
    {
        parent::setUp();
        $this->Product = TableRegistry::getTableLocator()->get('Products');
    }

    public function testChangeProductStatus()
    {
        $this->loginAsSuperadmin();
        $productId = 60;
        $status = APP_OFF;
        $this->httpClient->get('/admin/products/changeStatus/' . $productId . '/' . $status);
        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ]
        ])->first();
        $this->assertEquals($product->active, $status, 'changing product status did not work');
    }

    public function testEditPriceWithInvalidPriceAsSuperadmin()
    {
        $this->loginAsSuperadmin();
        $price = 'invalid-price';
        $this->changeProductPrice(346, $price);
        $response = $this->httpClient->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString('input format not correct: ' . $price, $response->msg);
        $this->assertJsonError();
    }

    public function testEditPriceOfNonExistingProductAsSuperadmin()
    {
        $this->loginAsSuperadmin();
        $productId = 1000;
        $this->changeProductPrice($productId, '0,15');
        // as long as isAuthorized does not return json on ajax requests...
        $this->assertAccessDeniedWithRedirectToLoginForm();
    }

    public function testEditPriceOfMeatManufactuerProductAsVegatableManufacturer()
    {
        $this->loginAsVegetableManufacturer();
        $productId = 102;
        $this->changeProductPrice($productId, '0,15');
        // as long as isAuthorized does not return json on ajax requests...
        $this->assertAccessDeniedWithRedirectToLoginForm();
    }

    public function testEditPriceOfProductAsSuperadminToZero()
    {
        $this->loginAsSuperadmin();
        $this->assertPriceChange(346, '0', '0,00');
    }

    public function testEditPriceOfProductAsSuperadmin()
    {
        $this->loginAsSuperadmin();
        $this->assertPriceChange(346, '2,20', '2,00');
    }

    public function testEditPricePerUnitOfProductAsSuperadmin()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $this->assertPriceChange($productId, 0, 0, true, 15, 'g', 100, 50);
        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'UnitProducts'
            ]
        ])->first();
        $this->assertRegExpWithUnquotedString($this->PricePerUnit->getPricePerUnitBaseInfo($product->unit_product->price_incl_per_unit, $product->unit_product->name, $product->unit_product->amount), '`15,00 € / 100 g');
        
    }

    public function testEditPriceOfAttributeAsSuperadmin()
    {
        $this->loginAsSuperadmin();
        $this->assertPriceChange('60-10', '1,25', '1,106195');
    }

    public function testEditPriceWith0PercentTax()
    {
        $this->loginAsSuperadmin();
        $this->assertPriceChange('163', '1,60', '1,60');
    }
    
    public function testEditDeliveryRhythmInvalidDeliveryRhythmA()
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '3-week');
        $this->assertRegExpWithUnquotedString('Der Lieferrhythmus ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditDeliveryRhythmInvalidDeliveryRhythmB()
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-week', '31.08.2018');
        $this->assertRegExpWithUnquotedString('Der Lieferrhythmus ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditDeliveryRhythmInvalidFirstDeliveryDay()
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '1-week', '30.08.2018');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein Freitag sein.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditDeliveryRhythmOk1Week()
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '1-week');
        $this->assertJsonOk();
    }
    
    public function testEditDeliveryRhythmInvalid2WeekWithoutDate()
    {
        $productId = 346;
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm($productId, '2-week');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein Freitag sein.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditDeliveryRhythmOkFirstOfMonth()
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '1-month', '03.08.2018');
        $this->assertJsonOk();
    }
    
    public function testEditDeliveryRhythmInvalidFirstOfMonth()
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '1-month', '10.08.2018');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein erster Freitag im Monat sein.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditDeliveryRhythmOkLastOfMonth()
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '0-month', '31.08.2018');
        $this->assertJsonOk();
    }
    
    public function testEditDeliveryRhythmInvalidLastOfMonth()
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-month', '10.08.2018');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag muss ein letzter Freitag im Monat sein.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditDeliveryRhythmInvalidIndividualWithoutDeliveryDay()
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-individual');
        $this->assertRegExpWithUnquotedString('Der erste Liefertag ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditDeliveryRhythmInvalidIndividualWithEmptyOrderPossibleUntil()
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-individual', '2018-08-31', '');
        $this->assertRegExpWithUnquotedString('Das Bestellbar-bis-Datum ist nicht gültig.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditDeliveryRhythmInvalidIndividualWithWrongOrderPossibleUntil()
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-individual', '2018-08-31', '2018-09-30');
        $this->assertRegExpWithUnquotedString('Das Bestellbar-bis-Datum muss kleiner als der Liefertag sein.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditDeliveryRhythmOkIndividual()
    {
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(346, '0-individual', '2018-08-31', '2018-08-28');
        $this->assertJsonOk();
    }
    
    public function testEditDeliveryRhythmIndividualInvalidSendOrderListDay()
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '0-individual', '2018-08-31', '2018-08-28', 2, '2019-01-01');
        $this->assertRegExpWithUnquotedString('Das Datum für den Bestellisten-Versand muss zwischen Bestellbar-bis-Datum und dem Liefertag liegen.', $response->msg);
        $this->assertJsonError();
    }
    
    public function testEditDeliveryRhythmOkWithDatabaseAsserts()
    {
        $productId = 346;
        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm($productId, '1-month', '03.08.2018');
        $this->assertJsonOk();
        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ]
        ])->first();
        $this->assertEquals($product->delivery_rhythm_type, 'month');
        $this->assertEquals($product->delivery_rhythm_count, 1);
        $this->assertEquals($product->delivery_rhythm_first_delivery_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')), '03.08.2018');
    }
    
    public function testEditDeliveryRhythmWeeklyInvalidSendOrderListsWeekday()
    {
        $this->loginAsSuperadmin();
        $response = $this->changeProductDeliveryRhythm(346, '1-week', '', '', 15);
        $this->assertRegExpWithUnquotedString('Bitte gib eine Zahl zwischen 0 und 6 an.', $response->msg);
        $this->assertJsonError();
    }
    
    /**
     * asserts price in database (getGrossPrice)
     */
    private function assertPriceChange($productId, $price, $expectedNetPrice, $pricePerUnitEnabled = false, $priceInclPerUnit = 0, $priceUnitName = '', $priceUnitAmount = 0, $priceQuantityInUnits = 0)
    {
        $price = Configure::read('app.numberHelper')->parseFloatRespectingLocale($price);
        $expectedNetPrice = Configure::read('app.numberHelper')->parseFloatRespectingLocale($expectedNetPrice);
        $this->changeProductPrice($productId, $price, $pricePerUnitEnabled, $priceInclPerUnit, $priceUnitName, $priceUnitAmount, $priceQuantityInUnits);
        $this->assertJsonOk();
        $netPrice = $this->Product->getNetPrice($productId, $price);
        $this->assertEquals(floatval($expectedNetPrice), $netPrice, 'editing price failed');
    }
}
