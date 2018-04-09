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
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator)
    {
        return $validator;
    }
    
    private function getPayments($manufacturerId, $customerId)
    {
        if (!$manufacturerId && !$customerId) {
            throw new InvalidParameterException('either manufacturerId or customerId needs to be set');
        }
        
        $query = $this->find('all');
        
        $query->where([
            'TimebasedCurrencyPayments.status' => APP_ON,
            'TimebasedCurrencyPayments.approval > ' . APP_DEL
        ]);
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
        
        return $query;
        
    }
    
    public function getUnapprovedCount($manufacturerId = null, $customerId = null)
    {
        $query = $this->getPayments($manufacturerId, $customerId);
        $query->where(
            ['TimebasedCurrencyPayments.approval' => APP_OFF]
        );
        $query->select(
            ['UnapprovedCount' => $query->func()->count('TimebasedCurrencyPayments.approval')]
        );
        $unapprovedCount = $query->toArray()[0]['UnapprovedCount'];
        if ($unapprovedCount == '') {
            $unapprovedCount = 0;
        }
        return $unapprovedCount;
    }
    
    /**
     * @param int $manufacturerId
     * @param int $customerId
     * @throws InvalidParameterException
     * @return int
     */
    public function getSum($manufacturerId = null, $customerId = null)
    {
        $query = $this->getPayments($manufacturerId, $customerId);
        $query->select(
            ['SumSeconds' => $query->func()->sum('TimebasedCurrencyPayments.seconds')]
        );
        
        $sumSeconds = $query->toArray()[0]['SumSeconds'];
        if ($sumSeconds == '') {
            $sumSeconds = 0;
        }
        return $sumSeconds;
    }

}
