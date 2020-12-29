<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;

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
class Product extends Entity
{

    protected $_virtual = ['next_delivery_day'];

    protected $next_delivery_day;

    protected function _getNextDeliveryDay()
    {
        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $pickupDay = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
        } else {
            $productTable = FactoryLocator::get('Table')->get('Products');
            $pickupDay = $productTable->calculatePickupDayRespectingDeliveryRhythm($this);
        }
        return $pickupDay;
    }

}
