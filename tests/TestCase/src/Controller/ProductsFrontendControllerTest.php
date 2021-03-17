<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;

class ProductsFrontendControllerTest extends AppCakeTestCase
{
    use AppIntegrationTestTrait;
    use LoginTrait;

    public $Product;

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
        $this->assertResponseContains('<div class="price">0,62 €</div><div class="deposit">+ <b>0,50 €</b> Pfand</div><div class="tax">0,07 €</div>');
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
        $this->changeManufacturerNoDeliveryDays($manufacturerId, Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb());
        $this->get($this->Slug->getProductDetail($productId, 'Artischocke'));
        $this->assertResponseContains('<i class="fa fa-fw fa-lg fa-times"></i> Lieferpause!');
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

    protected function changeProductDeliveryRhythm($productId, $deliveryRhythmType, $deliveryRhythmFirstDeliveryDay = '', $deliveryRhythmOrderPossibleUntil = '', $deliveryRhythmSendOrderListWeekday = '', $deliveryRhythmSendOrderListDay = '')
    {
        $this->ajaxPost('/admin/products/editDeliveryRhythm', [
            'productIds' => [$productId],
            'deliveryRhythmType' => $deliveryRhythmType,
            'deliveryRhythmFirstDeliveryDay' => $deliveryRhythmFirstDeliveryDay,
            'deliveryRhythmOrderPossibleUntil' => $deliveryRhythmOrderPossibleUntil,
            'deliveryRhythmSendOrderListWeekday' => $deliveryRhythmSendOrderListWeekday,
            'deliveryRhythmSendOrderListDay' => $deliveryRhythmSendOrderListDay,
        ]);
        return json_decode($this->_getBodyAsString());
    }
}
