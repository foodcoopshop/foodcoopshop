<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use App\View\Helper\MyTimeHelper;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait DeliveryRhythmConfigsTrait
{

    protected function prepareThursdayFridayConfig(): void
    {
        $this->changeConfiguration('FCS_WEEKLY_PICKUP_DAY', MyTimeHelper::FRIDAY);
        $this->changeConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 1);
    }

    protected function prepareWednesdayFridayConfig(): void
    {
        $this->changeConfiguration('FCS_WEEKLY_PICKUP_DAY', MyTimeHelper::FRIDAY);
        $this->changeConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 2);
    }

    protected function prepareTuesdayFridayConfig(): void
    {
        $this->changeConfiguration('FCS_WEEKLY_PICKUP_DAY', MyTimeHelper::FRIDAY);
        $this->changeConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 3);
    }

    protected function prepareMondayTuesdayConfig(): void
    {
        $this->changeConfiguration('FCS_WEEKLY_PICKUP_DAY', MyTimeHelper::TUESDAY);
        $this->changeConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 1);
    }

    protected function prepareSaturdayThursdayConfig(): void
    {
        $this->changeConfiguration('FCS_WEEKLY_PICKUP_DAY', MyTimeHelper::THURSDAY);
        $this->changeConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 5);
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->updateAll(['delivery_rhythm_send_order_list_weekday' => MyTimeHelper::SATURDAY], []);
    }

    protected function prepareSaturdayWednesdayConfig(): void
    {
        $this->changeConfiguration('FCS_WEEKLY_PICKUP_DAY', MyTimeHelper::WEDNESDAY);
        $this->changeConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 4);
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->updateAll(['delivery_rhythm_send_order_list_weekday' => MyTimeHelper::SATURDAY], []);
    }

}
