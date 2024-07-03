<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestEmailTransport;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class SendDeliveryNotesCommandTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public function tearDown(): void
    {
        parent::tearDown();
        unlink(TMP . 'Lieferschein-01.02.2018-28.02.2018-Demo-Milch-Hersteller-FoodCoop-Test.xlsx');
    }

    public function testSendDeliveryNotes()
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);
        $cronjobRunDay = '2018-03-01';
        $this->exec('send_delivery_notes ' . $cronjobRunDay);
        $this->runAndAssertQueue();
        $this->assertMailCount(1);
        $this->assertEquals(1, count(TestEmailTransport::getMessages()[0]->getAttachments()));
    }

}
