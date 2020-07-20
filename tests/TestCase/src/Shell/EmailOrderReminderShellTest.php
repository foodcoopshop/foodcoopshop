<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use App\Application;
use Cake\Console\CommandRunner;

class EmailOrderReminderShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $commandRunner;

    public function setUp(): void
    {
        parent::setUp();
        $this->EmailLog = $this->getTableLocator()->get('EmailLogs');
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testNoActiveOrder()
    {
        $this->commandRunner->run(['cake', 'email_order_reminder']);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(3, count($emailLogs), 'amount of sent emails wrong');
        $this->assertEmailLogs($emailLogs[0], 'Bestell-Erinnerung', ['Hallo Demo Admin,', 'ist schon wieder der letzte Bestelltag'], [Configure::read('test.loginEmailAdmin')]);
        $this->assertEmailLogs($emailLogs[1], 'Bestell-Erinnerung', ['Hallo Demo Mitglied,', 'ist schon wieder der letzte Bestelltag'], [Configure::read('test.loginEmailCustomer')]);
        $this->assertEmailLogs($emailLogs[2], 'Bestell-Erinnerung', ['Hallo Demo Superadmin,', 'ist schon wieder der letzte Bestelltag'], [Configure::read('test.loginEmailSuperadmin')]);
    }

    public function testGlobalDeliveryBreakEnabledAndNextDeliveryDay()
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', '2019-11-01');
        $this->commandRunner->run(['cake', 'email_order_reminder', '2019-10-27']);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(0, count($emailLogs));
    }

    public function testGlobalDeliveryBreakEnabledAndNotNextDeliveryDay()
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', '2019-11-08');
        $this->commandRunner->run(['cake', 'email_order_reminder', '2019-10-27']);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(3, count($emailLogs));
    }

    public function testActiveOrderDetail()
    {
        $pickupDay = '2019-11-08';
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');

        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get(1),
                [
                    'pickup_day' => $pickupDay
                ]
            )
        );
        $this->commandRunner->run(['cake', 'email_order_reminder', '2019-11-05']);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(2, count($emailLogs), 'superadmin has open order and got reminder email');
        $this->assertEmailLogs($emailLogs[0], '', ['heute'], ['fcs-demo-admin@mailinator.com']);
    }

    public function testIfServiceNotSubscribed()
    {
        $query = 'UPDATE '.$this->Customer->getTable().' SET email_order_reminder = 0;';
        $this->dbConnection->query($query);
        $this->commandRunner->run(['cake', 'email_order_reminder']);
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->EmailOrderReminder);
    }
}
