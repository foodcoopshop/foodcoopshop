<?php

namespace App\Model\Table;

use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PickupDaysTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id'
        ]);
        $this->setPrimaryKey(['customer_id']);
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator->allowEmpty('comment');
        $validator->maxLength('comment', 500, __('Please_enter_max_{0}_characters.', [500]));
        return $validator;
    }
    
    public function getUniquePickupDays($cartProducts)
    {
        $uniquePickupDays = [];
        foreach($cartProducts as $cartProduct) {
            $uniquePickupDays[] = $cartProduct->pickup_day;
        }
        return array_unique($uniquePickupDays);
    }
    
    /**
     * @param array $conditions
     * @param array $data
     * result $success
     */
    public function insertOrUpdate($conditions, $data)
    {
        $this->setPrimaryKey(['customer_id', 'pickup_day']);
        
        $pickupDayEntity = $this->find('all', [
            'conditions' => [
                $conditions
            ]
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
