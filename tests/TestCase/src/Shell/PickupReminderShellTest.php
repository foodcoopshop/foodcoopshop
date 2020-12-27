<?php

use App\Test\TestCase\AppCakeTestCase;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Core\Configure;
use App\Application;
use Cake\Console\CommandRunner;
use Cake\TestSuite\EmailTrait;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class PickupReminderShellTest extends AppCakeTestCase
{
    use EmailTrait;

    public $commandRunner;

    public function setUp(): void
    {
        parent::setUp();
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testCustomersDoNotHaveFutureOrders()
    {
        $this->commandRunner->run(['cake', 'pickup_reminder']);
        $this->assertMailCount(0);
    }

    public function testOneCustomerHasFutureOrdersLaterThanNextPickupDay()
    {
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->prepareOrderDetails();
        $this->commandRunner->run(['cake', 'pickup_reminder', '2018-03-10']);
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
        $this->commandRunner->run(['cake', 'pickup_reminder', '2018-03-10']);

        $this->assertMailCount(1);
        $this->assertMailSubjectContainsAt(0, 'Abhol-Erinnerung für Freitag, 16.03.2018');
        $this->assertMailContainsHtmlAt(0, '<li>1x Beuschl, Demo Fleisch-Hersteller</li>');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));

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
