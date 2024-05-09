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

class ManufacturerCsvWriterServiceTest extends AppCakeTestCase
{

    public $defaultHeader = 'Id;Name;PLZ;Ort;"Straße + Nummer";Adresszusatz;Handy;Telefon;E-Mail;Status;Pfandkonto;Lagerprodukte;"Nur für Mitglieder";Ansprechperson';

    public function testWriteDefault()
    {
        $this->setDummyRequest();

        $writerService = new ManufacturerCsvWriterService();
        $writerService->render();
        $result = $writerService->toString();
        $lines  = explode("\n", $result);

        $this->assertEquals(6, count($lines));
        $this->assertEquals(Writer::BOM_UTF8 . $this->defaultHeader, $lines[0]);
        
        $this->assertEquals('4;"Demo Fleisch-Hersteller";4644;Scharnstein;"Demostrasse 4";;;;fcs-demo-fleisch-hersteller@mailinator.com;1;;0;0;', $lines[1]);
        $this->assertEquals('5;"Demo Gemüse-Hersteller";4644;Scharnstein;"Demostrasse 4";;;;fcs-demo-gemuese-hersteller@mailinator.com;1;0,50;1;0;"Demo Admin"', $lines[2]);
        $this->assertEquals('15;"Demo Milch-Hersteller";4644;Scharnstein;"Demostrasse 4";;;;fcs-demo-milch-hersteller@mailinator.com;1;0,50;0;0;', $lines[3]);
        $this->assertEquals('16;"Hersteller ohne Customer-Eintrag";4644;Scharnstein;"Demostrasse 4";;;;fcs-hersteller-ohne-customer-eintrag@mailinator.com;1;;0;0;', $lines[4]);

    }

}
