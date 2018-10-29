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
        $this->Cronjob->cronjobRunDay = $this->Time->getTimeObjectUTC('2018-10-21 23:00:00')->toUnixString();
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(1, count($executedCronjobs));
        
        // run again, no cronjobs called
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(0, count($executedCronjobs));
    }
    
    public function testRunMonday()
    {
        $this->Cronjob->cronjobRunDay = $this->Time->getTimeObjectUTC('2018-10-22 23:00:00')->toUnixString();
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(2, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['time_interval'], 'day');
        $this->assertEquals($executedCronjobs[1]['time_interval'], 'week');
    }
    
    public function testPreviousCronjobLogError()
    {
        $time = '2018-10-22 23:00:00';
        $this->Cronjob->cronjobRunDay = $this->Time->getTimeObjectUTC($time)->toUnixString();
        $this->Cronjob->cronjobRunDay = strtotime($time);
        $this->Cronjob->CronjobLogs->save(
            $this->Cronjob->CronjobLogs->newEntity(
                [
                    'created' => $this->Time->correctTimezone($this->Time->getTimeObjectUTC($time)),
                    'cronjob_id' => 1,
                    'success' => 0
                ]
            )
        );
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(2, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['time_interval'], 'day');
        $this->assertEquals($executedCronjobs[1]['time_interval'], 'week');
    }
    
    public function testCronjobNotYetExecutedWithinTimeInterval()
    {
        $this->Cronjob->cronjobRunDay = $this->Time->getTimeObjectUTC('2018-10-23 22:30:01')->toUnixString();
        $this->Cronjob->CronjobLogs->save(
            $this->Cronjob->CronjobLogs->newEntity(
                [
                    'created' => $this->Time->correctTimezone($this->Time->getTimeObjectUTC('2018-10-22 22:30:00')),
                    'cronjob_id' => 1,
                    'success' => 1
                ]
            )
        );
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(1, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['time_interval'], 'day');
    }
    
    public function testCronjobAlreadyExecutedWithinTimeInterval()
    {
        $this->Cronjob->cronjobRunDay = $this->Time->getTimeObjectUTC('2018-10-23 22:29:59')->toUnixString();
        $this->Cronjob->CronjobLogs->save(
            $this->Cronjob->CronjobLogs->newEntity(
                [
                    'created' => $this->Time->correctTimezone($this->Time->getTimeObjectUTC('2018-10-22 22:30:01')),
                    'cronjob_id' => 1,
                    'success' => 1
                ]
            )
        );
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(0, count($executedCronjobs));
    }
    
    public function testCronjobWithException()
    {
        $this->Cronjob->cronjobRunDay = $this->Time->getTimeObjectUTC('2018-10-23 22:31:00')->toUnixString();
        $this->Cronjob->save(
            $this->Cronjob->patchEntity(
                $this->Cronjob->get(1),
                [
                    'name' => 'TestCronjobWithException'
                ]
            )
        );
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(1, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['success'], 0);
    }
    
    public function testCronjobAlreadyExecutedOnCurrentDay()
    {
        $this->Cronjob->cronjobRunDay = $this->Time->getTimeObjectUTC('2018-10-25 22:30:02')->toUnixString();
        $this->Cronjob->CronjobLogs->save(
            $this->Cronjob->CronjobLogs->newEntity(
                [
                    'created' => $this->Time->correctTimezone($this->Time->getTimeObjectUTC('2018-10-25 22:30:01')),
                    'cronjob_id' => 1,
                    'success' => 1
                ]
            )
        );
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(0, count($executedCronjobs));
    }
    
    public function testRunMonthlyBeforeNotBeforeTime()
    {
        $this->Cronjob->cronjobRunDay = $this->Time->getTimeObjectUTC('2018-10-11 07:29:00')->toUnixString();
        $this->Cronjob->save(
            $this->Cronjob->patchEntity(
                $this->Cronjob->get(1),
                [
                    'active' => APP_OFF
                ]
            )
        );
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(0, count($executedCronjobs));
    }
    
    public function testRunMonthlyAfterNotBeforeTime()
    {
        $this->Cronjob->cronjobRunDay = $this->Time->getTimeObjectUTC('2018-10-11 07:31:00')->toUnixString();
        $this->Cronjob->save(
            $this->Cronjob->patchEntity(
                $this->Cronjob->get(1),
                [
                    'active' => APP_OFF
                ]
            )
        );
        $executedCronjobs = $this->Cronjob->run();
        $this->assertEquals(1, count($executedCronjobs));
    }
    
    /**
     * @expectedException App\Lib\Error\Exception\InvalidParameterException
     * @expectedExceptionMessage weekday not available
     */
    public function testInvalidWeekday()
    {
        $this->Cronjob->save(
            $this->Cronjob->patchEntity(
                $this->Cronjob->get(2),
                [
                    'weekday' => ''
                ]
            )
        );
        $this->Cronjob->run();
        $this->assertEquals(0, count($executedCronjobs));
        $this->assertEmpty(0, $this->CronjobLogs->find('all')->all());
    }
    
    /**
     * @expectedException App\Lib\Error\Exception\InvalidParameterException
     * @expectedExceptionMessage day of month not available or not valid
     */
    public function testInvalidDayOfMonth()
    {
        $this->Cronjob->save(
            $this->Cronjob->patchEntity(
                $this->Cronjob->get(3),
                [
                    'day_of_month' => ''
                ]
            )
        );
        $this->Cronjob->run();
        $this->assertEmpty(0, $this->CronjobLogs->find('all')->all());
    }

}