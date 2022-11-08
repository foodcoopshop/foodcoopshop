<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;

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

class PickupReminderShellTest extends AppCakeTestCase
{

    protected $OrderDetail;

    use EmailTrait;

    public function testCustomersDoNotHaveFutureOrders()
    {
        $this->exec('pickup_reminder');
        $this->assertMailCount(0);
    }

    public function testOneCustomerHasFutureOrdersLaterThanNextPickupDay()
    {
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->prepareOrderDetails();
        $this->exec('pickup_reminder 2018-03-10');
        $this->assertMailCount(0);
    }

    public function testOneCustomerHasFutureOrdersForNextPickupDay()
    {
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->prepareOrderDetails();
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get(2),
                [
                    'created' => FrozenTime::create(2018,3,9,0,0,0),
                    'pickup_day' => FrozenDate::create(2018,3,16)
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
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->prepareOrderDetails();
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get(2),
                [
                    'created' => FrozenTime::create(2018,3,9,0,0,0),
                    'pickup_day' => FrozenDate::create(2018,3,16)
                ]
            )
        );
        $this->exec('pickup_reminder 2018-03-10');
        $this->runAndAssertQueue();
        $this->assertMailCount(0);
    }


    private function prepareOrderDetails()
    {
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get(1),
                [
                    'created' => FrozenTime::create(2018,3,9,0,0,0),
                    'pickup_day' => FrozenDate::create(2018,3,28)
                ]
            )
        );
    }

}
