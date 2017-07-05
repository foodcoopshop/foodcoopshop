<?php
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('Shell', 'Console');
App::uses('AppShell', 'Console/Command');
App::uses('EmailOrderReminderShell', 'Console/Command');
App::uses('AppCakeTestCase', 'Test');
App::uses('EmailLog', 'Model');

class EmailOrderReminderShellTest extends AppCakeTestCase
{
    public $EmailLog;

    public function setUp()
    {
        parent::setUp();

        $this->EmailLog = new EmailLog();

        $out = $this->getMock('ConsoleOutput', array(), array(), '', false);
        $in = $this->getMock('ConsoleInput', array(), array(), '', false);

        $this->EmailOrderReminder = $this->getMock(
            'EmailOrderReminderShell',
            array('in', 'err', 'createFile', '_stop', 'clear'),
            array($out, $out, $in)
        );
    }

    /**
     * TODO
     * - add test after order is placed
     * - consider weekday of calling the test - virutal host needs preset date
     */
    public function testNoActiveOrder()
    {
        $actual = $this->EmailOrderReminder->main();
        $emailLogs = $this->EmailLog->find('all');
        $this->assertEquals(2, count($emailLogs), 'amount of sent emails wrong');
        $this->assertEmailLogs($emailLogs[0], 'Bestell-Erinnerung', array('Hallo Demo Admin,', 'ist schon wieder der letzte Bestelltag'), array(Configure::read('test.loginEmailAdmin')));
        $this->assertEmailLogs($emailLogs[1], 'Bestell-Erinnerung', array('Hallo Demo Mitglied,', 'ist schon wieder der letzte Bestelltag'), array(Configure::read('test.loginEmailCustomer')));
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->EmailOrderReminder);
    }
}
