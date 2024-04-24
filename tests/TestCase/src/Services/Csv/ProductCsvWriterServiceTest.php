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
use Cake\Log\Log;

class ProductCsvWriterServiceTest extends AppCakeTestCase
{

    /*
    public function tearDown(): void
    {
        $this->assertLogFilesForErrors();
    }
    */

    public function testWrite()
    {
        $productIds = [344, 346, 349, 350, 351];
        $writerService = new ProductCsvWriterService();
        $writerService->setProductIds($productIds);
        $writerService->render();
        $result = $writerService->writer->toString();
		$lines  = explode("\n", $result);

        $this->assertEquals(10, count($lines));
        $this->assertEquals(Writer::BOM_UTF8 . 'Id;Produkt;Hersteller;Einheit', $lines[0]);
        $this->assertEquals('346;Artischocke;"Demo Gemüse-Hersteller";StÃ¼ck', $lines[1]);
        $this->assertEquals('344;Knoblauch;"Demo Gemüse-Hersteller";"100 g"', $lines[2]);
        $this->assertEquals('349;Lagerprodukt;"Demo Gemüse-Hersteller";', $lines[3]);
        $this->assertEquals('351;"Lagerprodukt 2";"Demo Gemüse-Hersteller";ca.Â 1Â kg', $lines[4]);
        $this->assertEquals('350;"Lagerprodukt mit Varianten";"Demo Gemüse-Hersteller";', $lines[5]);
        $this->assertEquals('350-13;"Lagerprodukt mit Varianten";"Demo Gemüse-Hersteller";"0,5 kg"', $lines[6]);
        $this->assertEquals('350-14;"Lagerprodukt mit Varianten";"Demo Gemüse-Hersteller";"1 kg"', $lines[7]);
        $this->assertEquals('350-15;"Lagerprodukt mit Varianten";"Demo Gemüse-Hersteller";ca. 0,5 kg', $lines[8]);

    }

}
