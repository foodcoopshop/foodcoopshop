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

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;

class EmailOrderReminderShellTest extends AppCakeTestCase
{
    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public $commandRunner;

    public function setUp(): void
    {
        parent::setUp();
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testNoActiveOrder()
    {
        $this->commandRunner->run(['cake', 'email_order_reminder']);

        $this->assertMailCount(3);

        $this->assertMailSubjectContainsAt(0, 'Bestell-Erinnerung FoodCoop Test');
        $this->assertMailContainsHtmlAt(0, 'Hallo Demo Admin,');
        $this->assertMailContainsHtmlAt(0, 'ist schon wieder der letzte Bestelltag');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailAdmin'));

        $this->assertMailSubjectContainsAt(1, 'Bestell-Erinnerung FoodCoop Test');
        $this->assertMailContainsHtmlAt(1, 'Hallo Demo Mitglied,');
        $this->assertMailContainsHtmlAt(1, 'ist schon wieder der letzte Bestelltag');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailCustomer'));

        $this->assertMailSubjectContainsAt(2, 'Bestell-Erinnerung FoodCoop Test');
        $this->assertMailContainsHtmlAt(2, 'Hallo Demo Superadmin,');
        $this->assertMailContainsHtmlAt(2, 'ist schon wieder der letzte Bestelltag');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));

    }

    public function testGlobalDeliveryBreakEnabledAndNextDeliveryDay()
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', '2019-11-01');
        $this->commandRunner->run(['cake', 'email_order_reminder', '2019-10-27']);
        $this->assertMailCount(0);
    }

    public function testGlobalDeliveryBreakEnabledAndNotNextDeliveryDay()
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', '2019-11-08');
        $this->commandRunner->run(['cake', 'email_order_reminder', '2019-10-27']);
        $this->assertMailCount(3);
    }

    public function testActiveOrder()
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
        $this->assertMailCount(2);
        $this->assertMailContainsHtmlAt(0, 'heute');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailAdmin'));
    }

    public function testIfServiceNotSubscribed()
    {
        $query = 'UPDATE '.$this->Customer->getTable().' SET email_order_reminder = 0;';
        $this->dbConnection->query($query);
        $this->commandRunner->run(['cake', 'email_order_reminder']);
        $this->assertMailCount(0);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->EmailOrderReminder);
    }
}
