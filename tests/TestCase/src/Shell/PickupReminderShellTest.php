<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use App\Application;
use Cake\Console\CommandRunner;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class PickupReminderShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $commandRunner;
    
    public function setUp()
    {
        parent::setUp();
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testCustomersDoNotHaveFutureOrders()
    {
        $this->commandRunner->run(['cake', 'pickup_reminder']);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
    }

    public function testOneCustomerHasFutureOrdersLaterThanNextPickupDay()
    {
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->prepareOrderDetails();
        $this->commandRunner->run(['cake', 'pickup_reminder', '2018-03-10']);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
    }
    
    public function testOneCustomerHasFutureOrdersForNextPickupDay()
    {
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->prepareOrderDetails();
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get(2),
                [
                    'created' => FrozenTime::create(2018,3,9,0,0,0),
                    'pickup_day' => FrozenDate::create(2018,3,16)
                ]
            )
        );
        $this->commandRunner->run(['cake', 'pickup_reminder', '2018-03-10']);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs), 'amount of sent emails wrong');
        
        $this->assertEmailLogs(
            $emailLogs[0],
            'Abhol-Erinnerung für Freitag, 16.03.2018',
            [
    			'<li>1x Beuschl, Demo Fleisch-Hersteller</li>',
            ],
            [
                Configure::read('test.loginEmailSuperadmin')
            ]
        );
        
    }
    
    private function prepareOrderDetails()
    {
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get(1),
                [
                    'created' => FrozenTime::create(2018,3,9,0,0,0),
                    'pickup_day' => FrozenDate::create(2018,3,28)
                ]
            )
        );
    }
    
}
