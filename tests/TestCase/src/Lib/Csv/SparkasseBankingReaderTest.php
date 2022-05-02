<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Lib\Csv\SparkasseBankingReader;
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;

class SparkasseBankingReaderTest extends AppCakeTestCase
{

    public function tearDown(): void
    {
        $this->assertLogFilesForErrors();
    }

    public function testRead()
    {
        $reader = SparkasseBankingReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'sparkasse.csv');
        $records = $reader->getPreparedRecords($reader->getRecords());
        foreach($records as $record) {
            $this->assertEquals(4, count($record));
        }

        $this->assertEquals('2022-04-13 00:00:00.000000', $records[0]['date']);
        $this->assertEquals(18, $records[0]['amount']);
        $this->assertEquals(Configure::read('test.adminId'), $records[0]['original_id_customer']);

        $this->assertEquals(1, count($records));
    }

    public function testCheckStructureNotOk()
    {
        $reader = SparkasseBankingReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'sparkasse-wrong-structure.csv');
        $this->assertFalse($reader->checkStructure());
    }

    public function testCheckStructureOk()
    {
        $reader = SparkasseBankingReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'sparkasse.csv');
        $this->assertTrue($reader->checkStructure());
    }

}
