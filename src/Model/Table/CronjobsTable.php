<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\I18n\I18n;
use Cake\ORM\Query;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;

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
class CronjobsTable extends AppTable
{

    public $cronjobRunDay;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->hasMany('CronjobLogs', [
            'foreignKey' => 'cronjob_id'
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->inList('time_interval', array_keys($this->getTimeIntervals()), __('The_time_interval_is_not_valid.'));
        $validator->allowEmptyString('day_of_month', __('Please_select_a_day_of_month.'), function($context) {
            if (!isset($context['data']['time_interval'])) {
                return true;
            }
            if ($context['data']['time_interval'] == 'month') {
                return false;
            }
            return true;
        });
        $validator->inList('day_of_month', array_keys($this->getDaysOfMonth()), __('The_day_of_month_is_not_valid.'));
        $validator->allowEmptyString('weekday', __('Please_select_a_weekday.'), function($context) {
            if (!isset($context['data']['time_interval'])) {
                return true;
            }
            if ($context['data']['time_interval'] == 'week') {
                return false;
            }
            return true;
        });
        $validator->inList('weekday', array_keys($this->getWeekdays()), __('The_weekday_is_not_valid.'));
        $validator->add('day_of_month', 'time-interval-day-or-week-no-day-of-month', [
            'rule' => function ($value, $context) {
                if (isset($context['data']['time_interval'])) {
                    if (in_array($context['data']['time_interval'], ['day', 'week'])) {
                        if ($value == '') {
                            return true;
                        } else {
                            $timeInterval = match($context['data']['time_interval']) {
                                'day' => __('daily'),
                                'week' => __('weekly'),
                            };
                            return __('No_day_of_month_allowed_for_time_interval_{0}.', [
                                $timeInterval,
                            ]);
                        }
                    }
                }
                return true;
            },
        ]);
        $validator->add('weekday', 'time-interval-day-or-month-no-weekday', [
            'rule' => function ($value, $context) {
                if (isset($context['data']['time_interval'])) {
                    if (in_array($context['data']['time_interval'], ['day', 'month'])) {
                        if ($value == '') {
                            return true;
                        } else {
                            $timeInterval = match($context['data']['time_interval']) {
                                'day' => __('daily'),
                                'month' => __('monthly'),
                            };
                            return __('No_weekday_allowed_for_time_interval_{0}.', [
                                $timeInterval,
                            ]);
                        }
                    }
                }
                return true;
            },
        ]);
        $validator->time('not_before_time', __('Please_enter_a_valid_time.'));
        return $validator;
    }

    public function validationPickupReminder(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->getAllowOnlyOneTimeIntervalValidator($validator, 'week', __('weekly'));
        return $validator;
    }

    public function validationEmailOrderReminder(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->getAllowOnlyOneTimeIntervalValidator($validator, 'week', __('weekly'));
        return $validator;
    }

    public function validationSendDeliveryNotes(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->getAllowOnlyOneTimeIntervalValidator($validator, 'month', __('monthly'));
        return $validator;
    }

    public function validationSendInvoicesToManufacturers(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->getAllowOnlyOneTimeIntervalValidator($validator, 'month', __('monthly'));
        return $validator;
    }

    public function validationSendOrderLists(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->getAllowOnlyOneTimeIntervalValidator($validator, 'day', __('daily'));
        return $validator;
    }

    private function getAllowOnlyOneTimeIntervalValidator($validator, $timeInterval, $timeIntervalString)
    {
        $validator = $validator->equals('time_interval', $timeInterval, __('The_time_interval_needs_to_equal_"{0}"', [
            $timeIntervalString,
        ]));
        return $validator;
    }

    public function getTimeIntervals()
    {
        return [
            'day'   => __('daily'),
            'week'  => __('weekly'),
            'month' => __('monthly'),
        ];
    }

    public function getDaysOfMonth()
    {
        $days = [];
        $i = 1;
        while($i<=31) {
            $days[$i] = $i;
            $i++;
        }
        $days[0] = __('Last_day_of_month');
        return $days;
    }

    public function getWeekdays()
    {
        $weekdays = [
            'Monday' => __('Monday'),
            'Tuesday' => __('Tuesday'),
            'Wednesday' => __('Wednesday'),
            'Thursday' => __('Thursday'),
            'Friday' => __('Friday'),
            'Saturday' => __('Saturday'),
            'Sunday' => __('Sunday'),
        ];
        return $weekdays;

    }

    public function findAvailable(Query $query)
    {
        if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            $query->where(['name <> "SendInvoicesToManufacturers"']);
        } else {
            $query->where(['name <> "SendInvoicesToCustomers"']);
            $query->where(['name <> "SendDeliveryNotes"']);
        }
        if (!Configure::read('app.htmlHelper')->paymentIsCashless()) {
            $query->where(['name <> "CheckCreditBalance"']);
        }
        if (!Configure::read('app.emailOrderReminderEnabled')) {
            $query->where(['name <> "EmailOrderReminder"']);
        }
        return $query;
    }

