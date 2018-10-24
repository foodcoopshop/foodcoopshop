<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\I18n\I18n;
use App\Lib\Error\Exception\InvalidParameterException;
use Cake\I18n\FrozenTime;

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
class CronjobsTable extends AppTable
{
    
    public $cronjobRunDay;
    
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->hasMany('CronjobLogs', [
            'foreignKey' => 'cronjob_id'
        ]);
    }
    
    public function run()
    {
        
        if (empty($this->cronjobRunDay)) {
            $this->cronjobRunDay = time();
        }
        
        $cronjobs = $this->find('all', [
            'conditions' => [
                'Cronjobs.active' => APP_ON
            ]
        ])->all();
        
        $tmpLocale = I18n::getLocale();
        I18n::setLocale('en_US');
        $currentWeekday = Configure::read('app.timeHelper')->getWeekdayName(date('w', $this->cronjobRunDay));
        I18n::setLocale($tmpLocale);
        
        $currentDayOfMonth = date('w', $this->cronjobRunDay);
        
        $executedCronjobs = [];
        
        foreach($cronjobs as $cronjob) {
            
            $shouldCronjobBeExecutedByTimeInterval = false;
            
            switch($cronjob->time_interval) {
                case 'day':
                    $timeInterval = '1 day';
                    $shouldCronjobBeExecutedByTimeInterval = true;
                    break;
                case 'week':
                    $cronjobWeekdayIsCurrentWeekday = $cronjob->weekday == $currentWeekday;
                    $timeInterval = '1 week';
                    if ($cronjobWeekdayIsCurrentWeekday) {
                        $shouldCronjobBeExecutedByTimeInterval = true;
                    }
                    break;
                case 'month':
                    $cronjobDayOfMonthIsCurrentDayOfMonth = $cronjob->day_of_month == $currentDayOfMonth;
                    $timeInterval = '1 month';
                    if ($cronjobDayOfMonthIsCurrentDayOfMonth) {
                        $shouldCronjobBeExecutedByTimeInterval = true;
                    }
                    break;
            }
            
            $executeCronjob = true;
            
            if (!$shouldCronjobBeExecutedByTimeInterval) {
                continue;
            }
            
            $cronjobLog = $this->CronjobLogs->find('all', [
                'conditions' => [
                    'CronjobLogs.cronjob_id' => $cronjob->id
                ],
                'order' => [
                    'CronjobLogs.created' => 'DESC'
                ]
            ])->first();
            
            $interval = $cronjob->time->copy()->modify('-' . $timeInterval);
            if (!(empty($cronjobLog) || $cronjobLog->success == APP_OFF || $cronjobLog->created->lte($interval))) {
                $executeCronjob = false;
            }
            
            if ($executeCronjob) {
                
                $shellName = $cronjob->name . 'Shell';
                if (!file_exists(ROOT . DS . 'src' . DS . 'Shell' . DS . $shellName . '.php')) {
                    throw new InvalidParameterException('shell not found: ' . $cronjob->name);
                }
                $shellClass = '\\App\\Shell\\' . $shellName;
                $shell = new $shellClass();
                try {
                    $success = $shell->main();
                    $success = $success !== true ? 0 : 1;
                } catch (Exception $e) {
                    $success = 0;
                }
                $executedCronjobs[] = [
                    'name' => $cronjob->name,
                    'time_interval' => $cronjob->time_interval,
                    'success' => $success
                ];
                
                $entity = $this->CronjobLogs->newEntity(
                    [
                        'cronjob_id' => $cronjob->id,
                        'created' => new FrozenTime($this->cronjobRunDay),
                        'success' => $success
                    ]
                );
                $this->CronjobLogs->save($entity);
            }
                
        }
        
        return $executedCronjobs;
        
    }
    
}
