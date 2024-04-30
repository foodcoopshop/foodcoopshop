<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use App\Services\Csv\Reader\Banking\RaiffeisenBankingReaderService;

class RaiffeisenBankingReaderServiceTest extends AppCakeTestCase
{

    public function tearDown(): void
    {
        $this->assertLogFilesForErrors();
    }

    public function testRead()
    {
        $reader = RaiffeisenBankingReaderService::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'raiffeisen.csv');
        $records = $reader->getPreparedRecords();
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
        $reader = RaiffeisenBankingReaderService::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'raiffeisen-wrong-structure.csv');
        $this->assertFalse($reader->checkStructure());
    }

    public function testCheckStructureOk()
    {
        $reader = RaiffeisenBankingReaderService::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'raiffeisen.csv');
        $this->assertTrue($reader->checkStructure());
    }

}
