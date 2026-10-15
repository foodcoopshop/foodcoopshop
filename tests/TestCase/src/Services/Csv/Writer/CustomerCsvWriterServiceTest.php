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
use App\Services\Csv\Writer\CustomerCsvWriterService;
use League\Csv\Bom;
use League\Csv\Reader;
use App\Test\Fixture\ProductsFixture;
use Cake\Core\Configure;

class CustomerCsvWriterServiceTest extends AppCakeTestCase
{

    public string $defaultHeader = 'Id;Name;PLZ;Ort;"Straße + Nummer";Adresszusatz;Handy;Telefon;Gruppe;E-Mail;Status;Guthaben;Bestell-Erinnerung;Guthaben-Erinnerung;Reg.-Datum;"Letzter Abholtag";Kommentar';

    public function testWriteDefault(): void
    {

        $writerService = new CustomerCsvWriterService();
        $writerService->render();
        $result = $writerService->toString();
        $lines  = explode("\n", $result);

        $this->assertEquals(5, count($lines));
        $this->assertEquals(Bom::Utf8->value . $this->defaultHeader, $lines[0]);
        $this->assertEquals('88;"Demo Admin";4644;Scharnstein;"Demostrasse 4";;0600/000000;;Admin;fcs-demo-admin@mailinator.com;1;0,00;1;1;02.12.2014;;test', $lines[1]);
        $this->assertEquals('87;"Demo Mitglied";4644;Scharnstein;"Demostrasse 4";;0664/000000000;;Mitglied;fcs-demo-mitglied@mailinator.com;1;100.000,00;1;1;02.12.2014;;', $lines[2]);
        $this->assertEquals('92;"Demo Superadmin";4644;Demostadt;"Demostrasse 4";;0600/000000;;Superadmin;fcs-demo-superadmin@mailinator.com;1;92,02;1;1;29.09.2016;02.02.2018;', $lines[3]);

    }

    public function testWriteConfigsEnabled(): void
    {

        $this->changeConfiguration('FCS_NEWSLETTER_ENABLED', true);
        $this->changeConfiguration('FCS_INFO_CHANNELS', '1,2,3');
        $this->changeConfiguration('FCS_MEMBER_FEE_PRODUCTS', ProductsFixture::ID_ARTICHOKE);
        $writerService = new CustomerCsvWriterService();
        $writerService->setRequestQueryParams(['year' => '2018']);
        $writerService->render();
        $result = $writerService->toString();
        $lines  = explode("\n", $result);

        $this->assertEquals(5, count($lines));
        $this->assertEquals(Bom::Utf8->value . $this->defaultHeader . ';Newsletter;Infokanal;"Mitgliedsbeitrag 2018"', $lines[0]);
        $this->assertEquals('92;"Demo Superadmin";4644;Demostadt;"Demostrasse 4";;0600/000000;;Superadmin;fcs-demo-superadmin@mailinator.com;1;92,02;1;1;29.09.2016;02.02.2018;;0;;1,82', $lines[3]);

    }

    public function testInfoChannelValuesMatchCsvHeader(): void
    {
        $this->changeConfiguration('FCS_INFO_CHANNELS', '1,2,3');
        $this->changeCustomer(Configure::read('test.customerId'), 'info_channel', 2);
        foreach ([0, 1,] as $newsletterEnabled) {
            foreach (['', (string) ProductsFixture::ID_ARTICHOKE,] as $memberFeeProducts) {
                $this->changeConfiguration('FCS_NEWSLETTER_ENABLED', $newsletterEnabled);
                $this->changeConfiguration('FCS_MEMBER_FEE_PRODUCTS', $memberFeeProducts);
                $writerService = new CustomerCsvWriterService();
                $writerService->setRequestQueryParams(['info_channel' => '2', 'year' => '2018',]);
                $writerService->render();
                $reader = Reader::fromString($writerService->toString());
                $reader->setDelimiter(';');
                $reader->setHeaderOffset(0);
                $records = iterator_to_array($reader->getRecords());
                $this->assertCount(1, $records);
                foreach ($records as $record) {
                    $this->assertSame('Signal', $record[__('Info channel')]);
                    $this->assertSame('', $record[__('Comment')]);
                    if ($newsletterEnabled) {
                        $this->assertSame('0', $record[__('Newsletter')]);
                    }
                    if ($memberFeeProducts !== '') {
                        $this->assertArrayHasKey(__('Member_fee') . ' 2018', $record);
                    }
                }
            }
        }
    }

    public function testWriteInfoChannelFilters(): void
    {
        $this->changeConfiguration('FCS_INFO_CHANNELS', '1,2,3');
        $this->changeCustomer(Configure::read('test.customerId'), 'info_channel', 2);
        $this->changeCustomer(Configure::read('test.adminId'), 'info_channel', 1);
        $writerService = new CustomerCsvWriterService();
        $writerService->setRequestQueryParams(['info_channel' => '2',]);
        $records = $writerService->getRecords();
        $this->assertCount(1, $records);
        $this->assertEquals(Configure::read('test.customerId'), $records[0][0]);
        $this->assertEquals('Signal', $records[0][17]);

        $writerService->setRequestQueryParams(['info_channel' => '1',]);
        $records = $writerService->getRecords();
        $this->assertCount(1, $records);
        $this->assertEquals(Configure::read('test.adminId'), $records[0][0]);
        $this->assertEquals('WhatsApp', $records[0][17]);

        $writerService->setRequestQueryParams(['info_channel' => '0',]);
        $records = $writerService->getRecords();
        $this->assertCount(1, $records);
        $this->assertSame('', $records[0][17]);
    }

}
