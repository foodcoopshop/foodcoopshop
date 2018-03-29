<?php

namespace App\Model\Table;

use Cake\Validation\Validator;
use App\Lib\Error\Exception\InvalidParameterException;

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
class TimebasedCurrencyPaymentsTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'id_manufacturer'
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator)
    {
        return $validator;
    }
    
    /**
     * @param int $customerId
     * @param int $manufacturerId
     * @throws InvalidParameterException
     * @return float
     */
    public function getSum($manufacturerId = null, $customerId = null)
    {
        if (!$manufacturerId && !$customerId) {
            throw new InvalidParameterException('either manufactureId or customerId needs to be set');
        }
        
        $query = $this->find('all');
        
        $query->select(
            ['SumTime' => $query->func()->sum('TimebasedCurrencyPayments.time')]
        );
        $query->where(
            ['TimebasedCurrencyPayments.status' => APP_ON]
        );
        if ($customerId) {
            $query->where(
                ['TimebasedCurrencyPayments.id_customer' => $customerId]
            );
        }
        if ($manufacturerId) {
            $query->where(
                ['TimebasedCurrencyPayments.id_manufacturer' => $manufacturerId]
            );
        }
        $sumTime = $query->toArray()[0]['SumTime'];
        if ($sumTime == '') {
            $sumTime = 0;
        }
        
        return $sumTime;
    }

}
