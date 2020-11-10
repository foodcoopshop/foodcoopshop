<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Lib\Csv\RaiffeisenBankingReader;
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;

class RaiffeisenBankingReaderTest extends AppCakeTestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->assertLogFilesForErrors();
    }

    public function testRead()
    {
        $reader = RaiffeisenBankingReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'test-data-raiffeisen.csv');
        $records = $reader->getPreparedRecords($reader->getRecords());
        foreach($records as $record) {
            $this->assertEquals(4, count($record));
        }

        $this->assertEquals('2019-02-01 12:51:14.563000', $records[2]['date']);
        $this->assertEquals(100, $records[2]['amount']);
        $this->assertEquals(Configure::read('test.adminId'), $records[2]['original_id_customer']);
        $this->assertEquals(Configure::read('test.superadminId'), $records[1]['original_id_customer']);

        $this->assertEquals(3, count($records));
    }

    public function testCheckStructureNotOk()
    {
        $reader = RaiffeisenBankingReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'test-data-raiffeisen-wrong-structure.csv');
        $this->assertFalse($reader->checkStructure());
    }

    public function testCheckStructureOk()
    {
        $reader = RaiffeisenBankingReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'test-data-raiffeisen.csv');
        $this->assertTrue($reader->checkStructure());
    }

}
