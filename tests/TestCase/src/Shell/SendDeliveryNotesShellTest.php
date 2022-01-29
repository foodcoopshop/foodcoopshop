<?php

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Console\CommandRunner;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestEmailTransport;
use App\Test\TestCase\Traits\QueueTrait;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class SendDeliveryNotesShellTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;
    use QueueTrait;

    public $commandRunner;

    public function setUp(): void
    {
        parent::setUp();
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testSendDeliveryNotes()
    {

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);

        $cronjobRunDay = '2018-03-01';
        $this->commandRunner->run(['cake', 'send_delivery_notes', $cronjobRunDay]);
        $this->runAndAssertQueue();

        $this->assertMailCount(1);
        $this->assertEquals(1, count(TestEmailTransport::getMessages()[0]->getAttachments()));

    }

}
