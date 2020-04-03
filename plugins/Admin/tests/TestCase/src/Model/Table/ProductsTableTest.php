<?php

use App\Lib\Error\Exception\InvalidParameterException;
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\I18n\FrozenDate;

/**
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
class ProductsTableTest extends AppCakeTestCase
{

    public $Product;

    public function setUp(): void
    {
        parent::setUp();
        $this->Product = TableRegistry::getTableLocator()->get('Products');
    }
    
    public function testChangeImageValidImageAndDeleteImage()
    {
        
        // add image
        $productId = 346;
        $products = [
            [$productId => Configure::read('app.cakeServerName') . '/img/tests/test-image.jpg']
        ];
        $this->Product->changeImage($products);
        
        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Images'
            ]
        ])->first();
        $imageId = $product->image->id_image;
        
        $imageIdAsPath = Configure::read('app.htmlHelper')->getProductImageIdAsPath($imageId);
        $thumbsPath = Configure::read('app.htmlHelper')->getProductThumbsPath($imageIdAsPath);
        
        foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
            $thumbsFileName = $thumbsPath . DS . $imageId . $options['suffix'] . '.' . 'jpg';
            $this->assertTrue(file_exists($thumbsFileName), 'physical file not added');
        }
        
        // delete image
        $products = [
            [$productId => 'no-image']
        ];
        $this->Product->changeImage($products);
        
        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Images'
            ]
        ])->first();
        
        $this->assertTrue(empty($product->image));
        
        foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
            $thumbsFileName = $thumbsPath . DS . $imageId . $options['suffix'] . '.' . 'jpg';
            $this->assertFalse(file_exists($thumbsFileName), 'physical file not deleted');
        }
        
    }
    
    public function testChangeImageInvalidImage()
    {
        $productId = 346;
        $products = [
            [$productId => Configure::read('app.cakeServerName') . '/css/global.css']
        ];
        $exceptionThrown = false;
        
        try {
            $this->Product->changeImage($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }
        
        $this->assertSame(true, $exceptionThrown);
    }
    
    public function testChangeImageNonExistingFile()
    {
        $productId = 346;
        $products = [
            [$productId => Configure::read('app.cakeServerName') . '/img/tests/non-existing-file.jpg']
        ];
        $exceptionThrown = false;
        
        try {
            $this->Product->changeImage($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }
        
        $this->assertSame(true, $exceptionThrown);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythmWeekWithFirstDeliveryDay()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-11-02'),
                    'is_stock_product' => '0'
                ]
            ),
            'currentDay' => '2018-10-07',
            'result' => '2018-11-02'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythmWeekNoFirstDeliveryDay()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0'
                ]
            ),
            'currentDay' => '2018-10-07',
            'result' => '2018-10-12'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythmWeekNormal()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0'
                ]
            ),
            'currentDay' => '2018-08-14',
            'result' => '2018-08-17'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythmWeekWithSendOrderListDayOneDayBeforeDefault()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'delivery_rhythm_send_order_list_weekday' => 2
                ]
            ),
            'currentDay' => '2017-08-08',
            'result' => '2017-08-18'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythmWeekWithSendOrderListDayTwoDaysBeforeDefault()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                    'delivery_rhythm_send_order_list_weekday' => 1
                ]
            ),
            'currentDay' => '2020-04-05',
            'result' => '2020-04-10'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm2WeekWithSendOrderListDayMonday()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_send_order_list_weekday' => 1,
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2019-03-01')
                ]
            ),
            'currentDay' => '2019-02-25',
            'result' => '2019-03-15'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm2WeekWithSendOrderListDayThursday()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_send_order_list_weekday' => 4,
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2019-03-01')
                ]
            ),
            'currentDay' => '2019-03-08',
            'result' => '2019-03-15'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm2WeekA()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-10')
                ]
            ),
            'currentDay' => '2018-08-14',
            'result' => '2018-08-24'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm2WeekB()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-03')
                ]
            ),
            'currentDay' => '2018-08-14',
            'result' => '2018-08-17'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm2WeekC()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-07-06')
                ]
            ),
            'currentDay' => '2018-09-15',
            'result' => '2018-09-28'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm2WeekD()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '2',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2019-03-22')
                ]
            ),
            'currentDay' => '2019-03-15',
            'result' => '2019-03-22'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm4Week()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'week',
                    'delivery_rhythm_count' => '4',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-03')
                ]
            ),
            'currentDay' => '2018-08-07',
            'result' => '2018-08-31'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm1MonthA()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '0',
                ]
            ),
            'currentDay' => '2017-08-07',
            'result' => '2017-09-01'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm1MonthB()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '1',
                    'is_stock_product' => '1',
                ]
            ),
            'currentDay' => '2017-08-07',
            'result' => '2017-08-11'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm0MonthA()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                ]
            ),
            'currentDay' => '2018-09-13',
            'result' => '2018-09-28'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythm0MonthB()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'month',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                ]
            ),
            'currentDay' => '2018-08-07',
            'result' => '2018-08-31'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    public function testCalculatePickupDayRespectingDeliveryRhythmIndividual()
    {
        $data = [
            'product' => $this->Product->newEntity(
                [
                    'delivery_rhythm_type' => 'individual',
                    'delivery_rhythm_count' => '0',
                    'is_stock_product' => '0',
                    'delivery_rhythm_first_delivery_day' => new FrozenDate('2018-08-03')
                ]
            ),
            'currentDay' => '2017-08-07',
            'result' => '2018-08-03'
        ];
        $this->assertPickupDay($data['product'], $data['currentDay'], $data['result']);
    }
    
    private function assertPickupDay($product, $currentDay, $expectedResult)
    {
        $result = $this->Product->calculatePickupDayRespectingDeliveryRhythm($product, $currentDay);
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetCompositeProductIdAndAttributeId()
    {
        $tests = [
            [
                'ids' => [
                    'productId' => 5,
                    'attributeId' => 0
                ],
                'result' => '5',
            ],
            [
                'ids' => [
                    'productId' => 8,
                    'attributeId' => 0
                ],
                'result' => 8,
            ],
            [
                'ids' => [
                    'productId' => 80,
                    'attributeId' => 9
                ],
                'result' => '80-9',
            ]
        ];

        foreach ($tests as $test) {
            $result = $this->Product->getCompositeProductIdAndAttributeId($test['ids']['productId'], $test['ids']['attributeId']);
            $this->assertEquals($test['result'], $result);
        }
    }
    
    public function testGetProductIdAndAttributeId()
    {
        $tests = [
            [
                'id' => '5',
                'result' => [
                    'productId' => 5,
                    'attributeId' => 0
                ]
            ],
            [
                'id' => 8,
                'result' => [
                    'productId' => 8,
                    'attributeId' => 0
                ]
            ],
            [
                'id' => '80-9',
                'result' => [
                    'productId' => 80,
                    'attributeId' => 9
                ]
            ]
        ];

        foreach ($tests as $test) {
            $result = $this->Product->getProductIdAndAttributeId($test['id']);
            $this->assertEquals($test['result'], $result);
        }
    }

    public function testAddProduct()
    {
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();
        $newProduct = $this->Product->add($manufacturer);

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $newProduct->id_product
            ],
            'contain' => [
                'CategoryProducts',
                'StockAvailables'
            ]
        ])->first();

        $this->assertEquals($product->id_product, $newProduct->id_product);
        $this->assertEquals($product->id_manufacturer, $manufacturerId);
        $this->assertEquals($product->active, APP_OFF);
        $this->assertEquals($product->category_products[0]->id_category, Configure::read('app.categoryAllProducts'));
        $this->assertEquals($product->name, 'Neues Produkt von ' . $manufacturer->name);
        $this->assertEquals($product->id_tax, $this->Manufacturer->getOptionDefaultTaxId($manufacturer->default_tax_id));
        $this->assertEquals($product->stock_available->quantity, 0);
    }

    /**
     * START tests change quantity
     */
    public function testChangeQuantityWithOneProductAndInvalidStringQuantity()
    {
        $products = [
            [346 => [
                'quantity' => 'invalid-quantity'
            ]]
        ];

        $exceptionThrown = false;

        try {
            $this->Product->changeQuantity($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }

        $this->assertProductQuantity($products, '97');
        $this->assertSame(true, $exceptionThrown);
    }

    public function testChangeQuantityWithOneProductAndNegativeQuantity()
    {
        $products = [
            [346 => [
                'quantity' => -50
            ]]
        ];
        $this->Product->changeQuantity($products);
        $this->assertProductQuantity($products, -50);
    }

    public function testChangeQuantityWithOneProduct()
    {
        $products = [
            [102 => [
                'quantity' => '5'
            ]]
        ];
        $this->Product->changeQuantity($products);
        $this->assertProductQuantity($products);
    }

    public function testChangeQuantityWithOneProductAttribute()
    {
        $products = [
            ['60-10' => [
                'quantity' => '5'
            ]]
        ];
        $this->Product->changeQuantity($products);
        $this->assertProductQuantity($products);
    }

    public function testChangeQuantityWithMultipleProductsAndAttributes()
    {
        $products = [
            [102 => [
                'quantity' => '5'
            ]],
            [346 => [
                'quantity' => '1'
            ]],
            ['60-10' => [
                'quantity' => '90'
            ]]
        ];
        $this->Product->changeQuantity($products);
        $this->assertProductQuantity($products);
    }

    /**
     * START tests change price
     */
    public function testChangePriceWithOneProductAndInvalidNegativePrice()
    {

        $products = [
            [346 => ['gross_price' => '-1']]
        ];

        $exceptionThrown = false;

        try {
            $this->Product->changePrice($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }

        $this->assertProductPrice($products, '1,82');
        $this->assertSame(true, $exceptionThrown);
    }

    public function testChangePriceOneProductAndInvalidStringPrice()
    {
        $products = [
            [346 => ['gross_price' => 'invalid-price']]
        ];

        $exceptionThrown = false;

        try {
            $this->Product->changePrice($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }

        $this->assertProductPrice($products, '1,82');
        $this->assertSame(true, $exceptionThrown);
    }

    public function testChangePriceWithOneProduct()
    {
        $products = [
            [102 => ['gross_price' => '5,22']]
        ];
        $success = $this->Product->changePrice($products);
        $this->assertTrue($success);
        $this->assertProductPrice($products);
    }

    public function testChangePriceWithOneProductAttribute()
    {
        $products = [
            ['60-10' => ['gross_price' => '3,22']]
        ];
        $success = $this->Product->changePrice($products);
        $this->assertTrue($success);
        $this->assertProductPrice($products);
    }

    public function testChangePriceWithMultipleProductsAndAttributes()
    {
        $products = [
            [102 => ['gross_price' => '5,22']],
            [346 => ['gross_price' => '1,00']],
            ['60-10' => ['gross_price' => '2,98']]
        ];
        $success = $this->Product->changePrice($products);
        $this->assertTrue($success);
        $this->assertProductPrice($products);
    }

    public function testChangePriceWithMultipleProductsAndOneWithInvalidNegativePrice()
    {

        // 1) change prices to same price to be able to test if the price has not changed
        $samePrice = '2,55';
        $products = [
            [346 => ['gross_price' => $samePrice]],
            [102 => ['gross_price' => $samePrice]],
            [103 => ['gross_price' => $samePrice]]
        ];
        $success = $this->Product->changePrice($products);
        $this->assertTrue($success);
        $this->assertProductPrice($products);
        
        // try to change prices, but include one invalid price
        $products = [
            [346 => ['gross_price' => '-1']], // invalid price
            [102 => ['gross_price' => '2,58']],
            [103 => ['gross_price' => '1,01']]
        ];

        $exceptionThrown = false;

        try {
            $this->Product->changePrice($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }

        $this->assertProductPrice($products, $samePrice);
        $this->assertSame(true, $exceptionThrown);
    }

    /**
     * START tests change deposit
     */

    public function testChangeDepositWithOneProduct()
    {
        $products = [
            [102 => '1,00']
        ];
        $this->Product->changeDeposit($products);
        $this->assertProductDeposit($products);
    }

    public function testChangeDepositWithOneProductAttribute()
    {
        $products = [
            ['60-10' => '1,00']
        ];
        $this->Product->changeDeposit($products);
        $this->assertProductDeposit($products);
    }

    public function testChangeDepositWithMultipleProductsAndOneWithInvalidNegativeDeposit()
    {

        // 1) change deposits to same deposit to be able to test if the price has not changed
        $sameDeposit = '1,00';
        $products = [
            [346 => $sameDeposit],
            [102 => $sameDeposit],
            [103 => $sameDeposit]
        ];
        $this->Product->changeDeposit($products);
        $this->assertProductDeposit($products);

        // try to change deposits, but include one invalid deposit
        $products = [
            [346 => '-1'], // invalid deposit
            [102 => '2,00'],
            [103 => '1,00']
        ];

        $exceptionThrown = false;

        try {
            $this->Product->changeDeposit($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }

        $this->assertProductDeposit($products, $sameDeposit);
        $this->assertSame(true, $exceptionThrown);
    }

    /**
     * START tests change status
     */

    public function testChangeStatusWithStringStatus()
    {
        $products = [
            [102 => 'invalid parameter']
        ];
        $this->expectException('App\Lib\Error\Exception\InvalidParameterException');
        $this->expectExceptionMessage('Products.active for product 102 needs to be 0 or 1');
        $this->Product->changeStatus($products);
    }

    public function testChangeStatusWithInvalidIntegerStatus()
    {
        $products = [
            [102 => 5] // invalid status
        ];
        $this->expectException('App\Lib\Error\Exception\InvalidParameterException');
        $this->expectExceptionMessage('Products.active for product 102 needs to be 0 or 1');
        $this->Product->changeStatus($products);
    }

    public function testChangeStatusForProductAttribute()
    {
        $products = [
            ['60-10' => 0]
        ];
        $this->expectException('App\Lib\Error\Exception\InvalidParameterException');
        $this->expectExceptionMessage('change status is not allowed for product attributes');
        $this->Product->changeStatus($products);
    }

    public function testChangeStatusDisableWithOneProduct()
    {
        $products = [
            [102 => 0]
        ];
        $response = $this->Product->changeStatus($products);
        $this->assertEquals(true, $response);
        $this->assertProductStatus($products);
    }

    public function testChangeStatusEnableWithOneProduct()
    {
        $products = [
            [102 => 1]
        ];
        $response = $this->Product->changeStatus($products);
        $this->assertEquals(true, $response);
        $this->assertProductStatus($products);
    }

    public function testChangeStatusWithMultipleProductsAndDifferentStati()
    {
        $products = [
            [102 => 1],
            [340 => 1],
            [346 => 0]
        ];
        $response = $this->Product->changeStatus($products);
        $this->assertEquals(true, $response);
        $this->assertProductStatus($products);
    }

    public function testChangeStatusWithOneProductAndInvalidStatus()
    {
        $products = [
            [102 => 5] // invalid status
        ];

        $exceptionThrown = false;

        try {
            $this->Product->changeStatus($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }

        $this->assertProductStatus($products, APP_ON);
        $this->assertSame(true, $exceptionThrown);
    }

    public function testChangeStatusWithMultipleProductsAndOneWithInvalidStatus()
    {
        $products = [
            [346 => 0],  // pass correct but different to current status
            [102 => -1], // invalid status
            [103 => 0]   // pass correct but different to current status
        ];

        $exceptionThrown = false;

        try {
            $this->Product->changeStatus($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }

        $this->assertProductStatus($products, APP_ON);
        $this->assertSame(true, $exceptionThrown);
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
            $this->Product->changeName($products);
        } catch (InvalidParameterException $e) {
            $exceptionThrown = true;
        }
        
        $expectedResult = ['name' => 'Artischocke', 'unity' => 'Stück', 'description' => '', 'description_short' => ''];
        $this->assertProductName($products, $expectedResult);
        $this->assertSame(true, $exceptionThrown);
    }
    
    public function testChangeNameForProductAttribute()
    {
        $products = [
            ['60-10' => 0]
        ];
        $this->expectException('App\Lib\Error\Exception\InvalidParameterException');
        $this->expectExceptionMessage('change name is not allowed for product attributes');
        $this->Product->changeName($products);
    }
    
    public function testChangeNameWithMultipleProducts()
    {
        
        $parameters = [
            'name' => 'test <b>name</b>', // no tags allowed
            'unity' => ' test unity ',    // trim and no tags allowed
            'description' => '    <p>test <br /><strong><em>description</em></strong></p>',
            'description_short' => '<p>test description<br /> <em>short</em></p>    '
        ];
        
        $products = [
            [102 => $parameters],
            [346 => $parameters]
        ];
        $this->Product->changeName($products);
        
        $expectedResults = [
            'name' => 'test name',
            'unity' => 'test unity',
            'description' => '<p>test <br /><strong><em>description</em></strong></p>',
            'description_short' => '<p>test description<br /> <em>short</em></p>'
        ];
        $this->assertProductName($products, $expectedResults);
    }
    
    /**
     * START helper methods
     */
    
    private function assertProductName($products, $expectedResults)
    {
        foreach ($products as $product) {
            $productId = key($product);
            $changedProduct = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $productId,
                ]
            ])->first();
            $this->assertEquals($expectedResults['name'], $changedProduct->name, 'changing the name did not work');
            $this->assertEquals($expectedResults['unity'], $changedProduct->unity, 'changing the unity did not work');
            $this->assertEquals($expectedResults['description'], $changedProduct->description, 'changing the description did not work');
            $this->assertEquals($expectedResults['description_short'], $changedProduct->description_short, 'changing the description short did not work');
        }
    }

    private function assertProductQuantity($products, $forceUseThisQuantity = null)
    {
        foreach ($products as $product) {
            $originalProductId = key($product);
            $productAndAttributeId = $this->Product->getProductIdAndAttributeId($originalProductId);
            $productId = $productAndAttributeId['productId'];
            $expectedQuantity = $product[$originalProductId]['quantity'];
            if ($forceUseThisQuantity) {
                $expectedQuantity = $forceUseThisQuantity;
            }
            if ($productAndAttributeId['attributeId'] == 0) {
                $contain = ['StockAvailables'];
            } else {
                $this->Product->getAssociation('ProductAttributes')->setConditions(
                    ['ProductAttributes.id_product_attribute' => $productAndAttributeId['attributeId']]
                );
                $contain = ['ProductAttributes.StockAvailables'];
            }
            $changedProduct = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $productId
                ],
                'contain' => $contain
            ])->first();
            if ($productAndAttributeId['attributeId'] == 0) {
                $result = $changedProduct->stock_available->quantity;
            } else {
                $result = $changedProduct->product_attributes[0]->stock_available->quantity;
            }
            $this->assertEquals($expectedQuantity, $result, 'changing the quantity flag did not work');
        }
    }

    private function assertProductDeposit($products, $forceUseThisDeposit = null)
    {
        foreach ($products as $product) {
            $originalProductId = key($product);
            $productAndAttributeId = $this->Product->getProductIdAndAttributeId($originalProductId);
            $productId = $productAndAttributeId['productId'];
            $expectedDeposit = $product[$originalProductId];
            if ($forceUseThisDeposit) {
                $expectedDeposit = $forceUseThisDeposit;
            }
            $expectedDeposit = Configure::read('app.numberHelper')->parseFloatRespectingLocale($expectedDeposit);
            if ($productAndAttributeId['attributeId'] == 0) {
                $contain = ['DepositProducts'];
            } else {
                $contain = ['ProductAttributes', 'ProductAttributes.DepositProductAttributes'];
                $this->Product->getAssociation('ProductAttributes')->setConditions(
                    ['ProductAttributes.id_product_attribute' => $productAndAttributeId['attributeId']]
                );
            }

            $changedProduct = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $productId
                ],
                'contain' => $contain
            ])->first();

            if ($productAndAttributeId['attributeId'] == 0) {
                $resultEntity = $changedProduct->deposit_product;
                ;
            } else {
                $resultEntity = $changedProduct->product_attributes[0]->deposit_product_attribute;
            }
            $this->assertEquals($expectedDeposit, $resultEntity->deposit, 'changing the deposit did not work');
        }
    }

    private function assertProductPrice($products, $forceUseThisPrice = null)
    {
        foreach ($products as $product) {
            $originalProductId = key($product);
            $productAndAttributeId = $this->Product->getProductIdAndAttributeId($originalProductId);
            $productId = $productAndAttributeId['productId'];
            $expectedPrice = $product[$originalProductId]['gross_price'];
            if ($forceUseThisPrice) {
                $expectedPrice = $forceUseThisPrice;
            }
            $expectedPrice = Configure::read('app.numberHelper')->parseFloatRespectingLocale($expectedPrice);
            if ($productAndAttributeId['attributeId'] == 0) {
                $contain = [];
            } else {
                $this->Product->getAssociation('ProductAttributes')->setConditions(
                    ['ProductAttributes.id_product_attribute' => $productAndAttributeId['attributeId']]
                );
                $contain = ['ProductAttributes'];
            }
            $changedProduct = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $productId
                ],
                'contain' => $contain
            ])->first();
            if ($productAndAttributeId['attributeId'] == 0) {
                $resultEntity = $changedProduct;
            } else {
                $resultEntity = $changedProduct->product_attributes[0];
            }
            $this->assertEquals($expectedPrice, $this->Product->getGrossPrice($productId, $resultEntity->price), 'changing the price did not work');
        }
    }

    private function assertProductStatus($products, $forceUseThisStatus = null)
    {
        foreach ($products as $product) {
            $productId = key($product);
            $expectedStatus = $product[$productId];
            if ($forceUseThisStatus) {
                $expectedStatus = $forceUseThisStatus;
            }
            $changedProduct = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $productId,
                ]
            ])->first();
            $this->assertEquals($expectedStatus, $changedProduct->active, 'changing the active flag did not work');
        }
    }
}
