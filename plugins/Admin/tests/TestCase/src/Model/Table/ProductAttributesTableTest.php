<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductAttributesTableTest extends AppCakeTestCase
{

    public $ProductAttribute;

    public function setUp()
    {
        parent::setUp();
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->ProductAttribute = TableRegistry::getTableLocator()->get('ProductAttributes');
    }

    public function testAddProductAttribute()
    {
        $productId = 346;
        $attributeId = 29;

        $this->ProductAttribute->add($productId, $attributeId);

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'ProductLangs',
                'ProductShops',
                'ProductAttributes.StockAvailables',
                'ProductAttributes.ProductAttributeShops',
            ]
        ])->first();

        $this->assertEquals($product->product_attributes[0]->product_attribute_shop->default_on, 1);
        $this->assertEquals($product->product_attributes[0]->product_attribute_shop->price, 0);
        $this->assertEquals($product->product_attributes[0]->stock_available->quantity, 999);
        $this->assertEquals($product->product_shop->price, 0);
    }
}
