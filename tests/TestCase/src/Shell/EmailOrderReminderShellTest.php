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
        $this->EmailLog = TableRegistry::get('EmailLogs');
        $this->EmailOrderReminder = new EmailOrderReminderShell(new ConsoleIo());
    }

    public function testNoActiveOrder()
    {
        $this->EmailOrderReminder->main();
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(2, count($emailLogs), 'amount of sent emails wrong');
        $this->assertEmailLogs($emailLogs[0], 'Bestell-Erinnerung', ['Hallo Demo Admin,', 'ist schon wieder der letzte Bestelltag'], [Configure::read('test.loginEmailAdmin')]);
        $this->assertEmailLogs($emailLogs[1], 'Bestell-Erinnerung', ['Hallo Demo Mitglied,', 'ist schon wieder der letzte Bestelltag'], [Configure::read('test.loginEmailCustomer')]);
    }

    public function testIfServiceNotSubscribed()
    {
        $query = 'UPDATE '.$this->Customer->getTable().' SET newsletter = 0;';
        self::$dbConnection->query($query);
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
