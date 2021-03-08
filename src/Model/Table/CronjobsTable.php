<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\I18n;
use App\Lib\Error\Exception\InvalidParameterException;
use Cake\I18n\Time;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
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

    public function run()
    {

        if (empty($this->cronjobRunDay)) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getTimeObjectUTC(date(Configure::read('DateFormat.DatabaseWithTimeAlt')))->toUnixString();
        }

        $cronjobs = $this->find('all', [
            'conditions' => [
                'Cronjobs.active' => APP_ON
            ]
        ])->all();

        $executedCronjobs = [];

        foreach($cronjobs as $cronjob) {

            if (!$this->executeCronjobRespectingTimeInterval($cronjob)) {
                continue;
            }

            $cronjobRunDayObject = new Time($this->cronjobRunDay);
            // to be able to use local time in fcs_cronjobs:time_interval, the current time needs to be adabped according to the local timezone
            $cronjobRunDayObject = $cronjobRunDayObject->modify(Configure::read('app.timeHelper')->getTimezoneDiffInSeconds($this->cronjobRunDay) . ' seconds');

            $cronjobNotBeforeTimeWithCronjobRunDay = new Time($cronjob->not_before_time);
            $cronjobNotBeforeTimeWithCronjobRunDay->setDate(
                $cronjobRunDayObject->year,
                $cronjobRunDayObject->month,
                $cronjobRunDayObject->day
            );
            $cronjobNotBeforeTimeWithCronjobRunDay->modify(Configure::read('app.timeHelper')->getTimezoneDiffInSeconds($cronjobNotBeforeTimeWithCronjobRunDay->getTimestamp()) . ' seconds');
            $cronjobNotBeforeTimeWithCronjobRunDay->setTimezone('UTC');

            $cronjobLog = $this->CronjobLogs->find('all', [
                'conditions' => [
                    'CronjobLogs.cronjob_id' => $cronjob->id,
                ],
                'order' => [
                    'CronjobLogs.created' => 'DESC'
                ]
            ])
            ->where(function (QueryExpression $exp) use ($cronjobRunDayObject) {
                return $exp->eq('DATE_FORMAT(CronjobLogs.created, \'%Y-%m-%d\')', $cronjobRunDayObject->i18nFormat(Configure::read('DateFormat.Database')));
            })->first();

            $executeCronjob = true;

            $timeIntervalObject = $cronjobNotBeforeTimeWithCronjobRunDay->copy()->modify('- 1' . $cronjob->time_interval);
            if (!(empty($cronjobLog) || $cronjobLog->success == APP_OFF || $cronjobLog->created->lt($timeIntervalObject))) {
                $executeCronjob = false;
            }

            if (!empty($cronjobLog) && $cronjobLog->success == APP_ON && $cronjobLog->created->gt($cronjobNotBeforeTimeWithCronjobRunDay)) {
                $executeCronjob = false;
            }

            if ($cronjobNotBeforeTimeWithCronjobRunDay->gt($cronjobRunDayObject)) {
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
        $shellName = $cronjob->name . 'Shell';
        if (!file_exists(ROOT . DS . 'src' . DS . 'Shell' . DS . $shellName . '.php')) {
            throw new InvalidParameterException('shell not found: ' . $cronjob->name);
        }
        $shellClass = '\\App\\Shell\\' . $shellName;
        $shell = new $shellClass();
        try {
            $success = $shell->main();
            $success = $success !== true ? 0 : 1;
        } catch (\Exception $e) {
            $success = 1;
            if (get_class($e) != 'Cake\Network\Exception\SocketException') {
                $success = 0;
            }
        }

        $entity = $this->CronjobLogs->newEntity(
            [
                'cronjob_id' => $cronjob->id,
                'created' => $cronjobRunDayObject,
                'success' => $success
            ]
        );
        $this->CronjobLogs->save($entity);

        return [
            'name' => $cronjob->name,
            'time_interval' => $cronjob->time_interval,
            'success' => $success
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
                    throw new InvalidParameterException('weekday not available');
                }
                $cronjobWeekdayIsCurrentWeekday = $cronjob->weekday == $currentWeekday;
                if ($cronjobWeekdayIsCurrentWeekday) {
                    $result = true;
                }
                break;
            case 'month':
                if ($cronjob->day_of_month == '' || $cronjob->day_of_month > 31) {
                    throw new InvalidParameterException('day of month not available or not valid');
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
