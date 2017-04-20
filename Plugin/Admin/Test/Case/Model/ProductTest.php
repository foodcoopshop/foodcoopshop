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

    public function testEditPrice()
    {
        $this->browser->doFoodCoopShopLogin();

        // change price to invalid string
        $price = 'invalid-price';
        $response = $this->changeProductPrice(346, $price);
        $this->assertRegExpWithUnquotedString('input format for price is wrong', $response->msg);
        $this->assertJsonError();

        $productId = 1000;
        $response = $this->changeProductPrice($productId, '0,15');
        $this->assertRegExpWithUnquotedString('product ' . $productId . ' not found', $response->msg);
        $this->assertJsonError();

        // change price of product
        $this->checkPriceChange(346, '2,20', '2,00');

        // change price of attribute
        $this->checkPriceChange('60-10', '1,25', '1,106195');

        // change price of product with 0% tax
        $this->checkPriceChange('163', '1,60', '1,60');
    }

    /**
     * checks price in database (getGrossPrice)
     */
    private function checkPriceChange($productId, $price, $expectedNetPrice)
    {
        $price = str_replace(',', '.', $price);
        $expectedNetPrice = str_replace(',', '.', $expectedNetPrice);
        $response = $this->changeProductPrice($productId, $price);
        $this->assertJsonOk();
        $netPrice = $this->Product->getNetPrice($productId, $price);
        $this->assertEquals(floatval($expectedNetPrice), $netPrice, 'editing price failed');
    }

    /**
     *
     * @param int $productId
     * @param double $price
     * @return json string
     */
    private function changeProductPrice($productId, $price)
    {
        $this->browser->ajaxPost('/admin/products/editPrice', array(
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
