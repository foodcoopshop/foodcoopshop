<?php

namespace App\Controller;

use Cake\Event\Event;
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
                
        $executedCronjobs = $this->Cronjob->run();
        $this->set('data', [
            'executedCronjobs' => $executedCronjobs
        ]);
        
        $this->set('_serialize', 'data');
           
    }

}
