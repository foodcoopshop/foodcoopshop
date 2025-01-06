<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;
use Cake\Datasource\EntityInterface;

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
class PickupDaysTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id'
        ]);
        $this->setPrimaryKey(['customer_id']);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->allowEmptyString('comment');
        $validator->maxLength('comment', 500, __('Please_enter_max_{0}_characters.', [500]));
        return $validator;
    }

    public function changeState($customerId, $pickupDay, $state): EntityInterface|false
    {
        $result = $this->insertOrUpdate(
            [
                'customer_id' => $customerId,
                'pickup_day' => $pickupDay,
            ],
            [
                'products_picked_up' => $state,
            ],
        );
        return $result;
    }

    public function getUniquePickupDays($cartProducts): array
    {
        $uniquePickupDays = [];
        foreach($cartProducts as $cartProduct) {
            if ($cartProduct->pickup_day != 'delivery-rhythm-triggered-delivery-break') {
                $uniquePickupDays[] = $cartProduct->pickup_day;
            }
        }
        return array_unique($uniquePickupDays);
    }

    public function insertOrUpdate($conditions, $data): EntityInterface|false
    {
        $this->setPrimaryKey(['customer_id', 'pickup_day']);

        $pickupDayEntity = $this->find('all', conditions: [
            $conditions
        ])->first();

        if (empty($pickupDayEntity)) {
            $pickupDayEntity = $this->newEntity($conditions);
        }

        $patchedEntity = $this->patchEntity(
            $pickupDayEntity,
            $data
        );

        $result = $this->save($patchedEntity);
        return $result;

    }

}
