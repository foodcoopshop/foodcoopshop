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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\ORM\TableRegistry;

class ProductsFrontendControllerTest extends AppCakeTestCase
{

    public $Product;

    public function setUp()
    {
        parent::setUp();
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->changeConfiguration('FCS_SHOW_PRODUCTS_FOR_GUESTS', true);
    }

    public function testProductDetailOfflineManufacturerPublicLoggedOut()
    {
        $productId = 60;
        $this->changeProductStatus($productId, 0);
        $this->browser->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assert404NotFoundHeader();
    }

    public function testProductDetailOfflineManufacturerPublicLoggedIn()
    {
        $this->loginAsCustomer();
        $productId = 60;
        $this->changeProductStatus($productId, 0);
        $this->browser->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assert404NotFoundHeader();
    }

    public function testProductDetailOnlineManufacturerPublicLoggedOut()
    {
        $response = $this->browser->get($this->Slug->getProductDetail(60, 'Demo Product'));
        $this->assertNotRegExpWithUnquotedString('0,62&nbsp;€', $response); // price must not be shown
        $this->assert200OkHeader();
    }

    public function testProductDetailOnlineManufacturerPublicLoggedOutShowProductPriceEnabled()
    {
        $this->changeConfiguration('FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS', 1);
        $response = $this->browser->get($this->Slug->getProductDetail(60, 'Demo Product'));
        $this->assertRegExpWithUnquotedString('<div class="price">0,62&nbsp;€</div><div class="deposit">+ <b>0,50&nbsp;€</b> Pfand</div><div class="tax">0,07&nbsp;€</div>', $response);
        $this->assert200OkHeader();
    }

    public function testProductDetailOnlineManufacturerPublicLoggedIn()
    {
        $this->loginAsCustomer();
        $this->browser->get($this->Slug->getProductDetail(60, 'Demo Product'));
        $this->assert200OkHeader();
    }

    public function testProductDetailOnlineManufacturerPrivateLoggedOut()
    {
        $productId = 60;
        $manufacturerId = 15;
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $this->browser->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assertAccessDeniedWithRedirectToLoginForm();
    }

    public function testProductDetailNonExistingLoggedOut()
    {
        $productId = 3;
        $this->browser->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assert404NotFoundHeader();
    }

    protected function changeProductStatus($productId, $active)
    {
        $query = 'UPDATE ' . $this->Product->getTable().' SET active = :active WHERE id_product = :productId;';
        $params = [
            'productId' => $productId,
            'active' => $active
        ];
        $statement = self::$dbConnection->prepare($query);
        $statement->execute($params);
    }
}
