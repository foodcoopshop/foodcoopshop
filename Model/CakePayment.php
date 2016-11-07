<?php
/**
 * CakePayment
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CakePayment extends AppModel
{

    public $belongsTo = array(
        'Customer' => array(
            'foreignKey' => 'id_customer'
        )
    );
    
    /**
     * @param int $manufacturerId
     * @return array
     */
    public function getMonthlyDepositSumByManufacturer($manufacturerId) {
        
        $conditions = array(
            'CakePayment.status' => APP_ON,
            'CakePayment.id_customer' => 0
        );
        $conditions['CakePayment.id_manufacturer'] = $manufacturerId;
        $conditions['CakePayment.type'] = 'deposit';
        
        $fields = array(
            'SUM(amount) as sumDepositReturned',
            'DATE_FORMAT(CakePayment.date_add, \'%Y-%c\') as month'
        );
        $paymentSum = $this->find('all', array(
            'fields' => $fields,
            'conditions' => $conditions,
            'order' => array('CakePayment.date_add' => 'DESC'),
            'group' => 'month'
        ));
        
        return $paymentSum;
    }

    /**
     * @param int $customerId
     * @param string $type
     * @return float
     */
    public function getSum($customerId, $type)
    {
        $conditions = array(
            'CakePayment.id_customer' => $customerId,
            'CakePayment.id_manufacturer' => 0,
            'CakePayment.status' => APP_ON
        );
    
        $conditions['CakePayment.type'] = $type;
    
        $paymentSum = $this->find('all', array(
            'fields' => array(
                'SUM(amount) as SumAmount'
            ),
            'conditions' => $conditions
        ));
    
        return $paymentSum[0][0]['SumAmount'];
    }
    
}

?>