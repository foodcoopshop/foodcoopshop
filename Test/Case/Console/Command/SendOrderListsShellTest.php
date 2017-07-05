<?php
App::uses('SendOrderListsShell', 'Console/Command');
App::uses('AppCakeTestCase', 'Test');
App::uses('EmailLog', 'Model');

class SendOrderListsShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $SendOrderLists;

    public function setUp()
    {
        parent::setUp();
        $this->EmailLog = new EmailLog();
        $this->SendOrderLists = $this->createMockShell('SendOrderListsShell');
    }

    public function testSendOrderListsIfNoOrdersAvailable()
    {
        $this->SendOrderLists->main();
        $emailLogs = $this->EmailLog->find('all');
        $this->assertEquals(0, count($emailLogs), 'amount of sent emails wrong');
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->SendOrderLists);
    }
}
