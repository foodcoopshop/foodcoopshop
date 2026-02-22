<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use App\Services\Csv\Reader\Banking\SparkasseDeBankingReaderService;

class SparkasseDeBankingReaderServiceTest extends AppCakeTestCase
{

    public function testRead(): void
    {
        $reader = SparkasseDeBankingReaderService::from(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'sparkasse-de.csv');
        $records = $reader->getPreparedRecords();
        foreach($records as $record) {
            $this->assertEquals(4, count($record));
        }

        $this->assertEquals('2026-01-21 00:00:00.000000', $records[0]['date']);
        $this->assertEquals(581.47, (float)$records[0]['amount']);
        $this->assertEquals(Configure::read('test.adminId'), $records[0]['original_id_customer']);

        $this->assertEquals(2, count($records));
    }

    public function testCheckStructureNotOk(): void
    {
        $reader = SparkasseDeBankingReaderService::from(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'sparkasse-de-wrong-structure.csv');
        $this->assertFalse($reader->checkStructure());
    }

    public function testCheckStructureOk(): void
    {
        $reader = SparkasseDeBankingReaderService::from(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'sparkasse-de.csv');
        $this->assertTrue($reader->checkStructure());
    }

    public function testReadMultipleRecords(): void
    {
        $reader = SparkasseDeBankingReaderService::from(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS. 'sparkasse-de-multi.csv');
        $records = $reader->getPreparedRecords();

        // Example: 3 lines in CSV, 1 with negative amount → 2 remaining lines
        $this->assertCount(2, $records);

        // Sort: newest date first
        $this->assertGreaterThan(
            strtotime($records[1]['date']),
            strtotime($records[0]['date'])
        );
    }

    public function testAmountNormalization(): void
    {
        $reader = SparkasseDeBankingReaderService::from(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS. 'sparkasse-de-amount.csv');
        $records = $reader->getPreparedRecords();

        $this->assertEquals(581.47, $records[0]['amount']);
        $this->assertEquals(999999, $records[1]['amount']);
    }

    public function testDateNormalization(): void
    {
        $reader = SparkasseDeBankingReaderService::from(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'sparkasse-de.csv');
        $records = $reader->getPreparedRecords();

        $this->assertEquals(
            '2026-01-21 00:00:00.000000',
            $records[0]['date']
        );
    }

    public function testContentBuilding(): void
    {
        $reader = SparkasseDeBankingReaderService::from(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'sparkasse-de.csv');
        $records = $reader->getPreparedRecords();
        $content = $records[0]['content'];

        $this->assertStringContainsString('GUTSCHR. UEBERWEISUNG', $content);
        $this->assertStringContainsString('838B34A3', $content);
        $this->assertStringContainsString('Donald Duck', $content);
        $this->assertStringContainsString('DE02120300000000202051', $content);
        $this->assertStringContainsString('INGDDEFF', $content);

        $this->assertStringNotContainsString('<br /><br />', $content);
    }

    public function testEmptyContentFieldsAreIgnored(): void
    {
        $reader = SparkasseDeBankingReaderService::from(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'sparkasse-de-empty-fields.csv');
        $records = $reader->getPreparedRecords();
        $content = $records[0]['content'];

        $records = $reader->getPreparedRecords(); $content = $records[0]['content']; 
        // Keine doppelten Zeilenumbrüche 
        $this->assertStringNotContainsString('<br /><br />', $content); 
        // Nur die nicht-leeren Felder sind enthalten 
        $this->assertStringContainsString('Donald Duck', $content); 
        $this->assertStringContainsString('DE02120300000000202051', $content); 
        $this->assertStringContainsString('INGDDEFF', $content); 
        // Die leeren Felder sind NICHT enthalten 
        $this->assertStringNotContainsString('Buchungstext', $content); 
        $this->assertStringNotContainsString('Verwendungszweck', $content);
    }

}
