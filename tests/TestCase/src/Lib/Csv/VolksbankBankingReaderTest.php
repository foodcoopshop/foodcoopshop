<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Lib\Csv\VolksbankBankingReader;
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;

class VolksbankBankingReaderTest extends AppCakeTestCase
{

    public function tearDown(): void
    {
        $this->assertLogFilesForErrors();
    }

    public function testRead()
    {
        $reader = VolksbankBankingReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'volksbank.csv');
        $records = $reader->getPreparedRecords($reader->getRecords());
        foreach($records as $record) {
            $this->assertEquals(4, count($record));
        }

        $this->assertEquals('2021-07-06 07:54:26.789861', $records[0]['date']);
        $this->assertEquals(100, $records[0]['amount']);
        $this->assertEquals(Configure::read('test.adminId'), $records[0]['original_id_customer']);

        $this->assertEquals(1, count($records));
    }

    public function testCheckStructureNotOk()
    {
        $reader = VolksbankBankingReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'volksbank-wrong-structure.csv');
        $this->assertFalse($reader->checkStructure());
    }

    public function testCheckStructureOk()
    {
        $reader = VolksbankBankingReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'volksbank.csv');
        $this->assertTrue($reader->checkStructure());
    }

}
