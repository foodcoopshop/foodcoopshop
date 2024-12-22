<?php
declare(strict_types=1);

namespace App\Model\Traits;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait NoDeliveryDaysOrdersExistTrait
{

    public function noDeliveryDaysOrdersExist($value, $context) {

        $manufacturerId = null;
        if (!empty($context['data']) && !empty($context['data']['id_manufacturer'])) {
            $manufacturerId = $context['data']['id_manufacturer'];
        }

        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');

        if (!is_null($manufacturerId)) {
            $productsAssociation = $orderDetailsTable->getAssociation('Products');
            $productsAssociation->setJoinType('INNER'); // necessary to apply condition
            $productsAssociation->setConditions([
                'Products.id_manufacturer' => $manufacturerId
            ]);
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        $query = $orderDetailsTable->find('all',
            conditions: [
                'pickup_day IN' => $value
            ],
            group: 'pickup_day',
            contain: [
                'Products'
            ]
        );
        $query->select(
            [
                'PickupDayCount' => $query->func()->count('OrderDetails.pickup_day'),
                'pickup_day'
            ]
        );

        $result = true;
        if (!empty($query->toArray())) {
            $pickupDaysInfo = [];
            foreach($query->toArray() as $orderDetail) {
                $formattedPickupDay = $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
                $pickupDaysInfo[] = $formattedPickupDay . ' (' . $orderDetail->PickupDayCount . 'x)';
            }
            $result = __('The_following_delivery_day(s)_already_contain_orders:_{0}._To_save_the_delivery_break_either_cancel_them_or_change_the_pickup_day.', [join(', ', $pickupDaysInfo)]);
        }

        return $result;
    }

}