    public function run()
    {

        if (empty($this->cronjobRunDay)) {
            $this->cronjobRunDay = (int) Configure::read('app.timeHelper')->getTimeObjectUTC(date(Configure::read('DateFormat.DatabaseWithTimeAlt')))->toUnixString();
        }

        $cronjobLogsTable = FactoryLocator::get('Table')->get('CronjobLogs');
        $cronjobLogsTable->deleteOldLogs($this->cronjobRunDay);

        $cronjobs = $this->find('all', conditions: [
            'Cronjobs.active' => APP_ON,
        ])->all();

        $executedCronjobs = [];

        foreach($cronjobs as $cronjob) {

            if (!$this->executeCronjobRespectingTimeInterval($cronjob)) {
                continue;
            }

            $cronjobRunDayObject = new DateTime($this->cronjobRunDay);
            // to be able to use local time in fcs_cronjobs:time_interval, the current time needs to be adabped according to the local timezone
            $cronjobRunDayObject = $cronjobRunDayObject->modify(Configure::read('app.timeHelper')->getTimezoneDiffInSeconds($this->cronjobRunDay) . ' seconds');

            $cronjobNotBeforeTimeWithCronjobRunDay = DateTime::createFromArray([
                'year' => $cronjobRunDayObject->year,
                'month' => $cronjobRunDayObject->month,
                'day' => $cronjobRunDayObject->day,
                'hour' => $cronjob->not_before_time->i18nFormat('H'),
                'minute' => $cronjob->not_before_time->i18nFormat('mm'),
                'timezone' => 'UTC',
            ]);
            $cronjobNotBeforeTimeWithCronjobRunDay->modify(Configure::read('app.timeHelper')->getTimezoneDiffInSeconds($cronjobNotBeforeTimeWithCronjobRunDay->getTimestamp()) . ' seconds');

            $cronjobLog = $cronjobLogsTable->find('all',
                conditions: [
                    'CronjobLogs.cronjob_id' => $cronjob->id,
                ],
                order: [
                    'CronjobLogs.created' => 'DESC'
                ]
            )
            ->where(function (QueryExpression $exp) use ($cronjobRunDayObject) {
                return $exp->eq('DATE_FORMAT(CronjobLogs.created, \'%Y-%m-%d\')', $cronjobRunDayObject->i18nFormat(Configure::read('DateFormat.Database')));
            })->first();

            $executeCronjob = true;
            $timeIntervalObject = $cronjobNotBeforeTimeWithCronjobRunDay->modify('- 1' . $cronjob->time_interval);

            if (!(empty($cronjobLog) || $cronjobLog->success == CronjobLogsTable::FAILURE || $cronjobLog->created->lessThan($timeIntervalObject))) {
                $executeCronjob = false;
            }

            if (!empty($cronjobLog) && (in_array($cronjobLog->success, [CronjobLogsTable::SUCCESS, CronjobLogsTable::RUNNING])) && $cronjobLog->created->greaterThan($cronjobNotBeforeTimeWithCronjobRunDay)) {
                $executeCronjob = false;
            }

            if ($cronjobNotBeforeTimeWithCronjobRunDay->greaterThan($cronjobRunDayObject)) {
                $executeCronjob = false;
            }

            if ($executeCronjob) {
                $executedCronjobs[] = $this->executeCronjobAndSaveLog($cronjob, $cronjobRunDayObject);
            }

        }

        return $executedCronjobs;

    }

    private function executeCronjobAndSaveLog($cronjob, $cronjobRunDayObject)
    {
        $commandName = $cronjob->getOriginalValues()['name'] . 'Command';
        if (!file_exists(ROOT . DS . 'src' . DS . 'Command' . DS . $commandName . '.php')) {
            throw new \Exception('command not found: ' . $commandName);
        }
        $commandClass = '\\App\\Command\\' . $commandName;
        $command = new $commandClass();

        $databasePreparedCronjobRunDay = Configure::read('app.timeHelper')->getTimeObjectUTC(
            $cronjobRunDayObject->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')
        ));
        $cronjobLogsTable = FactoryLocator::get('Table')->get('CronjobLogs');
        $entity = $cronjobLogsTable->newEntity(
            [
                'cronjob_id' => $cronjob->id,
                'created' => $databasePreparedCronjobRunDay,
                'success' => CronjobLogsTable::RUNNING,
            ]
        );
        $cronjobLogsTable->save($entity);

        try {
            $args = new Arguments([], [], []);
            $io = new ConsoleIo();
            $success = $command->execute($args, $io);
            $success = $success !== $command::CODE_SUCCESS ? CronjobLogsTable::FAILURE : CronjobLogsTable::SUCCESS;
        } catch (\Exception $e) {
            $success = CronjobLogsTable::FAILURE;
        }

        $entity->success = $success;
        $cronjobLogsTable->save($entity);

        return [
            'name' => $cronjob->getOriginalValues()['name'],
            'time_interval' => $cronjob->time_interval,
            'created' => $entity->created->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
            'success' => $success,
        ];
    }

    private function executeCronjobRespectingTimeInterval($cronjob)
    {

        $tmpLocale = I18n::getLocale();
        I18n::setLocale('en_US');
        $currentWeekday = Configure::read('app.timeHelper')->getWeekdayName(date('w', $this->cronjobRunDay));
        I18n::setLocale($tmpLocale);

        $currentDayOfMonth = date('j', $this->cronjobRunDay);
        $result = false;

        switch($cronjob->time_interval) {
            case 'day':
                $result = true;
                break;
            case 'week':
                if ($cronjob->weekday == '') {
                    throw new \Exception('weekday not available');
                }
                $cronjobWeekdayIsCurrentWeekday = $cronjob->weekday == $currentWeekday;
                if ($cronjobWeekdayIsCurrentWeekday) {
                    $result = true;
                }
                break;
            case 'month':
                if ($cronjob->day_of_month == '' || $cronjob->day_of_month > 31) {
                    throw new \Exception('day of month not available or not valid');
                }
                if ($cronjob->day_of_month == 0) {
                    $cronjob->day_of_month = Configure::read('app.timeHelper')->getNumberOfDays($this->cronjobRunDay);
                }
                $cronjobDayOfMonthIsCurrentDayOfMonth = $cronjob->day_of_month == $currentDayOfMonth;
                if ($cronjobDayOfMonthIsCurrentDayOfMonth) {
                    $result = true;
                }
                break;
        }

        return $result;

    }

}
