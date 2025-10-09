<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use App\View\Helper\MyTimeHelper;
use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class ChangeWeeklyPickupDayByOneDayCommandTest extends AppCakeTestCase
{

    public function testDecrease(): void
    {
        $this->exec('change_weekly_pickup_day_by_one_day decrease');
        $productsTable = $this->getTableLocator()->get('Products');
        $products = $productsTable->find()->where([
            'Products.delivery_rhythm_send_order_list_weekday' => MyTimeHelper::TUESDAY,
        ]);
        $this->assertCount(14, $products);
        $this->assertEquals(MyTimeHelper::THURSDAY, (int) Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY'));
    }

    public function testIncrease(): void
    {
        $this->exec('change_weekly_pickup_day_by_one_day increase');
        $productsTable = $this->getTableLocator()->get('Products');
        $products = $productsTable->find()->where([
            'Products.delivery_rhythm_send_order_list_weekday' => MyTimeHelper::THURSDAY,
        ]);
        $this->assertCount(14, $products);
        $this->assertEquals(MyTimeHelper::SATURDAY, (int) Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY'));
    }

}
