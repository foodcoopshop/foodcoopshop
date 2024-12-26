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

    protected $EmailOrderReminder;

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public function testNoActiveOrder()
    {
        $this->exec('email_order_reminder 2024-02-12');
        $this->runAndAssertQueue();

        $this->assertMailCount(3);

        $this->assertMailSubjectContainsAt(0, 'Bestell-Erinnerung FoodCoop Test');
        $this->assertMailContainsHtmlAt(0, 'Hallo Demo Admin,');
        $this->assertMailContainsHtmlAt(0, 'es sind schon wieder die letzten Bestelltage und es kann bis <b>morgen Mitternacht</b> bestellt werden.');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailAdmin'));

        $this->assertMailSubjectContainsAt(1, 'Bestell-Erinnerung FoodCoop Test');
        $this->assertMailContainsHtmlAt(1, 'Hallo Demo Mitglied,');
        $this->assertMailContainsHtmlAt(1, 'es sind schon wieder die letzten Bestelltage und es kann bis <b>morgen Mitternacht</b> bestellt werden.');
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailCustomer'));

        $this->assertMailSubjectContainsAt(2, 'Bestell-Erinnerung FoodCoop Test');
        $this->assertMailContainsHtmlAt(2, 'Hallo Demo Superadmin,');
        $this->assertMailContainsHtmlAt(2, 'es sind schon wieder die letzten Bestelltage und es kann bis <b>morgen Mitternacht</b> bestellt werden.');
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailSuperadmin'));

    }

    public function testNoActiveOrderServiceCalledSameDayAsLastOrderDay()
    {
        $this->exec('email_order_reminder 2024-02-13'); // called on a tuesday
        $this->runAndAssertQueue();
        $this->assertMailContainsHtmlAt(0, 'heute ist der letzte Bestelltag und es kann bis <b>heute Mitternacht</b> bestellt werden.');
    }

    public function testNoActiveOrderServiceCalledOneDayBeforeLastOrderDay()
    {
        $this->exec('email_order_reminder 2024-02-12'); // called on a monday
        $this->runAndAssertQueue();
        $this->assertMailContainsHtmlAt(0, 'es sind schon wieder die letzten Bestelltage und es kann bis <b>morgen Mitternacht</b> bestellt werden.');
    }

    public function testNoActiveOrderServiceCalledTwoDaysBeforeLastOrderDay()
    {
        $this->exec('email_order_reminder 2024-02-11'); // called on a sunday
        $this->runAndAssertQueue();
        $this->assertMailContainsHtmlAt(0, 'es sind schon wieder die letzten Bestelltage und es kann bis <b>Dienstag Mitternacht</b> bestellt werden.');
    }

    public function testDeliveryBreakGlobalEnabledAndNextDeliveryDay()
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', '2019-11-01');
        $this->exec('email_order_reminder 2019-10-27');
        $this->runAndAssertQueue();
        $this->assertMailCount(0);
    }

    public function testDeliveryBreakGlobalEnabledAndNotNextDeliveryDay()
    {
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', '2019-11-08');
        $this->exec('email_order_reminder 2019-10-27');
        $this->runAndAssertQueue();
        $this->assertMailCount(3);
    }

    public function testActiveOrderOrderedSameDay()
    {
        $pickupDay = '2019-11-08';
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');

        $orderDetailsTable->updateAll(['created' => '2019-11-08 00:00:00',], []);

        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get(1),
                [
                    'pickup_day' => $pickupDay,
                ]
            )
        );
        $this->exec('email_order_reminder 2019-11-05');
        $this->runAndAssertQueue();
        $this->assertMailCount(2);
        $this->assertMailContainsHtmlAt(0, 'heute');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailAdmin'));
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailCustomer'));
    }

    public function testActiveOrderOrderedEarly()
    {
        $pickupDay = '2019-11-08';
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->updateAll(['created' => '2019-11-08 00:00:00',], []);

        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get(1),
                [
                    'pickup_day' => $pickupDay,
                    'created' => '2019-11-01 00:00:00',
                ]
            )
        );
        $this->exec('email_order_reminder 2019-11-05');
        $this->runAndAssertQueue();
        $this->assertMailCount(3);
        $this->assertMailContainsHtmlAt(0, 'heute');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailAdmin'));
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailCustomer'));
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailSuperadmin'));
    }

    public function testIfServiceNotSubscribed()
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $customersTable->updateAll(['email_order_reminder_enabled' => 0], []);
        $this->exec('email_order_reminder');
        $this->runAndAssertQueue();
        $this->assertMailCount(0);
    }

    public function testApplyOpenOrderCheckForOrderReminderFalse()
    {
        Configure::write('app.applyOpenOrderCheckForOrderReminder', false);
        $pickupDay = '2019-11-08';
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');

        $orderDetailsTable->updateAll(['created' => '2019-11-08 00:00:00',], []);

        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get(1),
                [
                    'pickup_day' => $pickupDay,
                ]
            )
        );
        $this->exec('email_order_reminder 2019-11-05');
        $this->runAndAssertQueue();
        $this->assertMailCount(3);
        $this->assertMailContainsHtmlAt(0, 'heute');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailAdmin'));
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailCustomer'));
        $this->assertMailSentToAt(2, Configure::read('test.loginEmailSuperadmin'));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->EmailOrderReminder);
    }
}
