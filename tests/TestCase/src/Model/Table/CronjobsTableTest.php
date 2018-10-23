<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\I18n\Time;
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
        $this->assertEquals(1, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['name'], 'SendOrderLists');
        
        // run again, no cronjobs called
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(0, count($executedCronjobs));
    }
    
    public function testRunMonday()
    {
        $this->Cronjob->cronjobRunDay = strtotime('2018-10-22');
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(3, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['name'], 'EmailReminder');
        $this->assertEquals($executedCronjobs[1]['name'], 'PickupReminder');
        $this->assertEquals($executedCronjobs[2]['name'], 'SendOrderLists');
    }
    
    public function testPreviousCronjobLogError()
    {
        $time = '2018-10-22 17:13:19';
        $this->Cronjob->cronjobRunDay = strtotime($time);
        $this->Cronjob->CronjobLogs->save(
            $this->Cronjob->CronjobLogs->newEntity(
                [
                    'created' => new Time($time),
                    'cronjob_id' => 1,
                    'success' => 0
                ]
            )
        );
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(3, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['name'], 'EmailReminder');
    }
    
}