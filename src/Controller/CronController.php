<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Console\Shell;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\I18n\I18n;

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
class CronController extends AppController
{
    
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->AppAuth->allow('index');
    }
    
    public function index()
    {
        
        $this->RequestHandler->renderAs($this, 'json');
        
        $this->Cronjob = TableRegistry::getTableLocator()->get('Cronjobs');
        $this->CronjobLog = TableRegistry::getTableLocator()->get('CronjobLogs');
        
        $this->Cronjob->getAssociation('CronjobLogs')->sort(['CronjobLogs.created' => 'DESC']);
        $cronjobs = $this->Cronjob->find('all', [
            'conditions' => [
                'Cronjobs.active' => APP_ON
            ],
            'contain' => [
                'CronjobLogs'
            ]
        ])->all();
        
        $tmpLocale = I18n::getLocale();
        I18n::setLocale('en_US');
        $currentWeekday = Configure::read('app.timeHelper')->getWeekdayName(date('w'));
        I18n::setLocale($tmpLocale);
        
        $currentDayOfMonth = date('w');
        
        $executedCronjobs = [];
        
        foreach($cronjobs as $cronjob) {
            
            $executeCronjob = false;
            
            switch($cronjob->time_interval) {
                case 'day':
                    $executeCronjob = true;
                    break;
                case 'week':
                    $cronjobWeekdayIsCurrentWeekday = $cronjob->weekday == $currentWeekday;
                    if ($cronjobWeekdayIsCurrentWeekday) {
                        $executeCronjob = true;
                    }
                    break;
                case 'month':
                    $cronjobDayOfMonthIsCurrentDayOfMonth = $cronjob->day_of_month == $currentDayOfMonth;
                    if ($cronjobDayOfMonthIsCurrentDayOfMonth) {
                        $executeCronjob = true;
                    }
                    break;
            }
            
            if ($executeCronjob) {
                
//                 pr($cronjob);
//                 $shell = new Shell();
//                 $success = $shell->dispatchShell('BackupDatabase');

                $success = true; // dummy
                $executedCronjobs[] = [
                    'name' => $cronjob->name,
                    'success' => $success
                ];
                
                $entity = $this->CronjobLog->newEntity(
                    [
                        'cronjob_id' => $cronjob->id,
                        'success' => (int) $success
                    ]
                );
                $this->CronjobLog->save($entity);
                
            }
        }
        
        $this->set('data', [
            'executedCronjobs' => $executedCronjobs
        ]);
        
        $this->set('_serialize', 'data');
        
        
           
    }

}
