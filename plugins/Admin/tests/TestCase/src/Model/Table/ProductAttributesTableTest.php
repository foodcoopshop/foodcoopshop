<?php

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;

/**
 * ProductTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductAttributesTableTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public $ProductAttribute;

    public function setUp(): void
    {
        parent::setUp();
        $this->Product = $this->getTableLocator()->get('Products');
        $this->ProductAttribute = $this->getTableLocator()->get('ProductAttributes');
    }

    public function testAddProductAttribute()
    {
        $productId = 346;
        $attributeId = 29;

        $this->ProductAttribute->add($productId, $attributeId);

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId,
            ],
            'contain' => [
                'ProductAttributes.StockAvailables',
            ]
        ])->first();

        $this->assertEquals($product->product_attributes[0]->default_on, 1);
        $this->assertEquals($product->product_attributes[0]->price, 0);
        $this->assertEquals($product->product_attributes[0]->stock_available->quantity, 0);
        $this->assertEquals($product->price, 0);
    }

    public function testEditProductAttribute()
    {
        $productId = 350;
        $productAttributeId = 13;
        $barcode = '1234567890123';

        $this->loginAsSuperadmin();
        $this->ajaxPost('/admin/products/editProductAttribute', [
            'productId' => $productId,
            'productAttributeId' => $productAttributeId,
            'barcode' => $barcode,
            'deleteProductAttribute' => 0,
        ]);
        $this->assertJsonOk();

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId,
            ],
            'contain' => [
                'ProductAttributes.BarcodeProductAttributes',
            ]
        ])->first();

        $this->assertEquals($product->product_attributes[0]->barcode_product_attribute->barcode, $barcode);

    }

    public function testDeleteProductAttribute()
    {
        $productId = 350;
        $productAttributeId = 13;

        $this->loginAsSuperadmin();
        $this->ajaxPost('/admin/products/editProductAttribute', [
            'productId' => $productId,
            'productAttributeId' => $productAttributeId,
            'deleteProductAttribute' => 1,
        ]);
        $this->assertJsonOk();

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId,
            ],
            'contain' => [
                'ProductAttributes.BarcodeProductAttributes',
            ]
        ])->first();

        $this->assertEquals(count($product->product_attributes), 2);
        $this->assertEmpty($product->product_attributes[0]->barcode_product_attribute);

    }


}
