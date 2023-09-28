<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Lib\Csv\ProductReader;
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;

class ProductReaderTest extends AppCakeTestCase
{

    private $reader = null;

    public function setUp(): void
    {
        $this->reader = ProductReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'productCsvExports' . DS . 'test-products.csv');
        $this->reader->configureType();
    }

    public function tearDown(): void
    {
        $this->assertLogFilesForErrors();
    }

    public function testReadCsv()
    {
        $records = $this->reader->getPreparedRecords();

        $this->assertCount(2, $records);

        $columnCount = 10;
        $this->assertEquals($columnCount, count($records[0]));
        $this->assertEquals($columnCount, count($records[1]));

        $this->assertEquals('Brombeeren', $records[0]['ProductName']);
        $this->assertEquals('frisch geerntet', $records[0]['DescriptionShort']);
        $this->assertEquals('Brombeeren haben viel Vitamin C und sind sehr gesund', $records[0]['Description']);
        $this->assertEquals('1 kg', $records[0]['Unity']);
        $this->assertEquals('1', $records[0]['IsDeclarationOk']);
        $this->assertEquals('1', $records[0]['StorageLocationId']);
        $this->assertEquals(1, $records[0]['Status']);
        $this->assertEquals(23.3, $records[0]['PriceGross']);
        $this->assertEquals(10, $records[0]['TaxRate']);
        $this->assertEquals('2345678901235', $records[0]['Barcode']);
    }

    public function testImportSuccessful()
    {
        $manufacturerId = 5;
        $productEntities = $this->reader->import($manufacturerId);
        
        $this->assertCount(2, $productEntities);
        foreach($productEntities as $productEntity) {
            $this->assertIsObject($productEntity);
        }

        $this->assertEquals($manufacturerId, $productEntities[0]->id_manufacturer);
        $this->assertEquals('Brombeeren', $productEntities[0]->name);
        $this->assertEquals('frisch geerntet', $productEntities[0]->description_short);
        $this->assertEquals('Brombeeren haben viel Vitamin C und sind sehr gesund', $productEntities[0]->description);
        $this->assertEquals('1 kg', $productEntities[0]->unity);
        $this->assertEquals(1, $productEntities[0]->is_declaration_ok);
        $this->assertEquals(1, $productEntities[0]->id_storage_location);
        $this->assertEquals(1, $productEntities[0]->active);
        $this->assertEquals(21.181818, $productEntities[0]->price);
        $this->assertEquals(2, $productEntities[0]->id_tax);
        $this->assertEquals('2345678901235', $productEntities[0]->barcode_product->barcode);
    }

}
