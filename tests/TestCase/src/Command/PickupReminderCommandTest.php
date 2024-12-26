<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\I18n\DateTime;
use Cake\I18n\Date;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class PickupReminderCommandTest extends AppCakeTestCase
{

    use EmailTrait;

    public function testCustomersDoNotHaveFutureOrders()
    {
        $this->exec('pickup_reminder');
        $this->assertMailCount(0);
    }

    public function testOneCustomerHasFutureOrdersLaterThanNextPickupDay()
    {
        $this->prepareOrderDetails();
        $this->exec('pickup_reminder 2018-03-10');
        $this->assertMailCount(0);
    }

    public function testOneCustomerHasFutureOrdersForNextPickupDay()
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $this->prepareOrderDetails();
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get(2),
                [
                    'created' => DateTime::create(2018,3,9,0,0,0),
                    'pickup_day' => Date::create(2018,3,16)
                ]
            )
        );
        $this->exec('pickup_reminder 2018-03-10');
        $this->runAndAssertQueue();

        $this->assertMailCount(1);
        $this->assertMailSubjectContainsAt(0, 'Abhol-Erinnerung für Freitag, 16.03.2018');
        $this->assertMailContainsHtmlAt(0, '<li>1x Beuschl, Demo Fleisch-Hersteller</li>');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));

    }

    public function testOneCustomerHasFutureOrdersForNextPickupDayNotificationDisabled()
    {
        $this->changeCustomer(Configure::read('test.superadminId'), 'pickup_day_reminder_enabled', 0);
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $this->prepareOrderDetails();
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get(2),
                [
                    'created' => DateTime::create(2018,3,9,0,0,0),
                    'pickup_day' => Date::create(2018,3,16)
                ]
            )
        );
        $this->exec('pickup_reminder 2018-03-10');
        $this->runAndAssertQueue();
        $this->assertMailCount(0);
    }


    private function prepareOrderDetails()
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get(1),
                [
                    'created' => DateTime::create(2018,3,9,0,0,0),
                    'pickup_day' => Date::create(2018,3,28)
                ]
            )
        );
    }

}
