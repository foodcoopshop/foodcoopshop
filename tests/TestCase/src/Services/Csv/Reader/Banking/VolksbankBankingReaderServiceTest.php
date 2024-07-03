<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use App\Services\Csv\Reader\Banking\VolksbankBankingReaderService;

class VolksbankBankingReaderServiceTest extends AppCakeTestCase
{

    public function testRead()
    {
        $reader = VolksbankBankingReaderService::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'volksbank.csv');
        $records = $reader->getPreparedRecords();
        foreach($records as $record) {
            $this->assertEquals(4, count($record));
        }

        $this->assertEquals('2023-06-29 01:05:26.499223', $records[0]['date']);
        $this->assertEquals(100, $records[0]['amount']);
        $this->assertEquals(Configure::read('test.adminId'), $records[0]['original_id_customer']);

        $this->assertEquals(1, count($records));
    }

    public function testCheckStructureNotOk()
    {
        $reader = VolksbankBankingReaderService::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'volksbank-wrong-structure.csv');
        $this->assertFalse($reader->checkStructure());
    }

    public function testCheckStructureOk()
    {
        $reader = VolksbankBankingReaderService::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'volksbank.csv');
        $this->assertTrue($reader->checkStructure());
    }

}
