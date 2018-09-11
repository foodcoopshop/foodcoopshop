<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Shell\SendInvoicesShell;
use Cake\I18n\FrozenTime;

class SendInvoicesShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $Order;
    public $SendInvoices;

    public function setUp()
    {
        parent::setUp();
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->SendInvoices = new SendInvoicesShell(new ConsoleIo());
    }

    public function testSendInvoicesOk()
    {
        $this->prepareSendInvoices();
        
        // reset order detail created in order to make OrderDetail::legacyUpdateOrderStateToNewBilledState happen
        // and test for param &excludeCreatedLastMonth=1 in email to financial responsible
        // can be removed safely in FCS v3.0
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get(1),
                [
                    'created' => FrozenTime::create(2018,1,31,10,0,0)
                ]
            )
        );
        
        $this->SendInvoices->main();
        
        $orderDetails = $this->OrderDetail->find('all')->toArray();
        foreach($orderDetails as $orderDetail) {
            $expectedOrderState = ORDER_STATE_BILLED_CASHLESS;
            if ($orderDetail->id_order_detail == 4) {
                $expectedOrderState = ORDER_STATE_ORDER_PLACED;
            }
            $this->assertEquals($orderDetail->order_state, $expectedOrderState);
        }
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(4, count($emailLogs), 'amount of sent emails wrong');
        $this->assertEmailLogs(
            $emailLogs[1],
            'Rechnung Nr. 0001',
            [
                'Demo-Fleisch-Hersteller_4_Rechnung_0001_FoodCoop-Test.pdf',
                'Content-Type: application/pdf',
            ],
            [
                Configure::read('test.loginEmailMeatManufacturer')
            ]
        );
        
        $this->assertEmailLogs(
            $emailLogs[3],
            'Rechnungen für August 2018 wurden verschickt',
            [
                'excludeCreatedLastMonth=1',
            ],
            [
                Configure::read('test.loginEmailSuperadmin')
            ]
        );
        
    }

    public function testSendInvoicesNoInvoicesSentIfCalledMultipleTimes()
    {

        $this->prepareSendInvoices();
        $this->SendInvoices->main();
        $this->SendInvoices->main(); // sic! run again
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        
        // no additional (would be 8) emails should be sent if called twice on same day
        $this->assertEquals(6, count($emailLogs), 'amount of sent emails wrong');
        
        $this->assertEmailLogs(
            $emailLogs[4],
            'Rechnungen für August 2018 wurden verschickt',
            [
                'pickupDay[]=28.02.2018&groupBy=manufacturer</a>', // assures that excludeCreatedLastMonth=1 is not existing
            ],
            [
                Configure::read('test.loginEmailSuperadmin')
            ]
        );
        
    }
    
    private function prepareSendInvoices()
    {
        $this->loginAsSuperadmin();
        // add new orders
        $this->addProductToCart(346, 1);
        $this->addProductToCart(346, 1);
        $this->finishCart();
        $this->SendInvoices->cronjobRunDay = '2018-03-11';
    }

}
