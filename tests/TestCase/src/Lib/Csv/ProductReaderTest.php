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

class ProductReaderTest extends AppCakeTestCase
{

    private $reader = null;

    public function tearDown(): void
    {
        $this->assertLogFilesForErrors();
    }

    public function testReadCsv()
    {
        $this->reader = ProductReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'productCsvExports' . DS . 'test-products-valid.csv');
        $this->reader->configureType();
        $records = $this->reader->getPreparedRecords();

        $this->assertCount(2, $records);

        $columnCount = 10;
        $this->assertEquals($columnCount, count($records[0]));
        $this->assertEquals($columnCount, count($records[1]));

        $this->assertEquals('Brombeeren', $records[0]['ProductName']);
        $this->assertEquals('frisch geerntet <script>alert(\'evil\')</script>', $records[0]['DescriptionShort']);
        $this->assertEquals('Brombeeren haben viel <b>Vitamin C</b> und sind sehr gesund', $records[0]['Description']);
        $this->assertEquals('1 kg', $records[0]['Unity']);
        $this->assertEquals('1', $records[0]['IsDeclarationOk']);
        $this->assertEquals('1', $records[0]['StorageLocationId']);
        $this->assertEquals(1, $records[0]['Status']);
        $this->assertEquals(23.3, $records[0]['PriceGross']);
        $this->assertEquals(10, $records[0]['TaxRate']);
        $this->assertEquals('2345678901235', $records[0]['Barcode']);
    }

    public function testImportWithErrors()
    {
        $this->reader = ProductReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'productCsvExports' . DS . 'test-products-invalid.csv');
        $this->reader->configureType();
        $manufacturerId = 5;
        $productEntities = $this->reader->import($manufacturerId);

        $errorsA = $productEntities[0]->getErrors();
        $productNameErrorMessage = 'Der Name des Produktes muss aus mindestens 2 Zeichen bestehen.';
        $productActiveErrorMessage = 'Folgende Werte sind gültig: 0, 1';
        $barcodeErrorMessage = 'Die Länge des Barcodes muss genau 13 Zeichen betragen.';

        $this->assertEquals($productNameErrorMessage, $errorsA['name']['minLength']);
        $this->assertEquals($productActiveErrorMessage, $errorsA['active']['inList']);
        $this->assertEquals($barcodeErrorMessage, $errorsA['barcode_product']['barcode']['lengthBetween']);

        $errorsB = $productEntities[1]->getErrors();
        $this->assertEquals($productNameErrorMessage, $errorsB['name']['minLength']);
        $this->assertEquals($productActiveErrorMessage, $errorsB['active']['inList']);
        $this->assertEquals($barcodeErrorMessage, $errorsB['barcode_product']['barcode']['lengthBetween']);

        $productsTable = $this->getTableLocator()->get('Products');
        $this->assertCount(13, $productsTable->find('all'));

    }

    public function testImportSuccessful()
    {
        $this->reader = ProductReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'productCsvExports' . DS . 'test-products-valid.csv');
        $this->reader->configureType();
        $manufacturerId = 5;
        $productEntities = $this->reader->import($manufacturerId);

        $this->assertCount(2, $productEntities);

        $productsTable = $this->getTableLocator()->get('Products');
        $this->assertCount(15, $productsTable->find('all'));

        // first product
        $this->assertEquals($manufacturerId, $productEntities[0]->id_manufacturer);
        $this->assertEquals('Brombeeren', $productEntities[0]->name);
        $this->assertEquals('frisch geerntet alert(\'evil\')', $productEntities[0]->description_short);
        $this->assertEquals('Brombeeren haben viel <b>Vitamin C</b> und sind sehr gesund', $productEntities[0]->description);
        $this->assertEquals('1 kg', $productEntities[0]->unity);
        $this->assertEquals(1, $productEntities[0]->is_declaration_ok);
        $this->assertEquals(1, $productEntities[0]->id_storage_location);
        $this->assertEquals(1, $productEntities[0]->active);
        //$this->assertEquals(21.181818, $productEntities[0]->price);
        //$this->assertEquals(2, $productEntities[0]->id_tax);
        $this->assertEquals('2345678901235', $productEntities[0]->barcode_product->barcode);

        // second product
        $this->assertEquals(0, $productEntities[1]->active);
    }

}
