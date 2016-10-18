<?php

App::uses('AppCakeTestCase', 'Test');
App::uses('Product', 'Model');

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
class ProductTest extends AppCakeTestCase
{

    public $Product;

    public function setUp()
    {
        parent::setUp();
        $this->Product = new Product();
    }
    
    // called only after the first test method of this class
    public static function setUpBeforeClass()
    {
        self::initTestDatabase();
    }

    public function testEditPrice()
    {
        $this->browser->doFoodCoopShopLogin();
        
        // change price to invalid string
        $price = 'invalid-price';
        $response = $this->changeProductPrice(346, $price);
        $this->assertRegExp('/' . preg_quote('input format for price is wrong') . '/', $response->msg);
        $this->assertJsonError();
        
        $productId = 1000;
        $response = $this->changeProductPrice($productId, '0,15');
        $this->assertRegExp('/' . preg_quote('product ' . $productId . ' not found') . '/', $response->msg);
        $this->assertJsonError();
        
        // change price of product
        $this->checkPriceChange(346, '2,02');
        
        // change price of attribute
        $this->checkPriceChange('60-10', '1,25');
        
        // change price of product with 0% tax
        $this->checkPriceChange('163', '1,60');
    }

    /**
     * checks price in database (getGrossPrice)
     */
    private function checkPriceChange($productId, $price)
    {
        $response = $this->changeProductPrice($productId, $price);
        $this->assertJsonOk();
        $grossPrice = $this->Product->getGrossPrice($productId, $price);
        $this->assertEquals($price, $price, $grossPrice, 'editing price failed');
    }

    /**
     *
     * @param int $productId            
     * @return json string
     */
    private function changeProductPrice($productId, $price)
    {
        $this->browser->post('/admin/products/editPrice', array(
            'data' => array(
                'productId' => $productId,
                'price' => $price
            )
        ));
        return $this->browser->getJsonDecodedContent();
    }

    public function testGetProductIdAndAttributeId()
    {
        $tests = array(
            array(
                'id' => '5',
                'result' => array(
                    'productId' => 5,
                    'attributeId' => 0
                )
            ),
            array(
                'id' => 8,
                'result' => array(
                    'productId' => 8,
                    'attributeId' => 0
                )
            ),
            array(
                'id' => '80-9',
                'result' => array(
                    'productId' => 80,
                    'attributeId' => 9
                )
            )
        );
        
        foreach ($tests as $test) {
            $result = $this->Product->getProductIdAndAttributeId($test['id']);
            $this->assertEquals($test['result'], $result);
        }
    }
}

?>