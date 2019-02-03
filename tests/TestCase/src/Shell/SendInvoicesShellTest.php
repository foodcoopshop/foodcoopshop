<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\I18n\FrozenTime;
use App\Application;
use Cake\Console\CommandRunner;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class SendInvoicesShellTest extends AppCakeTestCase
{
    public $EmailLog;
    public $Order;
    public $commandRunner;
    
    public function setUp()
    {
        parent::setUp();
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }
    
    public function testContentOfInvoice()
    {
        $this->prepareSendInvoices();
        $this->httpClient->get('/admin/manufacturers/getInvoice.pdf?manufacturerId=4&dateFrom=01.02.2018&dateTo=28.02.2018&outputType=html');
        $content = $this->httpClient->getContent();
        $this->assertRegExpWithUnquotedString('<td>Gesamtsumme</td><td align="right">4,54</td>', $content);
    }

    public function testSendInvoicesOk()
    {
        
        Configure::write('app.dateOfFirstSendInvoiceCronjobWithPickupDayUpdate', '2018-03-11');
        
        $this->prepareSendInvoices();
        
        // reset order detail created in order to make OrderDetail::legacyUpdateOrderStateToNewBilledState happen
        // can be removed safely in FCS v3.0
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get(1),
                [
                    'created' => FrozenTime::create(2018,1,31,10,0,0)
                ]
            )
        );
        
        $this->changeConfiguration('FCS_USE_VARIABLE_MEMBER_FEE', 1);
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.meatManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'variable_member_fee', 10);
        
        $this->commandRunner->run(['cake', 'send_invoices', '2018-03-11']);
        
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
        
        $this->loginAsSuperadmin(); //should still be logged in as superadmin but is not...
        $this->httpClient->get($this->Slug->getActionLogsList() . '?dateFrom=11.03.2018&dateTo=11.03.2018');
        $content = $this->httpClient->getContent();
        $this->assertRegExpWithUnquotedString('4,09 €</b> (10%)', $content);
        $this->assertRegExpWithUnquotedString('0,62 €</b>', $content);
        
    }

    public function testSendInvoicesNoInvoicesSentIfCalledMultipleTimes()
    {

        $this->prepareSendInvoices();
        $this->commandRunner->run(['cake', 'send_invoices', '2018-03-11']);
        $this->commandRunner->run(['cake', 'send_invoices', '2018-03-11']); // sic! run again
        
        $emailLogs = $this->EmailLog->find('all')->toArray();
        
        // no additional (would be 8) emails should be sent if called twice on same day
        $this->assertEquals(6, count($emailLogs), 'amount of sent emails wrong');
        
        $this->assertEmailLogs(
            $emailLogs[4],
            'wurden verschickt',
            [
                'dateFrom=11.03.2018'
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
    }

}
