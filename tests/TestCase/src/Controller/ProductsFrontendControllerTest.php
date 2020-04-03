<?php
/**
 *
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
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class ProductsFrontendControllerTest extends AppCakeTestCase
{

    public $Product;

    public function setUp(): void
    {
        parent::setUp();
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->changeConfiguration('FCS_SHOW_PRODUCTS_FOR_GUESTS', true);
    }

    public function testProductDetailOfflineManufacturerPublicLoggedOut()
    {
        $productId = 60;
        $this->changeProductStatus($productId, 0);
        $this->httpClient->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assert404NotFoundHeader();
    }

    public function testProductDetailOfflineManufacturerPublicLoggedIn()
    {
        $this->loginAsCustomer();
        $productId = 60;
        $this->changeProductStatus($productId, 0);
        $this->httpClient->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assert404NotFoundHeader();
    }

    public function testProductDetailOnlineManufacturerPublicLoggedOut()
    {
        $this->httpClient->followOneRedirectForNextRequest();
        $response = $this->httpClient->get($this->Slug->getProductDetail(60, 'Demo Product'));
        $this->assertDoesNotMatchRegularExpressionWithUnquotedString('0,62 €', $response->getStringBody()); // price must not be shown
        $this->assert200OkHeader();
    }

    public function testProductDetailOnlineManufacturerPublicLoggedOutShowProductPriceEnabled()
    {
        $this->changeConfiguration('FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS', 1);
        $this->httpClient->followOneRedirectForNextRequest();
        $response = $this->httpClient->get($this->Slug->getProductDetail(60, 'Demo Product'));
        $this->assertRegExpWithUnquotedString('<div class="price">0,62 €</div><div class="deposit">+ <b>0,50 €</b> Pfand</div><div class="tax">0,07 €</div>', $response->getStringBody());
        $this->assert200OkHeader();
    }

    public function testProductDetailOnlineManufacturerPublicLoggedIn()
    {
        $this->loginAsCustomer();
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getProductDetail(60, 'Demo Product'));
        $this->assert200OkHeader();
    }

    public function testProductDetailOnlineManufacturerPrivateLoggedOut()
    {
        $productId = 60;
        $manufacturerId = 15;
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assertAccessDeniedWithRedirectToLoginForm();
    }

    public function testProductDetailNonExistingLoggedOut()
    {
        $productId = 3;
        $this->httpClient->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assert404NotFoundHeader();
    }
    
    public function testProductDetailIndividualDeliveryRhythmOrderPossibleUntilOver()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $this->changeProductDeliveryRhythm($productId, '0-individual', '31.08.2018', '28.08.2018');
        $this->httpClient->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assert404NotFoundHeader();
    }
    
    public function testProductDetailIndividualDeliveryRhythmOrderPossibleUntilNotOver()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $this->changeProductDeliveryRhythm($productId, '0-individual', date('Y-m-d', strtotime('next friday')), date('Y-m-d'));
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assert200OkHeader();
    }
    
    public function testProductDetailDeliveryBreakActive()
    {
        $this->loginAsSuperadmin();
        $productId = 346;
        $manufacturerId = 5;
        $this->changeManufacturerNoDeliveryDays($manufacturerId, Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb());
        $this->httpClient->get($this->Slug->getProductDetail($productId, 'Artischocke'));
        $this->assertRegExpWithUnquotedString('<i class="fa fa-lg fa-times"></i> Lieferpause!', $this->httpClient->getContent());
        
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
