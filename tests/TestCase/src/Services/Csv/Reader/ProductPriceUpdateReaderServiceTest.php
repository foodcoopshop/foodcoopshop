<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Volker Peters
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Services\Csv\Reader\ProductReaderService;
use App\Services\Csv\Reader\ProductPriceUpdateReaderService;

class ProductPriceUpdateReaderServiceTest extends AppCakeTestCase
{

    // Manufacturer "Demo Gemüse-Hersteller" from fixture (has 4 existing products)
    private const int MANUFACTURER_ID = 5;
    private const float SURCHARGE = 10.0;

    // Tax rate 10% → tax_id=2 in test fixtures
    private const string TAX_RATE = '10';

    private const string BASE_IMPORT_CSV = "Name,Kurze Beschreibung,Beschreibung,Bruttopreis,Steuersatz,Pfand,Menge,Einheit,Status,Produktdeklaration,Lagerort,Bestellnummer\n"
        . "Produkt Unveraendert,kurz,lang,1.10,10,0,0,1 Stück,1,0,Keine Kühlung,UPDATE-001\n"
        . "Produkt Preis Geaendert,kurz,lang,2.20,10,0,0,1 Stück,1,0,Keine Kühlung,UPDATE-002\n"
        . "Produkt Wird Deaktiviert,kurz,lang,3.30,10,0,0,1 Stück,1,0,Keine Kühlung,UPDATE-003\n";

    private const string PRICE_UPDATE_CSV = "Name,Kurze Beschreibung,Beschreibung,Bruttopreis,Steuersatz,Pfand,Menge,Einheit,Status,Produktdeklaration,Lagerort,Bestellnummer\n"
        . "Produkt Unveraendert,kurz,lang,1.10,10,0,0,1 Stück,1,0,Keine Kühlung,UPDATE-001\n"
        . "Produkt Preis Geaendert,kurz,lang,4.40,10,0,0,1 Stück,1,0,Keine Kühlung,UPDATE-002\n"
        . "Neues Produkt,kurz,lang,1.50,10,0,0,1 Stück,1,0,Keine Kühlung,UPDATE-NEW\n";
        // UPDATE-003 is intentionally missing → should be deactivated

    private function importBaseProducts(): void
    {
        $reader = ProductReaderService::fromString(self::BASE_IMPORT_CSV);
        $reader->configureType();
        $reader->import(self::MANUFACTURER_ID);
    }

    private function getProductByOrderNumber(string $orderNumber): mixed
    {
        $productsTable = $this->getTableLocator()->get('Products');
        return $productsTable->find('all',
            conditions: ['manufacturer_order_number' => $orderNumber],
            contain: ['PurchasePriceProducts'],
        )->first();
    }

    public function testPriceUpdateReturnsCorrectCounts(): void
    {
        $this->importBaseProducts();

        $reader = ProductPriceUpdateReaderService::fromString(self::PRICE_UPDATE_CSV);
        $reader->configureType();
        $result = $reader->priceUpdate(self::MANUFACTURER_ID, self::SURCHARGE);

        $this->assertEquals(2, $result['updated']);
        $this->assertEquals(1, $result['inserted']);
        $this->assertEquals(1, $result['deactivated']);
        $this->assertEmpty($result['errors']);
    }

    public function testPriceUpdateSetsPurchasePriceForExistingProduct(): void
    {
        $this->importBaseProducts();

        $reader = ProductPriceUpdateReaderService::fromString(self::PRICE_UPDATE_CSV);
        $reader->configureType();
        $reader->priceUpdate(self::MANUFACTURER_ID, self::SURCHARGE);

        // UPDATE-001: gross purchase 1.10, tax 10% → net = 1.10 / 1.10 = 1.0
        $product = $this->getProductByOrderNumber('UPDATE-001');
        $this->assertNotNull($product);
        $this->assertNotNull($product->purchase_price_product);
        $this->assertEqualsWithDelta(1.0, $product->purchase_price_product->price, 0.00001);
    }

    public function testPriceUpdateAppliesSurchargeToSellingPrice(): void
    {
        $this->importBaseProducts();

        $reader = ProductPriceUpdateReaderService::fromString(self::PRICE_UPDATE_CSV);
        $reader->configureType();
        $reader->priceUpdate(self::MANUFACTURER_ID, self::SURCHARGE);

        // UPDATE-001: purchase_net=1.0, surcharge=10% → selling_net = 1.0 * 1.10 = 1.10
        $product = $this->getProductByOrderNumber('UPDATE-001');
        $this->assertEqualsWithDelta(1.10, $product->price, 0.00001);

        // UPDATE-002: gross purchase 4.40, tax 10% → purchase_net = 4.0, selling_net = 4.0 * 1.10 = 4.40
        $product2 = $this->getProductByOrderNumber('UPDATE-002');
        $this->assertEqualsWithDelta(4.0, $product2->purchase_price_product->price, 0.00001);
        $this->assertEqualsWithDelta(4.40, $product2->price, 0.00001);
    }

    public function testPriceUpdateDeactivatesMissingProduct(): void
    {
        $this->importBaseProducts();

        $reader = ProductPriceUpdateReaderService::fromString(self::PRICE_UPDATE_CSV);
        $reader->configureType();
        $reader->priceUpdate(self::MANUFACTURER_ID, self::SURCHARGE);

        $product = $this->getProductByOrderNumber('UPDATE-003');
        $this->assertNotNull($product);
        $this->assertEquals(APP_OFF, $product->active);
    }

    public function testPriceUpdateInsertsNewProduct(): void
    {
        $this->importBaseProducts();

        $productsTable = $this->getTableLocator()->get('Products');
        $countBefore = $productsTable->find('all',
            conditions: ['id_manufacturer' => self::MANUFACTURER_ID],
        )->count();

        $reader = ProductPriceUpdateReaderService::fromString(self::PRICE_UPDATE_CSV);
        $reader->configureType();
        $reader->priceUpdate(self::MANUFACTURER_ID, self::SURCHARGE);

        $countAfter = $productsTable->find('all',
            conditions: ['id_manufacturer' => self::MANUFACTURER_ID],
        )->count();

        $this->assertEquals($countBefore + 1, $countAfter);

        // UPDATE-NEW: gross purchase 1.50, tax 10% → purchase_net = 1.50/1.10 ≈ 1.363636
        //             selling_net = 1.363636 * 1.10 = 1.50
        $newProduct = $this->getProductByOrderNumber('UPDATE-NEW');
        $this->assertNotNull($newProduct);
        $this->assertEquals('Neues Produkt', $newProduct->name);
        $this->assertEqualsWithDelta(1.50 / 1.10, $newProduct->purchase_price_product->price, 0.00001);
        $this->assertEqualsWithDelta(1.50, $newProduct->price, 0.00001);
    }

}
