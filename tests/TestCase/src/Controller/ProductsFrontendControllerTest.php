<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Lib\DeliveryRhythm\DeliveryRhythm;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;

class ProductsFrontendControllerTest extends AppCakeTestCase
{

    protected $Product;
    protected $Unit;

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->Product = $this->getTableLocator()->get('Products');
        $this->changeConfiguration('FCS_SHOW_PRODUCTS_FOR_GUESTS', true);
    }

    public function testProductDetailOfflineManufacturerPublicLoggedOut()
    {
        $productId = 60;
        $this->changeProductStatus($productId, 0);
        $this->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assertResponseCode(404);
    }

    public function testProductDetailOfflineManufacturerPublicLoggedIn()
    {
        $this->loginAsCustomer();
        $productId = 60;
        $this->changeProductStatus($productId, 0);
        $this->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assertResponseCode(404);
    }

    public function testProductDetailOnlineManufacturerPublicLoggedOut()
    {
        $this->get($this->Slug->getProductDetail(60, 'Milch'));
        $this->assertResponseNotContains('0,62 €'); // price must not be shown
        $this->assertResponseCode(200);
    }

    public function testProductDetailOnlineManufacturerPublicLoggedOutShowProductPriceEnabled()
    {
        $this->changeConfiguration('FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS', 1);
        $this->get($this->Slug->getProductDetail(60, 'Milch'));
        $this->assertResponseContains('<div class="price" title="Steuersatz: 13%">0,62 €</div><div class="deposit">+ <b>0,50 €</b> Pfand</div><div class="tax">0,07 €</div>');
        $this->assertResponseCode(200);
    }

    public function testProductDetailOnlineManufacturerPublicLoggedIn()
    {
        $this->loginAsCustomer();
        $this->get($this->Slug->getProductDetail(60, 'Milch'));
        $this->assertResponseCode(200);
    }

    public function testProductDetailOnlineManufacturerPrivateLoggedOut()
    {
        $productId = 60;
        $manufacturerId = 15;
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $this->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assertAccessDeniedFlashMessage();
    }

    public function testProductDetailNonExistingLoggedOut()
    {
        $productId = 3;
        $this->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assertResponseCode(404);
    }

    public function testProductDetailIndividualDeliveryRhythmOrderPossibleUntilOver()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $this->changeProductDeliveryRhythm($productId, '0-individual', '31.08.2018', '28.08.2018');
        $this->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assertResponseCode(404);
    }

    public function testProductDetailIndividualDeliveryRhythmOrderPossibleUntilNotOver()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $this->changeProductDeliveryRhythm($productId, '0-individual', date('Y-m-d', strtotime('next friday')), date('Y-m-d'));
        $this->get($this->Slug->getProductDetail($productId, 'Artischocke'));
        $this->assertResponseCode(200);
    }

    public function testProductDetailDeliveryBreakActive()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $manufacturerId = 5;
        $this->changeManufacturerNoDeliveryDays($manufacturerId, DeliveryRhythm::getDeliveryDateByCurrentDayForDb());
        $this->get($this->Slug->getProductDetail($productId, 'Artischocke'));
        $this->assertResponseContains('<i class="fas fa-fw fa-lg fa-times"></i> Lieferpause!');
    }

    public function testProductDetailProductNoPurchasePricePerPiece()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $productId = 340;
        $this->get($this->Slug->getProductDetail($productId, 'Beuschl'));
        $this->assertResponseCode(404);
    }

    public function testProductDetailProductNoPurchasePricePerUnit()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $productId = 340;
        $this->Unit = $this->getTableLocator()->get('Units');
        $this->Unit->saveUnits($productId, 0, true, 1, 'kg', 1, 0.4);
        $this->get($this->Slug->getProductDetail($productId, 'Beuschl'));
        $this->assertResponseCode(404);
    }

    public function testProductDetailAttributeNoPurchasePricePerPiece()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $productId = 350;
        $this->get($this->Slug->getProductDetail($productId, 'Lagerprodukt-mit-Varianten'));
        $this->assertResponseNotContains('1 kg');
        $this->assertResponseContains('0,5 kg');
        $this->assertResponseContains('ca. 0,5 kg');
    }

    public function testProductDetailAttributeNoPurchasePricePerUnit()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $productId = 348;
        $this->get($this->Slug->getProductDetail($productId, 'Rindfleisch'));
        $this->assertResponseNotContains('ca. 0,5 kg');
        $this->assertResponseContains('ca. 300 g');
    }

    public function testProductDetailProductWithAllAttributesRemovedDueToNoPurchasePrice()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $this->loginAsSuperadmin();
        $productId = 348;
        $this->Unit = $this->getTableLocator()->get('Units');
        $this->Unit->saveUnits($productId, 12, false, 1, 'kg', 1, 0.4);
        $this->get($this->Slug->getProductDetail($productId, 'Beuschl'));
        $this->assertResponseCode(404);
    }

    public function testProductDetailHtmlProductCatalogWeekly()
    {
        $this->loginAsCustomer();
        $productId = 60;
        $this->get($this->Slug->getProductDetail($productId, 'Milch'));
        $product = $this->Product->find('all', [
            'conditions' => [
                'id_product' => $productId,
            ],
        ])->first();
        $nextDeliveryDay = DeliveryRhythm::getNextDeliveryDayForProduct($product, $this);
        $pickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($nextDeliveryDay));
        $this->assertResponseContains('<span class="pickup-day">'.$pickupDay.'</span>');
    }

    public function testProductDetailHtmlProductCatalogInstantOrder()
    {
        $this->loginAsSuperadmin();
        $this->get($this->Slug->getOrderDetailsList().'/initInstantOrder/' . Configure::read('test.customerId'));
        $this->loginAsSuperadminAddOrderCustomerToSession($_SESSION);
        $productId = 60;
        $this->get($this->Slug->getProductDetail($productId, 'Milch'));
        $nextDeliveryDay = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
        $pickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($nextDeliveryDay));
        $this->assertResponseContains('<span class="pickup-day">'.$pickupDay.'</span>');
    }

    protected function changeProductStatus($productId, $active)
    {
        $query = 'UPDATE ' . $this->Product->getTable().' SET active = :active WHERE id_product = :productId;';
        $params = [
            'productId' => $productId,
            'active' => $active
        ];
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);
    }

}
