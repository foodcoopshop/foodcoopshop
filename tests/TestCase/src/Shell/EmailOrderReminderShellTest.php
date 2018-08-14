<?php

/**
 * TODO
 * - add test after order is placed
 * - consider weekday of calling the test - virutal host needs preset date
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Shell\EmailOrderReminderShell;
use Cake\Console\ConsoleIo;

class EmailOrderReminderShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $EmailOrderReminder;

    public function setUp()
    {
        parent::setUp();
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
        $this->EmailOrderReminder = new EmailOrderReminderShell(new ConsoleIo());
    }

    public function testNoActiveOrder()
    {
        $this->EmailOrderReminder->main();
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(3, count($emailLogs), 'amount of sent emails wrong');
        $this->assertEmailLogs($emailLogs[0], 'Bestell-Erinnerung', ['Hallo Demo Admin,', 'ist schon wieder der letzte Bestelltag'], [Configure::read('test.loginEmailAdmin')]);
        $this->assertEmailLogs($emailLogs[1], 'Bestell-Erinnerung', ['Hallo Demo Mitglied,', 'ist schon wieder der letzte Bestelltag'], [Configure::read('test.loginEmailCustomer')]);
        $this->assertEmailLogs($emailLogs[2], 'Bestell-Erinnerung', ['Hallo Demo Superadmin,', 'ist schon wieder der letzte Bestelltag'], [Configure::read('test.loginEmailSuperadmin')]);
    }

    public function testActiveOrderDetail()
    {
        $pickupDay = Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb();
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get(1),
                [
                    'pickup_day' => $pickupDay
                ]
            )
        );
        $this->EmailOrderReminder->main();
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(2, count($emailLogs), 'superadmin has open order and got reminder email');
    }

    public function testIfServiceNotSubscribed()
    {
        $query = 'UPDATE '.$this->Customer->getTable().' SET email_order_reminder = 0;';
        $this->dbConnection->query($query);
        $this->EmailOrderReminder->main();
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->EmailOrderReminder);
    }
}
