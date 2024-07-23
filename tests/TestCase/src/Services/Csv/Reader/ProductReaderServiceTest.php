<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Services\Csv\Reader\ProductReaderService;

class ProductReaderServiceTest extends AppCakeTestCase
{

    private $reader = null;

    public function testReadCsv()
    {
        $this->reader = ProductReaderService::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'productCsvExports' . DS . 'test-products-valid.csv');
        $this->reader->configureType();
        $records = $this->reader->getPreparedRecords();

        $this->assertCount(2, $records);
        $this->assertEquals(ProductReaderService::COLUMN_COUNT, count($records[0]));
        $this->assertEquals(ProductReaderService::COLUMN_COUNT, count($records[1]));

        $this->assertEquals('Brombeeren', $records[0]['Name']);
        $this->assertEquals('frisch geerntet <script>alert(\'evil\')</script>', $records[0]['Kurze Beschreibung']);
        $this->assertEquals('Brombeeren haben viel <b>Vitamin C</b> und sind sehr gesund', $records[0]['Beschreibung']);
        $this->assertEquals('1 kg', $records[0]['Einheit']);
        $this->assertEquals('1', $records[0]['Produktdeklaration']);
        $this->assertEquals('Keine Kühlung', $records[0]['Lagerort']);
        $this->assertEquals('1', $records[0]['Status']);
        $this->assertEquals(23.3, $records[0]['Bruttopreis']);
        $this->assertEquals(10, $records[0]['Steuersatz']);
        $this->assertEquals(0.5, $records[0]['Pfand']);
        $this->assertEquals('10', $records[0]['Menge']);
    }

    public function testImportWithErrors()
    {

        $this->changeConfiguration('FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS', 1);

        $this->reader = ProductReaderService::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'productCsvExports' . DS . 'test-products-invalid.csv');
        $this->reader->configureType();
        $manufacturerId = 5;
        $productEntities = $this->reader->import($manufacturerId);

        $errorsA = $productEntities[0]->getErrors();
        $productNameErrorMessage = 'Der Name des Produktes muss aus mindestens 2 Zeichen bestehen.';
        $productActiveErrorMessage = 'Folgende Werte sind gültig: 0, 1';
        $productPriceWrongErrorMessage = 'Bitte gib eine Zahl zwischen 0 und 2.000 an.';
        $productIdTaxWrongErrorMessage = 'Folgende Werte sind gültig: 0, 10, 13, 20';

        $this->assertEquals($productNameErrorMessage, $errorsA['name']['minLength']);
        $this->assertEquals($productActiveErrorMessage, $errorsA['active']['inList']);
        $this->assertEquals($productIdTaxWrongErrorMessage, $errorsA['id_tax']['inList']);
        $this->assertEquals('Der Lagerstand muss eine Zahl sein.', $errorsA['stock_available']['quantity']['numeric']);
        $this->assertEquals('Bitte gib eine Zahl zwischen 0 und 100 an.', $errorsA['deposit_product']['deposit']['lessThanOrEqual']);
        $this->assertEquals('Folgende Werte sind gültig: Keine Kühlung, Kühlschrank, Tiefkühler', $errorsA['id_storage_location'][0]);

        $errorsB = $productEntities[1]->getErrors();
        $this->assertEquals($productNameErrorMessage, $errorsB['name']['minLength']);
        $this->assertEquals($productActiveErrorMessage, $errorsB['active']['inList']);
        $this->assertEquals($productPriceWrongErrorMessage, $errorsB['price']['greaterThanOrEqual']);
        $this->assertEquals('Bitte gib eine Zahl zwischen -5.000 und 5.000 an. Feld: Lagerstand / verfügbare Menge', $errorsA['stock_available']['quantity']['lessThanOrEqual']);

        $productsTable = $this->getTableLocator()->get('Products');
        $this->assertCount(14, $productsTable->find('all'));

    }

    public function testImportSuccessful()
    {

        $this->changeConfiguration('FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS', 1);

        $this->reader = ProductReaderService::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'productCsvExports' . DS . 'test-products-valid.csv');
        $this->reader->configureType();
        $manufacturerId = 5;
        $productEntities = $this->reader->import($manufacturerId);
        $this->assertCount(2, $productEntities);

        $productsTable = $this->getTableLocator()->get('Products');
        $this->assertCount(16, $productsTable->find('all'));

        // first product
        $this->assertEquals($manufacturerId, $productEntities[0]->id_manufacturer);
        $this->assertEquals('Brombeeren', $productEntities[0]->name);
        $this->assertEquals('frisch geerntet alert(\'evil\')', $productEntities[0]->description_short);
        $this->assertEquals('Brombeeren haben viel <b>Vitamin C</b> und sind sehr gesund', $productEntities[0]->description);
        $this->assertEquals('1 kg', $productEntities[0]->unity);
        $this->assertEquals(1, $productEntities[0]->is_declaration_ok);
        $this->assertEquals(1, $productEntities[0]->id_storage_location);
        $this->assertEquals(1, $productEntities[0]->active);
        $this->assertEquals(21.181818, $productEntities[0]->price);
        $this->assertEquals(2, $productEntities[0]->id_tax);
        $this->assertEquals(10, $productEntities[0]->stock_available->quantity);
        $this->assertEquals(20, $productEntities[0]->category_products[0]->id_category);
        $this->assertEquals(0.5, $productEntities[0]->deposit_product->deposit);

        // second product
        $this->assertEquals(0, $productEntities[1]->id_tax);
        $this->assertEquals(0, $productEntities[1]->active);
        $this->assertEquals(1.4, $productEntities[1]->price);
        $this->assertEquals(0, $productEntities[1]->stock_available->quantity);
        $this->assertEquals(1, $productEntities[1]->stock_available->always_available);
    }

}
