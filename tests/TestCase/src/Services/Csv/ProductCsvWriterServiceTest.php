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

    /*
    public function tearDown(): void
    {
        $this->assertLogFilesForErrors();
    }
    */

    public function testWrite()
    {
        $productIds = [349, 350, 351];
        $writerService = new ProductCsvWriterService();
        $writerService->setProductIds($productIds);
        $writerService->render();
        $result = $writerService->writer->toString();
		$lines  = explode("\n", $result);

        $this->assertEquals(Writer::BOM_UTF8 . 'Produkt;Status', $lines[0]);
        $this->assertEquals('Lagerprodukt;1', $lines[1]);
        $this->assertEquals('"Lagerprodukt mit Varianten";1', $lines[2]);
        $this->assertEquals('"Lagerprodukt 2";1', $lines[3]);

    }

}
