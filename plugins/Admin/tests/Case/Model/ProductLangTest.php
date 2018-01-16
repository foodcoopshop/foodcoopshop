<?php

App::uses('InvalidParameterException', 'Error/Exceptions');
App::uses('AppCakeTestCase', 'Test');
App::uses('ProductLang', 'Model');

/**
 * ProductLangTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductLangTest extends AppCakeTestCase
{

    public $ProductLang;

    public function setUp()
    {
        parent::setUp();
        $this->ProductLang = new ProductLang();
    }

    public function testChangeNameWithOneProductAndInvalidStringName()
    {
        $products = array(
            array(346 => array(
                'name' => 'a', // at least 2 chars needed
                'unity' => '',
                'description' => 'Beschreibung',
                'description_short' => 'Kurze Beschreibung'
            ))
        );

        $exceptionThrown = false;

        try {
            $this->ProductLang->changeName($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }

        $expectedResult = array('name' => 'Artischocke', 'unity' => 'Stück', 'description' => '', 'description_short' => '');
        $this->assertProductName($products, $expectedResult);
        $this->assertSame(true, $exceptionThrown);
    }

    /**
     * @expectedException InvalidParameterException
     * @expectedExceptionMessage change name is not allowed for product attributes
     */
    public function testChangeNameForProductAttribute()
    {
        $products = array(
            array('60-10' => 0)
        );
        $this->ProductLang->changeName($products);
    }

    public function testChangeNameWithMultipleProducts()
    {

        $parameters = array(
            'name' => 'test <b>name</b>', // no tags allowed
            'unity' => ' test unity ',    // trim and no tags allowed
            'description' => '    <p>test <br /><b>description</b></p>', // b, p and br allowed
            'description_short' => '<p>test description<br /> short</p>    ' // b, p and br allowed
        );

        $products = array(
            array(102 => $parameters),
            array(346 => $parameters)
        );
        $this->ProductLang->changeName($products);

        $expectedResults = array(
            'name' => 'test name',
            'unity' => 'test unity',
            'description' => '<p>test <br /><b>description</b></p>',
            'description_short' => '<p>test description<br /> short</p>'
        );
        $this->assertProductName($products, $expectedResults);
    }

    private function assertProductName($products, $expectedResults)
    {
        foreach ($products as $product) {
            $productId = key($product);
            $this->ProductLang->recursive = -1;
            $changedProduct = $this->ProductLang->find('first', array(
                'conditions' => array(
                    'ProductLang.id_product' => $productId,
                )
            ));
            $this->assertEquals($expectedResults['name'], $changedProduct['ProductLang']['name'], 'changing the name did not work');
            $this->assertEquals($expectedResults['unity'], $changedProduct['ProductLang']['unity'], 'changing the unity did not work');
            $this->assertEquals($expectedResults['description'], $changedProduct['ProductLang']['description'], 'changing the description did not work');
            $this->assertEquals($expectedResults['description_short'], $changedProduct['ProductLang']['description_short'], 'changing the description short did not work');
        }
    }
}
