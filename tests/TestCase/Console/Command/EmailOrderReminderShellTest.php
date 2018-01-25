<?php
App::uses('EmailOrderReminderShell', 'Console/Command');
App::uses('AppCakeTestCase', 'Test');
App::uses('EmailLogs', 'Model');

/**
 * TODO
 * - add test after order is placed
 * - consider weekday of calling the test - virutal host needs preset date
 */
class EmailOrderReminderShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $EmailOrderReminder;

    public function setUp()
    {
        parent::setUp();
        $this->EmailLog = new EmailLog();
        $this->EmailOrderReminder = $this->createMockShell('EmailOrderReminderShell');
    }

    public function testNoActiveOrder()
    {
        $this->EmailOrderReminder->main();
        $emailLogs = $this->EmailLog->find('all');
        $this->assertEquals(2, count($emailLogs), 'amount of sent emails wrong');
        $this->assertEmailLogs($emailLogs[0], 'Bestell-Erinnerung', ['Hallo Demo Admin,', 'ist schon wieder der letzte Bestelltag'], [Configure::read('test.loginEmailAdmin')]);
        $this->assertEmailLogs($emailLogs[1], 'Bestell-Erinnerung', ['Hallo Demo Mitglied,', 'ist schon wieder der letzte Bestelltag'], [Configure::read('test.loginEmailCustomer')]);
    }

    public function testIfServiceNotSubscribed()
    {
        $sql = 'UPDATE '.$this->Customer->getTable().' SET newsletter = 0;';
        $this->Customer->query($sql);
        $this->EmailOrderReminder->main();
        $emailLogs = $this->EmailLog->find('all');
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->EmailOrderReminder);
    }
}
