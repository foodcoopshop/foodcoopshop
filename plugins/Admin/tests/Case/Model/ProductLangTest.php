<?php

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
use App\Error\Exception\InvalidParameterException;
use App\Test\TestCase\AppCakeTestCase;
use Cake\ORM\TableRegistry;

class ProductLangTest extends AppCakeTestCase
{

    public $ProductLang;

    public function setUp()
    {
        parent::setUp();
        $this->ProductLang = TableRegistry::get('ProductLangs');
    }

    public function testChangeNameWithOneProductAndInvalidStringName()
    {
        $products = [
            [346 => [
                'name' => 'a', // at least 2 chars needed
                'unity' => '',
                'description' => 'Beschreibung',
                'description_short' => 'Kurze Beschreibung'
            ]]
        ];

        $exceptionThrown = false;

        try {
            $this->ProductLang->changeName($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }

        $expectedResult = ['name' => 'Artischocke', 'unity' => 'Stück', 'description' => '', 'description_short' => ''];
        $this->assertProductName($products, $expectedResult);
        $this->assertSame(true, $exceptionThrown);
    }

    /**
     * @expectedException InvalidParameterException
     * @expectedExceptionMessage change name is not allowed for product attributes
     */
    public function testChangeNameForProductAttribute()
    {
        $products = [
            ['60-10' => 0]
        ];
        $this->ProductLang->changeName($products);
    }

    public function testChangeNameWithMultipleProducts()
    {

        $parameters = [
            'name' => 'test <b>name</b>', // no tags allowed
            'unity' => ' test unity ',    // trim and no tags allowed
            'description' => '    <p>test <br /><b>description</b></p>', // b, p and br allowed
            'description_short' => '<p>test description<br /> short</p>    ' // b, p and br allowed
        ];

        $products = [
            [102 => $parameters],
            [346 => $parameters]
        ];
        $this->ProductLang->changeName($products);

        $expectedResults = [
            'name' => 'test name',
            'unity' => 'test unity',
            'description' => '<p>test <br /><b>description</b></p>',
            'description_short' => '<p>test description<br /> short</p>'
        ];
        $this->assertProductName($products, $expectedResults);
    }

    private function assertProductName($products, $expectedResults)
    {
        foreach ($products as $product) {
            $productId = key($product);
            $changedProduct = $this->ProductLang->find('all', [
                'conditions' => [
                    'ProductLangs.id_product' => $productId,
                ]
            ])->first();
            $this->assertEquals($expectedResults['name'], $changedProduct['ProductLangs']['name'], 'changing the name did not work');
            $this->assertEquals($expectedResults['unity'], $changedProduct['ProductLangs']['unity'], 'changing the unity did not work');
            $this->assertEquals($expectedResults['description'], $changedProduct['ProductLangs']['description'], 'changing the description did not work');
            $this->assertEquals($expectedResults['description_short'], $changedProduct['ProductLangs']['description_short'], 'changing the description short did not work');
        }
    }
}
