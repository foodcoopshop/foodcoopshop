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
use App\Lib\Csv\BankingReader;
use App\Model\Entity\Customer;
use Cake\Core\Configure;

class BankingReaderTest extends AppCakeTestCase
{
    
    public function setUp(): void
    {
        // do not import database - no database needed for this test
    }
    
    public function testReadRaiffeisen()
    {
        $reader = BankingReader::createFromPath(TESTS . 'config' . DS . 'data' . DS . 'test-data-raiffeisen.csv');
        $records = $reader->getPreparedRecords($reader->getRecords());
        foreach($records as $record) {
            $this->assertEquals(4, count($record));
        }
        
        $this->assertTrue(get_class($records[0]['customer']) == Customer::class);
        $this->assertTrue(get_class($records[1]['customer']) == Customer::class);
        $this->assertEquals($records[0]['customer']['id_customer'], Configure::read('test.adminId'));
        $this->assertEquals($records[1]['customer']['id_customer'], Configure::read('test.superadminId'));
        $this->assertNull($records[2]['customer']);
        
        $this->assertEquals(3, count($records));
    }
    
}
