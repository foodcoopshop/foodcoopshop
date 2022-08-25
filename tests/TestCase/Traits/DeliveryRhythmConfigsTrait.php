<?php

namespace App\Test\TestCase\Traits;

use Cake\Datasource\FactoryLocator;

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

    protected $Products;

    protected function prepareThursdayFridayConfig()
    {
        $this->changeReadOnlyConfiguration('FCS_WEEKLY_PICKUP_DAY', 5);
        $this->changeReadOnlyConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 1);
    }

    protected function prepareWednesdayFridayConfig()
    {
        $this->changeReadOnlyConfiguration('FCS_WEEKLY_PICKUP_DAY', 5);
        $this->changeReadOnlyConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 2);
    }

    protected function prepareTuesdayFridayConfig()
    {
        $this->changeReadOnlyConfiguration('FCS_WEEKLY_PICKUP_DAY', 5);
        $this->changeReadOnlyConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 3);
    }

    protected function prepareMondayTuesdayConfig()
    {
        $this->changeReadOnlyConfiguration('FCS_WEEKLY_PICKUP_DAY', 2);
        $this->changeReadOnlyConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 1);
    }

    protected function prepareSaturdayThursdayConfig()
    {
        $this->changeReadOnlyConfiguration('FCS_WEEKLY_PICKUP_DAY', 4);
        $this->changeReadOnlyConfiguration('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA', 5);
        $this->Products = FactoryLocator::get('Table')->get('Products');
        $query = 'UPDATE ' . $this->Products->getTable().' SET delivery_rhythm_send_order_list_weekday = 6';
        $this->dbConnection->execute($query);
    }

}
