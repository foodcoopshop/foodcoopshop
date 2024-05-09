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
use League\Csv\Writer;
use App\Services\Csv\Writer\ManufacturerCsvWriterService;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;

class ManufacturerCsvWriterServiceTest extends AppCakeTestCase
{

    public $defaultHeader = 'Id;Name';

    public function testWriteDefault()
    {
        $this->setDummyRequest();

        $writerService = new ManufacturerCsvWriterService();
        $writerService->render();
        $result = $writerService->toString();
        $lines  = explode("\n", $result);

        $this->assertEquals(6, count($lines));
        $this->assertEquals(Writer::BOM_UTF8 . $this->defaultHeader, $lines[0]);
        
        $this->assertEquals('4;"Demo Fleisch-Hersteller"', $lines[1]);
        $this->assertEquals('5;"Demo Gemüse-Hersteller"', $lines[2]);
        $this->assertEquals('15;"Demo Milch-Hersteller"', $lines[3]);
        $this->assertEquals('16;"Hersteller ohne Customer-Eintrag"', $lines[4]);

    }

}
