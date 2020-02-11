<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Lib\Csv\CsvReader;

class CsvReaderTest extends AppCakeTestCase
{
    
    public function setUp(): void
    {
        // do not import database - no database needed for this test
    }
    
    public function testReadCsvRaiffeisen()
    {
        $reader = CsvReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'test-bank-account-data.csv');
        $reader->setType(CsvReader::TYPE_RAIFFEISEN);
        $records = $reader->getRecords();
        $this->assertEquals(3, count($reader));
        
        foreach($records as $record) {
            $this->assertEquals(7, count($record));
        }
        
    }
    
}
