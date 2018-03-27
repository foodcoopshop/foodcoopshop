<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TimebasedCurrencyOrdersTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->hasOne('Orders', [
            'foreignKey' => 'id_order'
        ]);
        $this->setPrimaryKey('id_order');
    }

    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('time_sum_tmp', 'Bitte gib an, wie viel du in Stunden zahlen möchtest.');
        $validator->numeric('time_sum_tmp', 'Bitte trage eine Zahl ein.');
        return $validator;
    }
    
    /**
     * @param int $customerId
     * @return float
     */
    public function getSum($customerId)
    {
        $conditions = [
            'Orders.id_customer' => $customerId,
        ];
        $conditions[] = $this->Orders->getOrderStateCondition(Configure::read('app.htmlHelper')->getOrderStateIds());
        
        $query = $this->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Orders'
            ]
        ]);
        $query->select(
            ['SumTime' => $query->func()->sum('TimebasedCurrencyOrders.time_sum')]
        );
        return $query->toArray()[0]['SumTime'];
    }

}
