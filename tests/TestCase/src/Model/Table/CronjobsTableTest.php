<?php
declare(strict_types=1);

use App\Model\Table\CronjobLogsTable;
use App\Test\TestCase\AppCakeTestCase;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CronjobsTableTest extends AppCakeTestCase
{

    public function testEditDailyValidations()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'time_interval' => 'day',
            'day_of_month' => 4,
            'weekday' => 'Sunday',
            'not_before_time' => 'wrong-time',
        ]);
        $errors = $result->getErrors();
        $this->assertEquals('Beim Interval "täglich" bitte keinen Tag (Monat) angeben.', $errors['day_of_month']['time-interval-day-or-week-no-day-of-month']);
        $this->assertEquals('Beim Interval "täglich" bitte keinen Wochentag angeben.', $errors['weekday']['time-interval-day-or-month-no-weekday']);
        $this->assertEquals('Bitte gib eine gültige Uhrzeit ein.', $errors['not_before_time']['time']);
    }

    public function testEditDailyOk()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'time_interval' => 'day',
            'day_of_month' => '',
            'weekday' => '',
            'not_before_time' => '18:00:00',
        ]);
        $this->assertEquals(false, $result->hasErrors());
    }

    public function testEditWeeklyValidations()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'time_interval' => 'week',
            'day_of_month' => '2',
            'weekday' => '',
        ]);
        $errors = $result->getErrors();
        $this->assertEquals('Bitte wähle einen Wochentag aus.', $errors['weekday']['_empty']);
        $this->assertEquals('Beim Interval "wöchentlich" bitte keinen Tag (Monat) angeben.', $errors['day_of_month']['time-interval-day-or-week-no-day-of-month']);
    }

    public function testEditWeeklyOk() {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'time_interval' => 'week',
            'day_of_month' => '',
            'weekday' => 'Sunday',
        ]);
        $this->assertEquals(false, $result->hasErrors());
    }

    public function testPickupReminderValidation()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'time_interval' => 'week',
            'day_of_month' => '2',
            'weekday' => '',
        ]);
        $errors = $result->getErrors();
        $this->assertEquals('Bitte wähle einen Wochentag aus.', $errors['weekday']['_empty']);
        $this->assertEquals('Beim Interval "wöchentlich" bitte keinen Tag (Monat) angeben.', $errors['day_of_month']['time-interval-day-or-week-no-day-of-month']);
    }

    public function testEditPickupReminderValidations()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'name' => 'PickupReminder',
            'time_interval' => 'month',
            'day_of_month' => '',
            'weekday' => 'Sunday',
        ],
            ['validate' => 'PickupReminder'],
        );
        $errors = $result->getErrors();
        $this->assertEquals('Das Intervall muss "wöchentlich" sein.', $errors['time_interval']['equals']);
    }

    public function testEditEmailOrderReminderValidations()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'name' => 'PickupReminder',
            'time_interval' => 'month',
            'day_of_month' => '',
            'weekday' => 'Sunday',
        ],
            ['validate' => 'EmailOrderReminder'],
        );
        $errors = $result->getErrors();
        $this->assertEquals('Das Intervall muss "wöchentlich" sein.', $errors['time_interval']['equals']);
    }

    public function testEditSendDeliveryNotesValidations()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'name' => 'SendDeliveryNotes',
            'time_interval' => 'week',
            'day_of_month' => '',
            'weekday' => 'Sunday',
        ],
            ['validate' => 'SendDeliveryNotes'],
        );
        $errors = $result->getErrors();
        $this->assertEquals('Das Intervall muss "monatlich" sein.', $errors['time_interval']['equals']);
    }

    public function testEditSendInvoicesToManufacturersValidations()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'name' => 'SendInvoicesToManufacturers',
            'time_interval' => 'day',
            'day_of_month' => '',
            'weekday' => '',
        ],
            ['validate' => 'SendInvoicesToManufacturers'],
        );
        $errors = $result->getErrors();
        $this->assertEquals('Das Intervall muss "monatlich" sein.', $errors['time_interval']['equals']);
    }

    public function testEditSendOrderListsValidations()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'name' => 'SendOrderLists',
            'time_interval' => 'week',
            'day_of_month' => '',
            'weekday' => '',
        ],
            ['validate' => 'SendOrderLists'],
        );
        $errors = $result->getErrors();
        $this->assertEquals('Das Intervall muss "täglich" sein.', $errors['time_interval']['equals']);
    }

    public function testEditMonthlyOk()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $entity = $cronjobsTable->get(1);
        $result = $cronjobsTable->patchEntity($entity, [
            'time_interval' => 'month',
            'day_of_month' => '2',
            'weekday' => '',
        ]);
        $this->assertEquals(false, $result->hasErrors());
    }

    public function testRunSunday()
    {
        $time = '2018-10-21 23:00:00';
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC($time)->toUnixString();
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals($executedCronjobs[0]['created'], $time);

        // run again, no cronjobs called
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(0, count($executedCronjobs));
    }

    public function testRunMonday()
    {
        $time = '2018-10-22 23:00:00';
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC($time)->toUnixString();
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(2, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['time_interval'], 'day');
        $this->assertEquals($executedCronjobs[1]['time_interval'], 'week');
        $this->assertEquals($executedCronjobs[0]['created'], $time);
    }

    public function testPreviousCronjobLogFailure()
    {
        $time = '2018-10-22 23:00:00';
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC($time)->toUnixString();
        $cronjobsTable->CronjobLogs->save(
            $cronjobsTable->CronjobLogs->newEntity(
                [
                    'created' => $this->Time->getTimeObjectUTC($time),
                    'cronjob_id' => 1,
                    'success' => CronjobLogsTable::FAILURE,
                ],
            )
        );
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(2, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['time_interval'], 'day');
        $this->assertEquals($executedCronjobs[1]['time_interval'], 'week');
        $this->assertEquals($executedCronjobs[0]['created'], $time);
    }

    public function testPreviousCronjobLogRunning()
    {
        $time = '2018-10-22 23:00:00';
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC($time)->toUnixString();
        $cronjobsTable->CronjobLogs->save(
            $cronjobsTable->CronjobLogs->newEntity(
                [
                    'created' => $this->Time->getTimeObjectUTC($time),
                    'cronjob_id' => 1,
                    'success' => CronjobLogsTable::RUNNING,
                ],
            )
        );
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(1, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['time_interval'], 'week');
        $this->assertEquals($executedCronjobs[0]['created'], $time);
    }

    public function testCronjobNotYetExecutedWithinTimeInterval()
    {
        $time = '2018-10-23 22:30:01';
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC($time)->toUnixString();
        $cronjobsTable->CronjobLogs->save(
            $cronjobsTable->CronjobLogs->newEntity(
                [
                    'created' => $this->Time->getTimeObjectUTC('2018-10-22 22:30:00'),
                    'cronjob_id' => 1,
                    'success' => CronjobLogsTable::SUCCESS,
                ],
            )
        );
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(1, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['time_interval'], 'day');
        $this->assertEquals($executedCronjobs[0]['created'], $time);
    }

    public function testCronjobAlreadyExecutedWithinTimeInterval()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC('2018-10-23 22:29:59')->toUnixString();
        $cronjobsTable->CronjobLogs->save(
            $cronjobsTable->CronjobLogs->newEntity(
                [
                    'created' => $this->Time->getTimeObjectUTC('2018-10-22 22:30:01'),
                    'cronjob_id' => 1,
                    'success' => CronjobLogsTable::SUCCESS,
                ],
            )
        );
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(0, count($executedCronjobs));
    }

    public function testCronjobWithException()
    {
        $time = '2018-10-23 22:31:00';
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC($time)->toUnixString();
        $cronjobsTable->save(
            $cronjobsTable->patchEntity(
                $cronjobsTable->get(1),
                [
                    'name' => 'TestCronjobWithException'
                ],
            )
        );
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(1, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['success'], 0);
        $this->assertEquals($executedCronjobs[0]['created'], $time);
    }

    public function testCronjobAlreadyExecutedOnCurrentDay()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC('2018-10-25 22:30:02')->toUnixString();
        $cronjobsTable->CronjobLogs->save(
            $cronjobsTable->CronjobLogs->newEntity(
                [
                    'created' => $this->Time->getTimeObjectUTC('2018-10-25 22:30:01'),
                    'cronjob_id' => 1,
                    'success' => CronjobLogsTable::SUCCESS,
                ]
            )
        );
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(0, count($executedCronjobs));
    }

    public function testRunMonthlyBeforeNotBeforeTime()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC('2018-10-11 07:29:00')->toUnixString();
        $cronjobsTable->save(
            $cronjobsTable->patchEntity(
                $cronjobsTable->get(1),
                [
                    'active' => APP_OFF,
                ],
            )
        );
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(0, count($executedCronjobs));
    }

    public function testRunMonthlyAfterNotBeforeTime()
    {
        $time = '2018-10-11 07:31:00';
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC($time)->toUnixString();
        $cronjobsTable->save(
            $cronjobsTable->patchEntity(
                $cronjobsTable->get(1),
                [
                    'active' => APP_OFF,
                ],
            )
        );
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(1, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['created'], $time);
    }

    public function testRunMonthlyLastDayOfMonthAfterNotBeforeTime()
    {
        $time = '2018-11-30 07:31:00';
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->cronjobRunDay = (int) $this->Time->getTimeObjectUTC($time)->toUnixString();
        $cronjobsTable->updateAll(
            [
                'active' => APP_OFF,
            ],
            [],
        );
        $cronjobsTable->save(
            $cronjobsTable->patchEntity(
                $cronjobsTable->get(3),
                [
                    'day_of_month' => 0,
                    'active' => APP_ON,
                ],
            ),
        );
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(1, count($executedCronjobs));
        $this->assertEquals($executedCronjobs[0]['created'], $time);
    }

    public function testInvalidWeekday()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->save(
            $cronjobsTable->patchEntity(
                $cronjobsTable->get(2),
                [
                    'weekday' => '',
                ]
            )
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('weekday not available');
        $executedCronjobs = $cronjobsTable->run();
        $this->assertEquals(0, count($executedCronjobs));
        $this->assertEmpty(0, $cronjobsTable->CronjobLogs->find('all')->all());
    }

    public function testInvalidDayOfMonth()
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobsTable->save(
            $cronjobsTable->patchEntity(
                $cronjobsTable->get(3),
                [
                    'day_of_month' => '',
                ]
            )
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('day of month not available or not valid');
        $cronjobsTable->run();
        $this->assertEmpty(0, $cronjobsTable->CronjobLogs->find('all')->all());
    }

}