<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;

class EmailOrderReminderCommandTest extends AppCakeTestCase
{

    protected $OrderDetail;

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public function testNoActiveOrder()
    {
        $this->exec('email_order_reminder');
        $this->runAndAssertQueue();

        $this->assertMailCount(3);

        $this->assertMailSubjectContainsAt(0, 'Bestell-Erinnerung FoodCoop Test');
        $this->assertMailContainsHtmlAt(0, 'Hallo Demo Admin,');
        $this->assertMailContainsHtmlAt(0, 'ist schon wieder der letzte Bestelltag');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailAdmin'));

        $this->assertMailSubjectContainsAt(1, 'Bestell-Erinnerung FoodCoop Test');
        $this->assertMailContainsHtmlAt(1, 'Hallo Demo Mitglied,');
        $this->assertMailContainsHtmlAt(1, 'ist schon wieder der letzte Bestelltag');
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailCustomer'));

        $this->assertMailSubjectContainsAt(2, 'Bestell-Erinnerung FoodCoop Test');
        $this->assertMailContainsHtmlAt(2, 'Hallo Demo Superadmin,');
        $this->assertMailContainsHtmlAt(2, 'ist schon wieder der letzte Bestelltag');
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailSuperadmin'));

    }

    public function testGlobalDeliveryBreakEnabledAndNextDeliveryDay()
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', '2019-11-01');
        $this->exec('email_order_reminder 2019-10-27');
        $this->runAndAssertQueue();
        $this->assertMailCount(0);
    }

    public function testGlobalDeliveryBreakEnabledAndNotNextDeliveryDay()
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', '2019-11-08');
        $this->exec('email_order_reminder 2019-10-27');
        $this->runAndAssertQueue();
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
        $this->exec('email_order_reminder 2019-11-05');
        $this->runAndAssertQueue();
        $this->assertMailCount(2);
        $this->assertMailContainsHtmlAt(0, 'heute');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailAdmin'));
    }

    public function testIfServiceNotSubscribed()
    {
        $query = 'UPDATE '.$this->Customer->getTable().' SET email_order_reminder_enabled = 0;';
        $this->dbConnection->query($query);
        $this->exec('email_order_reminder');
        $this->runAndAssertQueue();
        $this->assertMailCount(0);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->EmailOrderReminder);
    }
}
