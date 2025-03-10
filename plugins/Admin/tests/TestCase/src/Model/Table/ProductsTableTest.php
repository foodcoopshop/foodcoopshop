<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductsTableTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function testChangeImageValidImageAndDeleteImage(): void
    {

        // add image
        $productId = 346;
        $products = [
            [$productId => WWW_ROOT . 'img/tests/test-image.jpg']
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeImage($products);

        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId
            ],
            contain: [
                'Images'
            ]
        )->first();
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
        $productsTable->changeImage($products);

        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId
            ],
            contain: [
                'Images'
            ]
        )->first();

        $this->assertTrue(empty($product->image));

        foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
            $thumbsFileName = $thumbsPath . DS . $imageId . $options['suffix'] . '.' . 'jpg';
            $this->assertFalse(file_exists($thumbsFileName), 'physical file not deleted');
        }

    }

    public function testChangeImageInvalidImage(): void
    {
        $file = WWW_ROOT . '/css/global.css';
        $productId = 346;
        $products = [
            [$productId => $file]
        ];

        try {
            $productsTable = $this->getTableLocator()->get('Products');
            $productsTable->changeImage($products);
        } catch (Exception $e) {
            $this->assertEquals('file is not an image: ' . $file, $e->getMessage());
        }
    }

    public function testChangeImageInvalidDomain(): void
    {
        $productId = 346;
        $products = [
            [$productId => 'https://localhost:8080/img/tests/test-image.jpg']
        ];

        try {
            $productsTable = $this->getTableLocator()->get('Products');
            $productsTable->changeImage($products);
        } catch (Exception $e) {
            $this->assertEquals('invalid host', $e->getMessage());
        }
    }

    public function testChangeImageNonExistingFile(): void
    {
        $productId = 346;
        $products = [
            [$productId => Configure::read('App.fullBaseUrl') . '/img/tests/non-existing-file.jpg']
        ];
        $exceptionThrown = false;

        try {
            $productsTable = $this->getTableLocator()->get('Products');
            $productsTable->changeImage($products);
        } catch (Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertSame(true, $exceptionThrown);
    }

    public function testGetCompositeProductIdAndAttributeId(): void
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

        $productsTable = $this->getTableLocator()->get('Products');
        foreach ($tests as $test) {
            $result = $productsTable->getCompositeProductIdAndAttributeId($test['ids']['productId'], $test['ids']['attributeId']);
            $this->assertEquals($test['result'], $result);
        }
    }

    public function testGetProductIdAndAttributeId(): void
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

        $productsTable = $this->getTableLocator()->get('Products');
        foreach ($tests as $test) {
            $result = $productsTable->getProductIdAndAttributeId($test['id']);
            $this->assertEquals($test['result'], $result);
        }
    }

    public function testAddProduct(): void
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $productsTable = $this->getTableLocator()->get('Products');

        $manufacturerId = $customersTable->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $manufacturer = $manufacturersTable->find('all',
            conditions: [
                'Manufacturers.id_manufacturer' => $manufacturerId,
            ]
        )->first();

        $name = 'New product <b>test</b>';
        $descriptionShort = 'description short<img src="test.jpg" />';
        $description = 'description <img src="test.jpg" />';
        $unity = '<b>piece</b>';
        $isDeclarationOk = 0;
        $idStorageLocation = 1;
        $barcode = '1234567890123';
        $newProduct = $productsTable->add($manufacturer, $name, $descriptionShort, $description, $unity, $isDeclarationOk, $idStorageLocation, $barcode);

        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $newProduct->id_product
            ],
            contain: [
                'CategoryProducts',
                'StockAvailables',
                'BarcodeProducts',
            ]
        )->first();

        $this->assertEquals($product->id_product, $newProduct->id_product);
        $this->assertEquals($product->id_manufacturer, $manufacturerId);
        $this->assertEquals($product->active, APP_OFF);
        $this->assertEquals($product->category_products[0]->id_category, Configure::read('app.categoryAllProducts'));
        $this->assertEquals($product->name, 'New product test');
        $this->assertEquals($product->description_short, 'description short');
        $this->assertEquals($product->description, $description);
        $this->assertEquals($product->unity, 'piece');
        $this->assertEquals($product->is_declaration_ok, $isDeclarationOk);
        $this->assertEquals($product->id_tax, $manufacturersTable->getOptionDefaultTaxId($manufacturer->default_tax_id));
        $this->assertEquals($product->stock_available->quantity, 0);
        $this->assertEquals($product->id_storage_location, $idStorageLocation);
        $this->assertEquals($product->barcode_product->barcode, $barcode);
    }

    /**
     * START tests change quantity
     */
    public function testChangeQuantityWithOneProductAndInvalidStringQuantity(): void
    {
        $products = [
            [346 => [
                'quantity' => 'invalid-quantity'
            ]]
        ];

        $exceptionThrown = false;

        try {
            $productsTable = $this->getTableLocator()->get('Products');
            $productsTable->changeQuantity($products);
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertProductQuantity($products, 97.000);
        $this->assertSame(true, $exceptionThrown);
    }

    public function testChangeQuantityWithOneProductAndNegativeQuantity(): void
    {
        $products = [
            [346 => [
                'quantity' => -50
            ]]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeQuantity($products);
        $this->assertProductQuantity($products, -50);
    }

    public function testChangeQuantityWithOneProduct(): void
    {
        $products = [
            [102 => [
                'quantity' => '5'
            ]]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeQuantity($products);
        $this->assertProductQuantity($products);
    }

    public function testChangeQuantityWithOneProductAttribute(): void
    {
        $products = [
            ['60-10' => [
                'quantity' => '5'
            ]]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeQuantity($products);
        $this->assertProductQuantity($products);
    }

    public function testChangeQuantityWithMultipleProductsAndAttributes(): void
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
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeQuantity($products);
        $this->assertProductQuantity($products);
    }

    /**
     * START tests change price
     */
    public function testChangePriceWithOneProductAndInvalidNegativePrice(): void
    {

        $products = [
            [346 => ['gross_price' => '-1']]
        ];

        $exceptionThrown = false;

        try {
            $productsTable = $this->getTableLocator()->get('Products');
            $productsTable->changePrice($products);
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertProductPrice($products, '1,82');
        $this->assertSame(true, $exceptionThrown);
    }

    public function testChangePriceOneProductAndInvalidStringPrice(): void
    {
        $products = [
            [346 => ['gross_price' => 'invalid-price']]
        ];

        $exceptionThrown = false;

        try {
            $productsTable = $this->getTableLocator()->get('Products');
            $productsTable->changePrice($products);
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertProductPrice($products, '1,82');
        $this->assertSame(true, $exceptionThrown);
    }

    public function testChangePriceWithOneProduct(): void
    {
        $products = [
            [102 => ['gross_price' => '5,22']]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $success = $productsTable->changePrice($products);
        $this->assertTrue($success);
        $this->assertProductPrice($products);
    }

    public function testChangePriceWithOneProductAttribute(): void
    {
        $products = [
            ['60-10' => ['gross_price' => '3,22']]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $success = $productsTable->changePrice($products);
        $this->assertTrue($success);
        $this->assertProductPrice($products);
    }

    public function testChangePriceWithMultipleProductsAndAttributes(): void
    {
        $products = [
            [102 => ['gross_price' => '5,22']],
            [346 => ['gross_price' => '1,00']],
            ['60-10' => ['gross_price' => '2,98']]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $success = $productsTable->changePrice($products);
        $this->assertTrue($success);
        $this->assertProductPrice($products);
    }

    public function testChangePriceWithMultipleProductsAndOneWithInvalidNegativePrice(): void
    {

        // 1) change prices to same price to be able to test if the price has not changed
        $samePrice = '2,55';
        $products = [
            [346 => ['gross_price' => $samePrice]],
            [102 => ['gross_price' => $samePrice]],
            [103 => ['gross_price' => $samePrice]]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $success = $productsTable->changePrice($products);
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
            $productsTable->changePrice($products);
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertProductPrice($products, $samePrice);
        $this->assertSame(true, $exceptionThrown);
    }

    /**
     * START tests change deposit
     */

    public function testChangeDepositWithOneProduct(): void
    {
        $products = [
            [102 => '1,00']
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeDeposit($products);
        $this->assertProductDeposit($products);
    }

    public function testChangeDepositWithOneProductAttribute(): void
    {
        $products = [
            ['60-10' => '1,00']
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeDeposit($products);
        $this->assertProductDeposit($products);
    }

    public function testChangeDepositWithMultipleProductsAndOneWithInvalidNegativeDeposit(): void
    {

        // 1) change deposits to same deposit to be able to test if the price has not changed
        $sameDeposit = '1,00';
        $products = [
            [346 => $sameDeposit],
            [102 => $sameDeposit],
            [103 => $sameDeposit]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeDeposit($products);
        $this->assertProductDeposit($products);

        // try to change deposits, but include one invalid deposit
        $products = [
            [346 => '-1'], // invalid deposit
            [102 => '2,00'],
            [103 => '1,00']
        ];

        $exceptionThrown = false;

        try {
            $productsTable->changeDeposit($products);
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertProductDeposit($products, $sameDeposit);
        $this->assertSame(true, $exceptionThrown);
    }

    /**
     * START tests change status
     */

    public function testChangeStatusWithStringStatus(): void
    {
        $products = [
            [102 => 'invalid parameter']
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Products.active for product 102 needs to be 0 or 1');
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeStatus($products);
    }

    public function testChangeStatusWithInvalidIntegerStatus(): void
    {
        $products = [
            [102 => 5] // invalid status
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Products.active for product 102 needs to be 0 or 1');
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeStatus($products);
    }

    public function testChangeStatusForProductAttribute(): void
    {
        $products = [
            ['60-10' => 0]
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('change status is not allowed for product attributes');
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeStatus($products);
    }

    public function testChangeStatusDisableWithOneProduct(): void
    {
        $products = [
            [102 => 0]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $response = $productsTable->changeStatus($products);
        $this->assertEquals(true, $response);
        $this->assertProductStatus($products);
    }

    public function testChangeStatusEnableWithOneProduct(): void
    {
        $products = [
            [102 => 1]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $response = $productsTable->changeStatus($products);
        $this->assertEquals(true, $response);
        $this->assertProductStatus($products);
    }

    public function testChangeStatusWithMultipleProductsAndDifferentStati(): void
    {
        $products = [
            [102 => 1],
            [340 => 1],
            [346 => 0]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $response = $productsTable->changeStatus($products);
        $this->assertEquals(true, $response);
        $this->assertProductStatus($products);
    }

    public function testChangeStatusWithOneProductAndInvalidStatus(): void
    {
        $products = [
            [102 => 5] // invalid status
        ];

        $exceptionThrown = false;

        try {
            $productsTable = $this->getTableLocator()->get('Products');
            $productsTable->changeStatus($products);
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertProductStatus($products, APP_ON);
        $this->assertSame(true, $exceptionThrown);
    }

    public function testChangeStatusWithMultipleProductsAndOneWithInvalidStatus(): void
    {
        $products = [
            [346 => 0],  // pass correct but different to current status
            [102 => -1], // invalid status
            [103 => 0]   // pass correct but different to current status
        ];

        $exceptionThrown = false;

        try {
            $productsTable = $this->getTableLocator()->get('Products');
            $productsTable->changeStatus($products);
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertProductStatus($products, APP_ON);
        $this->assertSame(true, $exceptionThrown);
    }

    public function testChangeNameWithOneProductAndInvalidStringName(): void
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
            $productsTable = $this->getTableLocator()->get('Products');
            $productsTable->changeName($products);
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $expectedResult = ['name' => 'Artischocke', 'unity' => 'Stück', 'description' => '', 'description_short' => ''];
        $this->assertProductName($products, $expectedResult);
        $this->assertSame(true, $exceptionThrown);
    }

    public function testChangeNameForProductAttribute(): void
    {
        $products = [
            ['60-10' => 0]
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('change name is not allowed for product attributes');
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeName($products);
    }

    public function testChangeNameWithMultipleProducts(): void
    {
        $parameters = [
            'name' => 'test <b>name</b>', // no tags allowed
            'unity' => ' test unity ',    // trim and no tags allowed
            'description' => '    <p>test <br /><strong><em>description</em></strong><img src="/test.jpg" /><img src="data:image/png;base64,iVBORw0KGgoAAAANSUCYII=" /></p>',
            'description_short' => '<p>test description<br /> <em>short</em></p>    ',
            'id_storage_location' => 2,
            'barcode' => '1234567890123',
        ];

        $products = [
            [102 => $parameters],
            [346 => $parameters]
        ];
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeName($products);

        $expectedResults = [
            'name' => 'test name',
            'unity' => 'test unity',
            'description' => '<p>test <br /><strong><em>description</em></strong><img src="/test.jpg" /><img src="invalid-image" /></p>',
            'description_short' => '<p>test description<br /> <em>short</em></p>',
            'id_storage_location' => 2,
            'barcode' => '1234567890123',
        ];
        $this->assertProductName($products, $expectedResults);
    }

    private function assertProductName($products, $expectedResults): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        foreach ($products as $product) {

            $productId = key($product);
            $changedProduct = $productsTable->find('all',
                conditions: [
                    'Products.id_product' => $productId,
                ],
                contain: [
                    'BarcodeProducts',
                ]
            )->first();
            $this->assertEquals($expectedResults['name'], $changedProduct->name);
            $this->assertEquals($expectedResults['unity'], $changedProduct->unity);
            $this->assertEquals($expectedResults['description'], $changedProduct->description);
            $this->assertEquals($expectedResults['description_short'], $changedProduct->description_short);

            if (isset($expectedResults['id_storage_location'])) {
                $this->assertEquals($expectedResults['id_storage_location'], $changedProduct->id_storage_location);
            }

            if (isset($expectedResults['barcode'])) {
                $this->assertEquals($expectedResults['barcode'], $changedProduct->barcode_product->barcode);
            }

        }
    }

    private function assertProductQuantity($products, $forceUseThisQuantity = null): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        foreach ($products as $product) {
            $originalProductId = key($product);
            $productAndAttributeId = $productsTable->getProductIdAndAttributeId($originalProductId);
            $productId = $productAndAttributeId['productId'];
            $expectedQuantity = (float) $product[$originalProductId]['quantity'];
            if ($forceUseThisQuantity) {
                $expectedQuantity = $forceUseThisQuantity;
            }
            if ($productAndAttributeId['attributeId'] == 0) {
                $contain = ['StockAvailables'];
            } else {
                $productsTable->getAssociation('ProductAttributes')->setConditions(
                    ['ProductAttributes.id_product_attribute' => $productAndAttributeId['attributeId']]
                );
                $contain = ['ProductAttributes.StockAvailables'];
            }
            $changedProduct = $productsTable->find('all',
                conditions: [
                    'Products.id_product' => $productId
                ],
                contain: $contain
            )->first();
            if ($productAndAttributeId['attributeId'] == 0) {
                $result = $changedProduct->stock_available->quantity;
            } else {
                $result = $changedProduct->product_attributes[0]->stock_available->quantity;
            }
            $this->assertEquals($expectedQuantity, $result);
        }
    }

    private function assertProductDeposit($products, $forceUseThisDeposit = null): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        foreach ($products as $product) {
            $originalProductId = key($product);
            $productAndAttributeId = $productsTable->getProductIdAndAttributeId($originalProductId);
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
                $productsTable->getAssociation('ProductAttributes')->setConditions(
                    ['ProductAttributes.id_product_attribute' => $productAndAttributeId['attributeId']]
                );
            }

            $changedProduct = $productsTable->find('all',
                conditions: [
                    'Products.id_product' => $productId
                ],
                contain: $contain,
            )->first();

            if ($productAndAttributeId['attributeId'] == 0) {
                $resultEntity = $changedProduct->deposit_product;
                ;
            } else {
                $resultEntity = $changedProduct->product_attributes[0]->deposit_product_attribute;
            }
            $this->assertEquals($expectedDeposit, $resultEntity->deposit, 'changing the deposit did not work');
        }
    }

    private function assertProductPrice($products, $forceUseThisPrice = null): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        foreach ($products as $product) {
            $originalProductId = key($product);
            $productAndAttributeId = $productsTable->getProductIdAndAttributeId($originalProductId);
            $productId = $productAndAttributeId['productId'];
            $expectedPrice = $product[$originalProductId]['gross_price'];
            if ($forceUseThisPrice) {
                $expectedPrice = $forceUseThisPrice;
            }
            $contain = ['Taxes'];
            $expectedPrice = Configure::read('app.numberHelper')->parseFloatRespectingLocale($expectedPrice);
            if ($productAndAttributeId['attributeId'] > 0) {
                $productsTable->getAssociation('ProductAttributes')->setConditions(
                    ['ProductAttributes.id_product_attribute' => $productAndAttributeId['attributeId']]
                );
                $contain[] = 'ProductAttributes';
            }
            $changedProduct = $productsTable->find('all',
                conditions: [
                    'Products.id_product' => $productId
                ],
                contain: $contain,
            )->first();
            if ($productAndAttributeId['attributeId'] == 0) {
                $resultEntity = $changedProduct;
            } else {
                $resultEntity = $changedProduct->product_attributes[0];
            }
            $taxRate = $changedProduct->tax->rate ?? 0;
            $this->assertEquals($expectedPrice, $productsTable->getGrossPrice($resultEntity->price, $taxRate));
        }
    }

    private function assertProductStatus($products, $forceUseThisStatus = null): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        foreach ($products as $product) {
            $productId = key($product);
            $expectedStatus = $product[$productId];
            if ($forceUseThisStatus) {
                $expectedStatus = $forceUseThisStatus;
            }
            $changedProduct = $productsTable->find('all',
                conditions: [
                    'Products.id_product' => $productId,
                ]
            )->first();
            $this->assertEquals($expectedStatus, $changedProduct->active, 'changing the active flag did not work');
        }
    }
}
