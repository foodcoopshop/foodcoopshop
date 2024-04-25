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
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Services\Csv\ProductCsvWriterService;
use League\Csv\Writer;

class ProductCsvWriterServiceTest extends AppCakeTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->updateAll(['is_stock_product' => APP_ON], []);
        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $manufacturersTable->updateAll(['stock_management_enabled' => APP_ON], []);
    }

    public function testWriteWithoutPurchasePrices()
    {

        $productIds = [344, 346, 349, 350, 351];
        $writerService = new ProductCsvWriterService();
        $writerService->setProductIds($productIds);
        $writerService->render();
        $result = $writerService->writer->toString();
		$lines  = explode("\n", $result);

        $this->assertEquals(10, count($lines));
        $this->assertEquals(Writer::BOM_UTF8 . 'Id;Produkt;Hersteller;Status;Einheit;Anzahl;"Verkaufspreis brutto";"Preis pro";Lagerwert', $lines[0]);
        $this->assertEquals('346;Artischocke;"Demo Gemüse-Hersteller";1;Stück;97;1,820000;;176,54', $lines[1]);
        $this->assertEquals('344;Knoblauch;"Demo Gemüse-Hersteller";1;"100 g";78;0,640000;;49,92', $lines[2]);
        $this->assertEquals('349;Lagerprodukt;"Demo Gemüse-Hersteller";1;;5;5,000000;;25,00', $lines[3]);
        $this->assertEquals('351;"Lagerprodukt 2";"Demo Gemüse-Hersteller";1;ca. 1 kg;999;15,000000;"1 kg";14.985,00', $lines[4]);
        $this->assertEquals('350-13;"Lagerprodukt mit Varianten";"Demo Gemüse-Hersteller";1;"0,5 kg";5;2,000000;;10,00', $lines[5]);
        $this->assertEquals('350-14;"Lagerprodukt mit Varianten";"Demo Gemüse-Hersteller";1;"1 kg";999;4,000000;;3.996,00', $lines[6]);
        $this->assertEquals('350-15;"Lagerprodukt mit Varianten";"Demo Gemüse-Hersteller";1;ca. 0,5 kg;999;10,000000;"1 kg";4.995,00', $lines[7]);
        $this->assertEquals(';;;;;;;;24.237,46', $lines[8]);

    }

    public function testWriteWithPurchasePrices()
    {

        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);

        $productIds = [347, 346, 60, 350, 351];
        $writerService = new ProductCsvWriterService();
        $writerService->setProductIds($productIds);
        $writerService->render();
        $result = $writerService->writer->toString();
		$lines  = explode("\n", $result);

        $this->assertEquals(10, count($lines));
        $this->assertEquals(Writer::BOM_UTF8 . 'Id;Produkt;Hersteller;Status;Einheit;Anzahl;"Einkaufspreis netto";"Preis pro";Lagerwert', $lines[0]);
        $this->assertEquals('346;Artischocke;"Demo Gemüse-Hersteller";1;Stück;97;1,200000;;116,40', $lines[1]);
        $this->assertEquals('347;Forelle;"Demo Fleisch-Hersteller";1;Stück;999;0,980000;"100 g";3.426,57', $lines[2]);
        $this->assertEquals('351;"Lagerprodukt 2";"Demo Gemüse-Hersteller";1;ca. 1 kg;999;0,000000;"1 kg";0,00', $lines[3]);
        $this->assertEquals('350-13;"Lagerprodukt mit Varianten";"Demo Gemüse-Hersteller";1;"0,5 kg";5;1,400000;;7,00', $lines[4]);
        $this->assertEquals('350-14;"Lagerprodukt mit Varianten";"Demo Gemüse-Hersteller";1;"1 kg";999;0,000000;;0,00', $lines[5]);
        $this->assertEquals('350-15;"Lagerprodukt mit Varianten";"Demo Gemüse-Hersteller";1;ca. 0,5 kg;999;6,000000;"1 kg";2.997,00', $lines[6]);
        $this->assertEquals('60-10;Milch;"Demo Milch-Hersteller";1;0,5l;19;0,250000;;4,75', $lines[7]);
        $this->assertEquals(';;;;;;;;6.551,72', $lines[8]);

    }

}
