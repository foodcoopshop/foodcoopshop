<?php

App::uses('AppCakeTestCase', 'Test');
App::uses('Product', 'Model');

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
class ProductsControllerTest extends AppCakeTestCase
{

    public $Product;

    public function setUp()
    {
        parent::setUp();
        $this->Product = new Product();
        $this->changeConfiguration('FCS_SHOW_PRODUCTS_FOR_GUESTS', true);
    }

    public function testProductDetailOfflineManufacturerPublicLoggedOut()
    {
        $productId = 47;
        $this->changeProductStatus($productId, false);
        $this->browser->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assert404NotFoundHeader();
    }

    public function testProductDetailOfflineManufacturerPublicLoggedIn()
    {
        $this->loginAsCustomer();
        $productId = 47;
        $this->changeProductStatus($productId, false);
        $this->browser->get($this->Slug->getProductDetail($productId, 'Demo Product'));
        $this->assert404NotFoundHeader();
    }

    public function testProductDetailOnlineManufacturerPublicLoggedOut()
    {
        $this->browser->get($this->Slug->getProductDetail(47, 'Demo Product'));
        $this->assert200OkHeader();
    }

    public function testProductDetailOnlineManufacturerPublicLoggedIn()
    {
        $this->loginAsCustomer();
        $this->browser->get($this->Slug->getProductDetail(47, 'Demo Product'));
        $this->assert200OkHeader();
    }

    public function testProductDetailOnlineManufacturerPrivateLoggedOut()
    {
        $productId = 47;
        $manufacturerId = 15;
        $this->changeManufacturer($manufacturerId, 'is_private', true);
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
        $sql = 'UPDATE ' . $this->Product->tablePrefix . $this->Product->useTable.' SET active = :active WHERE id_product = :productId;';
        $params = array(
            'productId' => $productId,
            'active' => $active
        );
        $this->Product->getDataSource()->fetchAll($sql, $params);
    }
}
