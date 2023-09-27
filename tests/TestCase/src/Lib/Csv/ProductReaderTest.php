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

    public function setUp(): void
    {
        $this->reader = ProductReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'productCsvExports' . DS . 'test-products.csv');
    }

    public function tearDown(): void
    {
        $this->assertLogFilesForErrors();
    }

    public function testRead()
    {
        $records = $this->reader->getRecords();

        $this->assertCount(2, $records);
        $this->assertEquals(8, count($this->reader->nth(0)));
        $this->assertEquals(8, count($this->reader->nth(1)));

        $this->assertEquals(5, $this->reader->nth(0)['ManufacturerId']);
        $this->assertEquals('Brombeeren', $this->reader->nth(0)['ProductName']);
        $this->assertEquals('frisch geerntet', $this->reader->nth(0)['DescriptionShort']);
        $this->assertEquals('Brombeeren haben viel Vitamin C und sind sehr gesund', $this->reader->nth(0)['Description']);
        $this->assertEquals('1 kg', $this->reader->nth(0)['Unity']);
        $this->assertEquals('1', $this->reader->nth(0)['IsDeclarationOk']);
        $this->assertEquals('1', $this->reader->nth(0)['StorageLocationId']);
        $this->assertEquals('2345678901235', $this->reader->nth(0)['Barcode']);

    }

    public function testImport()
    {
        $productEntities = $this->reader->import();
        foreach($productEntities as $productEntity) {
            $this->assertIsObject($productEntity);
        }
    }

}
