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
use App\Services\Csv\Writer\CustomerCsvWriterService;

class CustomerCsvWriterServiceTest extends AppCakeTestCase
{

    public string $defaultHeader = 'Id;Name;PLZ;Ort;"Straße + Nummer";Adresszusatz;Handy;Telefon;Gruppe;E-Mail;Status;Guthaben;Bestell-Erinnerung;Guthaben-Aufladung-Erinnerung;Reg.-Datum;"Letzter Abholtag";Kommentar';

    public function testWriteDefault()
    {

        $writerService = new CustomerCsvWriterService();
        $writerService->render();
        $result = $writerService->toString();
        $lines  = explode("\n", $result);

        $this->assertEquals(5, count($lines));
        $this->assertEquals(Writer::BOM_UTF8 . $this->defaultHeader, $lines[0]);
        $this->assertEquals('88;"Demo Admin";4644;Scharnstein;"Demostrasse 4";;0600/000000;;Admin;fcs-demo-admin@mailinator.com;1;0,00;1;1;02.12.2014;;test', $lines[1]);
        $this->assertEquals('87;"Demo Mitglied";4644;Scharnstein;"Demostrasse 4";;0664/000000000;;Mitglied;fcs-demo-mitglied@mailinator.com;1;100.000,00;1;1;02.12.2014;;', $lines[2]);
        $this->assertEquals('92;"Demo Superadmin";4644;Demostadt;"Demostrasse 4";;0600/000000;;Superadmin;fcs-demo-superadmin@mailinator.com;1;92,02;1;1;29.09.2016;02.02.2018;', $lines[3]);

    }

    public function testWriteConfigsEnabled()
    {

        $this->changeConfiguration('FCS_NEWSLETTER_ENABLED', true);
        $this->changeConfiguration('FCS_MEMBER_FEE_PRODUCTS', '346');
        $writerService = new CustomerCsvWriterService();
        $writerService->setRequestQueryParams(['year' => '2018']);
        $writerService->render();
        $result = $writerService->toString();
        $lines  = explode("\n", $result);

        $this->assertEquals(5, count($lines));
        $this->assertEquals(Writer::BOM_UTF8 . $this->defaultHeader . ';Newsletter;"Mitgliedsbeitrag 2018"', $lines[0]);
        $this->assertEquals('92;"Demo Superadmin";4644;Demostadt;"Demostrasse 4";;0600/000000;;Superadmin;fcs-demo-superadmin@mailinator.com;1;92,02;1;1;29.09.2016;02.02.2018;;0;1,82', $lines[3]);

    }

}
