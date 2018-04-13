<?php

namespace App\Model\Table;

use Cake\Log\Log;
use Cake\ORM\TableRegistry;
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

    /**
     * @param Validator $validator
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('seconds_sum_tmp', 'Bitte gib an, wie viel du in Stunden zahlen möchtest.');
        $validator->numeric('seconds_sum_tmp', 'Bitte trage eine Zahl ein.');
        return $validator;
    }
    
    /**
     * @param Order $order
     */
    public function updateSums($order)
    {
        $this->TimebasedCurrencyOrderDetail = TableRegistry::get('TimebasedCurrencyOrderDetails');
        
        $query = $this->TimebasedCurrencyOrderDetail->find('all');
        $query->contain(['OrderDetails']);
        $query->select(['sumMoneyIncl' => $query->func()->sum('TimebasedCurrencyOrderDetails.money_incl')]);
        $query->select(['sumMoneyExcl' => $query->func()->sum('TimebasedCurrencyOrderDetails.money_excl')]);
        $query->select(['sumSeconds' => $query->func()->sum('TimebasedCurrencyOrderDetails.seconds')]);
        
        $query->group('OrderDetails.id_order');
        $query->having(['OrderDetails.id_order' => $order->id_order]);
        
        $timebasedCurrencyOrderDetails = $query->first();
        
        // if last timebased_currency_order_detail was deleted, $timebasedCurrencyOrderDetails is empty => avoid notices
        if (empty($timebasedCurrencyOrderDetails)) {
            $sumMoneyIncl = 0;
            $sumMoneyExcl = 0;
            $sumSeconds = 0;
        } else {
            $sumMoneyIncl = $timebasedCurrencyOrderDetails['sumMoneyIncl'];
            $sumMoneyExcl = $timebasedCurrencyOrderDetails['sumMoneyExcl'];
            $sumSeconds = $timebasedCurrencyOrderDetails['sumSeconds'];
        }
        
        $timebasedCurrencyOrder2update = [
            'money_incl_sum' => $sumMoneyIncl,
            'money_excl_sum' => $sumMoneyExcl,
            'seconds_sum' => $sumSeconds
        ];
        
        $this->save(
            $this->patchEntity($order->timebased_currency_order, $timebasedCurrencyOrder2update)
        );
    }
}
