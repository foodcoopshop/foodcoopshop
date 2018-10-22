<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CronjobsTableTest extends AppCakeTestCase
{
    public $Cronjob;
    
    public function setUp()
    {
        parent::setUp();
        $this->Cronjob = TableRegistry::getTableLocator()->get('Cronjobs');
    }
    
    public function testRunSunday()
    {
        $this->Cronjob->cronjobRunDay = strtotime('2018-10-21');
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(2, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['name'], 'BackupDatabase');
        $this->assertEquals($executedCronjobs[1]['name'], 'SendOrderLists');
    }
    
    public function testRunMonday()
    {
        $this->Cronjob->cronjobRunDay = strtotime('2018-10-22');
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(4, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['name'], 'BackupDatabase');
        $this->assertEquals($executedCronjobs[1]['name'], 'EmailReminder');
        $this->assertEquals($executedCronjobs[2]['name'], 'PickupReminder');
        $this->assertEquals($executedCronjobs[3]['name'], 'SendOrderLists');
        
        $executedCronjobs = $this->Cronjob->run();
        pr($executedCronjobs);
        
    }
    
}